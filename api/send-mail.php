<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Charger les variables d'environnement
$env = parse_ini_file('../.env');

// Récupérer les données du formulaire
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier les données requises
if (!isset($data['name']) || !isset($data['email']) || !isset($data['subject']) || !isset($data['message'])) {
    header('HTTP/1.1 400 Bad Request');
    die(json_encode(['error' => 'Données manquantes']));
}

try {
    // Récupérer l'email de destination depuis la base de données
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
        $env['DB_USER'],
        $env['DB_PASSWORD']
    );
    
    $stmt = $pdo->query("SELECT email FROM contact_info LIMIT 1");
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    $to_email = $contact['email'];

    // Préparer l'email
    $to = $to_email;
    $subject = "Nouveau message de contact - " . htmlspecialchars($data['subject']);
    
    // Corps du message
    $message = "
    <html>
    <head>
        <title>Nouveau message de contact</title>
    </head>
    <body>
        <h2>Nouveau message de contact reçu</h2>
        <p><strong>De:</strong> " . htmlspecialchars($data['name']) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($data['email']) . "</p>
        <p><strong>Sujet:</strong> " . htmlspecialchars($data['subject']) . "</p>
        <p><strong>Message:</strong></p>
        <p>" . nl2br(htmlspecialchars($data['message'])) . "</p>
    </body>
    </html>
    ";

    // En-têtes pour l'email HTML
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . $data['email'],
        'Reply-To: ' . $data['email'],
        'X-Mailer: PHP/' . phpversion()
    ];

    // Envoi de l'email
    if(mail($to, $subject, $message, implode("\r\n", $headers))) {
        echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès']);
    } else {
        throw new Exception('Échec de l\'envoi du message');
    }

} catch(Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
} 