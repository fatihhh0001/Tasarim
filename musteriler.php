<?php
// musteriler.php - 

require_once 'includes/db.php';

$mesaj = "";
$mesaj_tur = "";

// --- A) YENİ MÜŞTERİ EKLEME (AYNI) ---
if (isset($_POST['musteri_ekle'])) {
    $ad     = htmlspecialchars($_POST['ad_soyad']);
    $tel    = htmlspecialchars($_POST['telefon']);
    $adres  = htmlspecialchars($_POST['adres']);

    try {
        $sql = "SELECT yeni_musteri_kayit(?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ad, $tel, $adres]);
        $mesaj = "Yeni müşteri başarıyla kaydedildi!";
        $mesaj_tur = "success";
    } catch (PDOException $e) {
        $mesaj = "Kayıt Başarısız: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// --- B) MÜŞTERİ GÜNCELLEME (DEĞİŞEN KISIM) ---
if (isset($_POST['musteri_guncelle'])) {
    $id     = $_POST['kisi_id'];
    $ad     = htmlspecialchars($_POST['ad_soyad']);
    $tel    = htmlspecialchars($_POST['telefon']);
    $adres  = htmlspecialchars($_POST['adres']);
    $bakiye = $_POST['bakiye']; // Yeni alan: Bakiye

    try {
        $pdo->beginTransaction(); // İki tabloyu güncelleyeceğimiz için işlem başlatıyoruz

        // 1. Kişisel bilgileri güncelle (kisi tablosu)
        $sql1 = "UPDATE kisi SET ad_soyad = ?, telefon = ?, adres = ? WHERE kisi_id = ?";
        $pdo->prepare($sql1)->execute([$ad, $tel, $adres, $id]);

        // 2. Bakiyeyi güncelle (musteri tablosu)
        $sql2 = "UPDATE musteri SET bakiye = ? WHERE kisi_id = ?";
        $pdo->prepare($sql2)->execute([$bakiye, $id]);

        $pdo->commit(); // Onayla
        $mesaj = "Müşteri bilgileri ve bakiyesi güncellendi.";
        $mesaj_tur = "success";
    } catch (PDOException $e) {
        $pdo->rollBack(); // Hata varsa geri al
        $mesaj = "Güncelleme Hatası: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// --- C) MÜŞTERİ SİLME (AYNI) ---
if (isset($_POST['musteri_sil'])) {
    $sil_id = $_POST['silinecek_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kisi WHERE kisi_id = ?");
        $stmt->execute([$sil_id]);
        $mesaj = "Müşteri silindi.";
        $mesaj_tur = "warning";
    } catch (PDOException $e) {
        $mesaj = "Silinemedi: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// 3. LİSTELEME İŞLEMİ (AYNI)
$sorgu = "SELECT k.kisi_id, k.ad_soyad, k.telefon, k.adres, m.bakiye 
          FROM musteri m 
          JOIN kisi k ON m.kisi_id = k.kisi_id 
          ORDER BY k.kisi_id DESC";
$stmt = $pdo->query($sorgu);
$musteriler = $stmt->fetchAll();

require_once 'musteriler_view.php';
?>