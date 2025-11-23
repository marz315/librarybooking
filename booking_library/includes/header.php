<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Booking Perpustakaan</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <h1 class="logo">Booking Library</h1>
                <nav>
                    <ul>
                        <li><a href="index.php">Jadual</a></li>
                        <li><a href="booking.php">Buat Tempahan</a></li>
                        <?php if (checkAdminAuth()): ?>
                            <li><a href="admin.php">Panel Admin</a></li>
                            <li><a href="logout.php">Log Keluar</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <div class="container">