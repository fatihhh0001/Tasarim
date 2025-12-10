<?php include 'includes/menu.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users"></i> Müşteri Listesi</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ekleModal">
        <i class="fas fa-plus"></i> Yeni Müşteri Ekle
    </button>
</div>

<?php if (!empty($mesaj)): ?>
    <div class="alert alert-<?php echo $mesaj_tur; ?> alert-dismissible fade show">
        <?php echo $mesaj; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="kutu">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ad Soyad</th>
                <th>Telefon</th>
                <th>Adres</th>
                <th>Bakiye</th>
                <th style="width: 200px;">İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($musteriler as $m): ?>
            <tr>
                <td>#<?php echo $m['kisi_id']; ?></td>
                <td class="fw-bold"><?php echo $m['ad_soyad']; ?></td>
                <td><?php echo $m['telefon']; ?></td>
                <td><?php echo $m['adres']; ?></td>
                <td>
                    <?php if ($m['bakiye'] > 0): ?>
                        <span class="badge bg-danger"><?php echo number_format($m['bakiye'], 2); ?> ₺</span>
                    <?php else: ?>
                        <span class="badge bg-success">Yok</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="hayvanlar.php?musteri_id=<?php echo $m['kisi_id']; ?>" class="btn btn-sm btn-info text-white" title="Hayvanlar"><i class="fas fa-paw"></i></a>

                        <button type="button" class="btn btn-sm btn-warning text-dark" 
                                onclick="musteriDuzenle(
                                    <?= $m['kisi_id'] ?>, 
                                    '<?= addslashes($m['ad_soyad']) ?>', 
                                    '<?= $m['telefon'] ?>', 
                                    '<?= addslashes(preg_replace( "/\r|\n/", " ", $m['adres'])) ?>',
                                    <?= $m['bakiye'] ?>  // YENİ: Bakiye verisi
                                )"
                                data-bs-toggle="modal" data-bs-target="#guncelleModal" title="Düzenle">
                            <i class="fas fa-edit"></i>
                        </button>

                        <form action="musteriler.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Emin misiniz?');">
                            <input type="hidden" name="silinecek_id" value="<?= $m['kisi_id'] ?>">
                            <button type="submit" name="musteri_sil" class="btn btn-sm btn-danger" title="Sil"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="ekleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Yeni Müşteri</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="musteriler.php" method="POST"><div class="modal-body"><div class="mb-3"><label>Ad Soyad</label><input type="text" name="ad_soyad" class="form-control" required></div><div class="mb-3"><label>Telefon</label><input type="text" name="telefon" class="form-control" maxlength="11"></div><div class="mb-3"><label>Adres</label><textarea name="adres" class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="submit" name="musteri_ekle" class="btn btn-primary">Kaydet</button></div></form></div></div></div>

<div class="modal fade" id="guncelleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Müşteri Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="musteriler.php" method="POST">
          <div class="modal-body">
            <input type="hidden" name="kisi_id" id="gun_id">
            
            <div class="mb-3">
                <label>Ad Soyad</label>
                <input type="text" name="ad_soyad" id="gun_ad" class="form-control" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Telefon</label>
                    <input type="text" name="telefon" id="gun_tel" class="form-control" maxlength="11">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-danger">Güncel Bakiye (TL)</label>
                    <input type="number" step="0.01" name="bakiye" id="gun_bakiye" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label>Adres</label>
                <textarea name="adres" id="gun_adres" class="form-control" rows="2"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="musteri_guncelle" class="btn btn-warning">Güncelle</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function musteriDuzenle(id, ad, tel, adres, bakiye) {
    document.getElementById('gun_id').value = id;
    document.getElementById('gun_ad').value = ad;
    document.getElementById('gun_tel').value = tel;
    document.getElementById('gun_adres').value = adres;
    document.getElementById('gun_bakiye').value = bakiye; // Bakiyeyi kutuya yaz
}
</script>

<?php include 'includes/footer.php'; ?>