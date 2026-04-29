// Check authentication — parent only
async function initParentDashboard() {
    const data = await checkAuthShared(['parent']);
    if (!data) return;
    const name = data.name || 'Parent';
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
        const response = await fetch('../api/dashboard.php?role=parent');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('childAttendance').textContent = (data.stats.child_attendance || '95') + '%';
            document.getElementById('averageGrade').textContent = data.stats.average_grade || 'B+';
            document.getElementById('pendingFees').textContent = '$' + (data.stats.pending_fees || '250');
            document.getElementById('unreadMessages').textContent = data.stats.unread_messages || '2';
            
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
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Mathematics',
                data: [75, 78, 82, 85, 87, 88],
                borderColor: '#4A90E2',
                backgroundColor: 'rgba(74, 144, 226, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'English',
                data: [80, 82, 85, 88, 90, 92],
                borderColor: '#27AE60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Science',
                data: [70, 72, 74, 75, 76, 78],
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
initParentDashboard();
loadNotifications();
