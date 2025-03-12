<?php

namespace Dolphin\ShippingMethod\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class AllCategory implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('All categories')],
            ['value' => 1, 'label' => __('Specific categories')],
        ];
    }

   
}
