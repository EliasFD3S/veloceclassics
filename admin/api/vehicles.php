<?php
require_once 'db.php';
require_once 'protect.php';

// Activer le rapport d'erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    // Récupérer la méthode HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch($method) {
        case 'GET':
            // Récupérer un véhicule spécifique
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("
                    SELECT v.*, GROUP_CONCAT(vi.image_url) as images 
                    FROM vehicles v 
                    LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id 
                    WHERE v.id = ? 
                    GROUP BY v.id
                ");
                $stmt->execute([$_GET['id']]);
                $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($vehicle) {
                    // Convertir la chaîne d'images en tableau
                    $vehicle['images'] = $vehicle['images'] ? explode(',', $vehicle['images']) : [];
                    
                    echo json_encode([
                        'success' => true,
                        'vehicle' => $vehicle
                    ]);
                } else {
                    throw new Exception("Véhicule non trouvé");
                }
            }
            break;

        case 'POST':
            // Vérification des données requises
            if (empty($_POST['brand'])) {
                throw new Exception("Le champ 'brand' est requis");
            }
            if (empty($_POST['modele'])) {
                throw new Exception("Le champ 'modele' est requis");
            }
            if (empty($_POST['annee'])) {
                throw new Exception("Le champ 'annee' est requis");
            }
            if (!isset($_POST['cout_fixe'])) {
                throw new Exception("Le champ 'cout_fixe' est requis");
            }

            $pdo->beginTransaction();
            
            try {
                // Si un ID est fourni, c'est une mise à jour
                if (isset($_POST['vehicle_id'])) {
                    $sql = "UPDATE vehicles 
                            SET brand = :brand, 
                                modele = :modele, 
                                annee = :annee, 
                                cout_fixe = :cout_fixe, 
                                description = :description
                            WHERE id = :id";
                    
                    $stmt = $pdo->prepare($sql);
                    $success = $stmt->execute([
                        ':brand' => $_POST['brand'],
                        ':modele' => $_POST['modele'],
                        ':annee' => $_POST['annee'],
                        ':cout_fixe' => $_POST['cout_fixe'],
                        ':description' => $_POST['description'] ?? '',
                        ':id' => $_POST['vehicle_id']
                    ]);

                    // Gérer les images si nécessaire
                    if (isset($_POST['image_urls']) && is_array($_POST['image_urls'])) {
                        // Supprimer les anciennes images
                        $stmt = $pdo->prepare("DELETE FROM vehicle_images WHERE vehicle_id = ?");
                        $stmt->execute([$_POST['vehicle_id']]);

                        // Ajouter les nouvelles images
                        $stmt = $pdo->prepare("INSERT INTO vehicle_images (vehicle_id, image_url) VALUES (?, ?)");
                        foreach ($_POST['image_urls'] as $imageUrl) {
                            $stmt->execute([$_POST['vehicle_id'], $imageUrl]);
                        }
                    }

                    $message = 'Véhicule modifié avec succès';
                    $vehicleId = $_POST['vehicle_id'];
                } else {
                    // Code existant pour l'insertion...
                    $sql = "INSERT INTO vehicles (brand, modele, annee, cout_fixe, description) 
                            VALUES (:brand, :modele, :annee, :cout_fixe, :description)";
                    
                    $stmt = $pdo->prepare($sql);
                    $success = $stmt->execute([
                        ':brand' => $_POST['brand'],
                        ':modele' => $_POST['modele'],
                        ':annee' => $_POST['annee'],
                        ':cout_fixe' => $_POST['cout_fixe'],
                        ':description' => $_POST['description'] ?? ''
                    ]);

                    $vehicleId = $pdo->lastInsertId();
                    $message = 'Véhicule ajouté avec succès';
                }

                if (!$success) {
                    throw new Exception("Erreur lors de l'opération sur le véhicule");
                }

                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'vehicleId' => $vehicleId
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'DELETE':
            // Récupérer l'ID depuis l'URL pour DELETE
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception("ID du véhicule requis pour la suppression");
            }

            $pdo->beginTransaction();
            try {
                // Supprimer d'abord les images associées si nécessaire
                $stmt = $pdo->prepare("DELETE FROM vehicle_images WHERE vehicle_id = ?");
                $stmt->execute([$id]);

                // Supprimer le véhicule
                $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
                $success = $stmt->execute([$id]);

                if (!$success) {
                    throw new Exception("Erreur lors de la suppression du véhicule");
                }

                $pdo->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Véhicule supprimé avec succès'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        default:
            throw new Exception("Méthode HTTP non supportée");
    }

} catch (Exception $e) {
    error_log("Erreur dans vehicles.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 