<?php

namespace Magesuite\DynamicShippingTaxclass\Model\Command;

class GetQuoteItems
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {

        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
    }

    public function execute()
    {
        $quoteId = $this->checkoutSession->getQuoteId();

        if (!empty($quoteId)) {
            $quote = $this->cartRepository->get($quoteId);
            return $quote->getAllItems();
        }

        return $this->checkoutSession->getQuote()->getAllItems();
    }
}
