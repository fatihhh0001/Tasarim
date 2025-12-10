<?php
// muayene.php - RANDEVU SİSTEMİYLE ENTEGRE EDİLMİŞ HALİ
require_once 'includes/db.php';

$mesaj = "";
$mesaj_tur = "";

// --- KAYIT İŞLEMİ ---
if (isset($_POST['islem_tamamla'])) {
    
    $islem_turu = $_POST['islem_turu']; // 'planli' veya 'acil'
    $veteriner_id = 1; 

    $secilen_hizmetler = isset($_POST['hizmetler']) ? $_POST['hizmetler'] : [];
    $secilen_ilaclar   = isset($_POST['ilaclar']) ? $_POST['ilaclar'] : [];
    $teshis            = $_POST['teshis_ozeti'];

    try {
        $pdo->beginTransaction();

        // ----------------------------------------------------------
        // ADIM 1: RANDEVU ID BELİRLEME (KRİTİK KISIM)
        // ----------------------------------------------------------
        $randevu_id = 0;

        if ($islem_turu == 'planli') {
            // A) PLANLI: Kullanıcı var olan randevuyu seçti, onu kullan.
            $randevu_id = $_POST['mevcut_randevu_id'];
        } else {
            // B) ACİL/RANDEVUSUZ: Yeni bir randevu kaydı oluştur.
            $hayvan_id = $_POST['hayvan_id'];
            
            // Müşteriyi bul
            $stmt = $pdo->prepare("SELECT musteri_id FROM hayvan WHERE hayvan_id = ?");
            $stmt->execute([$hayvan_id]);
            $musteri_id = $stmt->fetchColumn();

            // Anlık randevu oluştur
            $sql_r = "INSERT INTO randevu (musteri_id, veteriner_id, hayvan_id, tarih_saat) 
                      VALUES (?, ?, ?, CURRENT_TIMESTAMP) RETURNING randevu_id";
            $stmtR = $pdo->prepare($sql_r);
            $stmtR->execute([$musteri_id, $veteriner_id, $hayvan_id]);
            $randevu_id = $stmtR->fetchColumn();
        }

        // ----------------------------------------------------------
        // ADIM 2: MUAYENE KAYDI (AYNI)
        // ----------------------------------------------------------
        $toplam_tutar = 0;
        $eklenecek_hizmetler = [];

        // Hizmet tutarlarını hesapla
        if (!empty($secilen_hizmetler)) {
            foreach ($secilen_hizmetler as $hizmet_id) {
                $stmt = $pdo->prepare("SELECT birim_ucret FROM hizmet WHERE hizmet_id = ?");
                $stmt->execute([$hizmet_id]);
                $fiyat = $stmt->fetchColumn();
                $toplam_tutar += $fiyat;
                $eklenecek_hizmetler[] = ['id' => $hizmet_id, 'fiyat' => $fiyat];
            }
        }

        // Muayeneyi kaydet (Tetikleyici bakiye ekleyecek)
        $sql_m = "INSERT INTO muayene (randevu_id, teshis_ozeti, yekun_tutar) 
                  VALUES (?, ?, ?) RETURNING muayene_id";
        $stmtM = $pdo->prepare($sql_m);
        $stmtM->execute([$randevu_id, $teshis, $toplam_tutar]);
        $muayene_id = $stmtM->fetchColumn();

        // Hizmet detaylarını kaydet
        foreach ($eklenecek_hizmetler as $h) {
             $pdo->prepare("INSERT INTO muayene_hizmet (muayene_id, hizmet_id, adet, uygulanan_fiyat) VALUES (?, ?, 1, ?)")->execute([$muayene_id, $h['id'], $h['fiyat']]);
        }

        // ----------------------------------------------------------
        // ADIM 3: REÇETE İŞLEMLERİ (AYNI)
        // ----------------------------------------------------------
        $ilac_var_mi = false;
        foreach ($secilen_ilaclar as $id => $adet) { if ($adet > 0) { $ilac_var_mi = true; break; } }

        if ($ilac_var_mi) {
            $pdo->prepare("INSERT INTO recete (muayene_id) VALUES (?)")->execute([$muayene_id]);
            $recete_id = $pdo->lastInsertId('recete_recete_id_seq');

            foreach ($secilen_ilaclar as $ilac_id => $adet) {
                if ($adet > 0) {
                    $kullanim = $_POST['kullanim'][$ilac_id];
                    $pdo->prepare("INSERT INTO recete_ilac (recete_id, ilac_id, kullanim_sekli, adet) VALUES (?, ?, ?, ?)")->execute([$recete_id, $ilac_id, $kullanim, $adet]);
                }
            }
        }

        $pdo->commit();
        $mesaj = "Muayene tamamlandı.";
        $mesaj_tur = "success";

    } catch (Exception $e) {
        $pdo->rollBack();
        $mesaj = "Hata: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// --- VERİ LİSTELEME ---

// 1. BEKLEYEN RANDEVULARI ÇEK (YENİ!)
// Henüz muayenesi yapılmamış (muayene tablosunda randevu_id'si olmayan) randevular.
$bekleyen_randevular = $pdo->query("
    SELECT r.randevu_id, r.tarih_saat, h.ad AS hayvan_adi, k.ad_soyad AS sahip 
    FROM randevu r
    JOIN hayvan h ON r.hayvan_id = h.hayvan_id
    JOIN musteri m ON r.musteri_id = m.kisi_id
    JOIN kisi k ON m.kisi_id = k.kisi_id
    WHERE r.randevu_id NOT IN (SELECT randevu_id FROM muayene)
    ORDER BY r.tarih_saat ASC
")->fetchAll();


// 2. Geçmiş Muayeneler
$muayeneler = $pdo->query("SELECT m.muayene_id, m.teshis_ozeti, m.yekun_tutar, r.tarih_saat, h.ad AS hayvan_adi, k.ad_soyad AS sahip FROM muayene m JOIN randevu r ON m.randevu_id = r.randevu_id JOIN hayvan h ON r.hayvan_id = h.hayvan_id JOIN musteri mus ON r.musteri_id = mus.kisi_id JOIN kisi k ON mus.kisi_id = k.kisi_id ORDER BY m.muayene_id DESC LIMIT 10")->fetchAll();

// 3. Reçete Detayları (Aynı)
$recete_detaylari = [];
foreach ($muayeneler as $m) {
    $mid = $m['muayene_id'];
    $ilaclar_listesi = $pdo->query("SELECT i.ad AS ilac_adi, ri.kullanim_sekli, ri.adet FROM recete r JOIN recete_ilac ri ON r.recete_id = ri.recete_id JOIN ilac i ON ri.ilac_id = i.ilac_id WHERE r.muayene_id = $mid")->fetchAll();
    if (!empty($ilaclar_listesi)) { $recete_detaylari[$mid] = $ilaclar_listesi; }
}

// Diğer listeler
$hayvanlar = $pdo->query("SELECT h.hayvan_id, h.ad, k.ad_soyad FROM hayvan h JOIN musteri m ON h.musteri_id = m.kisi_id JOIN kisi k ON m.kisi_id = k.kisi_id ORDER BY h.ad")->fetchAll();
$hizmetler = $pdo->query("SELECT * FROM hizmet ORDER BY ad")->fetchAll();
$ilaclar   = $pdo->query("SELECT * FROM ilac WHERE stok_adet > 0 ORDER BY ad")->fetchAll();

require_once 'muayene_view.php';
?>