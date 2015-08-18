<?php
    namespace FileCache;

    class FileCache {

        protected static $_cache = null;

        protected $cacheDir;

        protected $ttl = 60;

        protected $prefix;
        protected $suffix;


        private function __construct ($cacheDir=null)
        {
            if (!$cacheDir) {
                $cacheDir = realPath(sys_get_temp_dir()) . '/cache';
            } 

            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }

            if (!is_writable($cacheDir)) {
                echo 'Error: This dir is not writ', PHP_EOL;die;
            }

            $this->cacheDir = (string) $cacheDir;
        }

        public static function __callStatic($functionName, $params)
        {
            if (!FileCache::$_cache) {
                FileCache::$_cache = new FileCache();
            }

            if ($functionName == 'set') {
                $ttl = isset($params[2]) ? $params[2] : FileCache::$_cache->ttl ;
                return FileCache::$_cache->$functionName($params[0], $params[1], $ttl);
            }

            if ($functionName == 'get') {
                return FileCache::$_cache->$functionName($params[0]);
            }

            if ($functionName == 'deleteAll') {
                FileCache::$_cache->$functionName();
            }
        }

        private function serialize($value)
        {
            return serialize($value);
        }

        private function getKey($key)
        {
            return $this->prefix . $key . $this->suffix;
        }

        private function getFile($key)
        {
            $fKey = $this->getKey($key);
            return $this->cacheDir . '/' . $fKey;
        }

        private function get($key)
        {
            $cacheFile  = $this->getFile($key);

            if (!is_file($cacheFile)) {
                return null;
            }

            $content = unserialize(file_get_contents($cacheFile));

            if (!array_key_exists('value', $content)) {
                return null;
            }

            if (time() > $content['ttl']) {
                $this->delete($key);
                return null;
            }

            return unserialize($content['value']);
        }

        private function set($key, $value, $ttl=null)
        {
            $cacheFile  = $this->getFile($key);

            $fValue     = $this->serialize($value);

            if (!$ttl) {
                $ttl = $this->ttl;
            }

            $content = $this->serialize([
                'value' => $fValue,
                'ttl'   => (int) $ttl + time(),
            ]);

            return file_put_contents($cacheFile, $content);
        }

        //todo...
        private function has($key) {
            return true;
        }

        private function delete($key)
        {
            $cacheFile = $this->getFile($key);
            if (is_file($cacheFile)) {
                return unlink($cacheFile);
            }
            return false;
        }

        //todo...
        private function deleteAll() {
            $oh = opendir($this->cacheDir);

            if (!$oh) {
                //todo...
            }

            while(($fileName = readdir($oh)) !== false) {
                if ($fileName == '.' || $fileName == '..') {
                    continue;
                }

                $cacheFilePath = $this->cacheDir . '/' . $fileName;

                if (is_file($cacheFilePath)) {
                    unlink($cacheFilePath);
                }
            }
        }
    }
