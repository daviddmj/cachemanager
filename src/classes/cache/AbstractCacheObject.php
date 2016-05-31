<?php
/**
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

namespace cache\file;

use cache\exception\FileOperationException;
use cache\interfaces\CacheObjectInterface;
use cache\manager\CacheManager;

/**
 * Abstract Cache Object class
 */
abstract class AbstractCacheObject implements CacheObjectInterface
{
    // Cache directory use mode
    const USE_EXISTING_DIR = 1;
    const FORCE_CREATE_DIR = 2;

    // Data storage mode
    const RAW_DATA         = 1;
    const COMPRESSED_DATA  = 2;

    /** @var string */
    private $cacheDirectory = '.';

    /** @var null|string $cacheFile */
    private $cacheFile = 'content.cache';

    /** @var int $expirationDelay in minutes, 12 hours by default */
    private $expirationDelay = 720;

    /** @var string $name */
    private $name;

    /** @var mixed $content */
    private $content = null;

    /** @var bool $modified */
    private $modified = false;

    /** @var null|CacheManager $cacheManager */
    private $cacheManager = null;

    /** @var bool $compressed */
    private $compressed = false;

    /**
     * CacheFile constructor.
     * @param $name
     * @throws \Exception
     */
    public function __construct($name = null)
    {
        if (!$name) {
            throw new \Exception('Instance name must be provided');
        }

        $this->setName($name);
        $this->setCacheFile(sprintf('%s.cache', $this->getName()));
    }

    /**
     * @return $this|null
     * @throws \Exception
     */
    public function refresh()
    {
        if (!file_exists($this->getCachePath())) {
            return null;
        }

        $cacheObject = unserialize(file_get_contents($this->getCachePath()));

        if (!is_object($cacheObject) || time() > strtotime($cacheObject->expirationDate)) {
            $this->deleteFile();
            return null;
        }

        $this->content = $cacheObject->content;
        $this->compressed = $cacheObject->compressed;
        $this->modified = false;

        return $this->content;
    }

    /**
     * @return bool
     */
    public function isModified()
    {
        return $this->modified;
    }

    /**
     * @return string
     */
    public function getExpirationDate()
    {
        return date('Y-m-d H:i:s', strtotime("now + {$this->expirationDelay} seconds"));
    }

    /**
     * @return string
     */
    public function getCacheDirectory()
    {
        return $this->cacheDirectory;
    }

    /**
     * @param string $cacheDirectory
     * @param int $cacheDirectoryMode
     * @return $this
     * @throws FileOperationException
     */
    public function setCacheDirectory($cacheDirectory, $cacheDirectoryMode = self::USE_EXISTING_DIR)
    {
        if ($cacheDirectoryMode == self::FORCE_CREATE_DIR && (!is_dir($cacheDirectory) && !mkdir($cacheDirectory))) {
            throw new FileOperationException(sprintf('unable to create cache directory "%s"', $cacheDirectory));
        }

        if ($cacheDirectoryMode == self::USE_EXISTING_DIR && !is_dir($cacheDirectory)) {
            throw new FileOperationException(sprintf('directory "%s" not found', $cacheDirectory));
        }

        $this->cacheDirectory = $cacheDirectory;

        return $this;
    }

    /**
     * @return string
     */
    public function getCacheFile()
    {
        return $this->cacheFile;
    }

    /**
     * @param string $cacheFile
     * @return $this
     */
    public function setCacheFile($cacheFile)
    {
        $this->cacheFile = $cacheFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->getCacheDirectory().'/'.$this->getCacheFile();
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        if (!$this->content) $this->refresh();

        return $this->compressed ? unserialize(zlib_decode($this->content)) : $this->content;
    }

    /**
     * @param mixed $content
     * @param int $storageMode Data storage mode
     * @return $this
     */
    public function setContent($content = null, $storageMode = self::RAW_DATA)
    {
        if ($content) {
            $this->compressed = $storageMode == self::COMPRESSED_DATA;
            $this->content = $this->compressed ? zlib_encode(serialize($content), ZLIB_ENCODING_GZIP) : $content;
            $this->modified = true;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function deleteFile()
    {
        if (file_exists($this->getCachePath())) {
            if (!unlink($this->getCachePath())) {
                throw new FileOperationException(sprintf('unable to remove file %s', $this->getCachePath()));
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function writeFile()
    {
        if (!$this->isModified() || $this->getContent() == null) return false;

        $this->deleteFile();

        $dataObject = new \stdClass();
        $dataObject->expirationDate = $this->getExpirationDate();
        $dataObject->content = $this->content;
        $dataObject->compressed = $this->compressed;

        $this->modified = false;

        if (file_put_contents($this->getCachePath(), serialize($dataObject)) == false) {
            throw new FileOperationException(sprintf('unable to create file %s', $this->getCachePath()));
        }

        return $this;
    }

    /**
     * @param $expirationDelay
     * @return $this
     */
    public function setExpirationDelay($expirationDelay)
    {
        if (is_numeric($expirationDelay)) {
            $this->expirationDelay = $expirationDelay * 60;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasContent()
    {
        return $this->getContent() !== null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|CacheManager
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }

    /**
     * @param CacheManager|null $cacheManager
     * @return $this|mixed
     */
    public function setCacheManager($cacheManager = null)
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }

    /**
     * @param null|string $name
     * @return $this
     */
    public function setName($name = null)
    {
        if ($name) {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return gettype($this->getContent());
    }

    /**
     * @return null|int
     */
    public function getContentSize()
    {
        if ('string' == $this->getContentType()) {
            return strlen($this->getContent());
        }

        return null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}