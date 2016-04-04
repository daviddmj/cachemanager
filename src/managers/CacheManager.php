<?php
/**
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

namespace cache\manager;

use cache\file\CacheFile;
use cache\search;

/**
 * Class cacheManager
 */
final class CacheManager
{
    const TYPE_ARRAY  = 'array';
    const TYPE_STRING = 'string';

    /** @var CacheManager|null */
    private static $instance = null;

    /** @var array $cacheFiles */
    private $cacheFiles = [];

    /**
     * @param $cacheContent
     * @return string
     */
    static private function getSearchClass($cacheContent)
    {
        switch (gettype($cacheContent)) {
            case self::TYPE_ARRAY:
                return 'cache\search\arraySearch';
            case self::TYPE_STRING:
            default:
                return 'cache\search\textSearch';
        }
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
                    if ($cacheFileData instanceof CacheFile) {
                        $this->addCacheFile($cacheFileData);
                    }
                }
            }
        } else {
            if ($cacheFile instanceof CacheFile) {
                $this->addCacheFile($cacheFile);
            }
        }

        return $this;
    }

    /**
     * CacheManager constructor.
     */
    public function __construct()
    {
        $this->setCacheFiles(func_get_args());

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
     * @param string $needle
     * @return array
     */
    public function findCacheFilesMatching($needle)
    {
        $eligibleCache = [];

        /** @var CacheFile $cacheFile */
        foreach ($this->cacheFiles as $cacheFile) {
            $cacheContent = $cacheFile->getContent();

            if (call_user_func_array([self::getSearchClass($cacheContent), 'search'], [$needle, $cacheContent])) {
                $eligibleCache[$cacheFile->getName()] = $cacheFile;
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