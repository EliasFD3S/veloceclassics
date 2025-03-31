<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    if (!file_exists(__DIR__ . '/../.env')) {
        throw new Exception('Fichier .env non trouvé');
    }

    $dotenv = parse_ini_file(__DIR__ . '/../.env');
    
    if (!isset($_GET['vehicle_id'])) {
        throw new Exception('ID du véhicule non fourni');
    }

    $vehicleId = intval($_GET['vehicle_id']);

    $pdo = new PDO(
        "mysql:host={$dotenv['DB_HOST']};dbname={$dotenv['DB_NAME']};charset=utf8mb4",
        $dotenv['DB_USER'],
        $dotenv['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Récupérer d'abord les images de la table vehicle_images
    $stmt = $pdo->prepare("SELECT image_url, position FROM vehicle_images WHERE vehicle_id = ? ORDER BY position ASC");
    $stmt->execute([$vehicleId]);
    $images = $stmt->fetchAll();

    // Si aucune image n'est trouvée dans vehicle_images, vérifier le champ images JSON de la table vehicles
    if (empty($images)) {
        $stmt = $pdo->prepare("SELECT images FROM vehicles WHERE id = ?");
        $stmt->execute([$vehicleId]);
        $vehicle = $stmt->fetch();
        
        if ($vehicle && $vehicle['images']) {
            $imagesArray = json_decode($vehicle['images'], true);
            if (is_array($imagesArray)) {
                $images = array_map(function($url, $index) {
                    return [
                        'image_url' => $url,
                        'position' => $index
                    ];
                }, $imagesArray, array_keys($imagesArray));
            }
        }
    }

    echo json_encode([
        'success' => true,
        'images' => $images ?? []
    ], JSON_THROW_ON_ERROR);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_THROW_ON_ERROR);
}
?> 