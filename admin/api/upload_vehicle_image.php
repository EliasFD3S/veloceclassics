<?php
require_once 'protect.php';
header('Content-Type: application/json');

function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        return "Le fichier doit être au format JPG, PNG ou WEBP";
    }
    if ($file['size'] > $maxSize) {
        return "Le fichier ne doit pas dépasser 5MB";
    }

    return null;
}

try {
    if (!isset($_FILES['image'])) {
        throw new Exception("Aucun fichier n'a été envoyé");
    }

    $error = validateImage($_FILES['image']);
    if ($error) {
        throw new Exception($error);
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/vehicles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        echo json_encode([
            'success' => true,
            'image_url' => '/assets/vehicles/' . $fileName
        ]);
    } else {
        throw new Exception("Erreur lors de l'upload du fichier");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 