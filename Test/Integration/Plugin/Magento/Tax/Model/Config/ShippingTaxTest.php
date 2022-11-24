<?php

namespace Magesuite\DynamicShippingTaxclass\Test\Integration\Plugin\Magento\Tax\Model\Config;

/**
 * @magentoAppArea frontend
 */
class ShippingTaxTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected ?\Magento\Checkout\Model\Cart $cart;

    public function setUp(): void
    {
        parent::setUp();

        $this->cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
    }

    /**
     * @magentoDataFixture Magesuite_DynamicShippingTaxclass::Test/Integration/_files/product_tax_classes.php
     * @magentoConfigFixture current_store tax/classes/dynamic_shipping_tax_class 1
     * @magentoConfigFixture current_store carriers/flatrate/price 10
     */
    public function testUseNoDynamicTaxCalculation()
    {
        $shippingTaxAmount = $this->cart->getQuote()->getShippingAddress()->getShippingTaxAmount();

        $this->assertEquals($shippingTaxAmount, 0.0000);
    }

    /**
     * @magentoDataFixture Magesuite_DynamicShippingTaxclass::Test/Integration/_files/product_tax_classes.php
     * @magentoConfigFixture current_store tax/classes/dynamic_shipping_tax_class 2
     * @magentoConfigFixture current_store carriers/flatrate/price 10
     */
    public function testUseHighestProductTax()
    {
        $shippingTaxAmount = $this->cart->getQuote()->getShippingAddress()->getShippingTaxAmount();

        $this->assertEquals($shippingTaxAmount, 0.8100);
    }

    /**
     * @magentoDataFixture Magesuite_DynamicShippingTaxclass::Test/Integration/_files/product_tax_classes.php
     * @magentoConfigFixture current_store tax/classes/dynamic_shipping_tax_class 2
     */
    public function testTriggerRecollectDoesNotCauseInfiniteLoop()
    {
        $quote = $this->cart->getQuote();

        $quote->setTotalsCollectedFlag(false);
        $quote->setTriggerRecollect(true);
        $quote->save();

        $this->_objectManager->removeSharedInstance(\Magento\Checkout\Model\Session::class);
        $session = $this->_objectManager->get(\Magento\Checkout\Model\Session::class);

        $session->setQuoteId($quote->getId());
        $reloadedQuote = $session->getQuote();

        $this->assertEquals($quote->getId(), $reloadedQuote->getId());
    }
}
