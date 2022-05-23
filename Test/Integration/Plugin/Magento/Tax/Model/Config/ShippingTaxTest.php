<?php

namespace Magesuite\DynamicShippingTaxclass\Test\Integration\Plugin\Magento\Tax\Model\Config;

class ShippingTaxTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->cart = $this->objectManager->get(\Magento\Checkout\Model\Cart::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture loadProductTaxClasses
     * @magentoConfigFixture current_store tax/classes/dynamic_shipping_tax_class 1
     * @magentoConfigFixture current_store carriers/flatrate/price 10
     */
    public function testUseNoDynamicTaxCalculation()
    {
        $shippingTaxAmount = $this->cart->getQuote()->getShippingAddress()->getShippingTaxAmount();
        $this->assertEquals($shippingTaxAmount, 0.0000);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture loadProductTaxClasses
     * @magentoConfigFixture current_store tax/classes/dynamic_shipping_tax_class 2
     * @magentoConfigFixture current_store carriers/flatrate/price 10
     */
    public function testUseHighestProductTax()
    {
        $shippingTaxAmount = $this->cart->getQuote()->getShippingAddress()->getShippingTaxAmount();
        $this->assertEquals($shippingTaxAmount, 0.8100);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture loadProductTaxClasses
     * @magentoConfigFixture current_store tax/classes/dynamic_shipping_tax_class 2
     */
    public function testTriggerRecollectDoesNotCauseInfiniteLoop()
    {
        $quote = $this->cart->getQuote();

        $quote->setTotalsCollectedFlag(false);
        $quote->setTriggerRecollect(true);
        $quote->save();

        $this->objectManager->removeSharedInstance(\Magento\Checkout\Model\Session::class);
        $session = $this->objectManager->get(\Magento\Checkout\Model\Session::class);

        $session->setQuoteId($quote->getId());
        $reloadedQuote = $session->getQuote();

        $this->assertEquals($quote->getId(), $reloadedQuote->getId());
    }


    public static function loadProductTaxClasses()
    {
        include __DIR__.'/../../../../../_files/product_tax_classes.php';
    }
}
