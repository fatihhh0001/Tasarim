<?php include 'includes/menu.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-md"></i> Muayene & Reçete</h2>
    <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#islemModal">
        <i class="fas fa-plus-circle"></i> Yeni Muayene Başlat
    </button>
</div>

<?php if (!empty($mesaj)): ?>
    <div class="alert alert-<?= $mesaj_tur ?> alert-dismissible fade show">
        <?= $mesaj ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="kutu">
    <h5>Son Muayeneler</h5>
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr><th>Tarih</th><th>Hasta</th><th>Sahip</th><th>Teşhis</th><th>Tutar</th><th>Reçete</th></tr>
        </thead>
        <tbody>
            <?php foreach ($muayeneler as $m): ?>
            <tr>
                <td><?= date('d.m.Y H:i', strtotime($m['tarih_saat'])) ?></td>
                <td class="fw-bold"><?= $m['hayvan_adi'] ?></td>
                <td><?= $m['sahip'] ?></td>
                <td><?= $m['teshis_ozeti'] ?></td>
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
  <div class="modal-dialog modal-xl">
    <form class="modal-content" action="muayene.php" method="POST">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Muayene Başlat</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        
        <div class="row mb-3 border-bottom pb-3">
            <div class="col-12 text-center">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="islem_turu" id="turu_planli" value="planli" checked onclick="turDegistir('planli')">
                    <label class="btn btn-outline-danger" for="turu_planli">📅 Planlı Randevudan Seç</label>

                    <input type="radio" class="btn-check" name="islem_turu" id="turu_acil" value="acil" onclick="turDegistir('acil')">
                    <label class="btn btn-outline-danger" for="turu_acil">🚨 Acil / Randevusuz</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 border-end">
                <h6 class="text-danger border-bottom pb-2">1. Hasta Bilgisi</h6>
                
                <div id="blok_planli">
    <div class="mb-3">
        <label class="form-label fw-bold">Bekleyen Randevular</label>
        <select name="mevcut_randevu_id" class="form-select">
            <option value="">Randevu Seçiniz...</option>
            <?php foreach ($bekleyen_randevular as $br): ?>
                <option value="<?= $br['randevu_id'] ?>">
                    <?= date('d.m.Y H:i', strtotime($br['tarih_saat'])) ?> - <?= $br['hayvan_adi'] ?> (<?= $br['sahip'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <div class="form-text text-muted">Sadece henüz muayenesi yapılmamış randevular listelenir.</div>
    </div>
</div>

                <div id="blok_acil" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tüm Hastalar</label>
                        <select name="hayvan_id" class="form-select">
                            <option value="">Hasta Seçiniz...</option>
                            <?php foreach ($hayvanlar as $h): ?>
                                <option value="<?= $h['hayvan_id'] ?>"><?= $h['ad'] ?> (<?= $h['ad_soyad'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted">Randevu kaydı otomatik oluşturulacaktır.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Teşhis / Notlar</label>
                    <textarea name="teshis_ozeti" class="form-control" rows="8" required placeholder="Bulgular..."></textarea>
                </div>
            </div>

            <div class="col-md-4 border-end">
                <h6 class="text-primary border-bottom pb-2">2. Yapılan İşlemler</h6>
                <div style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group">
                        <?php foreach ($hizmetler as $hz): ?>
                        <li class="list-group-item">
                            <input class="form-check-input me-1" type="checkbox" name="hizmetler[]" value="<?= $hz['hizmet_id'] ?>" id="h_<?= $hz['hizmet_id'] ?>">
                            <label class="form-check-label stretched-link d-flex justify-content-between" for="h_<?= $hz['hizmet_id'] ?>">
                                <span><?= $hz['ad'] ?></span><span class="badge bg-secondary"><?= number_format($hz['birim_ucret'], 0) ?> ₺</span>
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="col-md-4">
                <h6 class="text-success border-bottom pb-2">3. Reçete Yaz</h6>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm">
                        <thead><tr><th>İlaç</th><th style="width:60px">Adet</th></tr></thead>
                        <tbody>
                            <?php foreach ($ilaclar as $ilac): ?>
                            <tr>
                                <td><strong><?= $ilac['ad'] ?></strong><br><small class="text-muted">Stok: <?= $ilac['stok_adet'] ?></small><input type="text" name="kullanim[<?= $ilac['ilac_id'] ?>]" class="form-control form-control-sm mt-1" placeholder="Kullanım"></td>
                                <td><input type="number" name="ilaclar[<?= $ilac['ilac_id'] ?>]" class="form-control form-control-sm" min="0" value="0"></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button><button type="submit" name="islem_tamamla" class="btn btn-danger px-4">Kaydet</button></div>
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