<?php include 'includes/menu.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-paw"></i> Hasta (Hayvan) Listesi</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#hayvanEkleModal">
        <i class="fas fa-plus"></i> Yeni Hasta Ekle
    </button>
</div>

<?php if (!empty($mesaj)): ?>
    <div class="alert alert-<?= $mesaj_tur ?> alert-dismissible fade show">
        <?= $mesaj ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="kutu">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Hayvan Adı</th>
                <th>Türü / Cinsi</th>
                <th>Sahibi</th>
                <th>Çip No</th>
                <th>Doğum Tarihi</th>
                <th style="width: 250px;">İşlem</th> </tr>
        </thead>
        <tbody>
            <?php foreach ($hayvanlar as $h): ?>
            <tr>
                <td>#<?= $h['hayvan_id'] ?></td>
                <td class="fw-bold text-primary"><?= $h['ad'] ?></td>
                <td>
                    <span class="badge bg-secondary"><?= $h['tur_adi'] ?></span>
                    <?= $h['cins_adi'] ?>
                </td>
                <td><?= $h['sahip'] ?></td>
                <td><?= $h['cip_no'] ?: '-' ?></td>
                <td><?= $h['dogum_tarihi'] ?></td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="muayene.php?hayvan_id=<?= $h['hayvan_id'] ?>" class="btn btn-sm btn-warning" title="Muayene Et">
                            <i class="fas fa-stethoscope"></i>
                        </a>

                        <button type="button" class="btn btn-sm btn-info text-white" 
                                onclick="hayvanDuzenle(
                                    <?= $h['hayvan_id'] ?>, 
                                    '<?= addslashes($h['ad']) ?>', 
                                    <?= $h['cins_id'] ?>, 
                                    '<?= $h['cip_no'] ?>', 
                                    '<?= $h['dogum_tarihi'] ?>'
                                )"
                                data-bs-toggle="modal" data-bs-target="#hayvanGuncelleModal" title="Düzenle">
                            <i class="fas fa-edit"></i>
                        </button>

                        <form action="hayvanlar.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Bu hastayı silmek istediğinize emin misiniz?');">
                            <input type="hidden" name="silinecek_id" value="<?= $h['hayvan_id'] ?>">
                            <button type="submit" name="hayvan_sil" class="btn btn-sm btn-danger" title="Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (count($hayvanlar) == 0): ?>
                <tr><td colspan="7" class="text-center text-muted">Kayıtlı hasta bulunamadı.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="hayvanEkleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h5 class="modal-title">Yeni Hasta Kaydı</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="hayvanlar.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Hayvan Sahibi</label>
                <select name="musteri_id" class="form-select" required>
                    <option value="">Seçiniz...</option>
                    <?php foreach ($musteriler as $mus): ?><option value="<?= $mus['kisi_id'] ?>"><?= $mus['ad_soyad'] ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Türü ve Cinsi</label>
                <select name="cins_id" class="form-select" required>
                    <option value="">Seçiniz...</option>
                    <?php foreach ($cinsler as $c): ?><option value="<?= $c['cins_id'] ?>"><?= $c['tur_adi'] ?> - <?= $c['ad'] ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Hayvan Adı</label><input type="text" name="ad" class="form-control" required></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Çip No</label><input type="text" name="cip_no" class="form-control"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Doğum Tarihi</label><input type="date" name="dogum_tarihi" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            </div>
          </div>
          <div class="modal-footer"><button type="submit" name="hayvan_ekle" class="btn btn-primary">Kaydet</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="hayvanGuncelleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Hasta Bilgilerini Düzenle</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="hayvanlar.php" method="POST">
          <div class="modal-body">
            <input type="hidden" name="hayvan_id" id="gun_id">
            
            <div class="mb-3">
                <label class="form-label">Hayvan Adı</label>
                <input type="text" name="ad" id="gun_ad" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Türü ve Cinsi</label>
                <select name="cins_id" id="gun_cins" class="form-select" required>
                    <?php foreach ($cinsler as $c): ?>
                        <option value="<?= $c['cins_id'] ?>"><?= $c['tur_adi'] ?> - <?= $c['ad'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Çip No</label>
                    <input type="text" name="cip_no" id="gun_cip" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Doğum Tarihi</label>
                    <input type="date" name="dogum_tarihi" id="gun_dogum" class="form-control">
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="hayvan_guncelle" class="btn btn-info text-white">Güncelle</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function hayvanDuzenle(id, ad, cinsID, cip, dogum) {
    document.getElementById('gun_id').value = id;
    document.getElementById('gun_ad').value = ad;
    document.getElementById('gun_cins').value = cinsID; // Dropdown'da ilgili cinsi seçer
    document.getElementById('gun_cip').value = cip;
    document.getElementById('gun_dogum').value = dogum;
}
</script>

<?php include 'includes/footer.php'; ?>