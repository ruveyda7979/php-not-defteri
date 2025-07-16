<?php
/**
 * Yardımcı Fonksiyonlar
 * Not Defteri Projesi için ortak kullanılacak fonksiyonlar
 */

/**
 * Kullanıcı girdilerini temizler
 * XSS ve SQL injection saldırılarını önler
 */
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Kullanıcının giriş yapıp yapmadığını kontrol eder
 * @return bool - Giriş yapmışsa true, yapmamışsa false
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Giriş yapılmamışsa anasayfaya yönlendirir
 * Korumalı sayfalar için kullanılır
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Tarihi Türkçe formatta gösterir
 * @param string $date - Veritabanından gelen tarih
 * @return string - Formatlanmış tarih
 */
function formatDate($date) {
    return date('d.m.Y H:i', strtotime($date));
}

/**
 * Uzun metinleri kısaltır
 * @param string $text - Kısaltılacak metin
 * @param int $length - Maksimum uzunluk
 * @return string - Kısaltılmış metin
 */
function shortText($text, $length = 100) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}
?>