<?php
require_once __DIR__ . '/../../api/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    // Obtenir la connexion à la base de données
    $pdo = getDBConnection();
    
    // Récupérer les données du formulaire
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Données invalides');
    }

    // Préparer la requête de mise à jour
    $sql = "UPDATE contact_info SET 
            telephone = :telephone,
            email = :email,
            linkedin_url = :linkedin_url,
            tiktok_url = :tiktok_url,
            instagram_url = :instagram_url,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = 1";
            
    $stmt = $pdo->prepare($sql);
    
    // Exécuter la requête avec les nouvelles valeurs
    $result = $stmt->execute([
        ':telephone' => $data['phone'],
        ':email' => $data['email'],
        ':linkedin_url' => $data['linkedin'],
        ':tiktok_url' => $data['tiktok'],
        ':instagram_url' => $data['instagram']
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Informations de contact mises à jour avec succès'
        ]);
    } else {
        throw new Exception('Erreur lors de la mise à jour des informations');
    }
} catch (Exception $e) {
    error_log('Erreur dans update_contact.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
} 