<?php
session_start();
include '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_GET['set_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de set manquant']);
    exit;
}

$set_id = $_GET['set_id'];

// Récupérer notes et commentaires avec les infos utilisateur
$stmt = $db->prepare("
    SELECT r.rating, r.comment, u.username, DATE_FORMAT(r.created_at, '%d/%m/%Y') AS date
    FROM SAE203_reviews r
    JOIN SAE203_user u ON r.user_id = u.id
    WHERE r.set_id = :set_id
    ORDER BY r.created_at DESC
");
$stmt->execute(['set_id' => $set_id]);
$reviews = $stmt->fetchAll();

if (!$reviews) {
    echo json_encode(['success' => true, 'avg_rating' => 0, 'count' => 0, 'comments' => []]);
    exit;
}

$count = count($reviews);
$sum = 0;
foreach ($reviews as $r) {
    $sum += $r['rating'];
}
$avg = round($sum / $count, 2);

echo json_encode([
    'success' => true,
    'avg_rating' => $avg,
    'count' => $count,
    'comments' => $reviews
]);
