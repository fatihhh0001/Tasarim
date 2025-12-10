-- 1. ADIM: ESKİLERİ TEMİZLE VE SAYACI SIFIRLA
TRUNCATE TABLE recete_ilac, recete, muayene_hizmet, muayene, randevu, 
asi_takip, hayvan, cins, tur, asi, ilac, tedarikci, hizmet, veteriner, musteri, kisi 
RESTART IDENTITY CASCADE;

-- Burdaki amacımız varsayılan değerleri verdik, uygulama ekranında çıkacak.
-- Türler
INSERT INTO tur (ad) VALUES 
('Kedi'), ('Köpek'), ('İnek'), ('Koyun');

-- Cinsler (Kedi)
INSERT INTO cins (tur_id, ad) VALUES 
(1, 'Tekir'), (1, 'Van Kedisi'), (1, 'British Shorthair'), (1, 'Scottish Fold');

-- Cinsler (Köpek)
INSERT INTO cins (tur_id, ad) VALUES 
(2, 'Golden Retriever'), (2, 'Kangal'), (2, 'Alman Kurdu'), (2, 'Rotweiller');

-- Cinsler (İnek)
INSERT INTO cins (tur_id, ad) VALUES 
(3, 'Holstein (Siyah Alaca)'), (3, 'Simental'), (3, 'Yerli Kara');

-- Cinsler (Koyun)
INSERT INTO cins (tur_id, ad) VALUES 
(4, 'Merinos'), (4, 'Kıvırcık'), (4, 'Karaman');

-- Hizmetler
INSERT INTO hizmet (ad, birim_ucret) VALUES 
('Genel Muayene', 500.00),
('Kısırlaştırma Ameliyatı (Kedi)', 3000.00),
('Kısırlaştırma Ameliyatı (Köpek)', 5000.00),
('Tırnak Kesimi', 200.00),
('İç-Dış Parazit Uygulaması', 400.00),
('Kuduz Aşısı Uygulama', 350.00),
('Röntgen Çekimi', 750.00),
('Ultrason', 1000.00),
('Diş Taşı Temizliği', 1500.00),
('Acil Müdahale / Serum', 800.00);

-- Aşılar
INSERT INTO asi (ad, tekrar_suresi_gun) VALUES 
('Kuduz Aşısı', 365),
('Karma Aşı (Kedi)', 365),
('Karma Aşı (Köpek)', 365),
('Lösemi Aşısı', 365),
('Bronşin (Barınak) Aşısı', 180),
('Şap Aşısı (Büyükbaş)', 180);

-- Tedarikçiler
INSERT INTO tedarikci (firma_adi, telefon) VALUES 
('VetEcza Deposu', '02121111111'),
('Anadolu Medikal', '03122222222');

-- İlaçlar
INSERT INTO ilac (tedarikci_id, ad, stok_adet, fiyat) VALUES 
(1, 'Antibiyotik Hap (500mg)', 100, 250.00),
(1, 'Ağrı Kesici Şurup', 50, 150.00),
(1, 'Göz Damlası', 20, 100.00),
(1, 'Vitamin Kompleks', 200, 300.00),
(2, 'Yara Merhemi', 40, 120.00),
(2, 'Pire Tasması', 30, 450.00),
(2, 'Sargı Bezi', 500, 50.00),
(2, 'Enjektör (100lü Paket)', 50, 200.00),
(1, 'Kulak Temizleme Solüsyonu', 25, 180.00),
(2, 'Kalsiyum Takviyesi (Büyükbaş)', 60, 400.00);

-- Test Kişileri ve Hayvan
INSERT INTO kisi (ad_soyad, telefon, adres, kisi_tipi) VALUES ('Vet. Ahmet Uzman', '05001234567', 'Klinik', 'V');
INSERT INTO veteriner (kisi_id, uzmanlik, diploma_no) VALUES (1, 'Cerrahi', 'DIP-001');

SELECT yeni_musteri_kayit('Mehmet Çiftçi', '05559998877', 'Adapazarı Köyü');

INSERT INTO hayvan (musteri_id, cins_id, ad, cip_no, dogum_tarihi) 
VALUES (2, 9, 'Sarıkız', 'TR-INEK-001', '2020-05-20');