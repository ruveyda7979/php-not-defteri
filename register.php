<?php
include 'db.php'; // Veritabanı bağlantısı
include 'functions.php'; // Yardımcı fonksiyonlar

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']); // Kullanıcı adını temizle
    $password = clean($_POST['password']); // Şifreyi temizle

    // Kullanıcı adı kontrolü
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        $error = "Bu kullanıcı adı zaten kullanılıyor!";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Şifreyi hashle
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt->execute([$username, $hashedPassword])) {
            header("Location: index.php?success=1"); // Başarılıysa giriş sayfasına yönlendir
            exit();
        } else {
            $error = "Kayıt işlemi başarısız oldu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol</title>
    <!-- Bootstrap ve özel stil dosyalarını ekliyoruz -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Tüm içeriği ortalayan ve kutu şeklinde gösteren container -->
    <div class="container" style="max-width: 400px; margin-top: 50px;">
        <h2 class="mb-4 text-center">Kayıt Ol</h2>

        <!-- Hata mesajı varsa göster -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Kayıt formu başlangıcı -->
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Kullanıcı Adı</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Şifre</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
            <a href="index.php" class="btn btn-secondary w-100 mt-2">Giriş</a>
        </form>
        <!-- Kayıt formu bitişi -->
    </div>
</body>
</html>
