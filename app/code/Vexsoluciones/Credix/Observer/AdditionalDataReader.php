<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class AdditionalDataReader extends AbstractDataAssignObserver
{
    const ADDITIONAL_DATA_CARD_NUMBER = 'card_number';
    const ADDITIONAL_DATA_MONTH = 'month';
    const ADDITIONAL_DATA_YEAR = 'year';
    const ADDITIONAL_DATA_CVV = 'cvv';
    const ADDITIONAL_DATA_QUOTA = 'quota';

    protected $additionalInformationList = [
        self::ADDITIONAL_DATA_CARD_NUMBER,
        self::ADDITIONAL_DATA_MONTH,
        self::ADDITIONAL_DATA_YEAR,
        self::ADDITIONAL_DATA_CVV,
        self::ADDITIONAL_DATA_QUOTA,
    ];

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (!isset($additionalData[$additionalInformationKey])) {
                continue;
            }

            $paymentInfo->setAdditionalInformation(
                $additionalInformationKey,
                $additionalData[$additionalInformationKey]
            );
        }
    }
}
