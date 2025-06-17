<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=nicolas.rapuzzi;charset=utf8', 'nicolas.rapuzzi', 'D]6LYvSr7hT40fF[
');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}
?>