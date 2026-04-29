// Check authentication
async function checkAuth() {
    try {
        const response = await fetch('../api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check_session'
        });
        
        const data = await response.json();
        
        if (!data.logged_in || data.role !== 'student') {
            window.location.href = '../index.html';
            return;
        }
        
        document.getElementById('userName').textContent = data.name;
        document.getElementById('welcomeName').textContent = data.name;
        loadDashboardData();
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = '../index.html';
    }
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
        const response = await fetch('../api/dashboard.php?role=student');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('attendance').textContent = (data.stats.attendance || '92') + '%';
            document.getElementById('averageGrade').textContent = data.stats.average_grade || 'A-';
            document.getElementById('pendingHomework').textContent = data.stats.pending_homework || '3';
            document.getElementById('upcomingExams').textContent = data.stats.upcoming_exams || '2';
        }
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
    }
}

// Handle assignment upload
document.getElementById('assignmentUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'upload_assignment');
    
    try {
        const response = await fetch('../api/student.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Assignment uploaded successfully!');
            this.reset();
            loadDashboardData();
        } else {
            alert('Failed to upload assignment: ' + data.message);
        }
    } catch (error) {
        alert('An error occurred while uploading the assignment.');
        console.error('Upload error:', error);
    }
});

// Initialize
checkAuth();
loadNotifications();
