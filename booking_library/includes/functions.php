<?php
// functions.php untuk Sistem Booking Perpustakaan

function getTimeSlots() {
    $slots = [];
    $start = strtotime('07:30');
    $end = strtotime('13:00');
    
    while ($start < $end) {
        $slot_start = date('H:i', $start);
        $start += 1800; // 30 minutes
        $slot_end = date('H:i', $start);
        $slots[] = $slot_start . '-' . $slot_end;
    }
    
    return $slots;
}

function getWeekDates($date = null) {
    if (!$date) $date = date('Y-m-d');
    
    $week_dates = [];
    $monday = date('Y-m-d', strtotime('monday this week', strtotime($date)));
    
    for ($i = 0; $i < 5; $i++) {
        $week_dates[] = date('Y-m-d', strtotime($monday . " +$i days"));
    }
    
    return $week_dates;
}

function getBookingsForWeek($pdo, $week_dates) {
    $bookings = [];
    $time_slots = getTimeSlots();
    
    foreach ($week_dates as $date) {
        foreach ($time_slots as $slot) {
            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_date = ? AND time_slot = ? AND status = 'approved'");
            $stmt->execute([$date, $slot]);
            $booking = $stmt->fetch();
            
            if ($booking) {
                $bookings[$date][$slot] = $booking;
            }
        }
    }
    
    return $bookings;
}

function formatDateDisplay($date) {
    $days = ['Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat'];
    $months = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sept', 'Okt', 'Nov', 'Dis'];
    
    $date_obj = new DateTime($date);
    $day_index = ($date_obj->format('N') - 1); // 1=Isnin, 2=Selasa, etc.
    $day_name = $days[$day_index];
    $date_num = $date_obj->format('j');
    $month_name = $months[$date_obj->format('n') - 1];
    
    return $day_name . "<br>" . $date_num . " " . $month_name;
}

function checkAdminAuth() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function redirectIfNotAdmin() {
    if (!checkAdminAuth()) {
        header("Location: admin-login.php");
        exit();
    }
}

function getPendingBookingsCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
    return $stmt->fetch()['count'];
}

function getApprovedBookingsCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'approved'");
    return $stmt->fetch()['count'];
}

function getRejectedBookingsCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'rejected'");
    return $stmt->fetch()['count'];
}

function getTotalBookingsCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    return $stmt->fetch()['count'];
}

function isSlotAvailable($pdo, $date, $time_slot) {
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_date = ? AND time_slot = ? AND status = 'approved'");
    $stmt->execute([$date, $time_slot]);
    
    return $stmt->fetch() ? false : true;
}

function hasPendingBooking($pdo, $date, $time_slot) {
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_date = ? AND time_slot = ? AND status = 'pending'");
    $stmt->execute([$date, $time_slot]);
    
    return $stmt->fetch() ? true : false;
}

function createBooking($pdo, $teacher_name, $teacher_email, $booking_date, $time_slot, $purpose) {
    $stmt = $pdo->prepare("INSERT INTO bookings (teacher_name, teacher_email, booking_date, time_slot, purpose, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    return $stmt->execute([$teacher_name, $teacher_email, $booking_date, $time_slot, $purpose]);
}

function getBookingById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function approveBooking($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
    return $stmt->execute([$id]);
}

function rejectBooking($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'rejected' WHERE id = ?");
    return $stmt->execute([$id]);
}

function getPendingBookings($pdo, $limit = null, $offset = 0) {
    $sql = "SELECT * FROM bookings WHERE status = 'pending' ORDER BY booking_date, time_slot";
    if ($limit) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    return $pdo->query($sql)->fetchAll();
}

function getApprovedBookings($pdo, $limit = null) {
    $sql = "SELECT * FROM bookings WHERE status = 'approved' ORDER BY booking_date DESC, time_slot";
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    return $pdo->query($sql)->fetchAll();
}

function getRejectedBookings($pdo, $limit = null) {
    $sql = "SELECT * FROM bookings WHERE status = 'rejected' ORDER BY booking_date DESC, time_slot";
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    return $pdo->query($sql)->fetchAll();
}

function getAllBookings($pdo) {
    return $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC")->fetchAll();
}

function getBookingsByDateRange($pdo, $start_date, $end_date) {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_date BETWEEN ? AND ? ORDER BY booking_date, time_slot");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll();
}

function getTodayBookings($pdo) {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_date = ? ORDER BY time_slot");
    $stmt->execute([$today]);
    return $stmt->fetchAll();
}

function getUpcomingBookings($pdo, $days = 7) {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+$days days"));
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_date BETWEEN ? AND ? AND status = 'approved' ORDER BY booking_date, time_slot");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll();
}

function validateBookingDate($date) {
    $booking_date = strtotime($date);
    $today = strtotime(date('Y-m-d'));
    
    if ($booking_date < $today) {
        return "Tarikh tempahan tidak boleh lepas dari hari ini";
    }
    
    // Check jika hari Ahad atau Sabtu
    $day_of_week = date('N', $booking_date);
    if ($day_of_week == 6 || $day_of_week == 7) { // 6=Sabtu, 7=Ahad
        return "Tempahan hanya boleh dibuat untuk hari Isnin hingga Jumaat";
    }
    
    return true;
}

function validateTimeSlot($time_slot) {
    $time_slots = getTimeSlots();
    return in_array($time_slot, $time_slots);
}

function sendBookingConfirmation($teacher_email, $teacher_name, $booking_date, $time_slot) {
    // Function untuk hantar email confirmation (boleh implement later)
    $subject = "Tempahan Perpustakaan Diterima";
    $message = "Hi $teacher_name,\n\nTempahan perpustakaan anda pada $booking_date ($time_slot) telah diterima dan menunggu kelulusan admin.\n\nTerima kasih.";
    
    // Untuk sekarang, return true sahaja
    // mail($teacher_email, $subject, $message);
    return true;
}

function sendBookingApproval($teacher_email, $teacher_name, $booking_date, $time_slot) {
    // Function untuk hantar email approval
    $subject = "Tempahan Perpustakaan Diluluskan";
    $message = "Hi $teacher_name,\n\nTempahan perpustakaan anda pada $booking_date ($time_slot) telah diluluskan.\n\nSila hadir pada masa yang ditetapkan.";
    
    // mail($teacher_email, $subject, $message);
    return true;
}

function sendBookingRejection($teacher_email, $teacher_name, $booking_date, $time_slot) {
    // Function untuk hantar email rejection
    $subject = "Tempahan Perpustakaan Ditolak";
    $message = "Hi $teacher_name,\n\nMaaf, tempahan perpustakaan anda pada $booking_date ($time_slot) telah ditolak.\n\nSila buat tempahan pada slot masa lain.";
    
    // mail($teacher_email, $subject, $message);
    return true;
}

function getBookingStats($pdo) {
    $stats = [];
    
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
    $status_counts = $stmt->fetchAll();
    
    foreach ($status_counts as $row) {
        $stats[$row['status']] = $row['count'];
    }
    
    // Ensure all statuses exist in stats
    $stats['pending'] = $stats['pending'] ?? 0;
    $stats['approved'] = $stats['approved'] ?? 0;
    $stats['rejected'] = $stats['rejected'] ?? 0;
    $stats['total'] = array_sum($stats);
    
    return $stats;
}

function formatMalaysianDate($date) {
    $days = ['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'];
    $months = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
    
    $timestamp = strtotime($date);
    $day_name = $days[date('w', $timestamp)];
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp) - 1];
    $year = date('Y', $timestamp);
    
    return "$day_name, $day $month $year";
}

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function debugLog($message) {
    $log_file = 'debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}
?>