<?php
/**
 * File-Based Caching System
 * Provides simple caching with TTL support for frequently accessed data
 * 
 * Usage:
 * $cache = new FileCache();
 * $cache->set('key', $data, 300); // Cache for 5 minutes
 * $data = $cache->get('key');
 * $cache->delete('key');
 * $cache->clear_all();
 */

class FileCache {
    private $cache_dir;
    private $default_ttl;
    
    /**
     * Constructor
     * 
     * @param string $cache_dir Cache directory path (default: logs/cache)
     * @param int $default_ttl Default TTL in seconds (default: 3600 = 1 hour)
     */
    public function __construct($cache_dir = null, $default_ttl = 3600) {
        if ($cache_dir === null) {
            $cache_dir = __DIR__ . '/../logs/cache';
        }
        
        $this->cache_dir = rtrim($cache_dir, '/');
        $this->default_ttl = max(1, (int)$default_ttl);
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cache_dir)) {
            @mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Generate cache key filename
     * 
     * @param string $key Cache key
     * @return string Cache file path
     */
    private function get_cache_file($key) {
        // Sanitize key to create safe filename
        $safe_key = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cache_dir . '/' . $safe_key . '.cache';
    }
    
    /**
     * Set cache value
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds (null = use default)
     * @return bool Success status
     */
    public function set($key, $value, $ttl = null) {
        if ($ttl === null) {
            $ttl = $this->default_ttl;
        }
        
        $ttl = max(1, (int)$ttl);
        $cache_file = $this->get_cache_file($key);
        
        // Prepare cache data
        $cache_data = [
            'key' => $key,
            'value' => $value,
            'created_at' => time(),
            'expires_at' => time() + $ttl,
            'ttl' => $ttl
        ];
        
        // Serialize and write to file
        $serialized = serialize($cache_data);
        
        if (@file_put_contents($cache_file, $serialized, LOCK_EX) !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get cache value
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if not found or expired
     * @return mixed Cached value or default
     */
    public function get($key, $default = null) {
        $cache_file = $this->get_cache_file($key);
        
        // Check if cache file exists
        if (!file_exists($cache_file)) {
            return $default;
        }
        
        // Read and unserialize cache data
        $serialized = @file_get_contents($cache_file);
        if ($serialized === false) {
            return $default;
        }
        
        $cache_data = @unserialize($serialized);
        if ($cache_data === false) {
            return $default;
        }
        
        // Check if cache has expired
        if (time() > $cache_data['expires_at']) {
            @unlink($cache_file);
            return $default;
        }
        
        return $cache_data['value'];
    }
    
    /**
     * Check if cache key exists and is valid
     * 
     * @param string $key Cache key
     * @return bool True if cache exists and is valid
     */
    public function exists($key) {
        $cache_file = $this->get_cache_file($key);
        
        if (!file_exists($cache_file)) {
            return false;
        }
        
        $serialized = @file_get_contents($cache_file);
        if ($serialized === false) {
            return false;
        }
        
        $cache_data = @unserialize($serialized);
        if ($cache_data === false) {
            return false;
        }
        
        // Check if expired
        if (time() > $cache_data['expires_at']) {
            @unlink($cache_file);
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete specific cache key
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete($key) {
        $cache_file = $this->get_cache_file($key);
        
        if (file_exists($cache_file)) {
            return @unlink($cache_file);
        }
        
        return true;
    }
    
    /**
     * Clear all cache files
     * 
     * @return int Number of files deleted
     */
    public function clear_all() {
        $count = 0;
        
        if (!is_dir($this->cache_dir)) {
            return $count;
        }
        
        $files = @glob($this->cache_dir . '/*.cache');
        if ($files === false) {
            return $count;
        }
        
        foreach ($files as $file) {
            if (@unlink($file)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Clear expired cache entries
     * 
     * @return int Number of expired files deleted
     */
    public function clear_expired() {
        $count = 0;
        
        if (!is_dir($this->cache_dir)) {
            return $count;
        }
        
        $files = @glob($this->cache_dir . '/*.cache');
        if ($files === false) {
            return $count;
        }
        
        foreach ($files as $file) {
            $serialized = @file_get_contents($file);
            if ($serialized === false) {
                continue;
            }
            
            $cache_data = @unserialize($serialized);
            if ($cache_data === false) {
                continue;
            }
            
            // Delete if expired
            if (time() > $cache_data['expires_at']) {
                if (@unlink($file)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public function get_stats() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'expired_files' => 0,
            'valid_files' => 0
        ];
        
        if (!is_dir($this->cache_dir)) {
            return $stats;
        }
        
        $files = @glob($this->cache_dir . '/*.cache');
        if ($files === false) {
            return $stats;
        }
        
        foreach ($files as $file) {
            $stats['total_files']++;
            $stats['total_size'] += filesize($file);
            
            $serialized = @file_get_contents($file);
            if ($serialized === false) {
                continue;
            }
            
            $cache_data = @unserialize($serialized);
            if ($cache_data === false) {
                continue;
            }
            
            if (time() > $cache_data['expires_at']) {
                $stats['expired_files']++;
            } else {
                $stats['valid_files']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Get cache info for a specific key
     * 
     * @param string $key Cache key
     * @return array|null Cache info or null if not found
     */
    public function get_info($key) {
        $cache_file = $this->get_cache_file($key);
        
        if (!file_exists($cache_file)) {
            return null;
        }
        
        $serialized = @file_get_contents($cache_file);
        if ($serialized === false) {
            return null;
        }
        
        $cache_data = @unserialize($serialized);
        if ($cache_data === false) {
            return null;
        }
        
        $is_expired = time() > $cache_data['expires_at'];
        
        return [
            'key' => $cache_data['key'],
            'created_at' => $cache_data['created_at'],
            'expires_at' => $cache_data['expires_at'],
            'ttl' => $cache_data['ttl'],
            'is_expired' => $is_expired,
            'time_remaining' => max(0, $cache_data['expires_at'] - time()),
            'file_size' => filesize($cache_file)
        ];
    }
}

// Global cache instance for convenience
$GLOBALS['file_cache'] = null;

/**
 * Get global cache instance
 * 
 * @return FileCache Cache instance
 */
function get_cache() {
    if ($GLOBALS['file_cache'] === null) {
        $GLOBALS['file_cache'] = new FileCache();
    }
    return $GLOBALS['file_cache'];
}

?>
