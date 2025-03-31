<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../db.php';
    header('Content-Type: application/json');

    // Obtenir la connexion à la base de données
    $pdo = getDBConnection();
    
    // Vérifier si la table existe
    try {
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'constants'");
        if ($tableCheck->rowCount() === 0) {
            // Créer la table si elle n'existe pas
            $createTable = "CREATE TABLE IF NOT EXISTS constants (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                value DECIMAL(10,2) NOT NULL,
                description VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $pdo->exec($createTable);
            error_log("Table constants créée avec succès");
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification/création de la table: " . $e->getMessage());
        throw new Exception("Erreur lors de la création de la table: " . $e->getMessage());
    }

    // Sélectionner les données
    try {
        $stmt = $pdo->query("SELECT * FROM constants");
        $constants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($constants) {
            echo json_encode([
                'success' => true,
                'data' => $constants
            ]);
        } else {
            // Si aucune donnée n'existe, créer les entrées par défaut
            $defaultConstants = [
                ['PRIX_KM', 1.50, 'Prix par kilomètre en euros'],
                ['COUT_CARBURANT', 2.20, 'Coût du carburant par litre en euros'],
                ['CONSOMMATION_MOYENNE', 12.00, 'Consommation moyenne en L/100km'],
                ['FRAIS_USURE', 15.00, 'Frais fixes d\'usure en euros']
            ];
            
            $insertStmt = $pdo->prepare("INSERT INTO constants (name, value, description) VALUES (?, ?, ?)");
            
            foreach ($defaultConstants as $constant) {
                $insertStmt->execute($constant);
            }
            
            echo json_encode([
                'success' => true,
                'data' => array_map(function($constant) {
                    return [
                        'name' => $constant[0],
                        'value' => $constant[1],
                        'description' => $constant[2]
                    ];
                }, $defaultConstants)
            ]);
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la manipulation des données: " . $e->getMessage());
        throw new Exception("Erreur lors de la manipulation des données: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log('Erreur dans get_constants.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
} 