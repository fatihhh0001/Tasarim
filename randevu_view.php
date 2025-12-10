<?php include 'includes/menu.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-alt"></i> Randevu Takvimi</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#randevuEkleModal">
        <i class="fas fa-plus"></i> Yeni Randevu Oluştur
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
                <th>Tarih & Saat</th>
                <th>Hasta Bilgisi</th>
                <th>Sahibi</th>
                <th>Veteriner Hekim</th>
                <th>Durum</th>
                <th style="width: 150px;">İşlem</th> </tr>
        </thead>
        <tbody>
            <?php foreach ($randevular as $r): 
                $zaman = strtotime($r['tarih_saat']);
                $su_an = time();
                $islem_yapildi = !empty($r['muayene_id']);
                $suresi_gecti = $su_an > $zaman;
            ?>
            <tr class="<?= ($islem_yapildi) ? 'table-success' : (($suresi_gecti) ? 'table-secondary text-muted' : '') ?>">
                <td>
                    <i class="far fa-clock"></i> <?= date('d.m.Y H:i', $zaman) ?>
                </td>
                <td class="fw-bold">
                    <?= $r['hayvan_adi'] ?> 
                    <small class="badge bg-light text-dark border"><?= $r['tur_adi'] ?></small>
                </td>
                <td><?= $r['musteri_adi'] ?></td>
                <td><?= $r['veteriner_adi'] ?></td>
                <td>
                    <?php if($islem_yapildi): ?>
                        <span class="badge bg-success"><i class="fas fa-check"></i> Tamamlandı</span>
                    <?php elseif($suresi_gecti): ?>
                        <span class="badge bg-secondary">Süresi Geçti</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Bekliyor</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        
                        <?php if($islem_yapildi): ?>
                            <a href="muayene.php" class="btn btn-sm btn-outline-success" title="Muayene Detayı">
                                <i class="fas fa-file-medical"></i>
                            </a>
                        <?php endif; ?>

                        <form action="randevu.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Bu randevuyu silmek istediğinize emin misiniz?');">
                            <input type="hidden" name="silinecek_id" value="<?= $r['randevu_id'] ?>">
                            <button type="submit" name="randevu_sil" class="btn btn-sm btn-danger" title="Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>

                    </div>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (count($randevular) == 0): ?>
                <tr><td colspan="6" class="text-center text-muted">Planlanmış randevu bulunmuyor.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="randevuEkleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Randevu Oluştur</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form action="randevu.php" method="POST"><div class="modal-body"><div class="mb-3"><label class="form-label">Hasta Seçimi</label><select name="hayvan_id" id="hayvanSelect" class="form-select" required onchange="musteriAta(this)"><option value="">Seçiniz...</option><?php foreach ($hayvanlar as $h): ?><option value="<?= $h['hayvan_id'] ?>" data-musteri="<?= $h['musteri_id'] ?>"><?= $h['ad'] ?> (Sahibi: <?= $h['sahip'] ?>)</option><?php endforeach; ?></select><input type="hidden" name="musteri_id" id="gizliMusteriID"></div><div class="mb-3"><label class="form-label">Veteriner Hekim</label><select name="veteriner_id" class="form-select" required><?php foreach ($veterinerler as $v): ?><option value="<?= $v['kisi_id'] ?>"><?= $v['ad_soyad'] ?> (<?= $v['uzmanlik'] ?>)</option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Randevu Tarihi ve Saati</label><input type="datetime-local" name="tarih_saat" class="form-control" required><div class="form-text text-danger">Dikkat: Aynı veterinere aynı saatte randevu verilemez!</div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button><button type="submit" name="randevu_olustur" class="btn btn-primary">Oluştur</button></div></form></div></div></div><script>function musteriAta(selectObject) {var secilenOption = selectObject.options[selectObject.selectedIndex];var musteriID = secilenOption.getAttribute('data-musteri');document.getElementById('gizliMusteriID').value = musteriID;}</script>

<?php include 'includes/footer.php'; ?>