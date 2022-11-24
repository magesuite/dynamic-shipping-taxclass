<?php

namespace Magesuite\DynamicShippingTaxclass\Plugin\Magento\Tax\Model\Config;

class ShippingTax
{
    protected \Magesuite\DynamicShippingTaxclass\Helper\Configuration $configuration;

    protected \Magesuite\DynamicShippingTaxclass\Model\Command\GetQuoteItems $getQuoteItems;

    protected \Magesuite\DynamicShippingTaxclass\Model\Command\GetHighestProductTaxClassId $getHighestProductTaxClassId;

    public function __construct(
        \Magesuite\DynamicShippingTaxclass\Helper\Configuration $configuration,
        \Magesuite\DynamicShippingTaxclass\Model\Command\GetQuoteItems $getQuoteItems,
        \Magesuite\DynamicShippingTaxclass\Model\Command\GetHighestProductTaxClassId $getHighestProductTaxClassId
    ) {
        $this->configuration = $configuration;
        $this->getQuoteItems = $getQuoteItems;
        $this->getHighestProductTaxClassId = $getHighestProductTaxClassId;
    }

    public function afterGetShippingTaxClass(\Magento\Tax\Model\Config $config, int $shippingTaxClass, $store = null)
    {
        $dynamicType = $this->configuration->getDynamicShippingTaxClass();

        if ($dynamicType === \Magesuite\DynamicShippingTaxclass\Model\System\Config\Source\Tax\Dynamic::NO_DYNAMIC_SHIPPING_TAX_CALCULATION) {
            return $shippingTaxClass;
        }

        $taxClassId = 0;

        if ($dynamicType === \Magesuite\DynamicShippingTaxclass\Model\System\Config\Source\Tax\Dynamic::USE_HIGHEST_PRODUCT_TAX) {
            $quoteItems = $this->getQuoteItems->execute();
            $taxClassId = $this->getHighestProductTaxClassId->execute($quoteItems, $store);
        }

        if (!$taxClassId) {
            return $shippingTaxClass;
        }

        return $taxClassId;
    }
}
