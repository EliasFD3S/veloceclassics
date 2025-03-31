<?php
header('Content-Type: application/json');

try {
    // Charger les variables d'environnement
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) {
        throw new Exception('Fichier .env non trouvé');
    }

    $env = parse_ini_file($envFile);
    
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
        $env['DB_USER'],
        $env['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Requête pour récupérer les véhicules avec leurs marques
    $query = "
        SELECT 
            v.id,
            v.modele,
            v.annee,
            v.cout_fixe,
            b.name as brand_name
        FROM vehicles v
        INNER JOIN brands b ON v.brand = b.id
        ORDER BY b.name, v.modele
    ";

    $stmt = $pdo->query($query);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les données pour le select
    $formatted_vehicles = array_map(function($vehicle) {
        return [
            'id' => $vehicle['id'],
            'name' => "{$vehicle['brand_name']} {$vehicle['modele']} ({$vehicle['annee']}) - {$vehicle['cout_fixe']}€",
            'price' => $vehicle['cout_fixe']
        ];
    }, $vehicles);

    echo json_encode($formatted_vehicles);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur serveur',
        'message' => $e->getMessage()
    ]);
} 