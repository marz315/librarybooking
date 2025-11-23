<?php
include 'config/database.php';
include 'includes/functions.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_name = $_POST['teacher_name'];
    $teacher_email = $_POST['teacher_email'];
    $booking_date = $_POST['booking_date'];
    $time_slot = $_POST['time_slot'];
    $purpose = $_POST['purpose'];
    
    // Check if slot is already booked
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_date = ? AND time_slot = ? AND status = 'approved'");
    $stmt->execute([$booking_date, $time_slot]);
    
    if ($stmt->fetch()) {
        $message = 'Slot ini telah ditempah. Sila pilih slot lain.';
        $message_type = 'error';
    } else {
        // Check for pending booking
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_date = ? AND time_slot = ? AND status = 'pending'");
        $stmt->execute([$booking_date, $time_slot]);
        
        if ($stmt->fetch()) {
            $message = 'Terdapat tempahan menunggu untuk slot ini. Sila pilih slot lain.';
            $message_type = 'error';
        } else {
            // Insert new booking
            $stmt = $pdo->prepare("INSERT INTO bookings (teacher_name, teacher_email, booking_date, time_slot, purpose, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            
            if ($stmt->execute([$teacher_name, $teacher_email, $booking_date, $time_slot, $purpose])) {
                $message = 'Tempahan berjaya dihantar! Menunggu kelulusan admin.';
                $message_type = 'success';
            } else {
                $message = 'Ralat: Tempahan gagal dihantar.';
                $message_type = 'error';
            }
        }
    }
}

$time_slots = getTimeSlots();

include 'includes/header.php';
?>

<div class="booking-form">
    <h2>Buat Tempahan Perpustakaan</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="booking.php">
        <div class="form-group">
            <label for="teacher_name">Nama Guru *</label>
            <input type="text" id="teacher_name" name="teacher_name" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="teacher_email">Email Guru *</label>
            <input type="email" id="teacher_email" name="teacher_email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="booking_date">Tarikh *</label>
            <input type="date" id="booking_date" name="booking_date" class="form-control" 
                   min="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="time_slot">Slot Masa *</label>
            <select id="time_slot" name="time_slot" class="form-control" required>
                <option value="">Pilih Slot Masa</option>
                <?php foreach ($time_slots as $slot): ?>
                    <option value="<?php echo $slot; ?>"><?php echo $slot; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="purpose">Tujuan Penggunaan *</label>
            <textarea id="purpose" name="purpose" class="form-control" rows="4" 
                      placeholder="Sila nyatakan tujuan penggunaan perpustakaan..." required></textarea>
        </div>
        
        <div class="form-actions">
            <a href="index.php" class="btn btn-danger">Batal</a>
            <button type="submit" class="btn btn-success">Hantar Tempahan</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>