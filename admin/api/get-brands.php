<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    // Log pour debug
    error_log("Début de get-brands.php");
    
    // Vérification de la connexion
    if (!isset($pdo)) {
        throw new Exception("Pas de connexion à la base de données");
    }
    
    // Exécution de la requête avec debug
    error_log("Exécution de la requête SELECT");
    $stmt = $pdo->query("SELECT * FROM brands ORDER BY created_at DESC");
    
    // Vérification des erreurs PDO
    if ($stmt === false) {
        $errorInfo = $pdo->errorInfo();
        error_log("Erreur PDO: " . print_r($errorInfo, true));
        throw new Exception("Erreur lors de l'exécution de la requête");
    }
    
    // Récupération des résultats
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Nombre de marques trouvées: " . count($brands));
    error_log("Données des marques: " . print_r($brands, true));
    
    // Envoi de la réponse
    echo json_encode([
        'success' => true,
        'brands' => $brands,
        'count' => count($brands)
    ]);

} catch (Exception $e) {
    error_log("Erreur dans get-brands.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la récupération des marques: ' . $e->getMessage(),
        'details' => 'Voir les logs pour plus de détails'
    ]);
} 