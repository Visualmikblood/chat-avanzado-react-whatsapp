<?php
/**
 * StorageAdapter.php
 * Clase para manejar la compatibilidad entre Localhost y Vercel/Supabase
 */

class StorageAdapter {
    
    /**
     * Sube un archivo al destino correcto dependiendo del entorno
     */
    public static function upload($file, $destinationFolder = 'uploads') {
        $isVercel = getenv('VERCEL') || getenv('SUPABASE_URL');

        // ==========================================
        // MODO LOCAL (Tu PC)
        // ==========================================
        if (!$isVercel) {
            $uploadDir = __DIR__ . '/../../' . $destinationFolder . '/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = $file['name'];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = uniqid('msg_') . '.' . $extension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                // Retorna URL relativa para local
                return '/' . $destinationFolder . '/' . $newFileName;
            }
            return false;
        }

        // ==========================================
        // MODO VERCEL / SUPABASE (Nube)
        // ==========================================
        else {
            // Necesitas configurar estas variables en Vercel
            $supabaseUrl = getenv('SUPABASE_URL'); 
            $supabaseKey = getenv('SUPABASE_SERVICE_ROLE_KEY'); // Usa la Service Role Key para permisos de escritura
            $bucketName = 'chat-files'; // Asegúrate de crear este bucket en Supabase Storage

            $fileName = $file['name'];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = uniqid('msg_') . '.' . $extension;
            $fileContent = file_get_contents($file['tmp_name']);

            // Usar cURL para subir a Supabase Storage API
            $url = "$supabaseUrl/storage/v1/object/$bucketName/$newFileName";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $supabaseKey",
                "Content-Type: " . $file['type']
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                // Retorna la URL pública de Supabase
                return "$supabaseUrl/storage/v1/object/public/$bucketName/$newFileName";
            }

            // Log de error para depuración en Vercel
            error_log("Supabase Upload Error: $response");
            return false;
        }
    }

    /**
     * Verifica si estamos en entorno Vercel
     */
    public static function isProduction() {
        return getenv('VERCEL') || getenv('SUPABASE_URL');
    }
}