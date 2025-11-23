<?php
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $secret_key = $_POST['secret_key'] ?? '';
    
    // Secret key untuk akses pantas (Ctrl+Alt+A)
    $valid_secret_keys = ['ADMIN_ACCESS_2024', 'LIBRARY_SECRET_123', 'SCHOOL_ADMIN_2024','admin123', 'library'] ;
    
    if (in_array($secret_key, $valid_secret_keys)) {
        $_SESSION['admin_logged_in'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kunci rahsia salah!']);
    }
    exit();
}
?>