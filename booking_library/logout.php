<?php
// logout.php
session_start();

// Clear semua session variables
$_SESSION = array();

// Destroy session
if (session_destroy()) {
    // Redirect ke halaman utama
    header("Location: index.php");
    exit();
} else {
    // Jika destroy gagal, clear manual dan redirect
    session_unset();
    header("Location: index.php");
    exit();
}
?>