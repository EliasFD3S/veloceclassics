document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleLogin();
        });
    }
});

function handleLogin() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    fetch('/admin/api/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: username,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/admin/public/admin.php';
        } else {
            alert(data.message || 'Erreur de connexion');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la connexion');
    });
}

// Vérifier périodiquement si la session est toujours active
function checkSession() {
    fetch('/admin/api/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                window.location.href = '/admin/public/login.php';
            }
        })
        .catch(error => {
            console.error('Erreur de vérification de session:', error);
        });
}

// Vérifier la session toutes les minutes
if (window.location.pathname.includes('/admin/public/admin.php')) {
    setInterval(checkSession, 60000);
} 