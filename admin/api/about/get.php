<?php
// Désactiver la mise en cache
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json');

// Afficher toutes les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Chemins absolus vers les fichiers requis
$dbPath = __DIR__ . '/../db.php';
$protectPath = __DIR__ . '/../protect.php';

// Vérifier si les fichiers requis existent
if (!file_exists($dbPath)) {
    die(json_encode(['success' => false, 'message' => "db.php non trouvé à $dbPath"]));
}
if (!file_exists($protectPath)) {
    die(json_encode(['success' => false, 'message' => "protect.php non trouvé à $protectPath"]));
}

// Inclure les fichiers
require_once $dbPath;
require_once $protectPath;

try {
    // Test de connexion à la base de données
    $pdo = getDBConnection();
    
    // Test simple pour voir si la table existe
    $tables = $pdo->query("SHOW TABLES LIKE 'about_section'")->fetchAll();
    if (empty($tables)) {
        die(json_encode([
            'success' => false,
            'message' => 'La table about_section n\'existe pas'
        ]));
    }
    
    // Requête simple
    $stmt = $pdo->prepare("SELECT * FROM about_section LIMIT 1");
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucune donnée trouvée',
            'debug' => [
                'query' => "SELECT * FROM about_section LIMIT 1",
                'rowCount' => $stmt->rowCount()
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} 