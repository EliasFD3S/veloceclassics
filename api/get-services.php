<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Charger les variables d'environnement
    $env = parse_ini_file(__DIR__ . '/../.env');
    if (!$env) {
        throw new Exception("Impossible de charger le fichier .env");
    }

    // Établir la connexion à la base de données
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
        $env['DB_USER'],
        $env['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    // Requête modifiée sans la condition WHERE active = 1
    $query = "SELECT id, name, description, price FROM services ORDER BY name";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($services === false) {
        throw new Exception("Erreur lors de la récupération des services");
    }
    
    echo json_encode($services, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
