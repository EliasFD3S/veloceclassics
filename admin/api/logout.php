<?php
session_start();
session_destroy();

// Redirection vers la page de login avec le bon chemin
header('Location: /admin/public/login.html');
exit;
?> 