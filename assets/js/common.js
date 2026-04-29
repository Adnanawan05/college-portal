// Common functions for all dashboards

async function logout() {
    try {
        await fetch('../api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=logout'
        });
        window.location.href = '../index.html';
    } catch (error) {
        console.error('Logout failed:', error);
        window.location.href = '../index.html';
    }
}

async function loadNotifications() {
    try {
        const response = await fetch('../api/notifications.php?action=get_notifications');
        const data = await response.json();
        
        if (data.success) {
            const badge = document.getElementById('notifCount');
            if (badge) {
                badge.textContent = data.count || '0';
                if (data.count > 0) {
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Failed to load notifications:', error);
    }
}

// Load notifications every 30 seconds
setInterval(loadNotifications, 30000);

/**
 * Check authentication for shared pages (classes, grades, attendance).
 * Allowed roles can be passed as an array, e.g. ['admin','teacher'].
 * If no allowedRoles provided, any logged-in user is allowed.
 */
async function checkAuthShared(allowedRoles) {
    try {
        const response = await fetch('../api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check_session'
        });

        const data = await response.json();

        if (!data.logged_in) {
            window.location.href = '../index.html';
            return null;
        }

        if (allowedRoles && !allowedRoles.includes(data.role)) {
            // Redirect to their own dashboard
            window.location.href = `../${data.role}.html`;
            return null;
        }

        return data;
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = '../index.html';
        return null;
    }
}

// Legacy: Check authentication on page load (role-agnostic, kept for compatibility)
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
        
        if (!data.logged_in) {
            window.location.href = '../index.html';
            return null;
        }
        
        return data;
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = '../index.html';
        return null;
    }
}
