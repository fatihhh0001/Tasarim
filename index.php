<?php

require_once 'includes/db.php';

// 1. TOPLAM CİRO (Tüm zamanların toplamı)
// COALESCE, eğer hiç kayıt yoksa NULL yerine 0 dönmesini sağlar.
$ciro = $pdo->query("SELECT COALESCE(SUM(yekun_tutar), 0) FROM muayene")->fetchColumn();

// 2. DİĞER İSTATİSTİKLER
$musteri_sayisi = $pdo->query("SELECT COUNT(*) FROM musteri")->fetchColumn();
$hasta_sayisi = $pdo->query("SELECT COUNT(*) FROM hayvan")->fetchColumn();
$kritik_stok = $pdo->query("SELECT COUNT(*) FROM ilac WHERE stok_adet < 20")->fetchColumn();

// 3. SON 5 MUAYENE HAREKETİ
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

// 4. KRİTİK STOKTAKİ İLAÇLAR
$biten_ilaclar = $pdo->query("SELECT ad, stok_adet FROM ilac WHERE stok_adet < 20 ORDER BY stok_adet ASC LIMIT 5")->fetchAll();

require_once 'index_view.php';
?>