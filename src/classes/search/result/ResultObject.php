<?php

namespace cache\search\result;

use cache\interfaces\CacheObjectInterface;
use cache\interfaces\ResultObjectInterface;

/**
 * Class ResultObject
 */
class ResultObject implements ResultObjectInterface
{
    /**
     * @var CacheObjectInterface
     */
    private $cacheObject = null;

    /**
     * @var mixed
     */
    private $result = null;

    /**
     * SearchResult constructor.
     * @param CacheObjectInterface $cacheObject
     * @param mixed $result
     */
    public function __construct(CacheObjectInterface $cacheObject, $result = null)
    {
        $this->setCacheObject($cacheObject);
        $this->setResult($result);
    }

    /**
     * @return CacheObjectInterface
     */
    public function getCacheObject()
    {
        return $this->cacheObject;
    }

    /**
     * @param CacheObjectInterface $cacheObject
     * @return $this
     */
    public function setCacheObject(CacheObjectInterface $cacheObject)
    {
        $this->cacheObject = $cacheObject;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }
}