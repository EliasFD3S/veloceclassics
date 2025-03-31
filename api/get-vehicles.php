<?php
// Nettoyer tous les buffers de sortie au début
while (ob_get_level()) {
    ob_end_clean();
}

$dotenv = parse_ini_file('../.env');

try {
    $pdo = new PDO("mysql:host={$dotenv['DB_HOST']};dbname={$dotenv['DB_NAME']}", 
                   $dotenv['DB_USER'], 
                   $dotenv['DB_PASSWORD'],
                   array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $query = "SELECT DISTINCT
                v.id,
                v.modele,
                v.annee,
                v.cout_fixe,
                v.description,
                b.name as brand_name,
                b.logo_url,
                (SELECT image_url FROM vehicle_images 
                 WHERE vehicle_id = v.id 
                 ORDER BY position ASC 
                 LIMIT 1) as image_url
              FROM vehicles v 
              LEFT JOIN brands b ON v.brand = b.id";
              
    $stmt = $pdo->query($query);
    $vehicles = $stmt->fetchAll();

    // Définir les en-têtes avant toute sortie
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    // S'assurer qu'il n'y a pas d'espace ou de caractère avant l'encodage JSON
    echo json_encode($vehicles, 
        JSON_UNESCAPED_UNICODE | 
        JSON_UNESCAPED_SLASHES | 
        JSON_NUMERIC_CHECK
    );

} catch(Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}

exit();
?> 