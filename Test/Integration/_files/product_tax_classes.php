<?php

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
$storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);

/** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
$scopeConfig = $objectManager->create(\Magento\Framework\App\Config\ScopeConfigInterface::class);
$countryId = $scopeConfig->getValue(
    'general/country/default',
    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
    $storeManager->getStore()->getId());

/** @var \Magento\Checkout\Model\Session $session */
$session = $objectManager->create('\Magento\Checkout\Model\Session');

/** @var \Magento\Checkout\Model\Cart $cart */
$cart = $objectManager->get('\Magento\Checkout\Model\Cart');

$taxes = [ 0.2, 0.6, 0.3, 0.9, 0.7, 0.1, 0.5, 0.4, 0.8 ];

foreach ($taxes as $tax) {

    $taxClass = $objectManager->create(\Magento\Tax\Model\ClassModel::class);
    $taxClass->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT);
    $taxClass->setClassName(sprintf('TAX %0.1lf', $tax));
    $taxClass->save($taxClass);

    $taxRate = $objectManager->create(\Magento\Tax\Model\Calculation\Rate::class)
        ->setCode(sprintf('%s-%0.1lf',  $countryId, $tax))
        ->setTaxCountryId($countryId)
        ->setZipIsRange('0')
        ->setTaxPostcode('*')
        ->setRate($tax);
    $taxRate->save();

    $taxRule = $objectManager->create(\Magento\Tax\Model\Calculation\Rule::class)
        ->setCode(sprintf('%s-%0.1lf',  $countryId, $tax))
        ->setPriority(0)
        ->setCustomerTaxClassIds([ 3 ])
        ->setProductTaxClassIds([ $taxClass->getId() ])
        ->setTaxRateIds([ $taxRate->getId() ]);
    $taxRule->save();

    $product = $objectManager->create(\Magento\Catalog\Model\Product::class)
        ->setSku(sprintf('sku-tax-%0.1lf', $tax))
        ->setName(sprintf('name-tax-%0.1lf', $tax))
        ->setAttributeSetId(4)
        ->setStatus(1)
        ->setWeight(1)
        ->setVisibility(4)
        ->setWebsiteIds([1])
        ->setTaxClassId($taxClass->getId())
        ->setTypeId('simple')
        ->setPrice(100)
        ->setStockData([
            'use_config_manage_stock' => 0,
            'manage_stock' => 1,
            'is_in_stock' => 1,
            'qty' => 1
        ]);
    $product->save();
    $session->getQuote()->addProduct($product);
}
$session->getQuote()->save();
$cart->getQuote()->getShippingAddress()->addData([
    'firstname' => 'Test',
    'lastname' => 'Test',
    'street' => 'Test',
    'city' => 'Test',
    'country_id' => 'US',
    'postcode' => '10000',
    'telephone' => '0123456789'
]);
$cart->getQuote()->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$cart->getQuote()->getShippingAddress()->setCollectShippingRates(true);
$cart->getQuote()->collectTotals();
$cart->save();
