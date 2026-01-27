-- Agregar campos de estado de lectura a la tabla messages
ALTER TABLE messages 
ADD COLUMN delivered_at TIMESTAMP NULL DEFAULT NULL AFTER created_at,
ADD COLUMN read_at TIMESTAMP NULL DEFAULT NULL AFTER delivered_at;
