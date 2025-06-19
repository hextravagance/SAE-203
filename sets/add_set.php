<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$username = $_SESSION['username'];

// Récupération de l'id user
$stmt = $db->prepare("SELECT id FROM SAE203_user WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
    exit;
}

$id_user = $user['id'];

// Lecture des données JSON POST
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!$input || !isset($input['type']) || !isset($input['set_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données manquantes ou invalides']);
    exit;
}

$type = $input['type'];
$set_id = $input['set_id'];

if (!in_array($type, ['wishlist', 'owned'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Type invalide']);
    exit;
}

// Vérifier que le set existe bien dans lego_db
$stmt = $db->prepare("SELECT COUNT(*) FROM lego_db WHERE id_set_number = :set_id");
$stmt->execute(['set_id' => $set_id]);
if ($stmt->fetchColumn() == 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Set introuvable']);
    exit;
}

// Définir la table cible
$table = $type === 'wishlist' ? 'SAE203_wishlisted' : 'SAE203_owned';

// Vérifier si le set est déjà ajouté (pour éviter doublons)
$stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE id_user = :id_user AND id_set_number = :set_id");
$stmt->execute(['id_user' => $id_user, 'set_id' => $set_id]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Set déjà ajouté']);
    exit;
}

// Insérer avec quantity = 1 par défaut
try {
    $stmt = $db->prepare("INSERT INTO $table (id_user, id_set_number, quantity) VALUES (:id_user, :set_id, 1)");
    $success = $stmt->execute(['id_user' => $id_user, 'set_id' => $set_id]);
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $errorInfo[2]]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Exception SQL : ' . $e->getMessage()]);
}
