<?php
// Oturumu başlat
session_start();

// Tüm oturum değişkenlerini temizle
$_SESSION = array();

// Oturumu sonlandır
session_destroy();

// index.php'ye yönlendir
header('Location: index.php');
exit();
?>
