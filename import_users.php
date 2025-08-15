<?php
// One-off script to import users from a TSV list into the `users` table.
// Usage: open this file in a browser or run via CLI: php import_users.php

session_start();
require_once __DIR__ . '/db.php';

function getUsersColumns(PDO $pdo): array
{
    $cols = [];
    try {
        $stmt = $pdo->query("SELECT column_name, is_nullable, data_type FROM information_schema.columns WHERE table_name = 'users'");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cols[$r['column_name']] = [
                'nullable' => (strtoupper((string)$r['is_nullable']) !== 'NO')
            ];
        }
    } catch (Throwable $e) {}
    return $cols;
}

function parseDateToYmd(?string $value): ?string
{
	$value = trim((string)$value);
	if ($value === '') { return null; }
	// Expecting dd/mm/yyyy
	$parts = preg_split('#[\\/\-]#', $value);
	if (count($parts) === 3) {
		// If the year part is the last, assume d/m/Y
		if (strlen($parts[2]) === 2) { $parts[2] = '20' . $parts[2]; }
		return sprintf('%04d-%02d-%02d', (int)$parts[2], (int)$parts[1], (int)$parts[0]);
	}
	// Fallback: try strtotime
	$ts = strtotime($value);
	return $ts ? date('Y-m-d', $ts) : null;
}

function upsertUsers(PDO $pdo, array $rows): array
{
    $inserted = 0; $updated = 0; $skipped = 0; $errors = [];

    $cols = getUsersColumns($pdo);
    $hasUserId = array_key_exists('user_id', $cols);
    $hasDisplay = array_key_exists('display_name', $cols);
    $keyCol = $hasUserId ? 'user_id' : ($hasDisplay ? 'display_name' : null);
    if ($keyCol === null) {
        return ['inserted'=>0,'updated'=>0,'skipped'=>count($rows),'errors'=>['users table has no user_id/display_name column']];
    }
    $roleCol = array_key_exists('user_role', $cols) ? 'user_role' : (array_key_exists('role', $cols) ? 'role' : null);
    $needsPassword = array_key_exists('password', $cols) && ($cols['password']['nullable'] === false);
    $defaultPassword = $needsPassword ? password_hash('Default#123', PASSWORD_BCRYPT) : null;

    foreach ($rows as $row) {
        $userId = trim($row['display_name'] ?? '');
        $fullName = trim($row['full_name'] ?? '');
        $email = trim($row['email'] ?? '');
        $tier = trim($row['tier'] ?? '');
        $role = trim($row['user_role'] ?? '');
        $start = parseDateToYmd($row['start_work'] ?? null);

        if ($userId === '' || $fullName === '') { $skipped++; continue; }

        try {
            // Check exists
            $chk = $pdo->prepare("SELECT 1 FROM users WHERE $keyCol = ? LIMIT 1");
            $chk->execute([$userId]);
            $exists = (bool)$chk->fetchColumn();

            $setCols = [];
            $params = [];
            $setCols[] = 'full_name = ?'; $params[] = $fullName;
            if (array_key_exists('email', $cols)) { $setCols[] = 'email = ?'; $params[] = $email; }
            if (array_key_exists('tier', $cols)) { $setCols[] = 'tier = ?'; $params[] = $tier; }
            if ($roleCol) { $setCols[] = "$roleCol = ?"; $params[] = ($role === '' ? 'User' : $role); }
            if (array_key_exists('start_work', $cols)) { $setCols[] = 'start_work = ?'; $params[] = $start; }
            if ($needsPassword) { $setCols[] = 'password = ?'; $params[] = $defaultPassword; }

            if ($exists) {
                $sql = 'UPDATE users SET ' . implode(', ', $setCols) . " WHERE $keyCol = ?";
                $params[] = $userId;
                $upd = $pdo->prepare($sql);
                $upd->execute($params);
                $updated++;
            } else {
                // Build insert
                $insCols = [$keyCol, 'full_name'];
                $insVals = [$userId, $fullName];
                if (array_key_exists('email', $cols)) { $insCols[]='email'; $insVals[]=$email; }
                if (array_key_exists('tier', $cols)) { $insCols[]='tier'; $insVals[]=$tier; }
                if ($roleCol) { $insCols[]=$roleCol; $insVals[]=($role === '' ? 'User' : $role); }
                if (array_key_exists('start_work', $cols)) { $insCols[]='start_work'; $insVals[]=$start; }
                if ($needsPassword) { $insCols[]='password'; $insVals[]=$defaultPassword; }

                $placeholders = rtrim(str_repeat('?,', count($insCols)), ',');
                $sql = 'INSERT INTO users (' . implode(',', $insCols) . ') VALUES (' . $placeholders . ')';
                $ins = $pdo->prepare($sql);
                $ins->execute($insVals);
                $inserted++;
            }
        } catch (Throwable $e) {
            $errors[] = $userId . ' → ' . $e->getMessage();
        }
    }

    return compact('inserted','updated','skipped','errors');
}

// Raw TSV pasted below
$tsv = <<<'TSV'
display_name	full_name	email	tier	user_role	start_work
Akbar	Fajar Achmad Akbar	akbar@powerpro.co.id	Tier 3	User	09/03/2017
Aldi	Rifaldi Hidayat	aldi@powerpro.co.id	Tier 2	User	13/08/2018
Andreas	Andreas Daniel Gunadi	andreas@powerpro.co.id	Tier 1	User	31/01/2023
Apip	Khairul Afip	afip@powerpro.co.id	Tier 3	User	20/01/2015
Apri	Muji Apriyanto	muji@powerpro.co.id	Tier 3	User	13/01/2014
Arbi	Arbiyanto Catur Wibisono	arbi@powerpro.co.id	New Born	User	
Aris	Charisma Prima Wijaya	aris@powerpro.co.id	Tier 2	User	02/06/2016
Basir	Abdul Basir	basir@powerpro.co.id	Tier 3	User	17/12/2012
Bowo	Ade Septiyan Nugroho	bowo@powerpro.co.id	Tier 3	User	22/03/2011
Danang	Danang Bagas Taranggono	danang.bagas@powerpro.co.id	Tier 3	User	03/05/2017
Dhani	Ahmad Adhani Nurrokhim	dhani@powerpro.co.id	New Born	User	
Dhika	Andhika Hastungkoro	dhika@powerpro.co.id	New Born	User	
Fachri	Fachri Huseini Muhammad	fachri@powerpro.co.id	Tier 3	User	26/01/2017
Farhan	Farhan Saputra	farhan@powerpro.co.id	Tier 1	User	24/10/2022
Hanip	Hanipul Haqiqi	hanip@powerpro.co.id	New Born	User	
Hasbi	M. Hasbiyanur	hasbi@powerpro.co.id	Tier 3	User	20/06/2013
Ichsan	Muhammad Ichsan	ichsan@powerpro.co.id	Tier 3	User	25/10/2010
Ichwan	Ichwan Noor Rachim	ichwan@powerpro.co.id	Tier 3	User	22/07/2011
Ilham	Ilham Adi Pramono	ilham@powerpro.co.id	Tier 3	User	08/01/2018
Imam	Imam Abdul Rakhman	imam@powerpro.id	Tier 3	User	17/06/2011
Indra	Indra Setiawan	indra@powerpro.co.id	Tier 3	User	26/07/2016
Iqhtiar	Iqhtiar Aji Pangestu	iqhtiar@powerpro.co.id	Tier 1	User	24/10/2022
Jaja	Jaja Suharja	jaja@powerpro.co.id	Tier 3	User	26/02/2018
Komeng	Rudianto	pms@powerpro.id	Tier 3	Administrator	05/04/2010
Lifi	Ahlifi Nizali Aziz	lifi@powerpro.co.id	Tier 1	User	24/10/2022
Mamat	Rahmad Zaelani	rahmad.zaelani@powerpro.co.id	Tier 3	User	10/12/2014
Mulya	Mulya Darmaji	mulya@powerpro.co.id	Tier 3	User	17/10/2011
Naufal	Raihan Alnaufal	naufal@powerpro.co.id	Tier 1	User	31/01/2023
Nur	Fahmi Nur Ikram	nur@powerpro.co.id	Tier 3	User	24/10/2018
Prad	Pradana Asih Widiyanto	pradana@powerpro.co.id	Tier 1	User	31/01/2023
Rafly	Rafly Fauzy	rafly@powerpro.co.id	Tier 1	User	31/01/2023
Rama	Rama Aditya	rama@powerpro.co.id	Tier 3	User	29/08/2016
Rey	Raihan Zakaria Effendy	rey@powerpro.co.id	New Born	User	
Ridho	Rafli Al Faridho	ridho@powerpro.co.id	Tier 1	User	31/01/2023
Ridwan	M. Ridwan	ridwan@powerpro.co.id	Tier 3	User	23/08/2012
Rizky	Muhamad Rizky	rizky@powerpro.co.id	Tier 1	User	31/01/2023
Robi	Robi Kurniawan	robi@powerpro.co.id	Tier 3	User	26/12/2016
Sahrul	Sahrul Ahmad Safi'i	sahrul@powerpro.co.id	Tier 3	User	10/02/2014
Sodik	Sodik Azhari	sodek@powerpro.co.id	Tier 3	User	29/01/2014
Vincent	Arya Vincent	vincent@powerpro.co.id	Tier 3	User	15/06/2016
Wahyudi	Ilham Tri Wahyudi	ilham.tri@powerpro.co.id	Tier 1	User	24/10/2022
Widi	Bayu Widiyanto	widi@powerpro.co.id	Tier 3	User	09/03/2017
Yosa	Yosa Kristian	yosa@powerpro.co.id	Tier 3	User	17/12/2012
Yudi	Muhammad Wahyudi	wahyudi@powerpro.co.id	Tier 3	User	26/07/2016
Ivan	Irvan Verdiansyah	irvan@powerpro.id	Tier 3	User	26/03/2008
Tri	Triono	account.executive@powerpro.id	Tier 3	Admin Officer	
Iam	M. Ilham Rizki	iam@powerpro.co.id	New Born	Admin Officer	
TSV;

function tsvToRows(string $tsv): array
{
	$lines = preg_split('/\r?\n/', trim($tsv));
	if (!$lines) { return []; }
	$header = array_map('trim', explode("\t", array_shift($lines)));
	$rows = [];
	foreach ($lines as $line) {
		if (trim($line) === '') { continue; }
		$cols = explode("\t", $line);
		$row = [];
		foreach ($header as $i => $key) { $row[$key] = $cols[$i] ?? ''; }
		$rows[] = $row;
	}
	return $rows;
}

$cols = getUsersColumns($pdo);
$rows = tsvToRows($tsv);
$result = upsertUsers($pdo, $rows);

$summary = "Users import finished\n";
$summary .= "Inserted: {$result['inserted']}\n";
$summary .= "Updated: {$result['updated']}\n";
$summary .= "Skipped: {$result['skipped']}\n";
if (!empty($result['errors'])) {
	$summary .= "Errors (" . count($result['errors']) . "):\n- " . implode("\n- ", $result['errors']);
}

// Echo for web context
@header('Content-Type: text/plain');
echo $summary;
// Persist to file so CLI callers under restricted shells still get results
@file_put_contents(__DIR__ . '/import_users.log', $summary);


