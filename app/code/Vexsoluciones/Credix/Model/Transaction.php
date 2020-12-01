<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Model;

use Magento\Framework\Model\AbstractModel;
use Vexsoluciones\Credix\Api\Data\TransactionInterface;

class Transaction extends AbstractModel implements TransactionInterface
{
    /**
     * {@inheritDoc}
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthorizationNumber($authorizationNumber)
    {
        return $this->setData(self::AUTHORIZATION_NUMBER, $authorizationNumber);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationNumber()
    {
        return $this->getData(self::AUTHORIZATION_NUMBER);
    }

    /**
     * {@inheritDoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function setVerificationNumReference($message)
    {
        return $this->setData(self::VERIFICATION_NUM_REFERENCE, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerificationNumReference()
    {
        return $this->getData(self::VERIFICATION_NUM_REFERENCE);
    }

    /**
     * {@inheritDoc}
     */
    public function setVerificationMessage($message)
    {
        return $this->setData(self::VERIFICATION_MESSAGE, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerificationMessage()
    {
        return $this->getData(self::VERIFICATION_MESSAGE);
    }
}
