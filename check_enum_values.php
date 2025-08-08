<?php
// check_enum_values.php - Script untuk cek enum values
require_once 'db.php';

echo "🔍 Checking enum values for user roles...\n\n";

try {
    // Cek enum values untuk role
    echo "📋 Checking user_role enum values:\n";
    $sql = "SELECT unnest(enum_range(NULL::user_role)) as role_value";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Valid role values:\n";
    foreach ($roles as $role) {
        echo "  - {$role['role_value']}\n";
    }
    
    // Cek enum values untuk tier
    echo "\n📋 Checking user_tier enum values:\n";
    $sql = "SELECT unnest(enum_range(NULL::user_tier)) as tier_value";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Valid tier values:\n";
    foreach ($tiers as $tier) {
        echo "  - {$tier['tier_value']}\n";
    }
    
    // Cek user yang sudah ada
    echo "\n👥 Current users with their roles:\n";
    $sql = "SELECT id, email, display_name, role, tier FROM users ORDER BY id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "  - ID: {$user['id']} | Email: {$user['email']} | Role: {$user['role']} | Tier: {$user['tier']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 
