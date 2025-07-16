<?php
include 'db.php';
include 'functions.php';
requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_notes':
        $stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'add_note':
        $title = clean($_POST['title'] ?? '');
        $content = clean($_POST['content'] ?? '');

        if ($title) {
            $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $content]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'update_note':
        $noteId = (int)($_POST['id'] ?? 0);
        $title = clean($_POST['title'] ?? '');
        $content = clean($_POST['content'] ?? '');

        $stmt = $pdo->prepare("UPDATE notes SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $success = $stmt->execute([$title, $content, $noteId, $_SESSION['user_id']]);
        echo json_encode(['success' => $success]);
        break;

    case 'delete_note':
        $noteId = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        $success = $stmt->execute([$noteId, $_SESSION['user_id']]);
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['error' => 'Geçersiz istek.']);
        break;
}
