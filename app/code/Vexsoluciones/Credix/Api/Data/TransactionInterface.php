<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Api\Data;

interface TransactionInterface
{
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const AUTHORIZATION_NUMBER = 'authorization_number';
    const TYPE = 'type';
    const MESSAGE = 'message';
    const VERIFICATION_NUM_REFERENCE = 'verification_num_reference';
    const VERIFICATION_MESSAGE = 'verification_message';

    /**
     * @param int $entityId
     * @return TransactionInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param string $orderId
     * @return TransactionInterface
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getOrderId();

    /**
     * @param string $authorizationNumber
     * @return TransactionInterface
     */
    public function setAuthorizationNumber($authorizationNumber);

    /**
     * @return string
     */
    public function getAuthorizationNumber();

    /**
     * @param string $type
     * @return TransactionInterface
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $message
     * @return TransactionInterface
     */
    public function setMessage($message);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     * @return TransactionInterface
     */
    public function setVerificationNumReference($message);

    /**
     * @return string
     */
    public function getVerificationNumReference();

    /**
     * @param string $message
     * @return TransactionInterface
     */
    public function setVerificationMessage($message);

    /**
     * @return string
     */
    public function getVerificationMessage();
}
