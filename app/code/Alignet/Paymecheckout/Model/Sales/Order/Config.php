<?php
/**
 * @copyright Copyright (c)  2019 Alignet  (https://www.pay-me.com)
 */

namespace Alignet\Paymecheckout\Model\Sales\Order;

use Alignet\Paymecheckout\Model\Sales\Order;

class Config extends \Magento\Sales\Model\Order\Config
{
    const XML_PATH_ORDER_STATUS_NEW         = 'payment/orba_payupl/order_status_new';
    const XML_PATH_ORDER_STATUS_HOLDED      = 'payment/orba_payupl/order_status_holded';
    const XML_PATH_ORDER_STATUS_PROCESSING  = 'payment/orba_payupl/order_status_processing';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $orderStatusFactory,
            $orderStatusCollectionFactory,
            $state
        );
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Gets PayuLatam-specific default status for state.
	 * 2020-12-08 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
	 * "«Declaration of Alignet\Paymecheckout\Model\Sales\Order\Config::getStateDefaultStatus($state)
	 * must be compatible with Magento\Sales\Model\Order\Config::getStateDefaultStatus($state): ?string»":
	 * https://github.com/innomuebles/m2/issues/6
     *
     * @param string $state
     * @return string
     */
    public function getStateDefaultStatus($state): ?string
    {
        switch ($state) {
            case Order::STATE_PENDING_PAYMENT:
                return $this->scopeConfig->getValue(self::XML_PATH_ORDER_STATUS_NEW, 'store');
            case Order::STATE_HOLDED:
                return $this->scopeConfig->getValue(self::XML_PATH_ORDER_STATUS_HOLDED, 'store');
            case Order::STATE_PROCESSING:
                return $this->scopeConfig->getValue(self::XML_PATH_ORDER_STATUS_PROCESSING, 'store');
        }
        return parent::getStateDefaultStatus($state);
    }
}
