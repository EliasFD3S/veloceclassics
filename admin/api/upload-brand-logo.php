<?php
session_start();
require_once __DIR__ . '/protect.php';

// Activation du logging détaillé
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/upload_debug.log');

function debug_log($message, $data = null) {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $log .= " - Data: " . print_r($data, true);
    }
    error_log($log);
}

header('Content-Type: application/json');

try {
    // Log de l'état de la session
    debug_log("État de la session", [
        'session_id' => session_id(),
        'session_status' => session_status(),
        'session_data' => $_SESSION,
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'non défini',
        'files' => $_FILES
    ]);

    // Vérification de la session
    if (!isLoggedIn()) {
        debug_log("Session invalide - SESSION:", $_SESSION);
        throw new Exception('Session expirée');
    }

    // Vérification du fichier
    if (!isset($_FILES['logo'])) {
        debug_log("Aucun fichier reçu - FILES:", $_FILES);
        throw new Exception('Aucun fichier reçu');
    }

    if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier'
        ];
        throw new Exception($errors[$_FILES['logo']['error']] ?? 'Erreur inconnue lors de l\'upload');
    }

    $file = $_FILES['logo'];
    debug_log("Informations du fichier", $file);

    // Vérification du type MIME réel
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    debug_log("Type MIME détecté", $mimeType);

    if ($mimeType !== 'image/png') {
        throw new Exception('Le fichier doit être au format PNG (type détecté: ' . $mimeType . ')');
    }

    // Création du chemin de destination
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/brands/';
    if (!file_exists($uploadDir)) {
        debug_log("Création du répertoire", $uploadDir);
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Impossible de créer le répertoire de destination');
        }
    }

    // Vérification des permissions
    if (!is_writable($uploadDir)) {
        debug_log("Problème de permissions", [
            'directory' => $uploadDir,
            'permissions' => substr(sprintf('%o', fileperms($uploadDir)), -4)
        ]);
        throw new Exception('Le répertoire de destination n\'est pas accessible en écriture');
    }

    // Génération du nom de fichier
    $uniqueName = uniqid('brand_', true) . '.png';
    $uploadPath = $uploadDir . $uniqueName;
    debug_log("Chemin de destination", $uploadPath);

    // Déplacement du fichier
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        debug_log("Erreur lors du déplacement", [
            'from' => $file['tmp_name'],
            'to' => $uploadPath,
            'permissions' => substr(sprintf('%o', fileperms($uploadDir)), -4)
        ]);
        throw new Exception('Erreur lors du déplacement du fichier');
    }

    debug_log("Upload réussi", $uploadPath);

    // Retour du chemin relatif
    $relativePath = '/assets/brands/' . $uniqueName;
    echo json_encode([
        'success' => true,
        'logo_url' => $relativePath
    ]);

} catch (Exception $e) {
    debug_log("ERREUR: " . $e->getMessage());
    debug_log("Trace complète: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'session_id' => session_id(),
            'session_status' => session_status(),
            'request_method' => $_SERVER['REQUEST_METHOD']
        ]
    ]);
} 