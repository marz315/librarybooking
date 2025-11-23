<?php
include 'config/database.php';
include 'includes/functions.php';

// Check admin authentication
redirectIfNotAdmin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: admin.php");
exit();
?>