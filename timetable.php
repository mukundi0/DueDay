<?php
require_once 'templates/header.php';

// DATA FETCHING - Updated to use the new recurring schedule table structure.
$user_schedule_sql = "SELECT 
                        cs.Entry_ID, 
                        cs.Day_Of_Week,
                        cs.Start_Time,
                        cs.End_Time,
                        c.Class_Name, 
                        v.Venue_Name 
                    FROM Class_Schedule cs 
                    JOIN Classes c ON cs.Class_ID = c.Class_ID 
                    JOIN Venues v ON cs.Venue_ID = v.Venue_ID 
                    JOIN User_Classes uc ON c.Class_ID = uc.Class_ID 
                    WHERE uc.User_ID = ? 
                    ORDER BY cs.Day_Of_Week, cs.Start_Time ASC";

$stmt = $conn->prepare($user_schedule_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$schedule_result = $stmt->get_result();

// Pre-initialize arrays for Monday to Friday
$schedule_by_day = [ 1 => [], 2 => [], 3 => [], 4 => [], 5 => [] ];

while ($row = $schedule_result->fetch_assoc()) {
    // Only add to the array if it's a weekday (or adjust as needed)
    if (array_key_exists($row['Day_Of_Week'], $schedule_by_day)) {
        $schedule_by_day[$row['Day_Of_Week']][] = $row;
    }
}
$stmt->close();
?>

<div class="timetable-container card">
    <div class="page-actions" style="border-bottom: 1px solid var(--medium-gray); padding-bottom: 20px; margin-bottom: 20px;">
        <h2 class="section-title" style="margin-bottom: 0;">My Weekly Schedule</h2>
    </div>
    
    <div class="weekly-timetable" id="weeklyView">
        <?php
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
        foreach ($days as $day_num => $day_name):
        ?>
            <div class="day-column">
                <div class="day-header"><?php echo $day_name; ?></div>
                <?php if (!empty($schedule_by_day[$day_num])): ?>
                    <?php foreach($schedule_by_day[$day_num] as $class): ?>
                        <div class="class-block">
                            <strong><?php echo date('h:i A', strtotime($class['Start_Time'])) . ' - ' . date('h:i A', strtotime($class['End_Time'])); ?></strong><br>
                            <?php echo htmlspecialchars($class['Class_Name']); ?><br>
                            <small><?php echo htmlspecialchars($class['Venue_Name']); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-classes-msg">No classes scheduled.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php 
require_once 'templates/footer.php';
$conn->close();
?>
