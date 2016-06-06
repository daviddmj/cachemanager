<?php
/**
 * Created by PhpStorm.
 * User: dafonso
 * Date: 06/06/16
 * Time: 18:08
 */

namespace cache\interfaces;


interface ResultObjectInterface
{
    /**
     * @return mixed
     */
    public function getCacheObject();

    /**
     * @param CacheObjectInterface $cacheObject
     * @return mixed
     */
    public function setCacheObject(CacheObjectInterface $cacheObject);

    /**
     * @return mixed
     */
    public function getResult();

    /**
     * @param $result
     * @return mixed
     */
    public function setResult($result);
}