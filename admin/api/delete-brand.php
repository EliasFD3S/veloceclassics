<?php
ob_start();
require_once 'db.php';
require_once 'protect.php';

header('Content-Type: application/json');

try {
    // Vérifier si l'ID est fourni via POST
    if (!isset($_POST['id'])) {
        throw new Exception('ID de la marque non fourni');
    }

    $brandId = intval($_POST['id']);

    // Vérifier si la marque est utilisée par des véhicules
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE brand = ?");
    $stmt->execute([$brandId]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Cette marque ne peut pas être supprimée car elle est utilisée par des véhicules');
    }

    // Vérifier si la marque existe et récupérer le chemin du logo
    $stmt = $pdo->prepare("SELECT logo_url FROM brands WHERE id = ?");
    $stmt->execute([$brandId]);
    $brand = $stmt->fetch();

    if (!$brand) {
        throw new Exception('Marque non trouvée');
    }

    $pdo->beginTransaction();

    try {
        // Supprimer le logo du serveur
        if ($brand['logo_url'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $brand['logo_url'])) {
            if (!unlink($_SERVER['DOCUMENT_ROOT'] . $brand['logo_url'])) {
                throw new Exception('Erreur lors de la suppression du logo');
            }
        }

        // Supprimer la marque de la base de données
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
        if (!$stmt->execute([$brandId])) {
            throw new Exception('Erreur lors de la suppression de la marque');
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Marque supprimée avec succès'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Erreur lors de la suppression de la marque: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
?> 