<?php
function getDBConnection() {
    try {
        // Charger les variables d'environnement depuis .env
        $envFile = __DIR__ . '/../../.env';
        if (!file_exists($envFile)) {
            throw new Exception('Le fichier .env n\'existe pas');
        }

        $env = parse_ini_file($envFile);
        if ($env === false) {
            throw new Exception('Impossible de lire le fichier .env');
        }

        // Configuration de la base de données depuis .env
        $host = $env['DB_HOST'];
        $dbname = $env['DB_NAME'];
        $username = $env['DB_USER'];
        $password = $env['DB_PASSWORD'];

        // Options PDO
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        // Création de la connexion
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            $options
        );

        return $pdo;

    } catch (PDOException $e) {
        error_log('Erreur de connexion à la base de données: ' . $e->getMessage());
        throw new Exception('Erreur de connexion à la base de données');
    } catch (Exception $e) {
        error_log('Erreur de configuration: ' . $e->getMessage());
        throw new Exception('Erreur de configuration de la base de données');
    }
}

// Vérification que la table 'brands' existe
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SHOW TABLES LIKE 'brands'");
    if ($stmt->rowCount() === 0) {
        // La table n'existe pas, on la crée
        $sql = "CREATE TABLE IF NOT EXISTS brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            logo_url VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($sql);
        error_log("Table 'brands' créée avec succès");
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la vérification/création de la table: " . $e->getMessage());
    throw new Exception("Erreur lors de la configuration de la base de données");
} 