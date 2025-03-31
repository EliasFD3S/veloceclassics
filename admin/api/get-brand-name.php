<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception("ID de la marque non fourni");
    }

    $brandId = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT name FROM brands WHERE id = ?");
    $stmt->execute([$brandId]);

    $brand = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$brand) {
        throw new Exception("Marque non trouvée");
    }

    echo json_encode([
        'success' => true,
        'brand_name' => $brand['name']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 