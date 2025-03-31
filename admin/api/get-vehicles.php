<?php
require_once 'db.php';
require_once 'protect.php';

header('Content-Type: application/json');

try {
    // Vérification de la connexion
    if (!isset($pdo)) {
        throw new Exception("Pas de connexion à la base de données");
    }

    // Préparer la clause WHERE si un ID est spécifié
    $where = '';
    $params = [];
    if (isset($_GET['id'])) {
        $vehicleId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($vehicleId === false) {
            throw new Exception('ID du véhicule invalide');
        }
        $where = 'WHERE v.id = ?';
        $params[] = $vehicleId;
    }

    // Requête avec jointure sur la table brands et vehicle_images
    $query = "
        SELECT 
            v.*,
            b.name as marque,
            b.logo_url as brand_logo_url,
            GROUP_CONCAT(DISTINCT vi.id) as image_ids,
            GROUP_CONCAT(DISTINCT vi.image_url) as image_urls
        FROM vehicles v
        LEFT JOIN brands b ON v.brand = b.id
        LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
        $where
        GROUP BY v.id
        ORDER BY v.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les données
    foreach ($vehicles as &$vehicle) {
        $images = [];
        if ($vehicle['image_ids'] && $vehicle['image_urls']) {
            $imageIds = explode(',', $vehicle['image_ids']);
            $imageUrls = explode(',', $vehicle['image_urls']);
            for ($i = 0; $i < count($imageIds); $i++) {
                $images[] = [
                    'id' => $imageIds[$i],
                    'image_url' => $imageUrls[$i]
                ];
            }
        }
        $vehicle['images'] = $images;
        
        unset($vehicle['image_ids']);
        unset($vehicle['image_urls']);
    }

    echo json_encode([
        'success' => true,
        'vehicles' => $vehicles
    ]);

} catch (Exception $e) {
    error_log("Erreur dans get-vehicles.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 