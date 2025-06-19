<?php
session_start();
include '../includes/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['set_id'], $data['user_id'], $data['rating'])) {
    echo json_encode(['success' => false, 'error' => 'Données incomplètes']);
    exit;
}

$set_id = $data['set_id'];
$user_id = $data['user_id'];
$rating = intval($data['rating']);
$comment = isset($data['comment']) ? trim($data['comment']) : '';

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Note invalide']);
    exit;
}

// Vérifier si l'utilisateur a déjà noté ce set
$stmt = $db->prepare("SELECT id FROM reviews WHERE set_id = :set_id AND user_id = :user_id");
$stmt->execute(['set_id' => $set_id, 'user_id' => $user_id]);
$existing = $stmt->fetch();

if ($existing) {
    // Mise à jour de l'avis
    $stmt = $db->prepare("UPDATE reviews SET rating = :rating, comment = :comment, created_at = NOW() WHERE id = :id");
    $stmt->execute(['rating' => $rating, 'comment' => $comment, 'id' => $existing['id']]);
} else {
    // Insertion nouvelle note
    $stmt = $db->prepare("INSERT INTO reviews (set_id, user_id, rating, comment, created_at) VALUES (:set_id, :user_id, :rating, :comment, NOW())");
    $stmt->execute(['set_id' => $set_id, 'user_id' => $user_id, 'rating' => $rating, 'comment' => $comment]);
}

echo json_encode(['success' => true]);
