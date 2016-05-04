<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\interfaces;

/**
 * An interface for models that provide detailed descriptions of their attributes.
 */
interface DescriptiveInterface
{
    /**
     * @return array customized attribute descriptions (name=>label)
     */
    public function attributeDescriptions();
    
    /** @param string $attribute the name of the attribute from which a description is needed.
     * @return string the description.*/
    public function getAttributeDescription($attribute);
}
