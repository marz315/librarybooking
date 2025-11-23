<?php
session_start();

// CONFIG UNTUK INFINITYFREE
$host = 'sql113.infinityfree.com';  // atau sqlXXX.epizy.com
$dbname = 'if0_40486614_booking_library';  // akan dapat nanti
$username = 'if0_40486614';  // akan dapat nanti  
$password = 'Pilot315';  // akan dapat nanti

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>