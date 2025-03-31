<?php
function generateVersionNumber() {
    return time(); // Utilise le timestamp actuel comme numéro de version
}

function updateVersionFile() {
    $version = generateVersionNumber();
    file_put_contents(__DIR__ . '/version.txt', $version);
    return $version;
}

// Appeler cette fonction pour obtenir la version actuelle
function getVersion() {
    if (!file_exists(__DIR__ . '/version.txt')) {
        return updateVersionFile();
    }
    return file_get_contents(__DIR__ . '/version.txt');
} 