<?php
include 'db.php'; // Veritabanı bağlantısı
include 'functions.php'; // Yardımcı fonksiyonlar
requireLogin(); // Giriş yapılmamışsa yönlendir

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
    <!-- Bootstrap ve özel stil dosyalarını ekliyoruz -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar: Hoş geldin ve çıkış butonu -->
    <nav class="navbar navbar-dark px-3">
        <span class="navbar-brand mb-0 h1">Notlarım</span>
        <div>
            <a href="dashboard.php" class="btn btn-light btn-sm me-2">Yeni Not Ekle</a>
            <a href="logout.php" class="btn btn-light btn-sm">Çıkış</a>
        </div>
    </nav>

    <!-- Notlar listesi -->
    <div class="container" style="max-width: 700px; margin-top: 40px;">
        <h3 class="mt-4 mb-3 text-center">Tüm Notlarım</h3>
        <div id="notes-list">
        <?php if (empty($notes)): ?>
            <div class="alert alert-info text-center">Henüz hiç notunuz yok.</div>
        <?php endif; ?>
        <?php foreach ($notes as $note): ?>
            <div class="card note-card p-3 mb-3" data-note-id="<?= $note['id'] ?>">
                <div class="note-view">
                    <h5 class="note-title-text"><?= htmlspecialchars($note['title']) ?></h5>
                    <p class="note-content-text"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                    <small class="text-muted">Oluşturulma: <?= formatDate($note['created_at']) ?></small>
                    <div class="mt-2">
                        <button class="btn btn-warning btn-sm edit-btn">Düzenle</button>
                        <button class="btn btn-danger btn-sm delete-btn">Sil</button>
                    </div>
                </div>
                <!-- Düzenleme formu (başta gizli) -->
                <form class="note-edit-form" style="display:none;">
                    <input type="text" name="title" class="form-control mb-2" value="<?= htmlspecialchars($note['title']) ?>" required>
                    <textarea name="content" class="form-control mb-2" required><?= htmlspecialchars($note['content']) ?></textarea>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm">Kaydet</button>
                        <button type="button" class="btn btn-secondary btn-sm cancel-edit">İptal</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <script>
    // Notlar listesinde düzenle/sil işlemleri için event delegation
    document.addEventListener('DOMContentLoaded', function() {
        // Silme işlemi
        document.querySelectorAll('.delete-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!confirm('Bu notu silmek istediğinize emin misiniz?')) return;
                const card = btn.closest('.note-card');
                const noteId = card.getAttribute('data-note-id');
                fetch('delete_note.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id=' + encodeURIComponent(noteId)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        card.remove();
                    } else {
                        alert(data.error || 'Silme işlemi başarısız!');
                    }
                });
            });
        });

        // Düzenleye tıklayınca formu aç
        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const card = btn.closest('.note-card');
                card.querySelector('.note-view').style.display = 'none';
                card.querySelector('.note-edit-form').style.display = 'block';
            });
        });

        // İptal butonuna tıklayınca formu kapat
        document.querySelectorAll('.cancel-edit').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const card = btn.closest('.note-card');
                card.querySelector('.note-edit-form').style.display = 'none';
                card.querySelector('.note-view').style.display = 'block';
            });
        });

        // Düzenleme formunu submit edince AJAX ile güncelle
        document.querySelectorAll('.note-edit-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const card = form.closest('.note-card');
                const noteId = card.getAttribute('data-note-id');
                const formData = new FormData(form);
                formData.append('id', noteId);
                fetch('update_note.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Güncellenen verileri göster
                        card.querySelector('.note-title-text').textContent = form.title.value;
                        card.querySelector('.note-content-text').textContent = form.content.value;
                        card.querySelector('.note-edit-form').style.display = 'none';
                        card.querySelector('.note-view').style.display = 'block';
                    } else {
                        alert(data.error || 'Güncelleme başarısız!');
                    }
                });
            });
        });
    });
    </script>
</body>
</html> 