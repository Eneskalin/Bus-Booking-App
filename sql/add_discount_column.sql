-- Coupons tablosuna discount_percentage kolonu ekle
ALTER TABLE Coupons ADD COLUMN discount_percentage INTEGER DEFAULT 10;

-- Mevcut kayıtları güncelle (varsa)
UPDATE Coupons SET discount_percentage = 10 WHERE discount_percentage IS NULL;
