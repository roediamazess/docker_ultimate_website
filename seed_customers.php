<?php
require_once 'db.php';

$customers = [
    [
        'customer_id' => 'CUST001',
        'name' => 'Grand Hyatt Hotel',
        'star' => 5,
        'room' => '500',
        'outlet' => '5',
        'type' => 'Hotel',
        'group' => 'Hyatt Group',
        'zone' => 'Central',
        'address' => '123 Main Street, Anytown',
        'billing' => 'Contract Maintenance',
        'status' => 'Active',
        'email_gm' => 'gm.grandhyatt@example.com',
        'email_executive' => 'exec.grandhyatt@example.com',
        'email_hr' => 'hr.grandhyatt@example.com',
        'email_acc_head' => 'acchead.grandhyatt@example.com',
        'email_chief_acc' => 'chiefacc.grandhyatt@example.com',
        'email_cost_control' => 'cost.grandhyatt@example.com',
        'email_ap' => 'ap.grandhyatt@example.com',
        'email_ar' => 'ar.grandhyatt@example.com',
        'email_fb' => 'fb.grandhyatt@example.com',
        'email_fo' => 'fo.grandhyatt@example.com',
        'email_hk' => 'hk.grandhyatt@example.com',
        'email_engineering' => 'eng.grandhyatt@example.com',
    ],
    [
        'customer_id' => 'CUST002',
        'name' => 'Burger Queen Restaurant',
        'star' => null,
        'room' => null,
        'outlet' => '1',
        'type' => 'Restaurant',
        'group' => 'Fast Food Inc.',
        'zone' => 'North',
        'address' => '456 Oak Avenue, Anytown',
        'billing' => 'Subscription',
        'status' => 'Active',
        'email_gm' => 'manager.burgerqueen@example.com',
        'email_executive' => null,
        'email_hr' => 'hr.burgerqueen@example.com',
        'email_acc_head' => 'accounting.burgerqueen@example.com',
        'email_chief_acc' => null,
        'email_cost_control' => null,
        'email_ap' => 'ap.burgerqueen@example.com',
        'email_ar' => 'ar.burgerqueen@example.com',
        'email_fb' => 'fb.burgerqueen@example.com',
        'email_fo' => null,
        'email_hk' => null,
        'email_engineering' => null,
    ],
    [
        'customer_id' => 'CUST003',
        'name' => 'Anytown University',
        'star' => null,
        'room' => '1000',
        'outlet' => '10',
        'type' => 'Education',
        'group' => 'State Universities',
        'zone' => 'West',
        'address' => '789 University Drive, Anytown',
        'billing' => 'Contract Maintenance',
        'status' => 'Active',
        'email_gm' => 'dean.anytownuni@example.com',
        'email_executive' => 'provost.anytownuni@example.com',
        'email_hr' => 'hr.anytownuni@example.com',
        'email_acc_head' => 'bursar.anytownuni@example.com',
        'email_chief_acc' => 'chiefacc.anytownuni@example.com',
        'email_cost_control' => null,
        'email_ap' => 'ap.anytownuni@example.com',
        'email_ar' => 'ar.anytownuni@example.com',
        'email_fb' => 'dining.anytownuni@example.com',
        'email_fo' => null,
        'email_hk' => 'facilities.anytownuni@example.com',
        'email_engineering' => 'eng.anytownuni@example.com',
    ]
];

try {
    $stmt = $pdo->prepare(
        'INSERT INTO customers (customer_id, name, star, room, outlet, type, "group", zone, address, billing, status, email_gm, email_executive, email_hr, email_acc_head, email_chief_acc, email_cost_control, email_ap, email_ar, email_fb, email_fo, email_hk, email_engineering, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );

    foreach ($customers as $customer) {
        $stmt->execute(array_values($customer));
    }

    echo "Successfully inserted 3 sample customers.";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>