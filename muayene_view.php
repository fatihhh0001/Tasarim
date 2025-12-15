<?php include 'includes/menu.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-md"></i> Muayene & Aşı İşlemleri</h2>
    <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#islemModal">
        <i class="fas fa-plus-circle"></i> Yeni İşlem Başlat
    </button>
</div>

<?php if (!empty($mesaj)): ?>
    <div class="alert alert-<?= $mesaj_tur ?> alert-dismissible fade show">
        <?= $mesaj ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="kutu">
    <h5>Son İşlemler</h5>
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr><th>Tarih</th><th>Hasta</th><th>Sahip</th><th>Teşhis / İşlem Notu</th><th>Tutar</th><th>Reçete</th></tr>
        </thead>
        <tbody>
            <?php foreach ($muayeneler as $m): ?>
            <tr>
                <td><?= date('d.m.Y H:i', strtotime($m['tarih_saat'])) ?></td>
                <td class="fw-bold"><?= $m['hayvan_adi'] ?></td>
                <td><?= $m['sahip'] ?></td>
                <td>
                    <div style="max-height: 60px; overflow-y:auto; font-size:0.9rem;">
                        <?= nl2br($m['teshis_ozeti']) ?>
                    </div>
                </td>
                <td class="text-success fw-bold"><?= number_format($m['yekun_tutar'], 2) ?> ₺</td>
                <td>
                    <?php if (isset($recete_detaylari[$m['muayene_id']])): ?>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#receteModal_<?= $m['muayene_id'] ?>">Reçete Gör</button>
                        <div class="modal fade" id="receteModal_<?= $m['muayene_id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Reçete</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><table class="table table-sm"><thead><tr><th>İlaç</th><th>Adet</th><th>Kullanım</th></tr></thead><tbody><?php foreach ($recete_detaylari[$m['muayene_id']] as $ilac): ?><tr><td><?= $ilac['ilac_adi'] ?></td><td><?= $ilac['adet'] ?></td><td><?= $ilac['kullanim_sekli'] ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
                    <?php else: ?><span class="text-muted small">Yok</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="islemModal" tabindex="-1">
  <div class="modal-dialog modal-xl" style="max-width: 95%;"> 
    <form class="modal-content" action="muayene.php" method="POST">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Muayene & Aşı Ekranı</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        
        <div class="row mb-3 border-bottom pb-3">
            <div class="col-12 text-center">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="islem_turu" id="turu_planli" value="planli" checked onclick="turDegistir('planli')">
                    <label class="btn btn-outline-danger" for="turu_planli">📅 Planlı Randevu</label>

                    <input type="radio" class="btn-check" name="islem_turu" id="turu_acil" value="acil" onclick="turDegistir('acil')">
                    <label class="btn btn-outline-danger" for="turu_acil">🚨 Acil / Randevusuz</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 border-end">
                <h6 class="text-danger border-bottom pb-2">1. Hasta Bilgisi</h6>
                
                <div id="blok_planli">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Randevu Seçin</label>
                        <select name="mevcut_randevu_id" class="form-select">
                            <option value="">Seçiniz...</option>
                            <?php foreach ($bekleyen_randevular as $br): ?>
                                <option value="<?= $br['randevu_id'] ?>"><?= date('d.m H:i', strtotime($br['tarih_saat'])) ?> - <?= $br['hayvan_adi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="blok_acil" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hasta Seçin</label>
                        <select name="hayvan_id" class="form-select">
                            <option value="">Seçiniz...</option>
                            <?php foreach ($hayvanlar as $h): ?>
                                <option value="<?= $h['hayvan_id'] ?>"><?= $h['ad'] ?> (<?= $h['ad_soyad'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notlar</label>
                    <textarea name="teshis_ozeti" class="form-control" rows="10" required placeholder="Teşhis, bulgular ve yapılanlar..."></textarea>
                </div>
            </div>

            <div class="col-md-3 border-end">
                <h6 class="text-primary border-bottom pb-2">2. Klinik Hizmetleri</h6>
                <div style="max-height: 450px; overflow-y: auto;">
                    <ul class="list-group">
                        <?php foreach ($hizmetler as $hz): ?>
                        <li class="list-group-item">
                            <input class="form-check-input me-1" type="checkbox" name="hizmetler[]" value="<?= $hz['hizmet_id'] ?>" id="h_<?= $hz['hizmet_id'] ?>">
                            <label class="form-check-label stretched-link d-flex justify-content-between" for="h_<?= $hz['hizmet_id'] ?>">
                                <span><?= $hz['ad'] ?></span>
                                <span class="badge bg-secondary"><?= number_format($hz['birim_ucret'], 0) ?> ₺</span>
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="col-md-3 border-end bg-light">
                <h6 class="text-info border-bottom pb-2">3. Aşı Uygulaması</h6>
                <div class="alert alert-info py-1 small"><i class="fas fa-info-circle"></i> Seçilen aşılar <b>Aşı Takip</b> sistemine işlenir.</div>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-borderless">
                        <thead><tr><th>Aşı</th><th style="width:80px">Fiyat</th></tr></thead>
                        <tbody>
                            <?php foreach ($asilar_db as $asi): ?>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="asilar[]" value="<?= $asi['asi_id'] ?>" id="asi_<?= $asi['asi_id'] ?>">
                                        <label class="form-check-label" for="asi_<?= $asi['asi_id'] ?>">
                                            <?= $asi['ad'] ?>
                                            <br><small class="text-muted"><?= $asi['tekrar_suresi_gun'] ?> günde bir</small>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="asi_fiyat[<?= $asi['asi_id'] ?>]" class="form-control form-control-sm" placeholder="₺" value="0">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-3">
                <h6 class="text-success border-bottom pb-2">4. Reçete Yaz</h6>
                <div style="max-height: 450px; overflow-y: auto;">
                    <table class="table table-sm">
                        <thead><tr><th>İlaç</th><th style="width:60px">Adet</th></tr></thead>
                        <tbody>
                            <?php foreach ($ilaclar as $ilac): ?>
                            <tr class="<?= ($ilac['stok_adet'] == 0) ? 'table-danger' : '' ?>">
                                <td>
                                    <strong><?= $ilac['ad'] ?></strong>
                                    <div class="text-muted small">
                                        Stok: <span class="fw-bold text-dark"><?= $ilac['stok_adet'] ?></span>
                                    </div>
                                    
                                    <input type="text" name="kullanim[<?= $ilac['ilac_id'] ?>]" class="form-control form-control-sm mt-1" placeholder="Kullanım Şekli">
                                </td>
                                <td>
                                    <input type="number" name="ilaclar[<?= $ilac['ilac_id'] ?>]" class="form-control form-control-sm" min="0" value="0" <?= ($ilac['stok_adet'] == 0) ? 'disabled' : '' ?>>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button><button type="submit" name="islem_tamamla" class="btn btn-danger px-5">KAYDET</button></div>
    </form>
  </div>
</div>

<script>
function turDegistir(tur) {
    if(tur == 'planli') {
        document.getElementById('blok_planli').style.display = 'block';
        document.getElementById('blok_acil').style.display = 'none';
    } else {
        document.getElementById('blok_planli').style.display = 'none';
        document.getElementById('blok_acil').style.display = 'block';
    }
}
</script>

<?php include 'includes/footer.php'; ?>