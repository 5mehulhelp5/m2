<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Model\ResourceModel;

use Vexsoluciones\Credix\Api\Data\TransactionInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{
    const MAIN_TABLE = 'vexsoluciones_credix_transactions';

    protected function _construct()
    {
        $this->_init(
            self::MAIN_TABLE,
            TransactionInterface::ENTITY_ID
        );
    }
}
