<?php
/**
 * Configuration file for loading environment variables
 */

class Config {
    private static $env = [];
    
    public static function load($envFile = null) {
        if ($envFile === null) {
            // Попробуем найти .env файл в разных местах
            $possiblePaths = [
                __DIR__ . '/../.env',
                __DIR__ . '/.env',
                '.env',
                '../.env'
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $envFile = $path;
                    break;
                }
            }
        }
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found');
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '"\' ');
                
                self::$env[$key] = $value;
                
                // Also set as environment variable
                putenv("$key=$value");
            }
        }
    }
    
    public static function get($key, $default = null) {
        return isset(self::$env[$key]) ? self::$env[$key] : $default;
    }
    
    public static function all() {
        return self::$env;
    }
}

// Auto-load environment variables
try {
    Config::load();
} catch (Exception $e) {
    error_log('Config Error: ' . $e->getMessage());
}
?>