<?php
// index.php - 
require_once 'includes/db.php';

// -------------------------------------------------------------------------
// 1. TOPLAM CİRO
//  'iki_tarih_arasi_ciro' saklı yordamını kullanıyoruz.
// -------------------------------------------------------------------------
try {
    // Başlangıç tarihi olarak sistemin çok öncesini, bitiş olarak bugünü veriyoruz.
    $baslangic = '2000-01-01'; 
    $bitis = date('Y-m-d');    
    
    $stmt = $pdo->prepare("SELECT iki_tarih_arasi_ciro(?, ?)");
    $stmt->execute([$baslangic, $bitis]);
    $ciro = $stmt->fetchColumn(); 
} catch (PDOException $e) {
    $ciro = 0;
}

// -------------------------------------------------------------------------
// 2. DİĞER İSTATİSTİKLER (Standart Sorgular)
// 
// -------------------------------------------------------------------------
$musteri_sayisi = $pdo->query("SELECT COUNT(*) FROM musteri")->fetchColumn();
$hasta_sayisi   = $pdo->query("SELECT COUNT(*) FROM hayvan")->fetchColumn();
$kritik_stok    = $pdo->query("SELECT COUNT(*) FROM ilac WHERE stok_adet < 20")->fetchColumn();

// -------------------------------------------------------------------------
// 3. SON 5 MUAYENE HAREKETİ (Standart Sorgu)
// -------------------------------------------------------------------------
$sql = "SELECT r.tarih_saat, 
               h.ad as hayvan_adi, 
               k.ad_soyad as sahip, 
               mu.yekun_tutar 
        FROM muayene mu
        JOIN randevu r ON mu.randevu_id = r.randevu_id
        JOIN hayvan h ON r.hayvan_id = h.hayvan_id
        JOIN musteri mus ON r.musteri_id = mus.kisi_id
        JOIN kisi k ON mus.kisi_id = k.kisi_id
        ORDER BY r.tarih_saat DESC 
        LIMIT 5";
$son_hareketler = $pdo->query($sql)->fetchAll();

// -------------------------------------------------------------------------
// 4. KRİTİK STOKTAKİ İLAÇLAR
//
// 'ilac_stok_kontrol' saklı yordamını kullanıyoruz.
// -------------------------------------------------------------------------
try {
    // Fonksiyon: ilac_stok_kontrol(limit_sayisi) -> 20'den az olanları getir.
    $stmt = $pdo->query("SELECT * FROM ilac_stok_kontrol(20) LIMIT 5");
    
    // saklı yordamın sütun adlarını 'ilac_adi' ve 'kalan_stok' olarak döndürüyor.
    // 'index_view.php' dosya 'ad' ve 'stok_adet' bekliyor.
    // 
    
    $biten_ilaclar = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $biten_ilaclar[] = [
            'ad' => $row['ilac_adi'],        // Fonksiyondan gelen isim -> View için 'ad' oldu
            'stok_adet' => $row['kalan_stok'] // Fonksiyondan gelen stok -> View için 'stok_adet' oldu
        ];
    }

} catch (PDOException $e) {
    $biten_ilaclar = [];
}

require_once 'index_view.php';
?>