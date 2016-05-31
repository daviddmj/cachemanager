<?php

namespace cache\search;

use cache\interfaces\SearchProcessorInterface;

/**
 * Class ObjectSearchProcessor
 *
 * Search through class properties/methods/constants/static properties for a matching name
 */
class ObjectSearchProcessor implements SearchProcessorInterface
{
    /**
     * @var array $eligibleProperties
     */
    private $eligibleProperties = [];

    /**
     * @var array $eligibleMethods
     */
    private $eligibleMethods = [];

    /**
     * @var array $eligibleStaticProperties
     */
    private $eligibleStaticProperties = [];

    /**
     * @var array $eligibleConstants
     */
    private $eligibleConstants = [];

    /**
     * @return int
     */
    private function getTotalCount()
    {
        return
            count($this->eligibleProperties) +
            count($this->eligibleMethods) +
            count($this->eligibleStaticProperties) +
            count($this->eligibleConstants);
    }

    /**
     * @param $needle
     * @param $class
     */
    private function findInClass($needle, $class)
    {
        $object = new \ReflectionClass($class);

        foreach ($object->getProperties() as $property) {
            if ($property->getName() == $needle) {
                array_push($this->eligibleProperties, $property->getName());
            }
        }

        foreach ($object->getMethods() as $method) {
            if ($method->getName() == $needle) {
                array_push($this->eligibleMethods, $method->getName());
            }
        }

        foreach ($object->getStaticProperties() as $staticProperty) {
            if ($staticProperty == $needle) {
                array_push($this->eligibleStaticProperties, $staticProperty);
            }
        }

        foreach ($object->getConstants() as $constant) {
            if ($constant == $needle) {
                array_push($this->eligibleConstants, $constant);
            }
        }
    }

    /**
     * @return string
     */
    function getName()
    {
        return 'object_search';
    }

    /**
     * @param $content
     * @return bool
     */
    function isSupported($content)
    {
        return 'object' == gettype($content);
    }

    /**
     * @param $needle
     * @param $content
     * @return mixed
     */
    function search($needle, $content)
    {
        if (!$this->isSupported($content)) return null;

        if (property_exists($content, $needle)) return $content;

        $currentClass = get_class($content);
        $this->findInClass($needle, $currentClass);

        while (($parentClass = get_parent_class($currentClass)) && ($parentClass !== $currentClass)) {
            $currentClass = $parentClass;
            $this->findInClass($needle, $currentClass);
        }

        return
            $this->getTotalCount() == 0
            ? null :
            [
                'properties'        => $this->eligibleProperties,
                'methods'           => $this->eligibleMethods,
                'static_properties' => $this->eligibleStaticProperties,
                'constants'         => $this->eligibleConstants
            ];
    }
}