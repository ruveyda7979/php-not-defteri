<?php
include 'db.php'; // Veritabanı bağlantısı
include 'functions.php'; // Yardımcı fonksiyonlar
requireLogin(); // Giriş yapılmamışsa yönlendir
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
        <span class="navbar-brand mb-0 h1">Hoş geldin, <?= $_SESSION['username'] ?>!</span>
        <a href="logout.php" class="btn btn-light btn-sm">Çıkış</a>
    </nav>

    <!-- Sadece yeni not ekleme formu -->
    <div class="container" style="max-width: 500px; margin-top: 50px;">
        <h3 class="mb-4 text-center">Yeni Not Ekle</h3>
        <!-- Not ekleme formu -->
        <div class="mb-4">
            <input type="text" id="noteTitle" class="form-control mb-2" placeholder="Not Başlığı">
            <textarea id="noteContent" class="form-control mb-2" placeholder="Not İçeriği"></textarea>
            <button id="addNoteBtn" class="btn btn-primary w-100">Not Ekle</button>
        </div>
        <div class="text-center mt-3">
            <a href="notes.php" class="btn btn-secondary">Notlarımı Görüntüle</a>
        </div>
    </div>

    <!-- JavaScript dosyası -->
    <script src="app.js"></script>
</body>
</html>
