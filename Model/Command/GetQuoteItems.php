<?php

namespace Magesuite\DynamicShippingTaxclass\Model\Command;

class GetQuoteItems
{
    protected \Magento\Checkout\Model\Cart $customerCart;

    public function __construct(
        \Magento\Checkout\Model\Cart $customerCart
    ) {

        $this->customerCart = $customerCart;
    }

    public function execute()
    {
        try {
            $quote = $this->customerCart->getQuote();

            return $quote->getAllItems();
        } catch (\Exception $e) {
            return [];
        }
    }
}
