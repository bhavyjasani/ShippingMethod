<?php

namespace Dolphin\ShippingMethod\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Women extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'Women';

    protected $_categoryRepository;
    protected $rateResultFactory;
    protected $rateMethodFactory;

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
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function getAllowedMethods()
    {
        return ['Women' => $this->getConfigData('name')];
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $allcategorym = $this->getConfigData('sallowcategory');
        $applicable = $this->getConfigData('showmethod');

        if($allcategorym == 1){

            // Fetch allowed categories from configuration
            $allowedCategories = $this->getConfigData('category');
            $allowedCategories = is_string($allowedCategories) ? explode(',', $allowedCategories) : [];
    
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
            
                // Check if any product belongs to an allowed category or its parent category
                if (array_intersect($allowedCategories, $categoryIds) || array_intersect($allowedCategories, $parentCategoryIds)) {
                    $isAllowed = true;
                    break; // Exit early if any product passes the category check
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
            
                    $amount = $this->getConfigData('price');
                    $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);
                    $method->setPrice($shippingPrice);
                    $method->setCost($amount);
            
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
}
