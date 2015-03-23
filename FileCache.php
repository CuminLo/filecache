<?php
    namespace pFileCache;

    class FileCache {

        protected $cacheDir;

        protected $ttl = 60;

        protected $prefix;
        protected $suffix;


        public function __construct ($cacheDir=null) {
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

        public function serialize($value) {
            return serialize($value);
        }

        public function getKey($key) {
            return $this->prefix . $key . $this->suffix;
        }

        public function getFile($key) {
            $fKey = $this->getKey($key);
            return $this->cacheDir . '/' . $fKey;
        }

        public function get($key) {
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

        public function set($key, $value, $ttl=null) {
            $cacheFile  = $this->getFile($key);

            $fValue     = $this->serialize($value);

            if (!$ttl) {
                $ttl = $this->ttl;
            }

            $content = $this->serialize([
                'value' => $fValue,
                'ttl'   => (int) $ttl + time(),
            ]);

            file_put_contents($cacheFile, $content);
        }

        //todo...
        public function has($key) {
            return true;
        }

        public function delete($key) {
            $cacheFile = $this->getFile($key);
            if (is_file) {
                return unlink($cacheFile);
            }
            return false;
        }

        //todo...
        public function deleteAll() {

        }
    }
