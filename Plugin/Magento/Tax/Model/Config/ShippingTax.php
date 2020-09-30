<?php

namespace Magesuite\DynamicShippingTaxclass\Plugin\Magento\Tax\Model\Config;

class ShippingTax
{
    protected $configuration;

    protected $cart;

    protected $session;

    protected $groupRepository;

    protected $taxCalculation;

    private function getHighestProductTaxClassId($quoteItems, $store)
    {
        $highestTaxClassId = 0;
        $highestTaxPercent = 0.0;
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getParentItem()) {
                continue;
            }
            $taxPercent = $quoteItem->getTaxPercent();
            if ($taxPercent > $highestTaxPercent) {
                $highestTaxPercent = $taxPercent;
                $highestTaxClassId = $quoteItem->getTaxClassId();
            }
        }
        return $highestTaxClassId;
    }

    private function getTaxPercent(int $productTaxClassId, $store)
    {
        $groupId = $this->customerSession->getCustomerGroupId();
        $group = $this->groupRepository->getById($groupId);
        $customerTaxClassId = $group->getTaxClassId();

        $request = $this->taxCalculation->getRateRequest(null, null, $customerTaxClassId, $store);
        $request->setData('product_class_id', $productTaxClassId);

        $taxPercent = $this->taxCalculation->getRate($request);
        if (!$taxPercent) {
            $taxPercent = 0.0;
        }
        return $taxPercent;
    }

    public function __construct(
        \Magesuite\DynamicShippingTaxclass\Helper\Configuration $configuration,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\Session $session,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository
    ) {
        $this->configuration = $configuration;
        $this->cart = $cart;
        $this->session = $session;
        $this->taxCalculation = $taxCalculation;
        $this->groupRepository = $groupRepository;
    }

    public function afterGetShippingTaxClass(\Magento\Tax\Model\Config $config, int $shippingTaxClass, $store = null)
    {
        $dynamicType = $this->configuration->getDynamicShippingTaxClass();
        if ($dynamicType === \Magesuite\DynamicShippingTaxclass\Model\System\Config\Source\Tax\Dynamic::NO_DYNAMIC_SHIPPING_TAX_CALCULATION) {
            return $shippingTaxClass;
        }

        $quoteItems = $this->cart->getItems();
        if (count($quoteItems) === 0) {
            return $shippingTaxClass;
        }
        $taxClassId = 0;
        if ($dynamicType === \Magesuite\DynamicShippingTaxclass\Model\System\Config\Source\Tax\Dynamic::USE_HIGHEST_PRODUCT_TAX) {
            $taxClassId = $this->getHighestProductTaxClassId($quoteItems, $store);
        }
        if (!$taxClassId) {
            $taxClassId = $shippingTaxClass;
        }
        return $taxClassId;
    }
}
