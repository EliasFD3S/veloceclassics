<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Charger les variables d'environnement depuis .env
$env = parse_ini_file('../../.env');

try {
    // Connexion directe à la base de données
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
        $env['DB_USER'],
        $env['DB_PASSWORD']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des données de la section À propos
    $query = "SELECT * FROM about_section ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $about = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'data' => $about
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucune donnée trouvée'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données'
    ]);
    error_log($e->getMessage());
}
?> 