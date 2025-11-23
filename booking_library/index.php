<?php
include 'config/database.php';
include 'includes/functions.php';

$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$week_dates = getWeekDates($current_date);
$time_slots = getTimeSlots();
$bookings = getBookingsForWeek($pdo, $week_dates);

include 'includes/header.php';
?>

<div class="schedule-container">
    <div class="schedule-header">
        <h2>Jadual Tempahan Perpustakaan</h2>
        <div class="week-navigation">
            <?php
            $prev_week = date('Y-m-d', strtotime($week_dates[0] . ' -7 days'));
            $next_week = date('Y-m-d', strtotime($week_dates[0] . ' +7 days'));
            ?>
            <a href="index.php?date=<?php echo $prev_week; ?>" class="btn btn-primary">Minggu Lepas</a>
            <span><strong>Minggu: <?php echo date('d/m/Y', strtotime($week_dates[0])) . ' - ' . date('d/m/Y', strtotime($week_dates[4])); ?></strong></span>
            <a href="index.php?date=<?php echo $next_week; ?>" class="btn btn-primary">Minggu Depan</a>
        </div>
    </div>
    
    <table class="schedule-table">
        <thead>
            <tr>
                <th>Masa</th>
                <?php foreach ($week_dates as $date): ?>
                    <th><?php echo formatDateDisplay($date); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($time_slots as $slot): ?>
                <tr>
                    <td class="time-column"><?php echo $slot; ?></td>
                    <?php foreach ($week_dates as $date): ?>
                        <?php
                        $booking = $bookings[$date][$slot] ?? null;
                        $is_booked = !empty($booking);
                        ?>
                        <td class="slot <?php echo $is_booked ? 'booked' : 'available'; ?>">
                            <?php if ($is_booked): ?>
                                <strong><?php echo htmlspecialchars($booking['teacher_name']); ?></strong>
                            <?php else: ?>
                                Tersedia
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 20px; display: flex; gap: 15px; justify-content: center;">
        <div style="display: flex; align-items: center; gap: 5px;">
            <div style="width: 20px; height: 20px; background-color: #d4edda; border: 1px solid #ddd;"></div>
            <span>Tersedia</span>
        </div>
        <div style="display: flex; align-items: center; gap: 5px;">
            <div style="width: 20px; height: 20px; background-color: #f8d7da; border: 1px solid #ddd;"></div>
            <span>Telah Ditempah</span>
        </div>
    </div>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="booking.php" class="btn btn-success">Buat Tempahan Baru</a>
</div>

<?php include 'includes/footer.php'; ?>