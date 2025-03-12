<?php

namespace Dolphin\ShippingMethod\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Psr\Log\LoggerInterface;

class Pricerange extends AbstractHelper
{
    protected $scopeConfig;
    protected $logger;
    protected $CollectionFactory;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CollectionFactory $CollectionFactory
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->CollectionFactory = $CollectionFactory;
    }

    public function getCustomRange()
    {
        $customRange = $this->scopeConfig->getValue('price_range/price/p_ranges', ScopeInterface::SCOPE_STORE);
        $Pricerange = json_decode($customRange);
        return $Pricerange;
    }

    public function getCustomRangearray() {
        $customRange = $this->scopeConfig->getValue('price_range/price/p_ranges', ScopeInterface::SCOPE_STORE);
        $Priceranges = json_decode($customRange);
    
        $collection = [];
    
        foreach ($Priceranges as $Pricerange) {
            $collection[] = $Pricerange->Sku;
        }
        return $collection;
    }
    

}