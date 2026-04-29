// Check authentication — admin only
async function initAdminDashboard() {
    const data = await checkAuthShared(['admin']);
    if (!data) return;
    const name = data.name || 'Admin';
    document.getElementById('userName').textContent = name;
    document.getElementById('welcomeName').textContent = name;
    loadDashboardData();
}

async function logout() {
    try {
        const response = await fetch('../api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=logout'
        });
        
        window.location.href = '../index.html';
    } catch (error) {
        console.error('Logout failed:', error);
    }
}

async function loadDashboardData() {
    try {
        const response = await fetch('../api/dashboard.php?role=admin');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalStudents').textContent = data.stats.total_students || '0';
            document.getElementById('totalTeachers').textContent = data.stats.total_teachers || '0';
            document.getElementById('totalClasses').textContent = data.stats.total_classes || '0';
            document.getElementById('monthlyRevenue').textContent = '$' + (data.stats.monthly_revenue || '0');
            
            // Create enrollment chart
            createEnrollmentChart(data.enrollment_data || []);
            createAttendanceChart(data.attendance_data || {});
        }
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
    }
}

function createEnrollmentChart(data) {
    const ctx = document.getElementById('enrollmentChart').getContext('2d');
    
    const defaultData = [380, 420, 450, 480, 520, 550];
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Students',
                data: data.length > 0 ? data : defaultData,
                backgroundColor: '#4A90E2',
                borderRadius: 6,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 500 // Faster animation
            },
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: '#F5F7FA'
                    },
                    ticks: {
                        stepSize: 150
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function createAttendanceChart(data) {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    
    const defaultData = {
        present: 85,
        late: 8,
        absent: 7
    };
    
    const chartData = Object.keys(data).length > 0 ? data : defaultData;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Late', 'Absent'],
            datasets: [{
                data: [chartData.present, chartData.late, chartData.absent],
                backgroundColor: ['#27AE60', '#F39C12', '#E74C3C'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 500 // Faster animation
            },
            cutout: '65%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Initialize
initAdminDashboard();
loadNotifications();
