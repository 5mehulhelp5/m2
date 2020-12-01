<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Vexsoluciones\Credix\Api\Data\TransactionInterface;
use Vexsoluciones\Credix\Model\ResourceModel\Transaction as TransactionResource;
use Zend_Db_Exception;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createTransactionsTable($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    private function createTransactionsTable(SchemaSetupInterface $setup): void
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(TransactionResource::MAIN_TABLE))
            ->addColumn(
                TransactionInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Primary Index Id'
            )
            ->addColumn(
                TransactionInterface::ORDER_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                TransactionInterface::AUTHORIZATION_NUMBER,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                TransactionInterface::TYPE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                TransactionInterface::MESSAGE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                TransactionInterface::VERIFICATION_NUM_REFERENCE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                TransactionInterface::VERIFICATION_MESSAGE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->setComment('My Table');

        $setup->getConnection()->createTable($table);
    }
}
