<?php
declare(strict_types=1);

namespace Dolphin\ShippingMethod\Block\Adminhtml;

use Magento\Framework\View\Element\Html\Select;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CategoryDropDown extends Select
{
    protected $productCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getCategoryOptions());
        }
        return parent::_toHtml();
    }

    private function getCategoryOptions(): array
    {
        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name');

        $options = [];

        $options[] = [
            'label' => __('Select Ctegory'),  // Default label
            'value' => '',               // Empty value to represent no selection
        ];
        
        foreach ($categories as $category) {
            $options[] = [
                'label' => $category->getName(), 
                'value' => $category->getId(), 
            ];
        }

        return $options;
    }
}
