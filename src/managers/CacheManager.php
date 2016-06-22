<?php
/**
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

namespace cache\manager;

use cache\interfaces\CacheObjectInterface;
use cache\interfaces\SearchProcessorInterface;
use cache\search\results\SearchResultObject;

/**
 * Class cacheManager
 */
final class CacheManager
{
    /** @var CacheManager|null */
    private static $instance = null;

    /** @var array $cacheObjects */
    private $cacheObjects = [];

    /** @var array $searchProcessors */
    private $searchProcessors = [];

    /**
     * @param mixed $cacheObject
     * @return $this
     */
    protected function addRecursive($cacheObject)
    {
        if (is_array($cacheObject)) {
            foreach ($cacheObject as $cacheObjectData) {
                if (is_array($cacheObjectData)) {
                    $this->addRecursive($cacheObjectData);
                } else {
                    if ($cacheObjectData instanceof CacheObjectInterface) {
                        $this->addCacheObject($cacheObjectData);
                    }
                }
            }
        } else {
            if ($cacheObject instanceof CacheObjectInterface) {
                $this->addCacheObject($cacheObject);
            }
        }

        return $this;
    }

    /**
     * CacheManager constructor.
     * @param array $searchProcessors
     */
    public function __construct(array $searchProcessors = [])
    {
        if (is_array($searchProcessors)) {
            foreach ($searchProcessors as $searchProcessor) {
                if ($searchProcessor instanceof SearchProcessorInterface) {
                    $this->addSearchProcessor($searchProcessor);
                }
            }
        }

        if (!self::$instance) {
            self::$instance = $this;
        }
    }

    /**
     * CacheManager Singleton
     * @param array $searchProcessors
     * @return $this
     */
    public static function getInstance(array $searchProcessors = [])
    {
        if (!self::$instance) {
            self::$instance = new self($searchProcessors);
        }
        return self::$instance;
    }

    /**
     * @param SearchProcessorInterface $searchProcessor
     * @return bool
     */
    public function containsSearchProcessor(SearchProcessorInterface $searchProcessor)
    {
        return (isset($this->searchProcessors[$searchProcessor->getName()]) && ($this->searchProcessors[$searchProcessor->getName()] instanceof SearchProcessorInterface));
    }

    /**
     * @return array
     */
    public function getSearchProcessors()
    {
        return $this->searchProcessors;
    }

    /**
     * @param SearchProcessorInterface $searchProcessor
     * @return $this
     */
    public function addSearchProcessor(SearchProcessorInterface $searchProcessor)
    {
        if (!$this->containsSearchProcessor($searchProcessor)) {
            $this->searchProcessors[$searchProcessor->getName()] = $searchProcessor;
        }

        return $this;
    }

    /**
     * @param SearchProcessorInterface $searchProcessor
     * @return $this
     */
    public function removeSearchProcessor(SearchProcessorInterface $searchProcessor)
    {
        if ($this->containsSearchProcessor($searchProcessor)) {
            unset($this->searchProcessors[$searchProcessor->getName()]);
        }

        return $this;
    }

    /**
     * @param string $searchProcessorName
     * @return searchProcessorInterface|null
     */
    public function getSearchProcessor($searchProcessorName)
    {
        return isset($this->searchProcessors[$searchProcessorName]) ? $this->searchProcessors[$searchProcessorName] : null;
    }

    /**
     * @return array
     */
    public function getCacheObjects()
    {
        return $this->cacheObjects;
    }

    /**
     * @param $cacheObjects
     * @return $this
     */
    public function setCacheObjects(array $cacheObjects)
    {
        $this->addRecursive($cacheObjects);

        return $this;
    }

    /**
     * @param CacheObjectInterface $cacheObject
     * @return bool
     */
    public function containsCacheObject(CacheObjectInterface $cacheObject)
    {
        return (isset($this->cacheObjects[$cacheObject->getName()]) && ($this->cacheObjects[$cacheObject->getName()] instanceof CacheObjectInterface));
    }

    /**
     * @param CacheObjectInterface $cacheObject
     * @return $this
     */
    public function addCacheObject(CacheObjectInterface $cacheObject)
    {
        if (!$this->containsCacheObject($cacheObject)) {
            $cacheObject->setCacheManager($this);
            $this->cacheObjects[$cacheObject->getName()] = $cacheObject;
        }

        return $this;
    }

    /**
     * @param CacheObjectInterface $cacheObject
     * @return $this
     */
    public function removeCacheObject(CacheObjectInterface $cacheObject)
    {
        if ($this->containsCacheObject($cacheObject)) {
            $cacheObject->setCacheManager(null);
            unset($this->cacheObjects[$cacheObject->getName()]);
        }

        return $this;
    }

    /**
     * @param string $cacheObjectName
     * @return CacheObjectInterface|null
     */
    public function getCacheObject($cacheObjectName)
    {
        return isset($this->cacheObjects[$cacheObjectName]) ? $this->cacheObjects[$cacheObjectName] : null;
    }

    /**
     * @param string $needle
     * @return SearchResultObject[]
     */
    public function findCacheObjectsMatching($needle)
    {
        $eligibleCacheObjects = [];

        /** @var CacheObjectInterface $cacheObject */
        foreach ($this->cacheObjects as $cacheObject) {
            $cacheContent = $cacheObject->getContent();

            /** @var SearchProcessorInterface $searchProcessor */
            foreach ($this->searchProcessors as $searchProcessor) {
                if ($result = $searchProcessor->search($needle, $cacheContent)) {
                    $eligibleCacheObjects[$cacheObject->getName()] = new SearchResultObject($cacheObject, $result);
                }
            }
        }

        return $eligibleCacheObjects;
    }

    /**
     * @return $this
     */
    public function deleteFiles()
    {
        /** @var CacheObjectInterface $cacheObject */
        foreach ($this->cacheObjects as $cacheObject) {
            $cacheObject->deleteFile();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        foreach ($this->cacheObjects as $cacheObject) {
            $this->removeCacheObject($cacheObject);
        }

        return $this;
    }

    /**
     * @param null|CacheObjectInterface $cacheObject
     * @return $this
     */
    public function flush($cacheObject = null) {
        if ($cacheObject instanceof CacheObjectInterface && $cacheObject->isModified()) {
            $cacheObject->writeFile();
        } else {
            /** @var CacheObjectInterface $cacheObject */
            foreach ($this->cacheObjects as $cacheObject) {
                if ($cacheObject->isModified()) $cacheObject->writeFile();
            }
        }

        return $this;
    }
}