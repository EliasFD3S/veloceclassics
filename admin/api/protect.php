<?php
// Définir le chemin de stockage des sessions
$sessionPath = __DIR__ . '/../sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// Configurer les paramètres de session
ini_set('session.gc_maxlifetime', 3600); // 1 heure
ini_set('session.cookie_lifetime', 3600);
session_set_cookie_params(3600, '/', '', true, true);

// Démarrer ou reprendre la session
session_start();

// Debug détaillé
error_log('=== DEBUG SESSION ===');
error_log('Session ID: ' . session_id());
error_log('Session Path: ' . session_save_path());
error_log('Session Data: ' . print_r($_SESSION, true));
error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
error_log('===================');

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    error_log('Vérification session - SESSION contient: ' . print_r($_SESSION, true));
    // Vérifions toutes les clés possibles
    $hasUserId = isset($_SESSION['user_id']);
    $hasAdmin = isset($_SESSION['admin']);
    $hasUsername = isset($_SESSION['username']);
    
    error_log("Status connexion - user_id: " . ($hasUserId ? 'oui' : 'non') . 
              ", admin: " . ($hasAdmin ? 'oui' : 'non') . 
              ", username: " . ($hasUsername ? 'oui' : 'non'));
              
    // Retourner true si l'une des conditions est vraie
    return ($hasUserId || ($hasAdmin && $_SESSION['admin'] === true));
}

// Vérification de la session
if (!isLoggedIn()) {
    error_log('Session non valide - Détails complets:');
    error_log('SESSION: ' . print_r($_SESSION, true));
    error_log('COOKIE: ' . print_r($_COOKIE, true));
    error_log('SERVER: ' . print_r($_SERVER, true));
    
    // Si c'est une requête AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Session expirée ou non valide',
            'redirect' => '/admin/public/login.php', // Corrigé le chemin
            'debug' => [
                'session_id' => session_id(),
                'session_data' => $_SESSION,
                'request_uri' => $_SERVER['REQUEST_URI']
            ]
        ]);
        exit;
    }
    
    // Si c'est une requête normale
    header('Location: /admin/public/login.php'); // Corrigé le chemin
    exit;
}

// Rafraîchir la session
$_SESSION['last_activity'] = time();
?> 