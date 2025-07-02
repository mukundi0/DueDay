<?php
require_once 'templates/header.php';

// --- ADMIN NOTICE BANNER ---
if (isset($user_role) && $user_role === 'Admin'): 
?>
<div class="admin-notice-banner">
    <p>You are logged in as an Administrator.</p>
    <a href="admin/admin_dashboard.php" class="btn">Go to Admin Dashboard</a>
</div>
<?php 
endif; 

// --- FIX: Get both the day of the week AND the current time ---
$todays_day_of_week = date('w') + 1; // 1 (Sun) to 7 (Sat)
$current_time = date('H:i:s');      // e.g., "18:00:00"

// --- DATA FETCHING FOR WIDGETS ---
// Stat Cards
$assignment_count = $conn->query("SELECT COUNT(*) as count FROM Assignments WHERE Assignment_DueDate >= NOW()")->fetch_assoc()['count'];
$poll_count = $conn->query("SELECT COUNT(*) as count FROM Polls WHERE Expires_At >= NOW()")->fetch_assoc()['count'];

// --- FIX: Modify the class count query to also check the time ---
$stmt_class_count = $conn->prepare(
    "SELECT COUNT(DISTINCT uc.Class_ID) as count 
     FROM user_classes uc 
     JOIN class_schedule cs ON uc.Class_ID = cs.Class_ID 
     WHERE uc.User_ID = ? AND cs.Day_Of_Week = ? AND cs.End_Time > ?"
);
$stmt_class_count->bind_param("iis", $user_id, $todays_day_of_week, $current_time);
$stmt_class_count->execute();
$class_count = $stmt_class_count->get_result()->fetch_assoc()['count'];
$stmt_class_count->close();

// Unread Comments Count Logic
$stmt_comments = $conn->prepare(
    "SELECT COUNT(ac.Comment_ID) as count
     FROM assignment_comments ac
     JOIN assignments a ON ac.Assignment_ID = a.Assignment_ID
     JOIN user_classes uc ON a.Class_ID = uc.Class_ID AND uc.User_ID = ?
     LEFT JOIN user_read_comments rc ON ac.Comment_ID = rc.Comment_ID AND rc.User_ID = ?
     WHERE rc.Comment_ID IS NULL"
);
$stmt_comments->bind_param("ii", $user_id, $user_id);
$stmt_comments->execute();
$unread_comments = $stmt_comments->get_result()->fetch_assoc()['count'] ?? 0;
$stmt_comments->close();

// Units You're Involved In
$units_with_status_sql = "SELECT c.Class_ID, c.Class_Name, (SELECT COUNT(*) FROM Assignments a WHERE a.Class_ID = c.Class_ID AND a.Assignment_DueDate >= NOW()) as pending_assignments, (SELECT COUNT(*) FROM Polls p WHERE p.Class_ID = c.Class_ID AND p.Expires_At >= NOW()) as active_polls FROM Classes c JOIN User_Classes uc ON c.Class_ID = uc.Class_ID WHERE uc.User_ID = ?";
$stmt_units = $conn->prepare($units_with_status_sql);
$stmt_units->bind_param("i", $user_id);
$stmt_units->execute();
$user_units = $stmt_units->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_units->close();

// --- FIX: Today's Classes Widget Query now checks the current time ---
$todays_classes_sql = "SELECT cs.Start_Time, cs.End_Time, c.Class_Name, v.Venue_Name 
                       FROM Class_Schedule cs 
                       JOIN Classes c ON cs.Class_ID = c.Class_ID 
                       JOIN Venues v ON cs.Venue_ID = v.Venue_ID 
                       JOIN User_Classes uc ON c.Class_ID = uc.Class_ID 
                       WHERE uc.User_ID = ? AND cs.Day_Of_Week = ? AND cs.End_Time > ?
                       ORDER BY cs.Start_Time ASC";
$stmt_today = $conn->prepare($todays_classes_sql);
$stmt_today->bind_param("iis", $user_id, $todays_day_of_week, $current_time);
$stmt_today->execute();
$todays_classes = $stmt_today->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_today->close();

// Assignments Due Widget
function format_due_date(string $due_date_str): string {
    $due = new DateTime($due_date_str); $now = new DateTime(); $diff = $now->diff($due);
    if ($due < $now) return 'Overdue';
    $days = $diff->days;
    if ($days == 0) return 'Due Today'; if ($days == 1) return 'Due Tomorrow';
    return "Due in {$days} Days";
}
$assignments_due = $conn->query("SELECT Assignment_Title, Assignment_DueDate FROM Assignments WHERE Assignment_DueDate >= NOW() ORDER BY Assignment_DueDate ASC LIMIT 3")->fetch_all(MYSQLI_ASSOC);

// Notifications Widget
$stmt_notifications = $conn->prepare("SELECT Notification_Content FROM Notifications n JOIN Notification_User nu ON n.Notification_ID = nu.Notification_ID WHERE nu.User_ID = ? ORDER BY n.Notification_Date DESC LIMIT 3");
$stmt_notifications->bind_param("i", $user_id);
$stmt_notifications->execute();
$notifications = $stmt_notifications->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_notifications->close();
?>

<div class="dashboard-overview">
    <div class="stat-card"> <div class="stat-value"><?php echo $assignment_count; ?></div> <div class="stat-label">Assignments</div> </div>
    <div class="stat-card"> <div class="stat-value"><?php echo $poll_count; ?></div> <div class="stat-label">Active Polls</div> </div>
    <div class="stat-card"> <div class="stat-value"><?php echo $class_count; ?></div> <div class="stat-label">Today's Classes</div> </div>
    <div class="stat-card"> <div class="stat-value"><?php echo $unread_comments; ?></div> <div class="stat-label">Unread Comments</div> </div>
</div>

<div class="widgets-container">
    <div class="card widget-card">
        <h2 class="section-title">Units You're Involved In</h2>
        <div class="units-grid">
            <?php if(empty($user_units)): ?><p>You are not enrolled in any units.</p><?php else: ?>
                <?php foreach($user_units as $unit): ?>
                    <div class="unit-card">
                        <div class="unit-name"><?php echo htmlspecialchars($unit['Class_Name']); ?></div>
                        <div class="unit-status-container">
                            <?php if($unit['pending_assignments'] > 0): ?>
                                <span class="unit-status unit-status--assignment">Assignment Pending</span>
                            <?php endif; ?>
                            <?php if($unit['active_polls'] > 0): ?>
                                <span class="unit-status unit-status--poll">Poll Active</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card list-widget">
        <h2 class="section-title">Today's Classes</h2>
        <?php if(empty($todays_classes)): ?><p class="widget-empty-msg">No classes scheduled for today.</p><?php else: ?>
            <ul class="widget-list">
                <?php foreach($todays_classes as $class): ?>
                <li class="widget-list-item">
                    <div class="item-content">
                        <span class="item-title"><?php echo htmlspecialchars($class['Class_Name']); ?></span>
                        <span class="item-meta"><?php echo htmlspecialchars($class['Venue_Name']); ?></span>
                    </div>
                    <span class="item-time"><?php echo date('h:i A', strtotime($class['Start_Time'])); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="timetable.php" class="list-widget-link">Go to Timetable</a>
    </div>

    <div class="card list-widget">
        <h2 class="section-title">Assignments Due</h2>
        <?php if(empty($assignments_due)): ?><p class="widget-empty-msg">No upcoming assignments.</p><?php else: ?>
            <ul class="widget-list">
                <?php foreach($assignments_due as $assignment): ?>
                <li class="widget-list-item">
                    <div class="item-content">
                        <span class="item-title"><?php echo htmlspecialchars($assignment['Assignment_Title']); ?></span>
                        <span class="item-meta status-due"><?php echo format_due_date($assignment['Assignment_DueDate']); ?></span>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="assignment.php" class="list-widget-link">View All Assignments</a>
    </div>

    <div class="card list-widget">
        <h2 class="section-title">Notifications</h2>
        <?php if(empty($notifications)): ?><p class="widget-empty-msg">No new notifications.</p><?php else: ?>
            <ul class="widget-list">
                <?php foreach($notifications as $notification): ?>
                <li class="widget-list-item">
                    <div class="item-content">
                        <span class="item-title"><?php echo htmlspecialchars($notification['Notification_Content']); ?></span>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php 
require_once 'templates/footer.php'; 
$conn->close();
?>