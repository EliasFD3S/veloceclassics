<?php
require_once '../api/protect.php'; // Utilise le même système de protection que les autres scripts
require_once 'version_manager.php';

header('Content-Type: application/json');

try {
    // Mettre à jour le numéro de version
    $newVersion = updateVersionFile();

    // Répondre avec la nouvelle version
    echo json_encode([
        'success' => true,
        'version' => $newVersion,
        'message' => 'Cache rafraîchi avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors du rafraîchissement du cache: ' . $e->getMessage()
    ]);
} 