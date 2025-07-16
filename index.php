<?php
// ==================== DOSYA INCLUDE İŞLEMLERİ ====================

include 'db.php';         // Veritabanı bağlantısı
include 'functions.php';  // Yardımcı fonksiyonlar

// ==================== GİRİŞ KONTROLÜ ====================

// Eğer kullanıcı zaten giriş yapmışsa dashboard'a yönlendir
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();  // Kodun devam etmesini engelle
}

// ==================== DEĞIŞKEN TANIMLAMALARI ====================

$error = '';    // Hata mesajları için
$success = '';  // Başarı mesajları için

// ==================== BAŞARI MESAJI KONTROLÜ ====================

// Kayıt sayfasından gelen başarı mesajını kontrol et
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.';
}

// ==================== GİRİŞ FORMU İŞLEMLERİ ====================

// Form POST ile gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form verilerini al ve temizle
    $username = clean($_POST['username']);  // XSS koruması
    $password = $_POST['password'];         // Şifre temizlenmez (hash kontrolü yapılacak)
    
    // Boş alan kontrolü
    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre boş olamaz!';
    } else {
        try {
            // Kullanıcıyı veritabanından bul
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            // Kullanıcı var mı ve şifre doğru mu?
            if ($user && password_verify($password, $user['password'])) {
                // Giriş başarılı - session'a kaydet
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Dashboard'a yönlendir
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Kullanıcı adı veya şifre hatalı!';
            }
        } catch(PDOException $e) {
            // Veritabanı hatası
            $error = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            // Güvenlik: Gerçek hatayı kullanıcıya gösterme
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Defteri - Giriş</title>
    
    <!-- Bootstrap CSS - hazır stil kütüphanesi -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Özel CSS dosyamız -->
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <!-- Ana giriş kartı -->
            <div class="card">
                <!-- Kart başlığı -->
                <div class="card-header text-center">
                    <h3>🗒️ Not Defteri</h3>
                    <p class="text-muted">Giriş Yap</p>
                </div>
                
                <!-- Kart içeriği -->
                <div class="card-body">
                    <!-- HATA MESAJI GÖSTER -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- BAŞARI MESAJI GÖSTER -->
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- GİRİŞ FORMU -->
                    <form method="POST">
                        <!-- Kullanıcı adı alanı -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <!-- Şifre alanı -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <!-- Giriş butonu -->
                        <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                    </form>
                </div>
                
                <!-- Kart alt bilgisi -->
                <div class="card-footer text-center">
                    <p class="mb-0">Hesabın yok mu? <a href="register.php">Kayıt Ol</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Özel JavaScript dosyamız -->
    <script src="app.js"></script>
</body>
</html>