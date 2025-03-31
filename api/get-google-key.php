<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    // Vérifier si le fichier .env existe
    if (!file_exists(__DIR__ . '/../.env')) {
        throw new Exception('Fichier .env non trouvé');
    }

    // Charger les variables d'environnement
    $env = parse_ini_file(__DIR__ . '/../.env');
    
    if (!$env) {
        throw new Exception('Impossible de lire le fichier .env');
    }

    if (!isset($env['API_GOOGLE'])) {
        throw new Exception('Clé API_GOOGLE non trouvée dans .env');
    }

    $apiKey = $env['API_GOOGLE'];

    echo json_encode(['apiKey' => $apiKey]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} 