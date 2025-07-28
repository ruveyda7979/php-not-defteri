<?php
include 'db.php';
include 'functions.php';
requireLogin();

header('Content-Type: application/json');

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['id'], $_POST['title'], $_POST['content'])
) {
    $noteId = intval($_POST['id']);
    $userId = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $status = trim($_POST['status'] ?? 'tamamlanmadı');
    // Notun kullanıcıya ait olup olmadığını kontrol et ve güncelle
    $stmt = $pdo->prepare('UPDATE notes SET title = ?, content = ?, status = ? WHERE id = ? AND user_id = ?');
    $success = $stmt->execute([$title, $content, $status, $noteId, $userId]);
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Güncellenemedi.']);
    }
    exit;
}
echo json_encode(['success' => false, 'error' => 'Geçersiz istek.']); 