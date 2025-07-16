<?php
include 'db.php';
include 'functions.php';
requireLogin();

// Notları veritabanından al
$stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Notlarım</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-dark px-3">
    <span class="navbar-brand mb-0 h1">Hoş geldin, <?= $_SESSION['username'] ?>!</span>
    <a href="logout.php" class="btn btn-light btn-sm">Çıkış</a>
</nav>

<div class="container">
    <h3 class="mt-4 mb-3">Notlarım</h3>

    <div class="mb-4">
        <input type="text" id="noteTitle" class="form-control mb-2" placeholder="Not Başlığı">
        <textarea id="noteContent" class="form-control mb-2" placeholder="Not İçeriği"></textarea>
        <button id="addNoteBtn" class="btn btn-primary">Not Ekle</button>
    </div>

    <?php foreach ($notes as $note): ?>
        <div class="card note-card p-3">
            <h5><?= htmlspecialchars($note['title']) ?></h5>
            <p class="note-content"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
            <small class="text-muted"><?= formatDate($note['created_at']) ?></small>

            <div class="mt-2">
                <button class="btn btn-sm btn-warning" onclick="toggleEditForm(<?= $note['id'] ?>)">Düzenle</button>
                <button class="btn btn-sm btn-danger" onclick="deleteNote(<?= $note['id'] ?>)">Sil</button>
            </div>

            <div class="mt-3" id="edit-form-<?= $note['id'] ?>" style="display: none;">
                <input type="text" id="edit-title-<?= $note['id'] ?>" class="form-control mb-2" value="<?= htmlspecialchars($note['title']) ?>">
                <textarea id="edit-content-<?= $note['id'] ?>" class="form-control mb-2"><?= htmlspecialchars($note['content']) ?></textarea>
                <button class="btn btn-success btn-sm" onclick="updateNote(<?= $note['id'] ?>)">Kaydet</button>
                <button class="btn btn-secondary btn-sm" onclick="toggleEditForm(<?= $note['id'] ?>)">İptal</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="app.js"></script>
</body>
</html>
