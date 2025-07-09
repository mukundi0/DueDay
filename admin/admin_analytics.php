<?php
require_once 'templates/header.php';

// --- DATA FETCHING FOR CHARTS ---

// 1. User Engagement: Distribution of users by role
$role_distribution_query = "SELECT r.Role_Name, COUNT(u.User_ID) as count FROM Role r LEFT JOIN Users u ON r.Role_ID = u.Role_ID GROUP BY r.Role_Name";
$role_data = $conn->query($role_distribution_query)->fetch_all(MYSQLI_ASSOC);
$role_labels = [];
$role_counts = [];
foreach ($role_data as $row) {
    $role_labels[] = $row['Role_Name'];
    $role_counts[] = $row['count'];
}

// 2. Content Insights: Number of assignments per class
$assignments_per_class_query = "SELECT c.Class_Name, COUNT(a.Assignment_ID) as count FROM Classes c LEFT JOIN Assignments a ON c.Class_ID = a.Class_ID GROUP BY c.Class_Name ORDER BY count DESC LIMIT 10";
$class_data = $conn->query($assignments_per_class_query)->fetch_all(MYSQLI_ASSOC);
$class_labels = [];
$class_assignment_counts = [];
foreach ($class_data as $row) {
    $class_labels[] = $row['Class_Name'];
    $class_assignment_counts[] = $row['count'];
}

// 3. Poll Performance: Turnout for each poll (total votes)
$poll_turnout_query = "SELECT p.Poll_Title, COUNT(pd.User_ID) as vote_count FROM Polls p LEFT JOIN Poll_Data pd ON p.Poll_ID = pd.Poll_ID GROUP BY p.Poll_ID ORDER BY vote_count DESC LIMIT 10";
$poll_data = $conn->query($poll_turnout_query)->fetch_all(MYSQLI_ASSOC);
$poll_labels = [];
$poll_vote_counts = [];
foreach ($poll_data as $row) {
    $poll_labels[] = $row['Poll_Title'];
    $poll_vote_counts[] = $row['vote_count'];
}

$conn->close();
?>
<head>
    <title>Analytics Dashboard - Admin</title>
</head>

<h1 class="page-title">Analytics Dashboard</h1>
<p style="margin-bottom: 20px;">An overview of platform usage and content engagement.</p>

<div class="stat-card-container">
    <div class="stat-card">
        <h3>User Role Distribution</h3>
        <canvas id="userRoleChart"></canvas>
    </div>

    <div class="stat-card">
        <h3>Top 10 Polls by Turnout</h3>
        <canvas id="pollTurnoutChart"></canvas>
    </div>
</div>

<div class="management-section">
    <h2>Content Insights: Assignments per Class</h2>
    <p>Showing top 10 classes by number of assignments created.</p>
    <canvas id="assignmentsPerClassChart" style="margin-top:20px;"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Render User Role Distribution Chart (Pie Chart)
    const userRoleCtx = document.getElementById('userRoleChart').getContext('2d');
    new Chart(userRoleCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($role_labels); ?>,
            datasets: [{
                label: 'Number of Users',
                data: <?php echo json_encode($role_counts); ?>,
                backgroundColor: [
                    'rgba(79, 70, 229, 0.8)', // Admin (Blue Accent)
                    'rgba(245, 158, 11, 0.8)', // Module Leader
                    'rgba(34, 197, 94, 0.8)', // Student
                    'rgba(239, 68, 68, 0.8)'  // Event Coordinator
                ],
                borderColor: 'rgba(255, 255, 255, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // 2. Render Assignments Per Class Chart (Bar Chart)
    const assignmentsCtx = document.getElementById('assignmentsPerClassChart').getContext('2d');
    new Chart(assignmentsCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($class_labels); ?>,
            datasets: [{
                label: 'Number of Assignments',
                data: <?php echo json_encode($class_assignment_counts); ?>,
                backgroundColor: 'rgba(79, 70, 229, 0.6)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // Makes it a horizontal bar chart
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // 3. Render Poll Turnout Chart (Doughnut Chart)
    const pollTurnoutCtx = document.getElementById('pollTurnoutChart').getContext('2d');
    new Chart(pollTurnoutCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($poll_labels); ?>,
            datasets: [{
                label: 'Total Votes',
                data: <?php echo json_encode($poll_vote_counts); ?>,
                backgroundColor: [
                    'rgba(5, 150, 105, 0.7)',
                    'rgba(29, 78, 216, 0.7)',
                    'rgba(217, 70, 239, 0.7)',
                    'rgba(244, 63, 94, 0.7)',
                    'rgba(253, 186, 116, 0.7)'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 10
                    }
                }
            }
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>