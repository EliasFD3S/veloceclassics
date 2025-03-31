<?php
require_once 'protect.php';
require_once 'db.php';

header('Content-Type: application/json');

try {
    // Récupérer les données JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['image_id'])) {
        throw new Exception('ID de l\'image manquant');
    }

    $imageId = filter_var($data['image_id'], FILTER_VALIDATE_INT);
    if ($imageId === false) {
        throw new Exception('ID de l\'image invalide');
    }

    $pdo = getDBConnection();

    // Récupérer le chemin de l'image avant la suppression
    $stmt = $pdo->prepare("SELECT image_url FROM vehicle_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image) {
        throw new Exception('Image non trouvée');
    }

    // Supprimer le fichier physique
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $image['image_url'];
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            error_log("Impossible de supprimer le fichier: " . $filePath);
            // On continue même si le fichier n'a pas pu être supprimé
        }
    }

    // Supprimer l'entrée de la base de données
    $stmt = $pdo->prepare("DELETE FROM vehicle_images WHERE id = ?");
    if (!$stmt->execute([$imageId])) {
        throw new Exception('Erreur lors de la suppression de l\'image de la base de données');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Image supprimée avec succès'
    ]);

} catch (Exception $e) {
    error_log("Erreur dans delete-vehicle-image.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 