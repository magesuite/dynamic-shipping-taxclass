<?php

namespace Magesuite\DynamicShippingTaxclass\Model\Command;

class GetHighestProductTaxClassId
{
    protected \Magento\Customer\Model\Session $customerSession;

    protected \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository;

    protected \Magento\Tax\Model\Calculation $taxCalculation;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository
    ) {

        $this->customerSession = $customerSession;
        $this->taxCalculation = $taxCalculation;
        $this->groupRepository = $groupRepository;
    }

    public function execute($quoteItems, $store)
    {
        $highestTaxClassId = 0;
        $highestTaxPercent = 0.0;

        foreach ($quoteItems as $quoteItem) {
            $taxClassId = $quoteItem->getTaxClassId();

            if ($quoteItem->getParentItem() || !$taxClassId) {
                continue;
            }

            $taxPercent = $this->getTaxPercent((int)$taxClassId, $store);

            if ($taxPercent > $highestTaxPercent) {
                $highestTaxPercent = $taxPercent;
                $highestTaxClassId = $taxClassId;
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
}
