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
        $status = clean($_POST['status'] ?? 'tamamlanmadı');
        $category = isset($_POST['category']) && $_POST['category'] !== '' ? (int)$_POST['category'] : null;
        $tagsRaw = $_POST['tags'] ?? '';

        if ($title) {
            // Notu ekle
            if ($category) {
                $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content, status, category_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $title, $content, $status, $category]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $title, $content, $status]);
            }
            $noteId = $pdo->lastInsertId();

            // Etiket işlemleri
            if (trim($tagsRaw) !== '') {
                $tagsArr = array_filter(array_map('trim', explode(',', $tagsRaw)));
                foreach ($tagsArr as $tagName) {
                    if ($tagName === '') continue;
                    // Etiket var mı kontrol et
                    $stmt = $pdo->prepare("SELECT id FROM tags WHERE user_id = ? AND name = ?");
                    $stmt->execute([$_SESSION['user_id'], $tagName]);
                    $tag = $stmt->fetch();
                    if ($tag) {
                        $tagId = $tag['id'];
                    } else {
                        // Yoksa ekle
                        $stmt = $pdo->prepare("INSERT INTO tags (user_id, name) VALUES (?, ?)");
                        $stmt->execute([$_SESSION['user_id'], $tagName]);
                        $tagId = $pdo->lastInsertId();
                    }
                    // note_tags ilişkisini ekle
                    $stmt = $pdo->prepare("INSERT IGNORE INTO note_tags (note_id, tag_id) VALUES (?, ?)");
                    $stmt->execute([$noteId, $tagId]);
                }
            }

            echo json_encode(['success' => true, 'id' => $noteId]);
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
