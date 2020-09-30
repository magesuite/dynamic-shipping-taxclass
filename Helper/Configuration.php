<?php

namespace Magesuite\DynamicShippingTaxclass\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DYNAMIC_SHIPPING_TAX_CLASS_PATH = 'tax/classes/dynamic_shipping_tax_class';

    protected $storeManager;

    protected $scopeConfig;

    protected function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->scopeConfig = $context->getScopeConfig();
    }

    public function getDynamicShippingTaxClass()
    {
        return (int)$this->scopeConfig->getValue(
            self::DYNAMIC_SHIPPING_TAX_CLASS_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
}
