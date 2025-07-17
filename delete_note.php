<?php
include 'db.php';
include 'functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $noteId = intval($_POST['id']);
    $userId = $_SESSION['user_id'];
    // Notun kullanıcıya ait olup olmadığını kontrol et
    $stmt = $pdo->prepare('DELETE FROM notes WHERE id = ? AND user_id = ?');
    $success = $stmt->execute([$noteId, $userId]);
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Silinemedi.']);
    }
    exit;
}
echo json_encode(['success' => false, 'error' => 'Geçersiz istek.']); 