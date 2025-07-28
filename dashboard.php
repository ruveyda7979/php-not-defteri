<?php
include 'db.php'; // Veritabanı bağlantısı
include 'functions.php'; // Yardımcı fonksiyonlar
requireLogin(); // Giriş yapılmamışsa yönlendir

// Kullanıcının kategorilerini çek

$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmtCat->fetchAll();

// Kullanıcının etiketlerini çek
$stmtTag = $pdo->prepare("SELECT * FROM tags WHERE user_id = ? ORDER BY name ASC");
$stmtTag->execute([$_SESSION['user_id']]);
$tags = $stmtTag->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Not Ekle</title>
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

    <!-- Sadece yeni not ekleme formu -->
    <div class="container" style="max-width: 500px; margin-top: 50px;">
        <h3 class="mb-4 text-center">Yeni Not Ekle</h3>
        <!-- Not ekleme formu -->
        <div class="mb-4">
            <input type="text" id="noteTitle" class="form-control mb-2" placeholder="Not Başlığı">
            <textarea id="noteContent" class="form-control mb-2" placeholder="Not İçeriği"></textarea>
            <!-- Durum seçimi -->
            <select id="noteStatus" class="form-control mb-2">
                <option value="tamamlanmadı" selected>Tamamlanmadı</option>
                <option value="tamamlandı">Tamamlandı</option>
                <option value="sorunlu">Sorunlu</option>
            </select>
            <!-- Kategori seçimi -->
            <select id="noteCategory" class="form-control mb-2">
                <option value="">Kategori Seç (İsteğe Bağlı)</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <!-- Etiket girişi -->
            <input type="text" id="noteTags" class="form-control mb-2" placeholder="Etiketler (virgülle ayır)">
            <button id="addNoteBtn" class="btn btn-primary w-100">Not Ekle</button>
        </div>
        <div class="text-center mt-3">
            <a href="notes.php" class="btn btn-secondary">Notlarımı Görüntüle</a>
        </div>
    </div>

    <!-- JavaScript dosyası -->
    <script src="app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
