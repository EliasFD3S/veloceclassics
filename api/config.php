<?php
// Supprimer les headers JSON car ce fichier ne doit pas envoyer de réponse
// header('Content-Type: application/json');
// header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    if (!file_exists('../.env')) {
        throw new Exception('Fichier .env non trouvé');
    }

    $dotenv = parse_ini_file('../.env');
    if ($dotenv === false) {
        throw new Exception('Erreur de lecture du fichier .env');
    }

    // Définir les variables sans faire d'output
    if (isset($dotenv['API_GOOGLE'])) {
        define('API_GOOGLE_KEY', trim($dotenv['API_GOOGLE']));
    }

    // Définir les autres variables nécessaires
    define('DB_HOST', $dotenv['DB_HOST'] ?? '');
    define('DB_NAME', $dotenv['DB_NAME'] ?? '');
    define('DB_USER', $dotenv['DB_USER'] ?? '');
    define('DB_PASSWORD', $dotenv['DB_PASSWORD'] ?? '');

} catch (Exception $e) {
    // Logger l'erreur au lieu de l'afficher
    error_log('Erreur de configuration : ' . $e->getMessage());
    throw $e; // Relancer l'exception pour la gestion d'erreur
}
?> 