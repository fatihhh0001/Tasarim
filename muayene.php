<?php
// muayene.php - 
require_once 'includes/db.php';

$mesaj = "";
$mesaj_tur = "";

// --- KAYIT İŞLEMİ ---
if (isset($_POST['islem_tamamla'])) {
    
    $islem_turu   = isset($_POST['islem_turu']) ? $_POST['islem_turu'] : 'acil';
    $veteriner_id = 1; 

    $teshis            = isset($_POST['teshis_ozeti']) ? $_POST['teshis_ozeti'] : '';
    $secilen_hizmetler = isset($_POST['hizmetler']) ? $_POST['hizmetler'] : [];
    $secilen_ilaclar   = isset($_POST['ilaclar']) ? $_POST['ilaclar'] : [];
    $secilen_asilar    = isset($_POST['asilar']) ? $_POST['asilar'] : [];

    try {
        $pdo->beginTransaction(); 

        // ----------------------------------------------------------
        // 1. RANDEVU VE HASTA BELİRLEME
        // ----------------------------------------------------------
        $randevu_id = 0;
        
        if ($islem_turu == 'planli') {
            if(empty($_POST['mevcut_randevu_id'])) throw new Exception("Lütfen randevu seçiniz.");
            $randevu_id = $_POST['mevcut_randevu_id'];
            
            $stmt = $pdo->prepare("SELECT hayvan_id FROM randevu WHERE randevu_id = ?");
            $stmt->execute([$randevu_id]);
            $hayvan_id = $stmt->fetchColumn();
            
        } else {
            if(empty($_POST['hayvan_id'])) throw new Exception("Lütfen hasta seçiniz.");
            $hayvan_id = $_POST['hayvan_id'];
            
            $stmt = $pdo->prepare("SELECT musteri_id FROM hayvan WHERE hayvan_id = ?");
            $stmt->execute([$hayvan_id]);
            $musteri_id = $stmt->fetchColumn();

            $sql_r = "INSERT INTO randevu (musteri_id, veteriner_id, hayvan_id, tarih_saat) 
                      VALUES (?, ?, ?, CURRENT_TIMESTAMP) RETURNING randevu_id";
            $stmtR = $pdo->prepare($sql_r);
            $stmtR->execute([$musteri_id, $veteriner_id, $hayvan_id]);
            $randevu_id = $stmtR->fetchColumn();
        }

        // ----------------------------------------------------------
        // 2. TUTAR HESAPLAMA
        // ----------------------------------------------------------
        $toplam_tutar = 0;
        
        // Hizmetler
        $eklenecek_hizmetler = [];
        if (!empty($secilen_hizmetler)) {
            foreach ($secilen_hizmetler as $hizmet_id) {
                $stmt = $pdo->prepare("SELECT birim_ucret FROM hizmet WHERE hizmet_id = ?");
                $stmt->execute([$hizmet_id]);
                $fiyat = $stmt->fetchColumn();
                $toplam_tutar += $fiyat;
                $eklenecek_hizmetler[] = ['id' => $hizmet_id, 'fiyat' => $fiyat];
            }
        }

        // Aşılar
        $eklenecek_asilar = [];
        if (!empty($secilen_asilar)) {
            foreach ($secilen_asilar as $asi_id) {
                $asi_fiyati = (isset($_POST['asi_fiyat']) && isset($_POST['asi_fiyat'][$asi_id])) ? (float)$_POST['asi_fiyat'][$asi_id] : 0;
                $toplam_tutar += $asi_fiyati;
                $eklenecek_asilar[] = ['id' => $asi_id, 'fiyat' => $asi_fiyati];
            }
        }

        // ----------------------------------------------------------
        // 3. MUAYENE KAYDI
        // ----------------------------------------------------------
        $sql_m = "INSERT INTO muayene (randevu_id, teshis_ozeti, yekun_tutar) 
                  VALUES (?, ?, ?) RETURNING muayene_id";
        $stmtM = $pdo->prepare($sql_m);
        $stmtM->execute([$randevu_id, $teshis, $toplam_tutar]);
        $muayene_id = $stmtM->fetchColumn();

        // Hizmet Detayları
        foreach ($eklenecek_hizmetler as $h) {
             $pdo->prepare("INSERT INTO muayene_hizmet (muayene_id, hizmet_id, adet, uygulanan_fiyat) VALUES (?, ?, 1, ?)")->execute([$muayene_id, $h['id'], $h['fiyat']]);
        }

        // ----------------------------------------------------------
        // 4. AŞI TAKİP KAYDI (DÜZELTİLDİ: SÜTUNLAR SENİN TABLONA GÖRE)
        // ----------------------------------------------------------
        foreach ($eklenecek_asilar as $a) {
            $asi_id = $a['id'];
            
            // Bilgi amaçlı aşının adını ve süresini çekiyoruz
            $stmt = $pdo->prepare("SELECT tekrar_suresi_gun, ad FROM asi WHERE asi_id = ?");
            $stmt->execute([$asi_id]);
            $asi_bilgi = $stmt->fetch();
            
            if ($asi_bilgi) {
                $bugun = date('Y-m-d'); // Uygulama Tarihi = BUGÜN
                
                // --- KRİTİK DÜZELTME ---
                // Sütunlar: hayvan_id, asi_id, uygulama_tarihi
                // veteriner_id YOK, sonraki_tarih YOK.
                $sql_asi = "INSERT INTO asi_takip (hayvan_id, asi_id, uygulama_tarihi) 
                            VALUES (?, ?, ?)";
                $pdo->prepare($sql_asi)->execute([$hayvan_id, $asi_id, $bugun]);
                
                // Bir sonraki tarihi biz hesaplayıp RAPORA (Teşhis özetine) yazalım ki bilgi kaybolmasın.
                $gun = $asi_bilgi['tekrar_suresi_gun'];
                $sonraki_tarih = date('Y-m-d', strtotime($bugun . " + $gun days"));
                
                $ek_not = "\n[AŞI]: " . $asi_bilgi['ad'] . " yapıldı. (Sonraki: $sonraki_tarih)";
                $pdo->prepare("UPDATE muayene SET teshis_ozeti = teshis_ozeti || ? WHERE muayene_id = ?")->execute([$ek_not, $muayene_id]);
            }
        }

        // ----------------------------------------------------------
        // 5. REÇETE İŞLEMLERİ
        // ----------------------------------------------------------
        $ilac_var_mi = false;
        foreach ($secilen_ilaclar as $adet) { if ($adet > 0) { $ilac_var_mi = true; break; } }

        if ($ilac_var_mi) {
            $pdo->prepare("INSERT INTO recete (muayene_id) VALUES (?)")->execute([$muayene_id]);
            $recete_id = $pdo->lastInsertId('recete_recete_id_seq');

            foreach ($secilen_ilaclar as $ilac_id => $adet) {
                if ($adet > 0) {
                    $kullanim = isset($_POST['kullanim'][$ilac_id]) ? $_POST['kullanim'][$ilac_id] : '';
                    $pdo->prepare("INSERT INTO recete_ilac (recete_id, ilac_id, kullanim_sekli, adet) VALUES (?, ?, ?, ?)")->execute([$recete_id, $ilac_id, $kullanim, $adet]);
                }
            }
        }

        $pdo->commit();
        $mesaj = "İşlem başarıyla kaydedildi.";
        $mesaj_tur = "success";

    } catch (Exception $e) {
        $pdo->rollBack();
        $mesaj = "Hata oluştu: " . $e->getMessage();
        $mesaj_tur = "danger";
    }
}

// --- VERİ LİSTELEME ---
$bekleyen_randevular = $pdo->query("SELECT r.randevu_id, r.tarih_saat, h.ad AS hayvan_adi, k.ad_soyad AS sahip FROM randevu r JOIN hayvan h ON r.hayvan_id = h.hayvan_id JOIN musteri m ON r.musteri_id = m.kisi_id JOIN kisi k ON m.kisi_id = k.kisi_id WHERE r.randevu_id NOT IN (SELECT randevu_id FROM muayene) ORDER BY r.tarih_saat ASC")->fetchAll();
$muayeneler = $pdo->query("SELECT m.muayene_id, m.teshis_ozeti, m.yekun_tutar, r.tarih_saat, h.ad AS hayvan_adi, k.ad_soyad AS sahip FROM muayene m JOIN randevu r ON m.randevu_id = r.randevu_id JOIN hayvan h ON r.hayvan_id = h.hayvan_id JOIN musteri mus ON r.musteri_id = mus.kisi_id JOIN kisi k ON mus.kisi_id = k.kisi_id ORDER BY m.muayene_id DESC LIMIT 10")->fetchAll();

$recete_detaylari = [];
foreach ($muayeneler as $m) {
    $mid = $m['muayene_id'];
    $ilaclar_listesi = $pdo->query("SELECT i.ad AS ilac_adi, ri.kullanim_sekli, ri.adet FROM recete r JOIN recete_ilac ri ON r.recete_id = ri.recete_id JOIN ilac i ON ri.ilac_id = i.ilac_id WHERE r.muayene_id = $mid")->fetchAll();
    if (!empty($ilaclar_listesi)) { $recete_detaylari[$mid] = $ilaclar_listesi; }
}

$hayvanlar = $pdo->query("SELECT h.hayvan_id, h.ad, k.ad_soyad FROM hayvan h JOIN musteri m ON h.musteri_id = m.kisi_id JOIN kisi k ON m.kisi_id = k.kisi_id ORDER BY h.ad")->fetchAll();
$hizmetler = $pdo->query("SELECT * FROM hizmet ORDER BY ad")->fetchAll();
$ilaclar   = $pdo->query("SELECT * FROM ilac WHERE stok_adet > 0 ORDER BY ad")->fetchAll();
$asilar_db = $pdo->query("SELECT * FROM asi ORDER BY ad")->fetchAll();

require_once 'muayene_view.php';
?>