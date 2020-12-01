<?php

namespace Meetanshi\Paymulti\Helper;
use Magento\Framework\App\Helper\AbstractHelper;

class Rounding extends AbstractHelper
{

    protected $extraPrice;

    protected $itemPrice;

    public function addExtraPrice($key, $value)
    {

        $this->extraPrice[$key] = $value;
    }

    public function addItemPrice($i, $key, $value)
    {

        $this->itemPrice[$i][$key] = $value;
    }

    public function convertRequest(array &$request)
    {

        $itemAmount = 0;
        $extraprice = 0;
        foreach ($this->itemPrice as $item) {
            $itemAmount = $itemAmount + ((int)$item['qty'] * (float)$item['amount']);
        }
        foreach ($this->extraPrice as $key => $value) {
            $extraprice = (float)$extraprice + (float)$value;
        }
        $baseprice = $extraprice + $itemAmount;

        $request['AMT'] = $baseprice;
        $request['ITEMAMT'] = $itemAmount;

        return $request;
    }
}