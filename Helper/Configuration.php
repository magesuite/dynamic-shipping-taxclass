<?php

namespace Magesuite\DynamicShippingTaxclass\Helper;

class Configuration
{
    const DYNAMIC_SHIPPING_TAX_CLASS_PATH = 'tax/classes/dynamic_shipping_tax_class';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getDynamicShippingTaxClass(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::DYNAMIC_SHIPPING_TAX_CLASS_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }
}
