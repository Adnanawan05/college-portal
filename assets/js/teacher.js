// Check authentication — teacher only
async function initTeacherDashboard() {
    const data = await checkAuthShared(['teacher']);
    if (!data) return;
    const name = data.name || 'Teacher';
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
        const response = await fetch('../api/dashboard.php?role=teacher');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('myClasses').textContent = data.stats.my_classes || '8';
            document.getElementById('totalStudents').textContent = data.stats.total_students || '240';
            document.getElementById('pendingHomework').textContent = data.stats.pending_homework || '15';
            document.getElementById('upcomingExams').textContent = data.stats.upcoming_exams || '3';
            
            createPerformanceChart();
        }
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
        createPerformanceChart();
    }
}

function createPerformanceChart() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [{
                label: 'Grade 10A',
                data: [75, 78, 80, 82, 85, 87],
                borderColor: '#4A90E2',
                backgroundColor: 'rgba(74, 144, 226, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Grade 11B',
                data: [70, 72, 75, 77, 80, 82],
                borderColor: '#27AE60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Grade 12A',
                data: [80, 82, 84, 85, 88, 90],
                borderColor: '#F39C12',
                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 500
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: '#F5F7FA'
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

// Initialize
initTeacherDashboard();
loadNotifications();
