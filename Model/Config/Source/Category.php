<?php

namespace Dolphin\ShippingMethod\Model\Config\Source;


use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Category implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name');

        $options = [];
        foreach ($categories as $category) {
            $options[] = [
                'value' => $category->getId(),
                'label' => $category->getName(),
            ];
        }
        // echo "<pre>";
        // print_r($options);
        return $options;
    }
}
