<?php
// hayvanlar.php -

require_once 'includes/db.php';

$mesaj = "";
$mesaj_tur = "";

// 1. HAYVAN EKLEME İŞLEMİ 
if (isset($_POST['hayvan_ekle'])) {
    $musteri_id = $_POST['musteri_id'];
    $cins_id    = $_POST['cins_id'];
    $ad         = htmlspecialchars($_POST['ad']);
    $cip        = htmlspecialchars($_POST['cip_no']);
    $dogum      = $_POST['dogum_tarihi'];

    try {
        $sql = "INSERT INTO hayvan (musteri_id, cins_id, ad, cip_no, dogum_tarihi) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$musteri_id, $cins_id, $ad, $cip, $dogum]);

        $mesaj = "$ad sisteme başarıyla kaydedildi!";
        $mesaj_tur = "success";
    } catch (PDOException $e) {
        $mesaj = "Hata: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// 2. HAYVAN GÜNCELLEME İŞLEMİ (YENİ EKLENDİ)
if (isset($_POST['hayvan_guncelle'])) {
    $id      = $_POST['hayvan_id'];
    $cins_id = $_POST['cins_id'];
    $ad      = htmlspecialchars($_POST['ad']);
    $cip     = htmlspecialchars($_POST['cip_no']);
    $dogum   = $_POST['dogum_tarihi'];
    // Not: Müşteri değişikliği yapmıyoruz, karışıklık olmasın diye. Sadece hayvan bilgilerini güncelliyoruz.

    try {
        $sql = "UPDATE hayvan SET ad = ?, cins_id = ?, cip_no = ?, dogum_tarihi = ? WHERE hayvan_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ad, $cins_id, $cip, $dogum, $id]);

        $mesaj = "Hasta bilgileri güncellendi.";
        $mesaj_tur = "success";
    } catch (PDOException $e) {
        $mesaj = "Güncelleme Hatası: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// 3. HAYVAN SİLME İŞLEMİ (YENİ EKLENDİ)
if (isset($_POST['hayvan_sil'])) {
    $sil_id = $_POST['silinecek_id'];
    try {
        // Veritabanındaki CASCADE ayarı sayesinde hayvana bağlı randevu ve muayeneler de silinir.
        $stmt = $pdo->prepare("DELETE FROM hayvan WHERE hayvan_id = ?");
        $stmt->execute([$sil_id]);
        
        $mesaj = "Hasta kaydı silindi.";
        $mesaj_tur = "warning";
    } catch (PDOException $e) {
        $mesaj = "Silinemedi: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// 4. LİSTELEME İŞLEMİ (AYNI)
$sql_list = "SELECT h.hayvan_id, h.ad, h.cip_no, h.dogum_tarihi, h.cins_id, 
                    k.ad_soyad AS sahip, 
                    c.ad AS cins_adi, 
                    t.ad AS tur_adi
             FROM hayvan h
             JOIN musteri m ON h.musteri_id = m.kisi_id
             JOIN kisi k ON m.kisi_id = k.kisi_id
             JOIN cins c ON h.cins_id = c.cins_id
             JOIN tur t ON c.tur_id = t.tur_id
             ORDER BY h.hayvan_id DESC";
$hayvanlar = $pdo->query($sql_list)->fetchAll();


// 5. FORM İÇİN LİSTELER (AYNI)
$musteriler = $pdo->query("SELECT m.kisi_id, k.ad_soyad FROM musteri m JOIN kisi k ON m.kisi_id = k.kisi_id ORDER BY k.ad_soyad")->fetchAll();
$cinsler = $pdo->query("SELECT c.cins_id, c.ad, t.ad as tur_adi FROM cins c JOIN tur t ON c.tur_id = t.tur_id ORDER BY t.ad, c.ad")->fetchAll();

require_once 'hayvanlar_view.php';
?>