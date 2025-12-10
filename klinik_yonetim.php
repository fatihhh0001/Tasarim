<?php
// klinik_yonetim.php -
require_once 'includes/db.php';

$mesaj = "";
$mesaj_tur = "";

// ... 
// 
// Sadece VETERİNER kısımlarını aşağıdakilerle değiştir:

// 1. İLAÇ EKLE (AYNI)
if (isset($_POST['ilac_ekle'])) {
    try { $pdo->prepare("INSERT INTO ilac (tedarikci_id, ad, stok_adet, fiyat) VALUES (?, ?, ?, ?)")->execute([$_POST['tedarikci_id'], $_POST['ad'], $_POST['stok_adet'], $_POST['fiyat']]); $mesaj = "İlaç eklendi!"; $mesaj_tur = "success"; } catch (PDOException $e) { $mesaj = "Hata: " . $e->getMessage(); $mesaj_tur = "danger"; }
}
// 2. HİZMET EKLE (AYNI)
if (isset($_POST['hizmet_ekle'])) {
    try { $pdo->prepare("INSERT INTO hizmet (ad, birim_ucret) VALUES (?, ?)")->execute([$_POST['ad'], $_POST['birim_ucret']]); $mesaj = "Hizmet eklendi!"; $mesaj_tur = "success"; } catch (PDOException $e) { $mesaj = "Hata: " . $e->getMessage(); $mesaj_tur = "danger"; }
}
// 3. AŞI EKLE (AYNI)
if (isset($_POST['asi_ekle'])) {
    try { $pdo->prepare("INSERT INTO asi (ad, tekrar_suresi_gun) VALUES (?, ?)")->execute([$_POST['ad'], $_POST['tekrar_suresi_gun']]); $mesaj = "Aşı eklendi!"; $mesaj_tur = "success"; } catch (PDOException $e) { $mesaj = "Hata: " . $e->getMessage(); $mesaj_tur = "danger"; }
}

// 4. VETERİNER EKLE (GÜNCELLENDİ: ADRES EKLENDİ)
if (isset($_POST['veteriner_ekle'])) {
    $ad      = htmlspecialchars($_POST['ad_soyad']);
    $tel     = htmlspecialchars($_POST['telefon']);
    $adres   = htmlspecialchars($_POST['adres']); // YENİ
    $uzmanlik = htmlspecialchars($_POST['uzmanlik']);
    $diploma = htmlspecialchars($_POST['diploma_no']);

    try {
        $pdo->beginTransaction();
        
        // Kişi tablosuna ekle (Adres dahil)
        $stmt = $pdo->prepare("INSERT INTO kisi (ad_soyad, telefon, adres, kisi_tipi) VALUES (?, ?, ?, 'V') RETURNING kisi_id");
        $stmt->execute([$ad, $tel, $adres]);
        $yeni_id = $stmt->fetchColumn();

        // Veteriner tablosuna ekle
        $stmt = $pdo->prepare("INSERT INTO veteriner (kisi_id, uzmanlik, diploma_no) VALUES (?, ?, ?)");
        $stmt->execute([$yeni_id, $uzmanlik, $diploma]);

        $pdo->commit();
        $mesaj = "Yeni veteriner hekim eklendi!";
        $mesaj_tur = "success";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $mesaj = "Hata: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// ... (STOK, HİZMET, AŞI GÜNCELLEME ) ...
//
if (isset($_POST['stok_guncelle'])) { try { $pdo->prepare("UPDATE ilac SET stok_adet = ? WHERE ilac_id = ?")->execute([$_POST['yeni_stok'], $_POST['ilac_id']]); $mesaj = "Stok güncellendi."; $mesaj_tur = "success"; } catch (PDOException $e) { $mesaj = "Hata: " . $e->getMessage(); $mesaj_tur = "danger"; } }
if (isset($_POST['hizmet_guncelle'])) { try { $pdo->prepare("UPDATE hizmet SET ad = ?, birim_ucret = ? WHERE hizmet_id = ?")->execute([$_POST['ad'], $_POST['birim_ucret'], $_POST['hizmet_id']]); $mesaj = "Hizmet güncellendi."; $mesaj_tur = "success"; } catch (PDOException $e) { $mesaj = "Hata: " . $e->getMessage(); $mesaj_tur = "danger"; } }
if (isset($_POST['asi_guncelle'])) { try { $pdo->prepare("UPDATE asi SET ad = ?, tekrar_suresi_gun = ? WHERE asi_id = ?")->execute([$_POST['ad'], $_POST['tekrar_suresi_gun'], $_POST['asi_id']]); $mesaj = "Aşı güncellendi."; $mesaj_tur = "success"; } catch (PDOException $e) { $mesaj = "Hata: " . $e->getMessage(); $mesaj_tur = "danger"; } }


// 8. VETERİNER GÜNCELLE ()
if (isset($_POST['veteriner_guncelle'])) {
    $id      = $_POST['kisi_id'];
    $ad      = htmlspecialchars($_POST['ad_soyad']);
    $tel     = htmlspecialchars($_POST['telefon']);
    $adres   = htmlspecialchars($_POST['adres']); // YENİ
    $uzmanlik = htmlspecialchars($_POST['uzmanlik']);
    $diploma = htmlspecialchars($_POST['diploma_no']);

    try {
        $pdo->beginTransaction();
        
        // Kişi bilgilerini güncelle (Adres dahil)
        $stmt = $pdo->prepare("UPDATE kisi SET ad_soyad = ?, telefon = ?, adres = ? WHERE kisi_id = ?");
        $stmt->execute([$ad, $tel, $adres, $id]);

        // Veteriner bilgilerini güncelle
        $stmt = $pdo->prepare("UPDATE veteriner SET uzmanlik = ?, diploma_no = ? WHERE kisi_id = ?");
        $stmt->execute([$uzmanlik, $diploma, $id]);

        $pdo->commit();
        $mesaj = "Veteriner bilgileri güncellendi.";
        $mesaj_tur = "success";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $mesaj = "Güncelleme hatası: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// ... (SİLME İŞLEMLERİ AYNI) ...
if (isset($_POST['ilac_sil'])) { try { $pdo->prepare("DELETE FROM ilac WHERE ilac_id = ?")->execute([$_POST['silinecek_id']]); $mesaj = "Silindi."; $mesaj_tur = "warning"; } catch (PDOException $e) { $mesaj = "Hata: " . $e->getMessage(); $mesaj_tur = "danger"; } }
if (isset($_POST['hizmet_sil'])) { try { $pdo->prepare("DELETE FROM hizmet WHERE hizmet_id = ?")->execute([$_POST['silinecek_id']]); $mesaj = "Silindi."; $mesaj_tur = "warning"; } catch (PDOException $e) { $mesaj = "Hata: " . $e->getMessage(); $mesaj_tur = "danger"; } }
if (isset($_POST['asi_sil'])) { try { $pdo->prepare("DELETE FROM asi WHERE asi_id = ?")->execute([$_POST['silinecek_id']]); $mesaj = "Silindi."; $mesaj_tur = "warning"; } catch (PDOException $e) { $mesaj = "Hata: " . $e->getMessage(); $mesaj_tur = "danger"; } }
if (isset($_POST['veteriner_sil'])) { try { $pdo->prepare("DELETE FROM kisi WHERE kisi_id = ?")->execute([$_POST['silinecek_id']]); $mesaj = "Veteriner silindi."; $mesaj_tur = "warning"; } catch (PDOException $e) { $mesaj = "Silinemedi! Randevuları olabilir."; $mesaj_tur = "danger"; } }

// =================================================================
// VERİLERİ ÇEKME 
// =================================================================
$ilaclar = $pdo->query("SELECT i.*, t.firma_adi FROM ilac i JOIN tedarikci t ON i.tedarikci_id = t.tedarikci_id ORDER BY i.stok_adet ASC")->fetchAll();
$hizmetler = $pdo->query("SELECT * FROM hizmet ORDER BY ad")->fetchAll();
$asilar = $pdo->query("SELECT * FROM asi ORDER BY ad")->fetchAll();
$tedarikciler = $pdo->query("SELECT * FROM tedarikci ORDER BY firma_adi")->fetchAll();

// Veterinerleri Çek ()
$veterinerler = $pdo->query("SELECT v.*, k.ad_soyad, k.telefon, k.adres FROM veteriner v JOIN kisi k ON v.kisi_id = k.kisi_id ORDER BY k.ad_soyad")->fetchAll();

require_once 'klinik_yonetim_view.php';
?>