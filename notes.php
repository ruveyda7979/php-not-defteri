<?php
include 'db.php'; // Veritabanı bağlantısı
include 'functions.php'; // Yardımcı fonksiyonlar
requireLogin(); // Giriş yapılmamışsa yönlendir

// Kategorileri çek

$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmtCat->fetchAll();
// Etiketleri çek
$stmtTag = $pdo->prepare("SELECT * FROM tags WHERE user_id = ? ORDER BY name ASC");
$stmtTag->execute([$_SESSION['user_id']]);
$tags = $stmtTag->fetchAll();

// Filtre parametrelerini al
$search = $_GET['search'] ?? '';
$filterCategory = $_GET['category'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterTag = $_GET['tag'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

// Toplam not sayısını hesapla (sayfalama için)
$countSql = "SELECT COUNT(*) FROM notes LEFT JOIN categories ON notes.category_id = categories.id WHERE notes.user_id = ?";
$countParams = [$_SESSION['user_id']];

if ($search !== '') {
    $countSql .= " AND (notes.title LIKE ? OR notes.content LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}
if ($filterCategory !== '') {
    $countSql .= " AND notes.category_id = ?";
    $countParams[] = $filterCategory;
}
if ($filterStatus !== '') {
    $countSql .= " AND notes.status = ?";
    $countParams[] = $filterStatus;
}
if ($filterTag !== '') {
    $countSql .= " AND notes.id IN (SELECT note_tags.note_id FROM note_tags JOIN tags ON note_tags.tag_id = tags.id WHERE tags.name LIKE ? AND tags.user_id = ?)";
    $countParams[] = "%$filterTag%";
    $countParams[] = $_SESSION['user_id'];
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalNotes = $countStmt->fetchColumn();
$totalPages = ceil($totalNotes / $perPage);

// Notları veritabanından al (sayfalama ile)
$sql = "SELECT notes.*, categories.name AS category_name FROM notes LEFT JOIN categories ON notes.category_id = categories.id WHERE notes.user_id = ?";
$params = [$_SESSION['user_id']];

// Başlık veya içerik araması
if ($search !== '') {
    $sql .= " AND (notes.title LIKE ? OR notes.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
// Kategori filtresi
if ($filterCategory !== '') {
    $sql .= " AND notes.category_id = ?";
    $params[] = $filterCategory;
}
// Durum filtresi
if ($filterStatus !== '') {
    $sql .= " AND notes.status = ?";
    $params[] = $filterStatus;
}
// Etiket filtresi
if ($filterTag !== '') {
    $sql .= " AND notes.id IN (SELECT note_tags.note_id FROM note_tags JOIN tags ON note_tags.tag_id = tags.id WHERE tags.name LIKE ? AND tags.user_id = ?)";
    $params[] = "%$filterTag%";
    $params[] = $_SESSION['user_id'];
}

$sql .= " ORDER BY notes.created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
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
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center justify-content-center" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menü">
                <!-- Modern hamburger icon (rounded, bold) -->
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
                <li><a class="dropdown-item" href="dashboard.php">Yeni Not Ekle</a></li>
                <li><a class="dropdown-item" href="notes.php">Notlarımı Görüntüle</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Çıkış</a></li>
            </ul>
        </div>
    </nav>

    <!-- Notlar listesi -->
    <div class="container" style="max-width: 700px; margin-top: 40px;">
        <h3 class="mt-4 mb-3 text-center">Tüm Notlarım</h3>
        <!-- Arama ve Filtreleme Formu -->
        <form class="d-flex gap-2 mb-4" method="get">
            <div class="flex-fill">
                <input type="text" name="search" class="form-control" placeholder="Başlık veya içerikte ara" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="flex-fill">
                <select name="category" class="form-control">
                    <option value="">Kategori (Tümü)</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filterCategory == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-fill">
                <select name="status" class="form-control">
                    <option value="">Durum (Tümü)</option>
                    <option value="tamamlanmadı" <?= $filterStatus == 'tamamlanmadı' ? 'selected' : '' ?>>Tamamlanmadı</option>
                    <option value="tamamlandı" <?= $filterStatus == 'tamamlandı' ? 'selected' : '' ?>>Tamamlandı</option>
                    <option value="sorunlu" <?= $filterStatus == 'sorunlu' ? 'selected' : '' ?>>Sorunlu</option>
                </select>
            </div>
            <div class="flex-fill">
                <input type="text" name="tag" class="form-control" placeholder="Etiket (örn: iş)" value="<?= htmlspecialchars($filterTag) ?>">
            </div>
            <div class="flex-fill">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>
        </form>
        <div id="notes-list">
        <?php if (empty($notes)): ?>
            <div class="alert alert-info text-center">Henüz hiç notunuz yok.</div>
        <?php endif; ?>
        <?php foreach ($notes as $note): ?>
            <div class="card note-card p-2 mb-2 compact-view" data-note-id="<?= $note['id'] ?>">
                <div class="note-view">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="note-title-text mb-0"><?= htmlspecialchars($note['title']) ?></h5>
                        <div class="d-flex gap-1">
                            <button class="btn btn-warning btn-sm edit-btn">Düzenle</button>
                            <button class="btn btn-danger btn-sm delete-btn">Sil</button>
                        </div>
                    </div>
                    <p class="note-content-text"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                    <?php if (!empty($note['category_name'])): ?>
                        <span class="badge bg-info text-dark mb-1">Kategori: <?= htmlspecialchars($note['category_name']) ?></span>
                    <?php endif; ?>
                    <?php
                    // Etiketleri çek
                    $stmtTags = $pdo->prepare("SELECT tags.name FROM note_tags JOIN tags ON note_tags.tag_id = tags.id WHERE note_tags.note_id = ? ORDER BY tags.name ASC");
                    $stmtTags->execute([$note['id']]);
                    $tagNames = $stmtTags->fetchAll(PDO::FETCH_COLUMN);
                    if ($tagNames): ?>
                        <div class="mb-1">
                            <span class="badge bg-secondary">Etiketler: <?= htmlspecialchars(implode(', ', $tagNames)) ?></span>
                        </div>
                    <?php endif; ?>
                    <!-- Durum gösterimi -->
                    <?php
                    $statusClass = '';
                    $statusText = '';
                    switch($note['status']) {
                        case 'tamamlandı':
                            $statusClass = 'bg-success';
                            $statusText = 'Tamamlandı';
                            break;
                        case 'sorunlu':
                            $statusClass = 'bg-danger';
                            $statusText = 'Sorunlu';
                            break;
                        default:
                            $statusClass = 'bg-secondary';
                            $statusText = 'Tamamlanmadı';
                    }
                    ?>
                    <span class="badge <?= $statusClass ?> mb-1">Durum: <?= $statusText ?></span>
                    <small class="text-muted">Oluşturulma: <?= formatDate($note['created_at']) ?></small>
                </div>
                <!-- Düzenleme formu (başta gizli) -->
                <form class="note-edit-form" style="display:none;">
                    <input type="text" name="title" class="form-control mb-2" value="<?= htmlspecialchars($note['title']) ?>" required>
                    <textarea name="content" class="form-control mb-2" required><?= htmlspecialchars($note['content']) ?></textarea>
                    <!-- Durum seçimi -->
                    <select name="status" class="form-control mb-2">
                        <option value="tamamlanmadı" <?= $note['status'] == 'tamamlanmadı' ? 'selected' : '' ?>>Tamamlanmadı</option>
                        <option value="tamamlandı" <?= $note['status'] == 'tamamlandı' ? 'selected' : '' ?>>Tamamlandı</option>
                        <option value="sorunlu" <?= $note['status'] == 'sorunlu' ? 'selected' : '' ?>>Sorunlu</option>
                    </select>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm">Kaydet</button>
                        <button type="button" class="btn btn-secondary btn-sm cancel-edit">İptal</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
        </div>

        <!-- Sayfalama Navigasyonu -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Not sayfaları" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Önceki</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Sonraki</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
                        
                        // Durum badge'ini güncelle
                        const statusBadge = card.querySelector('.badge[class*="bg-"]');
                        if (statusBadge) {
                            const status = form.status.value;
                            let statusClass = 'bg-secondary';
                            let statusText = 'Tamamlanmadı';
                            
                            switch(status) {
                                case 'tamamlandı':
                                    statusClass = 'bg-success';
                                    statusText = 'Tamamlandı';
                                    break;
                                case 'sorunlu':
                                    statusClass = 'bg-danger';
                                    statusText = 'Sorunlu';
                                    break;
                            }
                            
                            statusBadge.className = `badge ${statusClass} mb-1`;
                            statusBadge.textContent = `Durum: ${statusText}`;
                        }
                        
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