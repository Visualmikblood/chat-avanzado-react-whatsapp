-- Agregar campo bio a la tabla users
ALTER TABLE users ADD COLUMN bio VARCHAR(255) DEFAULT NULL AFTER avatar_url;
