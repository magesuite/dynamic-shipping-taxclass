<?php

namespace Magesuite\DynamicShippingTaxclass\Model\Command;

class GetQuoteItems
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $quoteItemCollectionFactory;

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
