<?php
// Session başlat (kullanıcı oturumu için)
session_start();

// Veritabanı bağlantı bilgileri
$host = 'localhost';           // XAMPP için localhost
$dbname = 'notdefteri';        // Oluşturduğun veritabanı adı
$username = 'root';            // XAMPP varsayılan kullanıcı
$password = '';                // XAMPP varsayılan şifre (boş)

try {
    // PDO ile MySQL bağlantısı
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Hata modunu ayarla
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Başarılı bağlantı mesajı (geliştirme aşamasında)
    // echo "Veritabanı bağlantısı başarılı!";
    
} catch(PDOException $e) {
    // Hata durumunda mesaj göster ve scripti durdur
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>