<?php
header('Content-Type: application/json');

// Charger les variables d'environnement depuis .env
$env = parse_ini_file('../.env');

try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
        $env['DB_USER'],
        $env['DB_PASSWORD']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT name, value FROM constants");
    $constants = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    echo json_encode($constants);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données']);
    error_log($e->getMessage());
}
?>