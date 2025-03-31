<?php
header('Content-Type: application/json');

try {
    // Charger les variables d'environnement
    if (!file_exists(__DIR__ . '/../.env')) {
        throw new Exception('Fichier .env non trouvé');
    }

    $dotenv = parse_ini_file(__DIR__ . '/../.env');
    if ($dotenv === false) {
        throw new Exception('Erreur de lecture du fichier .env');
    }

    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host={$dotenv['DB_HOST']};dbname={$dotenv['DB_NAME']};charset=utf8mb4",
        $dotenv['DB_USER'],
        $dotenv['DB_PASSWORD'],
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    // Récupération des slides
    $stmt = $pdo->query("SELECT * FROM carousel_slides WHERE active = 1 ORDER BY position ASC");
    $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'slides' => $slides
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?> 