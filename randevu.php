<?php
// randevu.php - SİLME ÖZELLİĞİ GÜNCELLENMİŞ
require_once 'includes/db.php';

$mesaj = "";
$mesaj_tur = "";

// 1. YENİ RANDEVU OLUŞTURMA (AYNI)
if (isset($_POST['randevu_olustur'])) {
    $musteri_id   = $_POST['musteri_id'];
    $hayvan_id    = $_POST['hayvan_id'];
    $veteriner_id = $_POST['veteriner_id'];
    $tarih_saat   = $_POST['tarih_saat'];

    try {
        $sql = "INSERT INTO randevu (musteri_id, veteriner_id, hayvan_id, tarih_saat) 
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$musteri_id, $veteriner_id, $hayvan_id, $tarih_saat]);

        $mesaj = "Randevu başarıyla oluşturuldu!";
        $mesaj_tur = "success";

    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'zaten randevusu var') !== false) {
             $mesaj = "HATA: Bu veterinerin belirtilen saatte zaten randevusu var!";
        } else {
             $mesaj = "İşlem Başarısız: " . $e->getMessage();
        }
        $mesaj_tur = "danger";
    }
}

// 2. RANDEVU SİLME İŞLEMİ (GÜNCELLENDİ - ARTIK POST İLE ÇALIŞIYOR)
if (isset($_POST['randevu_sil'])) {
    $sil_id = $_POST['silinecek_id'];
    
    try {
        // Önce buna bağlı muayene var mı kontrol et
        $stmt = $pdo->prepare("SELECT muayene_id FROM muayene WHERE randevu_id = ?");
        $stmt->execute([$sil_id]);
        $muayene_id = $stmt->fetchColumn();

        // Eğer muayene yapılmışsa, önce onları temizle (Zincirleme Silme)
        if ($muayene_id) {
            $pdo->prepare("DELETE FROM recete WHERE muayene_id = ?")->execute([$muayene_id]);
            $pdo->prepare("DELETE FROM muayene WHERE muayene_id = ?")->execute([$muayene_id]);
        }
        
        // Randevuyu sil
        $pdo->prepare("DELETE FROM randevu WHERE randevu_id = ?")->execute([$sil_id]);
        
        $mesaj = "Randevu (ve varsa ilişkili muayene kayıtları) silindi.";
        $mesaj_tur = "warning";

    } catch (PDOException $e) {
        $mesaj = "Silinemedi: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// 3. LİSTELEME (AYNI)
$sql_list = "SELECT r.randevu_id, r.tarih_saat, 
                    h.ad AS hayvan_adi, 
                    k_mus.ad_soyad AS musteri_adi,
                    k_vet.ad_soyad AS veteriner_adi,
                    t.ad AS tur_adi,
                    m.muayene_id
             FROM randevu r
             LEFT JOIN muayene m ON r.randevu_id = m.randevu_id
             JOIN hayvan h ON r.hayvan_id = h.hayvan_id
             JOIN cins c ON h.cins_id = c.cins_id
             JOIN tur t ON c.tur_id = t.tur_id
             JOIN musteri mus ON r.musteri_id = mus.kisi_id
             JOIN kisi k_mus ON mus.kisi_id = k_mus.kisi_id
             JOIN veteriner v ON r.veteriner_id = v.kisi_id
             JOIN kisi k_vet ON v.kisi_id = k_vet.kisi_id
             ORDER BY r.tarih_saat ASC";
$randevular = $pdo->query($sql_list)->fetchAll();

// 4. FORM İÇİN VERİLER (AYNI)
$veterinerler = $pdo->query("SELECT v.kisi_id, k.ad_soyad, v.uzmanlik FROM veteriner v JOIN kisi k ON v.kisi_id = k.kisi_id")->fetchAll();
$hayvanlar = $pdo->query("SELECT h.hayvan_id, h.ad, h.musteri_id, k.ad_soyad as sahip FROM hayvan h JOIN musteri m ON h.musteri_id = m.kisi_id JOIN kisi k ON m.kisi_id = k.kisi_id ORDER BY k.ad_soyad")->fetchAll();

require_once 'randevu_view.php';
?>