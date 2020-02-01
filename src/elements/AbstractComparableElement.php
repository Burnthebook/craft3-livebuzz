<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\elements;

use craft\base\Element;
use craft\base\ElementInterface;

abstract class AbstractComparableElement extends Element
{
    /**
     * Attributes/properties that will be compared when determining if two elements have differences
     * @return array
     */
    abstract protected static function defineComparableAttributes(): array;

    /**
     * Compares the current element with $element.
     * Returns true if there are differences and false if the elements are the same.
     *
     * @param ElementInterface $element
     * @return bool
     */
    public function isDifferent(ElementInterface $element): bool
    {
        if (!is_a($element, self::class)) {
            return true;
        }

        foreach ($this::defineComparableAttributes() as $attribute) {
            if ($this->$attribute != $element->$attribute) {
                return true;
            }
        }

        return false;
    }

    /**
     * Syncs this element to the $target element
     * @param ElementInterface $target
     */
    public function syncToElement(ElementInterface &$target)
    {
        if (!is_a($target, self::class)) {
            return;
        }

        foreach ($this::defineComparableAttributes() as $attribute) {
            $target->$attribute = $this->$attribute;
        }
    }
}
