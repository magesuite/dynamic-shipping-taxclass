<?php

namespace Magesuite\DynamicShippingTaxclass\Model\Command;

class GetQuoteItems
{
    protected \Magento\Checkout\Model\Session $checkoutSession;

    protected \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
    ) {

        $this->checkoutSession = $checkoutSession;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
    }

    public function execute()
    {
        $quoteId = $this->checkoutSession->getQuoteId();

        if (empty($quoteId)) {
            return $this->checkoutSession->getQuote()->getAllItems();
        }

        $quoteItemCollection = $this->quoteItemCollectionFactory->create();
        $quoteItemCollection->addFieldToFilter('quote_id', $quoteId);

        return $quoteItemCollection;
    }
}
