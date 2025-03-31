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

    // Requête adaptée à la structure exacte de la base de données
    $query = "
        SELECT 
            v.id,
            v.modele,
            v.annee,
            v.cout_fixe,
            v.description,
            v.images as vehicle_images,
            b.name as brand_name,
            b.logo_url as brand_logo
        FROM vehicles v
        INNER JOIN brands b ON v.brand = b.id
    ";

    $stmt = $pdo->query($query);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les données en respectant la structure JSON
    $formatted_vehicles = array_map(function($vehicle) {
        $images = json_decode($vehicle['vehicle_images'], true) ?? [];
        return [
            'id' => $vehicle['id'],
            'brand' => $vehicle['brand_name'],
            'model' => $vehicle['modele'],
            'year' => $vehicle['annee'],
            'price' => $vehicle['cout_fixe'],
            'description' => $vehicle['description'],
            'images' => $images,
            'brand_logo' => $vehicle['brand_logo']
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