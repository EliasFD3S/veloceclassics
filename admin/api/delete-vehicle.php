<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    // Vérifier si l'ID est fourni
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID du véhicule non fourni');
    }
    
    $vehicleId = intval($data['id']);
    
    // Supprimer d'abord les images associées
    $stmt = $pdo->prepare("DELETE FROM vehicle_images WHERE vehicle_id = ?");
    $stmt->execute([$vehicleId]);
    
    // Puis supprimer le véhicule
    $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicleId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Véhicule non trouvé');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Véhicule supprimé avec succès'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 