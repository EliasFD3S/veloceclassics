<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si le fichier .env existe
if (!file_exists('../.env')) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(['error' => 'Fichier .env non trouvé']));
}

// Charger les variables d'environnement
$env = parse_ini_file('../.env');

// Debug: Vérifier les informations de connexion (masquées pour la sécurité)
$debug_info = [
    'host' => $env['DB_HOST'],
    'dbname' => $env['DB_NAME'],
    'user' => str_repeat('*', strlen($env['DB_USER'])),
    'pass_length' => strlen($env['DB_PASSWORD'])
];

try {
    // Construction du DSN avec plus de paramètres de connexion
    $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";
    
    // Options de connexion étendues
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // Timeout de 5 secondes
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    // Tentative de connexion avec debug
    try {
        $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASSWORD'], $options);
        $debug_info['connection'] = 'Connexion réussie';
    } catch (PDOException $e) {
        $debug_info['connection_error'] = $e->getMessage();
        throw $e;
    }
    
    // Test de la connexion
    $pdo->query('SELECT 1');
    $debug_info['connection_test'] = 'Test de connexion réussi';
    
    // Requête sur contact_info
    $stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
    $contact = $stmt->fetch();
    
    if (!$contact) {
        $debug_info['query_result'] = 'Aucune donnée trouvée';
        throw new Exception('Aucune information de contact trouvée');
    }
    
    $debug_info['query_result'] = 'Données trouvées';
    
    header('Content-Type: application/json');
    echo json_encode([
        'data' => $contact,
        'debug' => $debug_info
    ]);
    
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => 'Erreur de connexion à la base de données',
        'details' => $e->getMessage(),
        'debug' => $debug_info
    ]);
} catch(Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => $e->getMessage(),
        'debug' => $debug_info
    ]);
} 