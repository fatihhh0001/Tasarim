<?php include 'includes/menu.php'; ?>

<div class="mb-4">
    <h2><i class="fas fa-cogs"></i> Klinik Yönetimi</h2>
    <p class="text-muted">İlaç stokları, hizmet fiyatları, aşı tanımları ve personel yönetimini buradan yapabilirsiniz.</p>
</div>

<?php if (!empty($mesaj)): ?>
    <div class="alert alert-<?= $mesaj_tur ?> alert-dismissible fade show">
        <?= $mesaj ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3" id="yonetimTab" role="tablist">
  <li class="nav-item"><button class="nav-link active" id="ilac-tab" data-bs-toggle="tab" data-bs-target="#ilac"><i class="fas fa-pills"></i> İlaç Stoğu</button></li>
  <li class="nav-item"><button class="nav-link" id="hizmet-tab" data-bs-toggle="tab" data-bs-target="#hizmet"><i class="fas fa-stethoscope"></i> Hizmetler</button></li>
  <li class="nav-item"><button class="nav-link" id="asi-tab" data-bs-toggle="tab" data-bs-target="#asi"><i class="fas fa-syringe"></i> Aşılar</button></li>
  <li class="nav-item"><button class="nav-link" id="vet-tab" data-bs-toggle="tab" data-bs-target="#veteriner"><i class="fas fa-user-md"></i> Veteriner Hekimler</button></li>
</ul>

<div class="tab-content kutu">
    
    <div class="tab-pane fade show active" id="ilac">
        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="text-primary">Eczane Deposu</h5><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ilacEkleModal">+ İlaç Ekle</button></div>
        <table class="table table-hover table-sm align-middle"><thead><tr><th>İlaç Adı</th><th>Stok</th><th>Fiyat</th><th>Tedarikçi</th><th>İşlem</th></tr></thead><tbody><?php foreach ($ilaclar as $ilac): ?><tr class="<?= ($ilac['stok_adet'] < 20) ? 'table-danger' : '' ?>"><td><?= $ilac['ad'] ?></td><td class="fw-bold"><?= $ilac['stok_adet'] ?></td><td><?= $ilac['fiyat'] ?> ₺</td><td><small class="text-muted"><?= $ilac['firma_adi'] ?></small></td><td><button class="btn btn-sm btn-outline-primary" onclick="stokDuzenle(<?= $ilac['ilac_id'] ?>, '<?= $ilac['ad'] ?>', <?= $ilac['stok_adet'] ?>)" data-bs-toggle="modal" data-bs-target="#stokGuncelleModal"><i class="fas fa-edit"></i></button><form action="klinik_yonetim.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Silmek istediğinize emin misiniz?');"><input type="hidden" name="silinecek_id" value="<?= $ilac['ilac_id'] ?>"><button type="submit" name="ilac_sil" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr><?php endforeach; ?></tbody></table>
    </div>

    <div class="tab-pane fade" id="hizmet">
        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="text-success">Hizmet Listesi</h5><button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#hizmetEkleModal">+ Hizmet Ekle</button></div>
        <table class="table table-hover align-middle"><thead><tr><th>Hizmet Adı</th><th>Ücret</th><th>İşlem</th></tr></thead><tbody><?php foreach ($hizmetler as $h): ?><tr><td><?= $h['ad'] ?></td><td class="fw-bold"><?= number_format($h['birim_ucret'], 2) ?> ₺</td><td><button class="btn btn-sm btn-outline-success" onclick="hizmetDuzenle(<?= $h['hizmet_id'] ?>, '<?= $h['ad'] ?>', <?= $h['birim_ucret'] ?>)" data-bs-toggle="modal" data-bs-target="#hizmetGuncelleModal"><i class="fas fa-edit"></i></button><form action="klinik_yonetim.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Silmek istediğinize emin misiniz?');"><input type="hidden" name="silinecek_id" value="<?= $h['hizmet_id'] ?>"><button type="submit" name="hizmet_sil" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr><?php endforeach; ?></tbody></table>
    </div>

    <div class="tab-pane fade" id="asi">
        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="text-info">Aşı Tanımları</h5><button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#asiEkleModal">+ Aşı Tanımla</button></div>
        <table class="table table-hover align-middle"><thead><tr><th>Aşı Adı</th><th>Tekrar Süresi</th><th>İşlem</th></tr></thead><tbody><?php foreach ($asilar as $a): ?><tr><td><?= $a['ad'] ?></td><td><?= $a['tekrar_suresi_gun'] ?> Gün</td><td><button class="btn btn-sm btn-outline-info" onclick="asiDuzenle(<?= $a['asi_id'] ?>, '<?= $a['ad'] ?>', <?= $a['tekrar_suresi_gun'] ?>)" data-bs-toggle="modal" data-bs-target="#asiGuncelleModal"><i class="fas fa-edit"></i></button><form action="klinik_yonetim.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Silmek istediğinize emin misiniz?');"><input type="hidden" name="silinecek_id" value="<?= $a['asi_id'] ?>"><button type="submit" name="asi_sil" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr><?php endforeach; ?></tbody></table>
    </div>

    <div class="tab-pane fade" id="veteriner">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-secondary">Veteriner Hekim Kadrosu</h5>
            <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#vetEkleModal">
                <i class="fas fa-plus"></i> Hekim Ekle
            </button>
        </div>
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>Uzmanlık</th>
                    <th>Diploma No</th>
                    <th>Telefon</th>
                    <th>Adres</th> <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($veterinerler as $v): ?>
                <tr>
                    <td class="fw-bold"><?= $v['ad_soyad'] ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $v['uzmanlik'] ?></span></td>
                    <td><?= $v['diploma_no'] ?></td>
                    <td><?= $v['telefon'] ?></td>
                    <td><small class="text-muted"><?= $v['adres'] ?></small></td> <td>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="vetDuzenle(
                                    <?= $v['kisi_id'] ?>, 
                                    '<?= addslashes($v['ad_soyad']) ?>', 
                                    '<?= $v['telefon'] ?>', 
                                    '<?= addslashes($v['uzmanlik']) ?>', 
                                    '<?= $v['diploma_no'] ?>',
                                    '<?= addslashes(preg_replace( "/\r|\n/", " ", $v['adres'])) ?>' // YENİ VERİ
                                )"
                                data-bs-toggle="modal" data-bs-target="#vetGuncelleModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="klinik_yonetim.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                            <input type="hidden" name="silinecek_id" value="<?= $v['kisi_id'] ?>">
                            <button type="submit" name="veteriner_sil" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="ilacEkleModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="klinik_yonetim.php" method="POST"><div class="modal-header bg-primary text-white"><h5 class="modal-title">İlaç Girişi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label>Ad</label><input type="text" name="ad" class="form-control mb-2" required><label>Stok</label><input type="number" name="stok_adet" class="form-control mb-2" required><label>Fiyat</label><input type="number" step="0.01" name="fiyat" class="form-control mb-2" required><label>Tedarikçi</label><select name="tedarikci_id" class="form-select"><?php foreach ($tedarikciler as $t): ?><option value="<?= $t['tedarikci_id'] ?>"><?= $t['firma_adi'] ?></option><?php endforeach; ?></select></div><div class="modal-footer"><button type="submit" name="ilac_ekle" class="btn btn-primary">Kaydet</button></div></form></div></div>
<div class="modal fade" id="hizmetEkleModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="klinik_yonetim.php" method="POST"><div class="modal-header bg-success text-white"><h5 class="modal-title">Hizmet Ekle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label>Ad</label><input type="text" name="ad" class="form-control mb-2" required><label>Ücret</label><input type="number" step="0.01" name="birim_ucret" class="form-control mb-2" required></div><div class="modal-footer"><button type="submit" name="hizmet_ekle" class="btn btn-success">Kaydet</button></div></form></div></div>
<div class="modal fade" id="asiEkleModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="klinik_yonetim.php" method="POST"><div class="modal-header bg-info text-white"><h5 class="modal-title">Aşı Ekle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label>Ad</label><input type="text" name="ad" class="form-control mb-2" required><label>Süre</label><input type="number" name="tekrar_suresi_gun" class="form-control mb-2" required></div><div class="modal-footer"><button type="submit" name="asi_ekle" class="btn btn-info">Kaydet</button></div></form></div></div>

<div class="modal fade" id="vetEkleModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="klinik_yonetim.php" method="POST">
      <div class="modal-header bg-secondary text-white"><h5 class="modal-title">Veteriner Ekle</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
          <div class="mb-2"><label>Ad Soyad</label><input type="text" name="ad_soyad" class="form-control" required></div>
          <div class="mb-2"><label>Telefon</label><input type="text" name="telefon" class="form-control" maxlength="11"></div>
          <div class="mb-2"><label>Adres</label><textarea name="adres" class="form-control" rows="2"></textarea></div>
          
          <div class="mb-2"><label>Uzmanlık</label><input type="text" name="uzmanlik" class="form-control" placeholder="Örn: Cerrahi" required></div>
          <div class="mb-2"><label>Diploma No</label><input type="text" name="diploma_no" class="form-control" required></div>
      </div>
      <div class="modal-footer"><button type="submit" name="veteriner_ekle" class="btn btn-secondary">Kaydet</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="stokGuncelleModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="klinik_yonetim.php" method="POST"><div class="modal-header bg-warning"><h5 class="modal-title">Stok Güncelle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="ilac_id" id="guncelle_ilac_id"><div class="mb-3"><label>Seçilen İlaç</label><input type="text" id="guncelle_ilac_ad" class="form-control" disabled></div><div class="mb-3"><label>Yeni Stok</label><input type="number" name="yeni_stok" id="guncelle_yeni_stok" class="form-control" required></div></div><div class="modal-footer"><button type="submit" name="stok_guncelle" class="btn btn-warning">Güncelle</button></div></form></div></div>
<div class="modal fade" id="hizmetGuncelleModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="klinik_yonetim.php" method="POST"><div class="modal-header bg-success text-white"><h5 class="modal-title">Hizmet Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="hizmet_id" id="gh_id"><div class="mb-2"><label>Hizmet Adı</label><input type="text" name="ad" id="gh_ad" class="form-control" required></div><div class="mb-2"><label>Ücret</label><input type="number" step="0.01" name="birim_ucret" id="gh_ucret" class="form-control" required></div></div><div class="modal-footer"><button type="submit" name="hizmet_guncelle" class="btn btn-success">Kaydet</button></div></form></div></div>
<div class="modal fade" id="asiGuncelleModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="klinik_yonetim.php" method="POST"><div class="modal-header bg-info text-white"><h5 class="modal-title">Aşı Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="asi_id" id="ga_id"><div class="mb-2"><label>Aşı Adı</label><input type="text" name="ad" id="ga_ad" class="form-control" required></div><div class="mb-2"><label>Süre</label><input type="number" name="tekrar_suresi_gun" id="ga_sure" class="form-control" required></div></div><div class="modal-footer"><button type="submit" name="asi_guncelle" class="btn btn-info">Kaydet</button></div></form></div></div>

<div class="modal fade" id="vetGuncelleModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="klinik_yonetim.php" method="POST">
      <div class="modal-header bg-secondary text-white"><h5 class="modal-title">Veteriner Düzenle</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
          <input type="hidden" name="kisi_id" id="gv_id">
          <div class="mb-2"><label>Ad Soyad</label><input type="text" name="ad_soyad" id="gv_ad" class="form-control" required></div>
          <div class="mb-2"><label>Telefon</label><input type="text" name="telefon" id="gv_tel" class="form-control"></div>
          <div class="mb-2"><label>Adres</label><textarea name="adres" id="gv_adres" class="form-control" rows="2"></textarea></div>
          
          <div class="mb-2"><label>Uzmanlık</label><input type="text" name="uzmanlik" id="gv_uzmanlik" class="form-control" required></div>
          <div class="mb-2"><label>Diploma No</label><input type="text" name="diploma_no" id="gv_diploma" class="form-control" required></div>
      </div>
      <div class="modal-footer"><button type="submit" name="veteriner_guncelle" class="btn btn-secondary">Güncelle</button></div>
    </form>
  </div>
</div>

<script>
function stokDuzenle(id, ad, stok) { document.getElementById('guncelle_ilac_id').value = id; document.getElementById('guncelle_ilac_ad').value = ad; document.getElementById('guncelle_yeni_stok').value = stok; }
function hizmetDuzenle(id, ad, ucret) { document.getElementById('gh_id').value = id; document.getElementById('gh_ad').value = ad; document.getElementById('gh_ucret').value = ucret; }
function asiDuzenle(id, ad, sure) { document.getElementById('ga_id').value = id; document.getElementById('ga_ad').value = ad; document.getElementById('ga_sure').value = sure; }
// YENİ FONKSİYON: ADRES EKLENDİ
function vetDuzenle(id, ad, tel, uzm, dip, adres) { 
    document.getElementById('gv_id').value = id; 
    document.getElementById('gv_ad').value = ad; 
    document.getElementById('gv_tel').value = tel; 
    document.getElementById('gv_uzmanlik').value = uzm; 
    document.getElementById('gv_diploma').value = dip; 
    document.getElementById('gv_adres').value = adres; // Adres verisini kutuya yaz
}
</script>

<?php include 'includes/footer.php'; ?>