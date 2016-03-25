<?php
/**
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

namespace cache\manager;

use cache\file\CacheFile;

/**
 * Class cacheManager
 */
final class CacheManager
{
    /** @var CacheManager|null */
    private static $instance = null;

    /** @var array $cacheFiles */
    private $cacheFiles = [];

    /**
     * @param $content
     * @param $data
     * @return array|null
     */
    protected function recursive_array_search($content,$data) {
        $iterator  = new \RecursiveArrayIterator($data);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $content) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @param mixed $cacheFile
     * @return $this
     */
    protected function addRecursive($cacheFile)
    {
        if (is_array($cacheFile)) {
            foreach ($cacheFile as $cacheFileData) {
                if (is_array($cacheFileData)) {
                    $this->addRecursive($cacheFileData);
                } else {
                    $this->addCacheFile($cacheFileData);
                }
            }
        } else {
            $this->addCacheFile($cacheFile);
        }

        return $this;
    }

    /**
     * CacheManager constructor.
     */
    public function __construct()
    {
        foreach (func_get_args() as $cacheFile) {
            $this->addRecursive($cacheFile);
        }

        if (!self::$instance) {
            self::$instance = $this;
        }
    }

    /**
     * CacheManager Singleton
     * @return $this
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self(func_get_args());
        }
        return self::$instance;
    }

    /**
     * @return array
     */
    public function getCacheFiles()
    {
        return $this->cacheFiles;
    }

    /**
     * @param $cacheFiles
     * @return $this
     */
    public function setCacheFiles($cacheFiles)
    {
        $this->addRecursive($cacheFiles);

        return $this;
    }

    /**
     * @param CacheFile $cacheFile
     * @return bool
     */
    public function contains(CacheFile $cacheFile)
    {
        return (isset($this->cacheFiles[$cacheFile->getName()]) && ($this->cacheFiles[$cacheFile->getName()] instanceof CacheFile));
    }

    /**
     * @param CacheFile $cacheFile
     * @return $this
     */
    public function addCacheFile(CacheFile $cacheFile)
    {
        if (!$this->contains($cacheFile)) {
            $this->cacheFiles[$cacheFile->getName()] = $cacheFile;
        }

        return $this;
    }

    /**
     * @param CacheFile $cacheFile
     * @return $this
     */
    public function removeCacheFile(CacheFile $cacheFile)
    {
        if ($this->contains($cacheFile)) {
            unset($this->cacheFiles[$cacheFile->getName()]);
        }

        return $this;
    }

    /**
     * @param string $cacheFileName
     * @return CacheFile|null
     */
    public function getCacheFile($cacheFileName)
    {
        return isset($this->cacheFiles[$cacheFileName]) ? $this->cacheFiles[$cacheFileName] : null;
    }

    /**
     * @param string $content
     * @return array
     */
    public function findCacheFilesMatching($content)
    {
        $eligibleCache = [];

        /** @var CacheFile $cacheFile */
        foreach ($this->cacheFiles as $cacheFile) {
            $cacheContent = $cacheFile->getContent();

            if ($cacheContent) {
                if (!is_array($cacheContent)) {
                    if (preg_match('#\b' . preg_quote($content, '#') . '\b#i', $cacheContent)) {
                        $eligibleCache[$cacheFile->getName()] = $cacheFile;
                    }
                } else {
                    if ($this->recursive_array_search($content, $cacheContent)) {
                        $eligibleCache[$cacheFile->getName()] = $cacheFile;
                    }
                }
            }
        }

        return $eligibleCache;
    }

    /**
     * @return $this
     */
    public function deleteFiles()
    {
        /** @var CacheFile $cacheFile */
        foreach ($this->cacheFiles as $cacheFile) {
            $cacheFile->deleteFile();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        foreach ($this->cacheFiles as $cacheFile) {
            $this->removeCacheFile($cacheFile);
        }

        return $this;
    }

    /**
     * @param null|CacheFile $cacheFile
     * @return $this
     */
    public function flush($cacheFile = null) {
        if ($cacheFile instanceof CacheFile) {
            $cacheFile->writeFile();
        } else {
            /** @var CacheFile $cacheFile */
            foreach ($this->cacheFiles as $cacheFile) {
                $cacheFile->writeFile();
            }
        }

        return $this;
    }
}