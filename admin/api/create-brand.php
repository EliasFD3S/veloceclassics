<?php
// Configuration initiale
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

require_once __DIR__ . '/protect.php';  // Utiliser le même système de protection
header('Content-Type: application/json');

// Fonction de log
function debug_log($message, $data = null) {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $log .= " - Data: " . print_r($data, true);
    }
    error_log($log);
}

try {
    debug_log("Démarrage du script");
    debug_log("Session actuelle", $_SESSION);
    
    // La vérification de session est maintenant gérée par protect.php
    
    // Vérification du fichier de configuration
    if (!file_exists(__DIR__ . '/db.php')) {
        throw new Exception('Fichier de configuration DB manquant');
    }

    // Lecture des données
    $input = file_get_contents('php://input');
    debug_log("Données reçues", $input);
    
    if (empty($input)) {
        throw new Exception('Aucune donnée reçue');
    }

    // Décodage JSON
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON invalide: ' . json_last_error_msg());
    }
    debug_log("Données décodées", $data);

    // Validation des données
    if (empty($data['name']) || empty($data['logo_url'])) {
        throw new Exception('Données manquantes (nom: ' . 
            (isset($data['name']) ? 'présent' : 'manquant') . 
            ', logo: ' . (isset($data['logo_url']) ? 'présent' : 'manquant') . ')');
    }

    // Connexion DB
    debug_log("Tentative de connexion à la DB");
    require_once __DIR__ . '/db.php';
    
    if (!isset($pdo)) {
        throw new Exception('Échec de la connexion à la base de données');
    }
    debug_log("Connexion DB réussie");

    // Préparation requête
    $stmt = $pdo->prepare("INSERT INTO brands (name, logo_url) VALUES (?, ?)");
    if (!$stmt) {
        throw new Exception('Erreur préparation requête: ' . print_r($pdo->errorInfo(), true));
    }
    debug_log("Requête préparée");

    // Exécution requête
    $name = htmlspecialchars($data['name']);
    $logo_url = htmlspecialchars($data['logo_url']);
    debug_log("Tentative d'insertion", ['name' => $name, 'logo_url' => $logo_url]);
    
    $result = $stmt->execute([$name, $logo_url]);
    
    if (!$result) {
        throw new Exception('Erreur insertion: ' . print_r($stmt->errorInfo(), true));
    }
    
    $brand_id = $pdo->lastInsertId();
    debug_log("Insertion réussie", ['brand_id' => $brand_id]);

    // Réponse succès
    echo json_encode([
        'success' => true,
        'message' => 'Marque créée avec succès',
        'brand_id' => $brand_id
    ]);
    debug_log("Réponse envoyée avec succès");

} catch (Exception $e) {
    debug_log("ERREUR: " . $e->getMessage());
    debug_log("Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => 'Voir les logs pour plus de détails'
    ]);
}

exit; 