<?php
// Désactiver la mise en cache
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json');

// Afficher toutes les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../db.php';
require_once '../protect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();  // Utiliser la bonne fonction
    
    // Récupérer les données du formulaire
    $main_text = $_POST['main_text'] ?? '';
    $feature_1_title = $_POST['feature_1_title'] ?? '';
    $feature_1_description = $_POST['feature_1_description'] ?? '';
    $feature_1_icon = $_POST['feature_1_icon'] ?? '';
    $feature_2_title = $_POST['feature_2_title'] ?? '';
    $feature_2_description = $_POST['feature_2_description'] ?? '';
    $feature_2_icon = $_POST['feature_2_icon'] ?? '';
    $feature_3_title = $_POST['feature_3_title'] ?? '';
    $feature_3_description = $_POST['feature_3_description'] ?? '';
    $feature_3_icon = $_POST['feature_3_icon'] ?? '';

    // Vérifier si des données existent déjà
    $stmt = $pdo->query("SELECT id FROM about_section LIMIT 1");
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Mise à jour des données existantes
        $sql = "UPDATE about_section SET 
                main_text = :main_text,
                feature_1_title = :feature_1_title,
                feature_1_description = :feature_1_description,
                feature_1_icon = :feature_1_icon,
                feature_2_title = :feature_2_title,
                feature_2_description = :feature_2_description,
                feature_2_icon = :feature_2_icon,
                feature_3_title = :feature_3_title,
                feature_3_description = :feature_3_description,
                feature_3_icon = :feature_3_icon,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $existing['id'],
            'main_text' => $main_text,
            'feature_1_title' => $feature_1_title,
            'feature_1_description' => $feature_1_description,
            'feature_1_icon' => $feature_1_icon,
            'feature_2_title' => $feature_2_title,
            'feature_2_description' => $feature_2_description,
            'feature_2_icon' => $feature_2_icon,
            'feature_3_title' => $feature_3_title,
            'feature_3_description' => $feature_3_description,
            'feature_3_icon' => $feature_3_icon
        ]);
    } else {
        // Insertion de nouvelles données
        $sql = "INSERT INTO about_section (
                main_text,
                feature_1_title, feature_1_description, feature_1_icon,
                feature_2_title, feature_2_description, feature_2_icon,
                feature_3_title, feature_3_description, feature_3_icon
            ) VALUES (
                :main_text,
                :feature_1_title, :feature_1_description, :feature_1_icon,
                :feature_2_title, :feature_2_description, :feature_2_icon,
                :feature_3_title, :feature_3_description, :feature_3_icon
            )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'main_text' => $main_text,
            'feature_1_title' => $feature_1_title,
            'feature_1_description' => $feature_1_description,
            'feature_1_icon' => $feature_1_icon,
            'feature_2_title' => $feature_2_title,
            'feature_2_description' => $feature_2_description,
            'feature_2_icon' => $feature_2_icon,
            'feature_3_title' => $feature_3_title,
            'feature_3_description' => $feature_3_description,
            'feature_3_icon' => $feature_3_icon
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Données mises à jour avec succès'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} 