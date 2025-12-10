

-- 1. KISI TABLOSU (Üst Varlık - Kalıtım Çatısı)
CREATE TABLE kisi (
    kisi_id SERIAL PRIMARY KEY,  -- Otomatik artan ID
    ad_soyad VARCHAR(100) NOT NULL,
    telefon CHAR(11),
    adres TEXT,
    kisi_tipi CHAR(1) NOT NULL, -- 'M': Müşteri, 'V': Veteriner
    CONSTRAINT chk_kisi_tipi CHECK (kisi_tipi IN ('M', 'V'))
);

-- 2. MUSTERI TABLOSU (Alt Varlık - Kalıtım)

CREATE TABLE musteri (
    kisi_id INTEGER PRIMARY KEY, 
    bakiye DECIMAL(10, 2) DEFAULT 0,
    kayit_tarihi DATE DEFAULT CURRENT_DATE,
    CONSTRAINT fk_musteri_kisi FOREIGN KEY (kisi_id) 
        REFERENCES kisi(kisi_id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

-- 3. VETERINER TABLOSU (Alt Varlık - Kalıtım)

CREATE TABLE veteriner (
    kisi_id INTEGER PRIMARY KEY,
    uzmanlik VARCHAR(50),
    diploma_no VARCHAR(20),
    CONSTRAINT fk_veteriner_kisi FOREIGN KEY (kisi_id) 
        REFERENCES kisi(kisi_id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

-- 4. TUR TABLOSU (Kedi, Köpek vb.)
CREATE TABLE tur (
    tur_id SERIAL PRIMARY KEY,
    ad VARCHAR(50) NOT NULL
);

-- 5. CINS TABLOSU
CREATE TABLE cins (
    cins_id SERIAL PRIMARY KEY,
    tur_id INTEGER NOT NULL,
    ad VARCHAR(50) NOT NULL,
    CONSTRAINT fk_cins_tur FOREIGN KEY (tur_id) REFERENCES tur(tur_id)
);

-- 6. HAYVAN TABLOSU
-- Müşteri ID değişirse hayvan da güncellensin diye buraya da eklendi.
CREATE TABLE hayvan (
    hayvan_id SERIAL PRIMARY KEY,
    musteri_id INTEGER NOT NULL,
    cins_id INTEGER NOT NULL,
    ad VARCHAR(50) NOT NULL,
    cip_no CHAR(15) UNIQUE,
    dogum_tarihi DATE,
    CONSTRAINT fk_hayvan_musteri FOREIGN KEY (musteri_id) 
        REFERENCES musteri(kisi_id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_hayvan_cins FOREIGN KEY (cins_id) REFERENCES cins(cins_id)
);

-- 7. ASI TABLOSU (Tanımlar)
CREATE TABLE asi (
    asi_id SERIAL PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    tekrar_suresi_gun INTEGER
);

-- 8. ASI_TAKIP TABLOSU
CREATE TABLE asi_takip (
    takip_id SERIAL PRIMARY KEY,
    hayvan_id INTEGER NOT NULL,
    asi_id INTEGER NOT NULL,
    uygulama_tarihi DATE NOT NULL,
    gelecek_tarih DATE,
    CONSTRAINT fk_takip_hayvan FOREIGN KEY (hayvan_id) 
        REFERENCES hayvan(hayvan_id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_takip_asi FOREIGN KEY (asi_id) REFERENCES asi(asi_id)
);

-- 9. RANDEVU TABLOSU
CREATE TABLE randevu (
    randevu_id SERIAL PRIMARY KEY,
    musteri_id INTEGER NOT NULL,
    veteriner_id INTEGER NOT NULL,
    hayvan_id INTEGER NOT NULL,
    tarih_saat TIMESTAMP NOT NULL,
    CONSTRAINT fk_randevu_musteri FOREIGN KEY (musteri_id) REFERENCES musteri(kisi_id),
    CONSTRAINT fk_randevu_veteriner FOREIGN KEY (veteriner_id) REFERENCES veteriner(kisi_id),
    CONSTRAINT fk_randevu_hayvan FOREIGN KEY (hayvan_id) 
        REFERENCES hayvan(hayvan_id) 
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- 10. MUAYENE TABLOSU
CREATE TABLE muayene (
    muayene_id SERIAL PRIMARY KEY,
    randevu_id INTEGER NOT NULL UNIQUE, 
    teshis_ozeti TEXT,
    yekun_tutar DECIMAL(10, 2),
    CONSTRAINT fk_muayene_randevu FOREIGN KEY (randevu_id) 
        REFERENCES randevu(randevu_id)
        ON DELETE CASCADE -- Randevu silinirse muayene kaydı da silinsin
        ON UPDATE CASCADE
);

-- 11. HIZMET TABLOSU (Hizmet Kataloğu)
CREATE TABLE hizmet (
    hizmet_id SERIAL PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    birim_ucret DECIMAL(10, 2)
);

-- 12. MUAYENE_HIZMET TABLOSU (ARA TABLO)
CREATE TABLE muayene_hizmet (
    muayene_id INTEGER NOT NULL,
    hizmet_id INTEGER NOT NULL,
    adet INTEGER DEFAULT 1,
    uygulanan_fiyat DECIMAL(10, 2),
    PRIMARY KEY (muayene_id, hizmet_id), 
    CONSTRAINT fk_mh_muayene FOREIGN KEY (muayene_id) 
        REFERENCES muayene(muayene_id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_mh_hizmet FOREIGN KEY (hizmet_id) REFERENCES hizmet(hizmet_id)
);

-- 13. TEDARIKCI TABLOSU
CREATE TABLE tedarikci (
    tedarikci_id SERIAL PRIMARY KEY,
    firma_adi VARCHAR(100) NOT NULL,
    telefon CHAR(11)
);

-- 14. ILAC TABLOSU
CREATE TABLE ilac (
    ilac_id SERIAL PRIMARY KEY,
    tedarikci_id INTEGER NOT NULL,
    ad VARCHAR(100) NOT NULL,
    stok_adet INTEGER DEFAULT 0,
    fiyat DECIMAL(10, 2),
    CONSTRAINT fk_ilac_tedarikci FOREIGN KEY (tedarikci_id) REFERENCES tedarikci(tedarikci_id)
);

-- 15. RECETE TABLOSU
CREATE TABLE recete (
    recete_id SERIAL PRIMARY KEY,
    muayene_id INTEGER NOT NULL,
    tarih DATE DEFAULT CURRENT_DATE,
    CONSTRAINT fk_recete_muayene FOREIGN KEY (muayene_id) 
        REFERENCES muayene(muayene_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- 16. RECETE_ILAC TABLOSU (ARA TABLO)
CREATE TABLE recete_ilac (
    recete_id INTEGER NOT NULL,
    ilac_id INTEGER NOT NULL,
    kullanim_sekli VARCHAR(200),
    adet INTEGER NOT NULL,
    PRIMARY KEY (recete_id, ilac_id), 
    CONSTRAINT fk_ri_recete FOREIGN KEY (recete_id) 
        REFERENCES recete(recete_id) 
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_ri_ilac FOREIGN KEY (ilac_id) REFERENCES ilac(ilac_id)
);