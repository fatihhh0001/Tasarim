
-- 1. SAKLI YORDAMLAR (STORED PROCEDURES / FUNCTIONS)


-- Fonksiyon 1: Yeni Müşteri Ekleme (Kalıtım Yönetimi)
-- Amaç: Tek seferde hem Kisi hem Musteri tablosuna kayıt atmak.
CREATE OR REPLACE FUNCTION yeni_musteri_kayit(
    p_ad_soyad VARCHAR,
    p_telefon CHAR,
    p_adres TEXT
) RETURNS VOID AS $$
DECLARE
    v_yeni_id INTEGER;
BEGIN
    -- Önce üst tabloya (Kisi) ekle ve ID'yi al
    INSERT INTO kisi (ad_soyad, telefon, adres, kisi_tipi)
    VALUES (p_ad_soyad, p_telefon, p_adres, 'M')
    RETURNING kisi_id INTO v_yeni_id;

    -- Sonra alt tabloya (Musteri) ekle
    INSERT INTO musteri (kisi_id, bakiye, kayit_tarihi)
    VALUES (v_yeni_id, 0, CURRENT_DATE);
END;
$$ LANGUAGE plpgsql;


-- Fonksiyon 2: İki Tarih Arası Ciro Hesaplama
-- Amaç: Muhasebe raporu.
CREATE OR REPLACE FUNCTION iki_tarih_arasi_ciro(
    p_baslangic DATE,
    p_bitis DATE
) RETURNS DECIMAL AS $$
DECLARE
    v_toplam DECIMAL(10, 2);
BEGIN
    SELECT COALESCE(SUM(yekun_tutar), 0)
    INTO v_toplam
    FROM muayene
    INNER JOIN randevu ON muayene.randevu_id = randevu.randevu_id
    WHERE randevu.tarih_saat::DATE BETWEEN p_baslangic AND p_bitis;

    RETURN v_toplam;
END;
$$ LANGUAGE plpgsql;


-- Fonksiyon 3: Kritik Stok Kontrolü
-- Amaç: Biten ilaçları görmek.
CREATE OR REPLACE FUNCTION ilac_stok_kontrol(p_kritik_sinir INTEGER)
RETURNS TABLE(ilac_adi VARCHAR, kalan_stok INTEGER) AS $$
BEGIN
    RETURN QUERY
    SELECT ad, stok_adet
    FROM ilac
    WHERE stok_adet <= p_kritik_sinir;
END;
$$ LANGUAGE plpgsql;


-- Fonksiyon 4: Randevu Tarihi Güncelleme
-- Amaç: Randevu yönetimini kolaylaştırmak.
CREATE OR REPLACE FUNCTION randevu_tarih_guncelle(
    p_randevu_id INTEGER,
    p_yeni_tarih TIMESTAMP
) RETURNS VOID AS $$
BEGIN
    UPDATE randevu
    SET tarih_saat = p_yeni_tarih
    WHERE randevu_id = p_randevu_id;
END;
$$ LANGUAGE plpgsql;



-- 2. TETİKLEYİCİLER (TRIGGERS)


-- Tetikleyici 1: İlaç Stoğunu Otomatik Düşür
-- Tablo: recete_ilac
CREATE OR REPLACE FUNCTION func_stok_dus() RETURNS TRIGGER AS $$
BEGIN
    UPDATE ilac
    SET stok_adet = stok_adet - NEW.adet
    WHERE ilac_id = NEW.ilac_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_stok_dus
AFTER INSERT ON recete_ilac
FOR EACH ROW
EXECUTE FUNCTION func_stok_dus();


-- Tetikleyici 2: Müşteri Bakiyesini Güncelle (Muayene Ücreti)
-- Tablo: muayene
CREATE OR REPLACE FUNCTION func_bakiye_guncelle() RETURNS TRIGGER AS $$
DECLARE
    v_musteri_id INTEGER;
BEGIN
    -- Randevu üzerinden müşteri ID'sini bul
    SELECT musteri_id INTO v_musteri_id
    FROM randevu
    WHERE randevu_id = NEW.randevu_id;

    -- Müşterinin bakiyesine tutarı ekle
    UPDATE musteri
    SET bakiye = bakiye + NEW.yekun_tutar
    WHERE kisi_id = v_musteri_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_bakiye_guncelle
AFTER INSERT ON muayene
FOR EACH ROW
EXECUTE FUNCTION func_bakiye_guncelle();


-- Tetikleyici 3: Gelecek Aşı Tarihini Otomatik Hesapla
-- Tablo: asi_takip
CREATE OR REPLACE FUNCTION func_asi_tarih_hesapla() RETURNS TRIGGER AS $$
DECLARE
    v_gun_sayisi INTEGER;
BEGIN
    -- Aşının tekrar süresini bul
    SELECT tekrar_suresi_gun INTO v_gun_sayisi
    FROM asi
    WHERE asi_id = NEW.asi_id;

    -- Eğer tekrar süresi varsa, gelecek tarihi hesapla
    IF v_gun_sayisi IS NOT NULL THEN
        NEW.gelecek_tarih := NEW.uygulama_tarihi + (v_gun_sayisi * INTERVAL '1 day');
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_asi_tarih_hesapla
BEFORE INSERT ON asi_takip
FOR EACH ROW
EXECUTE FUNCTION func_asi_tarih_hesapla();


-- Tetikleyici 4: Randevu Çakışmasını Engelle
-- Tablo: randevu
CREATE OR REPLACE FUNCTION func_randevu_cakisma() RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM randevu
        WHERE veteriner_id = NEW.veteriner_id
        AND tarih_saat = NEW.tarih_saat
    ) THEN
        RAISE EXCEPTION 'Bu veterinerin belirtilen saatte zaten randevusu var!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_randevu_cakisma_kontrol
BEFORE INSERT ON randevu
FOR EACH ROW
EXECUTE FUNCTION func_randevu_cakisma();