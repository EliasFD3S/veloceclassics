<?php
require_once 'db.php';

// Configuration de la session
session_start();

try {
    // Vérifier les données POST
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        throw new Exception('Données de connexion manquantes');
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Connexion à la base de données
    $pdo = getDBConnection();

    // Rechercher l'utilisateur dans la table administrateurs
    $stmt = $pdo->prepare("SELECT id, password, name FROM administrateurs WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Régénérer l'ID de session pour la sécurité
        session_regenerate_id(true);
        
        // Définir la session admin
        $_SESSION['admin'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['last_activity'] = time();

        // Si c'est une requête AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie',
                'admin_name' => $admin['name'],
                'redirect' => '/admin/public/admin.php'
            ]);
        } else {
            // Si c'est une requête normale, redirection directe
            header('Location: /admin/public/admin.php');
        }
        exit;
    } else {
        throw new Exception('Identifiants invalides');
    }
} catch (Exception $e) {
    error_log('Erreur de connexion: ' . $e->getMessage());
    
    // Si c'est une requête AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } else {
        // Si c'est une requête normale
        header('Location: /admin/public/login.html?error=' . urlencode($e->getMessage()));
    }
    exit;
}
?> 