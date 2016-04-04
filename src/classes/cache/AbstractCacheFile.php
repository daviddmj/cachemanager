<?php
/**
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

namespace cache\file;

use cache\exception\FileOperationException;
use cache\interfaces\CacheFileInterface;

/**
 * Abstract Cache File class
 */
abstract class AbstractCacheFile implements CacheFileInterface
{
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
        return date('Y-m-d H:i:s', strtotime("this day + {$this->expirationDelay} minutes"));
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
     * @param bool $createCacheDirectory
     * @return $this
     * @throws \Exception
     */
    public function setCacheDirectory($cacheDirectory, $createCacheDirectory = false)
    {
        if ($createCacheDirectory) {
            if (!is_dir($cacheDirectory) && !mkdir($cacheDirectory)) {
                throw new FileOperationException(sprintf('unable to create cache directory "%s"', $cacheDirectory));
            }
        }

        if (is_dir($cacheDirectory)) {
            $this->cacheDirectory = $cacheDirectory;
        }

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
        if (!$this->content) {
            return $this->refresh();
        }

        return $this->content;
    }

    /**
     * @param mixed $content
     * @return $this
     */
    public function setContent($content = null)
    {
        if ($content) {
            $this->content = $content;
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
        return gettype($this->content);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}