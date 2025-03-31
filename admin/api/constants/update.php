<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../db.php';
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Méthode non autorisée'
        ]);
        exit;
    }

    // Obtenir la connexion à la base de données
    $pdo = getDBConnection();
    
    // Récupérer les données du formulaire
    $data = $_POST;

    if (!$data) {
        throw new Exception('Données invalides');
    }

    // Liste des constantes attendues
    $expectedConstants = ['PRIX_KM', 'COUT_CARBURANT', 'CONSOMMATION_MOYENNE', 'FRAIS_USURE'];
    
    // Préparer la requête de mise à jour
    $stmt = $pdo->prepare("UPDATE constants SET value = :value, updated_at = CURRENT_TIMESTAMP WHERE name = :name");
    
    // Mettre à jour chaque constante
    foreach ($expectedConstants as $constantName) {
        if (isset($data[$constantName])) {
            $value = filter_var($data[$constantName], FILTER_VALIDATE_FLOAT);
            
            if ($value === false || $value < 0) {
                throw new Exception("Valeur invalide pour $constantName");
            }
            
            $stmt->execute([
                ':name' => $constantName,
                ':value' => $value
            ]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Constantes mises à jour avec succès'
    ]);

} catch (Exception $e) {
    error_log('Erreur dans update_constants.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
} 