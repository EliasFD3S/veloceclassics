<?php
// Désactiver la mise en tampon de sortie
ob_start();

require_once 'config.php';

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de véhicule invalide');
    }

    // Créer la connexion PDO
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
    } catch (PDOException $e) {
        error_log("Erreur de connexion PDO : " . $e->getMessage());
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Préparer et exécuter la requête
    try {
        $stmt = $pdo->prepare("SELECT id, vehicle_id, image_url FROM vehicle_images WHERE vehicle_id = ? ORDER BY position ASC");
        $stmt->execute([intval($_GET['id'])]);
        $images = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur de requête SQL : " . $e->getMessage());
        throw new Exception('Erreur lors de la récupération des images');
    }

    // Nettoyer tout le buffer
    ob_clean();

    // Définir les headers
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    // Si aucune image trouvée, renvoyer un tableau vide
    if (empty($images)) {
        echo json_encode([]);
        exit;
    }

    // Envoyer les données
    echo json_encode($images, 
        JSON_UNESCAPED_UNICODE | 
        JSON_UNESCAPED_SLASHES | 
        JSON_THROW_ON_ERROR
    );

} catch (Exception $e) {
    // Nettoyer le buffer en cas d'erreur
    ob_clean();
    
    // Logger l'erreur
    error_log("Erreur dans get-vehicle-images.php : " . $e->getMessage());
    
    // Envoyer l'erreur
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
}

// Terminer le script
exit;