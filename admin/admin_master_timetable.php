<?php
require_once 'templates/header.php';

// --- DATA FETCHING ---
$schedule_data = $conn->query("
    SELECT cs.Day_Of_Week, cs.Start_Time, cs.End_Time, c.Class_Name, v.Venue_Name
    FROM class_schedule cs
    JOIN Classes c ON cs.Class_ID = c.Class_ID
    JOIN Venues v ON cs.Venue_ID = v.Venue_ID
    ORDER BY cs.Start_Time, v.Venue_Name
")->fetch_all(MYSQLI_ASSOC);
$conn->close();

// Organize schedule data into a structured array for easy lookup
$timetable = [];
foreach ($schedule_data as $entry) {
    $day = $entry['Day_Of_Week'];
    $start_hour = date('H', strtotime($entry['Start_Time']));
    $timetable[$day][$start_hour][] = $entry;
}

$days_of_week = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
$time_slots = range(8, 17); // For 8 AM to 5 PM
?>

<head>
    <title>Master Timetable - Admin</title>
    <style>
        /* Basic styling for the master timetable grid */
        .master-timetable {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .master-timetable th, .master-timetable td {
            border: 1px solid var(--border-color);
            padding: 8px;
            text-align: center;
            vertical-align: top;
            height: 100px;
        }
        .master-timetable th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        .time-slot-header {
            font-weight: 600;
            font-size: 0.9em;
            text-align: right;
            vertical-align: middle;
        }
        .class-entry {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 4px;
            padding: 5px;
            margin-bottom: 5px;
            font-size: 0.8em;
            text-align: left;
        }
        .class-entry strong {
            display: block;
            color: var(--blue-accent);
        }
    </style>
</head>

<h1 class="page-title">Master Weekly Timetable</h1>
<p style="margin-bottom: 20px;">A complete overview of all scheduled classes across all venues.</p>

<div class="management-section">
    <table class="master-timetable">
        <thead>
            <tr>
                <th>Time</th>
                <?php foreach ($days_of_week as $day_name): ?>
                    <th><?php echo $day_name; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($time_slots as $hour): ?>
                <tr>
                    <td class="time-slot-header"><?php echo date('g:00 A', strtotime("$hour:00")); ?></td>
                    <?php foreach ($days_of_week as $day_num => $day_name): ?>
                        <td>
                            <?php
                            // Check if there are any entries for this day and hour
                            if (isset($timetable[$day_num][$hour])) {
                                foreach ($timetable[$day_num][$hour] as $class) {
                                    echo '<div class="class-entry">';
                                    echo '<strong>' . htmlspecialchars($class['Class_Name']) . '</strong>';
                                    echo '<span>' . htmlspecialchars($class['Venue_Name']) . '</span><br>';
                                    echo '<small>' . date('h:i A', strtotime($class['Start_Time'])) . ' - ' . date('h:i A', strtotime($class['End_Time'])) . '</small>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'templates/footer.php'; ?>