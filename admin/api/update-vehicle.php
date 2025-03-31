<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $vehicleId = $_POST['id'];
    
    // Gérer les nouvelles images
    if (!empty($_FILES['images'])) {
        $uploadDir = '../../assets/vehicles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $uploadedImages = [];
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid('vehicle_') . '_' . basename($_FILES['images']['name'][$key]);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $filePath)) {
                    $uploadedImages[] = [
                        'name' => $fileName,
                        'url' => '/assets/vehicles/' . $fileName
                    ];
                }
            }
        }
    }
    
    // Mettre à jour le véhicule
    $stmt = $pdo->prepare("
        UPDATE vehicles 
        SET brand = ?, modele = ?, annee = ?, cout_fixe = ?, description = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $_POST['brand'],
        $_POST['modele'],
        $_POST['annee'],
        $_POST['cout_fixe'],
        $_POST['description'],
        $vehicleId
    ]);
    
    // Ajouter les nouvelles images
    if (!empty($uploadedImages)) {
        $stmtImages = $pdo->prepare("
            INSERT INTO vehicle_images (vehicle_id, image_url)
            VALUES (?, ?)
        ");
        
        foreach ($uploadedImages as $image) {
            $stmtImages->execute([$vehicleId, $image['url']]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Véhicule mis à jour avec succès'
    ]);

} catch (Exception $e) {
    error_log("Erreur lors de la mise à jour du véhicule: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la mise à jour du véhicule: ' . $e->getMessage()
    ]);
} 