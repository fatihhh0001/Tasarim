<?php include 'includes/menu.php'; ?>

<div class="row mb-4 text-center">
    
    <div class="col-md-3">
        <div class="kutu border-start border-4 border-success py-4 h-100">
            <h5 class="text-secondary">Toplam Ciro</h5>
            <h2 class="text-success fw-bold"><?= number_format($ciro, 2) ?> ₺</h2>
            <i class="fas fa-coins fa-2x text-success opacity-50"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="kutu border-start border-4 border-primary py-4 h-100">
            <h5 class="text-secondary">Kayıtlı Müşteri</h5>
            <h2 class="text-primary fw-bold"><?= $musteri_sayisi ?></h2>
            <i class="fas fa-users fa-2x text-primary opacity-50"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="kutu border-start border-4 border-info py-4 h-100">
            <h5 class="text-secondary">Kayıtlı Hasta</h5>
            <h2 class="text-info fw-bold"><?= $hasta_sayisi ?></h2>
            <i class="fas fa-cat fa-2x text-info opacity-50"></i>
        </div>
    </div>

    <div class="col-md-3">
        <div class="kutu border-start border-4 border-danger py-4 h-100">
            <h5 class="text-secondary">Kritik İlaç</h5>
            <h2 class="text-danger fw-bold"><?= $kritik_stok ?></h2>
            <i class="fas fa-pills fa-2x text-danger opacity-50"></i>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-md-8">
        <div class="kutu h-100">
            <h5 class="border-bottom pb-2 mb-3">
                <i class="fas fa-history text-primary"></i> Son 5 Muayene
            </h5>
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Hasta</th>
                        <th>Sahip</th>
                        <th>Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($son_hareketler as $hareket): ?>
                    <tr>
                        <td><?= date('d.m H:i', strtotime($hareket['tarih_saat'])) ?></td>
                        <td class="fw-bold"><?= $hareket['hayvan_adi'] ?></td>
                        <td><?= $hareket['sahip'] ?></td>
                        <td class="text-success fw-bold"><?= number_format($hareket['yekun_tutar'], 2) ?> ₺</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($son_hareketler)) echo "<tr><td colspan='4' class='text-muted'>Henüz işlem yok.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="kutu h-100">
            <h5 class="border-bottom pb-2 mb-3 text-danger">
                <i class="fas fa-exclamation-triangle"></i> Azalan Stoklar
            </h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($biten_ilaclar as $ilac): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $ilac['ad'] ?>
                    <span class="badge bg-danger rounded-pill"><?= $ilac['stok_adet'] ?> Adet</span>
                </li>
                <?php endforeach; ?>
                <?php if(empty($biten_ilaclar)) echo "<li class='list-group-item text-muted'>Stok sorunu yok.</li>"; ?>
            </ul>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>