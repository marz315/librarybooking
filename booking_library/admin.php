<?php
include 'config/database.php';
include 'includes/functions.php';

// Check admin authentication
redirectIfNotAdmin();

// Handle cancel booking
if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $cancel_reason = $_POST['cancel_reason'];
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'rejected', purpose = CONCAT(purpose, ' [CANCELLED: ', ?, ']') WHERE id = ?");
    if ($stmt->execute([$cancel_reason, $booking_id])) {
        $success_message = "Booking successfully cancelled!";
    } else {
        $error_message = "Failed to cancel booking!";
    }
}

// Handle edit booking
if (isset($_POST['edit_booking'])) {
    $booking_id = $_POST['booking_id'];
    $teacher_name = $_POST['teacher_name'];
    $teacher_email = $_POST['teacher_email'];
    $booking_date = $_POST['booking_date'];
    $time_slot = $_POST['time_slot'];
    $purpose = $_POST['purpose'];
    
    // Check if new slot is available
    if (isSlotAvailable($pdo, $booking_date, $time_slot) || hasPendingBooking($pdo, $booking_date, $time_slot)) {
        $stmt = $pdo->prepare("UPDATE bookings SET teacher_name = ?, teacher_email = ?, booking_date = ?, time_slot = ?, purpose = ? WHERE id = ?");
        if ($stmt->execute([$teacher_name, $teacher_email, $booking_date, $time_slot, $purpose, $booking_id])) {
            $success_message = "Booking successfully updated!";
        } else {
            $error_message = "Failed to update booking!";
        }
    } else {
        $error_message = "Selected time slot is not available!";
    }
}

// Get bookings
$pending_bookings = getPendingBookings($pdo);
$approved_bookings = getApprovedBookings($pdo);
$rejected_bookings = getRejectedBookings($pdo);

// Get counts
$pending_count = getPendingBookingsCount($pdo);
$approved_count = getApprovedBookingsCount($pdo);
$rejected_count = getRejectedBookingsCount($pdo);
$total_count = getTotalBookingsCount($pdo);

include 'includes/header.php';
?>

<div class="admin-panel">
    <h2>Admin Panel - Booking Management</h2>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3 style="color: var(--warning); margin: 0;"><?php echo $pending_count; ?></h3>
            <p style="margin: 5px 0 0 0;">Pending</p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3 style="color: var(--success); margin: 0;"><?php echo $approved_count; ?></h3>
            <p style="margin: 5px 0 0 0;">Approved</p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3 style="color: var(--danger); margin: 0;"><?php echo $rejected_count; ?></h3>
            <p style="margin: 5px 0 0 0;">Rejected</p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3 style="color: var(--primary); margin: 0;"><?php echo $total_count; ?></h3>
            <p style="margin: 5px 0 0 0;">Total</p>
        </div>
    </div>
    
    <!-- Pending Bookings -->
    <div style="display: flex; border-bottom: 1px solid #ddd; margin-bottom: 20px;">
        <div style="padding: 10px 20px; border-bottom: 3px solid var(--secondary); font-weight: bold;">
            Pending Approval (<?php echo $pending_count; ?>)
        </div>
    </div>
    
    <div class="booking-list">
        <?php if (empty($pending_bookings)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>No pending bookings.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pending_bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h4><?php echo htmlspecialchars($booking['teacher_name']); ?></h4>
                        <span class="booking-status status-pending">Pending</span>
                    </div>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['teacher_email']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></p>
                    <p><strong>Time:</strong> <?php echo $booking['time_slot']; ?></p>
                    <p><strong>Purpose:</strong> <?php echo nl2br(htmlspecialchars($booking['purpose'])); ?></p>
                    <p><strong>Submitted:</strong> <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></p>
                    <div class="booking-actions">
                        <a href="approve.php?id=<?php echo $booking['id']; ?>" class="btn btn-success" 
                           onclick="return confirm('Approve this booking?')">Approve</a>
                        <a href="reject.php?id=<?php echo $booking['id']; ?>" class="btn btn-danger"
                           onclick="return confirm('Reject this booking?')">Reject</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Approved Bookings with Edit/Cancel Options -->
    <h3 style="margin-top: 40px;">Approved Bookings (<?php echo $approved_count; ?>)</h3>
    <div class="booking-list">
        <?php if (empty($approved_bookings)): ?>
            <p>No approved bookings.</p>
        <?php else: ?>
            <?php foreach ($approved_bookings as $booking): ?>
                <div class="booking-card approved">
                    <div class="booking-header">
                        <h4><?php echo htmlspecialchars($booking['teacher_name']); ?></h4>
                        <span class="booking-status status-approved">Approved</span>
                    </div>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['teacher_email']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></p>
                    <p><strong>Time:</strong> <?php echo $booking['time_slot']; ?></p>
                    <p><strong>Purpose:</strong> <?php echo nl2br(htmlspecialchars($booking['purpose'])); ?></p>
                    <p><strong>Approved:</strong> <?php echo date('d/m/Y H:i', strtotime($booking['updated_at'])); ?></p>
                    <div class="booking-actions">
                        <button class="btn btn-warning" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($booking)); ?>)">Edit</button>
                        <button class="btn btn-danger" onclick="openCancelModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['teacher_name']); ?>')">Cancel</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Rejected Bookings -->
    <h3 style="margin-top: 40px;">Rejected Bookings (<?php echo $rejected_count; ?>)</h3>
    <div class="booking-list">
        <?php if (empty($rejected_bookings)): ?>
            <p>No rejected bookings.</p>
        <?php else: ?>
            <?php foreach ($rejected_bookings as $booking): ?>
                <div class="booking-card rejected">
                    <div class="booking-header">
                        <h4><?php echo htmlspecialchars($booking['teacher_name']); ?></h4>
                        <span class="booking-status status-rejected">Rejected</span>
                    </div>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['teacher_email']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></p>
                    <p><strong>Time:</strong> <?php echo $booking['time_slot']; ?></p>
                    <p><strong>Purpose:</strong> <?php echo nl2br(htmlspecialchars($booking['purpose'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Booking Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Booking</h3>
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" action="admin.php">
            <div class="modal-body">
                <input type="hidden" name="booking_id" id="edit_booking_id">
                
                <div class="form-group">
                    <label for="edit_teacher_name">Teacher Name</label>
                    <input type="text" id="edit_teacher_name" name="teacher_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_teacher_email">Teacher Email</label>
                    <input type="email" id="edit_teacher_email" name="teacher_email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_booking_date">Date</label>
                    <input type="date" id="edit_booking_date" name="booking_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_time_slot">Time Slot</label>
                    <select id="edit_time_slot" name="time_slot" class="form-control" required>
                        <option value="">Select Time Slot</option>
                        <?php 
                        $time_slots = getTimeSlots();
                        foreach ($time_slots as $slot): 
                        ?>
                            <option value="<?php echo $slot; ?>"><?php echo $slot; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_purpose">Purpose</label>
                    <textarea id="edit_purpose" name="purpose" class="form-control" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">Cancel</button>
                <button type="submit" name="edit_booking" class="btn btn-success">Update Booking</button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cancel Booking</h3>
            <button class="close-modal" onclick="closeCancelModal()">&times;</button>
        </div>
        <form method="POST" action="admin.php">
            <div class="modal-body">
                <input type="hidden" name="booking_id" id="cancel_booking_id">
                
                <div class="form-group">
                    <label for="cancel_reason">Cancellation Reason</label>
                    <textarea id="cancel_reason" name="cancel_reason" class="form-control" rows="4" placeholder="Enter reason for cancellation..." required></textarea>
                </div>
                
                <p><strong>Teacher:</strong> <span id="cancel_teacher_name"></span></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="closeCancelModal()">Back</button>
                <button type="submit" name="cancel_booking" class="btn btn-warning">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>

<script>
// Edit Modal Functions
function openEditModal(booking) {
    document.getElementById('edit_booking_id').value = booking.id;
    document.getElementById('edit_teacher_name').value = booking.teacher_name;
    document.getElementById('edit_teacher_email').value = booking.teacher_email;
    document.getElementById('edit_booking_date').value = booking.booking_date;
    document.getElementById('edit_time_slot').value = booking.time_slot;
    document.getElementById('edit_purpose').value = booking.purpose;
    
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Cancel Modal Functions
function openCancelModal(bookingId, teacherName) {
    document.getElementById('cancel_booking_id').value = bookingId;
    document.getElementById('cancel_teacher_name').textContent = teacherName;
    
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
    document.getElementById('cancel_reason').value = '';
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const editModal = document.getElementById('editModal');
    const cancelModal = document.getElementById('cancelModal');
    
    if (event.target === editModal) {
        closeEditModal();
    }
    if (event.target === cancelModal) {
        closeCancelModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>