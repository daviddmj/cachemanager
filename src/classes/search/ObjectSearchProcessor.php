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
    function isEligible($content)
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
        if (!$this->isEligible($content)) return null;

        if (property_exists($content, $needle)) return $content;

        $object = new \ReflectionClass(get_parent_class($content) ? : get_class($content));

        $eligibleProperties = [];
        foreach ($object->getProperties() as $property) {
            if ($property->getName() == $needle) {
                array_push($eligibleProperties, $property->getName());
            }
        }

        $eligibleMethods = [];
        foreach ($object->getMethods() as $method) {
            if ($method->getName() == $needle) {
                array_push($eligibleMethods, $method->getName());
            }
        }

        $eligibleStaticProperties = [];
        foreach ($object->getStaticProperties() as $staticProperty) {
            if ($staticProperty == $needle) {
                array_push($eligibleStaticProperties, $staticProperty);
            }
        }

        $eligibleConstants = [];
        foreach ($object->getConstants() as $constant) {
            if ($constant == $needle) {
                array_push($eligibleConstants, $constant);
            }
        }

        return
            (count($eligibleProperties)+count($eligibleMethods)+count($eligibleStaticProperties)+count($eligibleConstants)) == 0
            ? null :
            [
                'properties'        => $eligibleProperties,
                'methods'           => $eligibleMethods,
                'static_properties' => $eligibleStaticProperties,
                'constants'         => $eligibleConstants
            ];
    }
}