<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC");
    $brands = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'brands' => $brands
    ]);
} catch (Exception $e) {
    error_log("Erreur dans get-brands-select.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la récupération des marques'
    ]);
} 