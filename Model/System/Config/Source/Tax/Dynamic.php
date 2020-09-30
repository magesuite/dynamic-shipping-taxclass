<?php

namespace Magesuite\DynamicShippingTaxclass\Model\System\Config\Source\Tax;

class Dynamic implements \Magento\Framework\Data\OptionSourceInterface
{
    const NO_DYNAMIC_SHIPPING_TAX_CALCULATION = 1;
    const USE_HIGHEST_PRODUCT_TAX = 2;

    protected $options;

    public function toOptionArray()
    {
        if ($this->options === null) {
            $options = [
                [
                    'value' => self::NO_DYNAMIC_SHIPPING_TAX_CALCULATION,
                    'label' => __('No dynamic shipping tax calculation')
                ],
                [
                    'value' => self::USE_HIGHEST_PRODUCT_TAX,
                    'label' => __('Use the highest product tax')
                ]
            ];
            $this->options = $options;
        }
        return $this->options;
    }
}
