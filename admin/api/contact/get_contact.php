<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../db.php';
    header('Content-Type: application/json');

    // Vérifier que le fichier db.php existe
    if (!file_exists(__DIR__ . '/../db.php')) {
        throw new Exception('Le fichier db.php n\'existe pas au chemin: ' . __DIR__ . '/../db.php');
    }

    // Obtenir la connexion à la base de données
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('La connexion à la base de données a échoué');
    }

    // Vérifier si la table existe
    try {
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'contact_info'");
        if ($tableCheck->rowCount() === 0) {
            // Créer la table si elle n'existe pas
            $createTable = "CREATE TABLE IF NOT EXISTS contact_info (
                id INT AUTO_INCREMENT PRIMARY KEY,
                telephone VARCHAR(20),
                email VARCHAR(255),
                linkedin_url VARCHAR(255),
                tiktok_url VARCHAR(255),
                instagram_url VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $pdo->exec($createTable);
            error_log("Table contact_info créée avec succès");
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification/création de la table: " . $e->getMessage());
        throw new Exception("Erreur lors de la création de la table: " . $e->getMessage());
    }

    // Sélectionner les données
    try {
        $stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($contact) {
            echo json_encode([
                'success' => true,
                'data' => $contact
            ]);
        } else {
            // Si aucune donnée n'existe, créer une entrée par défaut
            $insertStmt = $pdo->prepare("INSERT INTO contact_info (telephone, email, linkedin_url, tiktok_url, instagram_url) 
                                       VALUES (:phone, :email, :linkedin, :tiktok, :instagram)");
            
            $defaultData = [
                ':phone' => '+33123456789',
                ':email' => 'contact@veloceclassics.fr',
                ':linkedin' => 'https://www.linkedin.com/company/veloce-classics',
                ':tiktok' => 'https://www.tiktok.com/@veloceclassics',
                ':instagram' => 'https://www.instagram.com/veloceclassics'
            ];
            
            $insertStmt->execute($defaultData);
            error_log("Données par défaut insérées avec succès");
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'telephone' => $defaultData[':phone'],
                    'email' => $defaultData[':email'],
                    'linkedin_url' => $defaultData[':linkedin'],
                    'tiktok_url' => $defaultData[':tiktok'],
                    'instagram_url' => $defaultData[':instagram']
                ]
            ]);
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la manipulation des données: " . $e->getMessage());
        throw new Exception("Erreur lors de la manipulation des données: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log('Erreur dans get_contact.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
} 