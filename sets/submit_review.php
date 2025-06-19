<?php
session_start();
include '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$set_id = $data['set_id'] ?? null;
$rating = intval($data['rating'] ?? 0);
$comment = trim($data['comment'] ?? '');

if (!$set_id || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$username = $_SESSION['username'];
$stmt = $db->prepare("SELECT id FROM SAE203_user WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
    exit;
}

$id_user = $user['id'];

// Vérifier si l'utilisateur a déjà noté ce set (optionnel)
// $checkStmt = $db->prepare("SELECT id FROM SAE203_reviews WHERE user_id = :user_id AND set_id = :set_id");
// $checkStmt->execute(['user_id' => $id_user, 'set_id' => $set_id]);
// if ($checkStmt->fetch()) {
//     echo json_encode(['success' => false, 'message' => 'Vous avez déjà noté ce set.']);
//     exit;
// }

$stmt = $db->prepare("INSERT INTO SAE203_reviews (user_id, set_id, rating, comment, created_at) VALUES (:user_id, :set_id, :rating, :comment, NOW())");
$res = $stmt->execute([
    'user_id' => $id_user,
    'set_id' => $set_id,
    'rating' => $rating,
    'comment' => $comment,
]);

if ($res) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
}
