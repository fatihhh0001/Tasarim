<?php
// includes/db.php


$host = "localhost";
$dbname = "veteriner3";
$user = "postgres";


$password = "root"; 

try {
    // Port 5432 olarak kalmalı (PostgreSQL varsayılan portu)
    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;";
    
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Bağlantı başarılıysa ekrana bir şey yazmasına gerek yok, sessizce devam etsin.
    
} catch (PDOException $e) {
    // Hata mesajını süsleyip ekrana basalım
    die("<div style='background-color: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center; font-family: sans-serif; margin: 20px;'>
            <h3 style='margin-top:0;'>⚠️ Veritabanı Bağlantı Hatası!</h3>
            <p><strong>Hata Mesajı:</strong> " . $e->getMessage() . "</p>
            <hr>
            <small>İpucu: XAMPP 'php.ini' dosyasından 'extension=pdo_pgsql' satırını açtın mı?</small>
         </div>");
}
?>