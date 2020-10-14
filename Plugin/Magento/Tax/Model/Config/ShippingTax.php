<?php

namespace Magesuite\DynamicShippingTaxclass\Plugin\Magento\Tax\Model\Config;

class ShippingTax
{
    /**
     * @var \Magesuite\DynamicShippingTaxclass\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\ResourceModel\GroupRepository
     */
    protected $groupRepository;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
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
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository
    ) {
        $this->configuration = $configuration;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->taxCalculation = $taxCalculation;
        $this->groupRepository = $groupRepository;
    }

    public function afterGetShippingTaxClass(\Magento\Tax\Model\Config $config, int $shippingTaxClass, $store = null)
    {
        $dynamicType = $this->configuration->getDynamicShippingTaxClass();
        if ($dynamicType === \Magesuite\DynamicShippingTaxclass\Model\System\Config\Source\Tax\Dynamic::NO_DYNAMIC_SHIPPING_TAX_CALCULATION) {
            return $shippingTaxClass;
        }

        $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
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
