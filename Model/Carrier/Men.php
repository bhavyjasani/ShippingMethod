<?php

namespace Dolphin\ShippingMethod\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
// use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class Men extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'Men';

    protected $_categoryRepository;
    protected $rateResultFactory;
    protected $rateMethodFactory;
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->_categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig; 
        $this->logger = $logger;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function getAllowedMethods()
    {
        return ['Men' => $this->getConfigData('name')];
    }

    public function collectRates(RateRequest $request)
    {
        
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $CategoryPrice = $this->getCategoryPrice();
        $applicable = $this->getConfigData('showmethod');

        

        if(!empty($CategoryPrice)){
            $quoteItems = $request->getAllItems();
                    
            $isAllowed = false; // Start with assuming we have an allowed category
    
            foreach ($quoteItems as $item) {
                $product = $item->getProduct();
                $categoryIds = $product->getCategoryIds();
                

                // Get the parent category IDs for the product
                $parentCategoryIds = [];
                foreach ($categoryIds as $categoryId) {
                    $category = $this->_categoryRepository->get($categoryId);
                    $parentCategory = $category->getParentCategory();
                    if ($parentCategory) {
                        $parentCategoryIds[] = $parentCategory->getId();
                    }
                }
                
                $matchedCategories = array_intersect(array_keys($CategoryPrice), $categoryIds);
                $matchedParentCategories = array_intersect(array_keys($CategoryPrice), $parentCategoryIds);

                // Check if any product belongs to an allowed category or its parent category
                if (!empty($matchedCategories) || !empty($matchedParentCategories)) {

                    $matchedCategoryPrices = [];
                    foreach ($matchedCategories as $categoryId) {
                        $matchedCategoryPrices[$categoryId] = $CategoryPrice[$categoryId];
                    }
                    foreach ($matchedParentCategories as $parentCategoryId) {
                        $matchedCategoryPrices[$parentCategoryId] = $CategoryPrice[$parentCategoryId]; 
                    }
                    if (!empty($matchedCategoryPrices)) {
                        $isAllowed = true;
                        $maxPrice = max($matchedCategoryPrices);
                    }
                }
            }
            
            // If no products belong to allowed categories, show error
            if ((!$isAllowed) && $applicable) {
                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
                return $error;
            }else{
                if((!$applicable && $isAllowed) || $applicable){

                    /** @var \Magento\Shipping\Model\Rate\Result $result */
                    $result = $this->rateResultFactory->create();
            
                    /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                    $method = $this->rateMethodFactory->create();
            
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));
            
                    $method->setMethod($this->_code);
                    $method->setMethodTitle($this->getConfigData('name'));
            
                    //$amount = $this->getConfigData('price');
                    $shippingPrice = $this->getFinalPriceWithHandlingFee($maxPrice);
                    $method->setPrice($shippingPrice);
                    $method->setCost($maxPrice);
            
                    $result->append($method);
            
                    return $result;
                }else{
                    return false;
                }
            }
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
        
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();
        
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));
        
        $amount = $this->getConfigData('price');
        $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);
        $method->setPrice($shippingPrice);
        $method->setCost($amount);
        
        $result->append($method);
        
        return $result;
            
    }

    public function getCategoryPrice()
    {
        $categoryPrice = $this->scopeConfig->getValue('carriers/Men/category', ScopeInterface::SCOPE_STORE);
        if (!$categoryPrice) {
            return [];
        }
        
        $categorysPrice = json_decode($categoryPrice);

        $collection = [];
        foreach ($categorysPrice as $category) {
            if ($category->Category != '') {
                $collection[$category->Category] = $category->Price;
            }
        }

        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/testlog.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('testt'); // Print string type data
        // $logger->info('Data::' . print_r($collection, true));

        return $collection;
    }

}


