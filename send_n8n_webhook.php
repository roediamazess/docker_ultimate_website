<?php
// Fungsi untuk mengirim notifikasi ke n8n webhook (untuk diteruskan ke WhatsApp/Telegram)
function send_n8n_webhook($event, $data = []) {
    $webhook_url = 'https://n8n.yourdomain.com/webhook/notify'; // Ganti dengan URL webhook n8n Anda
    $payload = [
        'event' => $event,
        'data' => $data
    ];
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
