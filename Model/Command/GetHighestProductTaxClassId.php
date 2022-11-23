<?php

namespace Magesuite\DynamicShippingTaxclass\Model\Command;

class GetHighestProductTaxClassId
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\ResourceModel\GroupRepository
     */
    protected $groupRepository;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;

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
            if ($quoteItem->getParentItem() || $quoteItem->getTaxClassId()) {
                continue;
            }

            $taxPercent = $this->getTaxPercent((int)$quoteItem->getTaxClassId(), $store);

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
}
