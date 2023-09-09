<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_Quotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Quotesystem\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class CopyDataPatch implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Webkul\Quotesystem\Model\ResourceModel\Quotes\CollectionFactory $quoteCollection
     * @param \Webkul\Quotesystem\Model\QuoteDetailsFactory $quoteDetails
     * @param \Webkul\Quotesystem\Model\Quotes $quotes
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Webkul\Quotesystem\Model\ResourceModel\Quotes\CollectionFactory $quoteCollection,
        \Webkul\Quotesystem\Model\QuoteDetailsFactory $quoteDetails,
        \Webkul\Quotesystem\Model\Quotes $quotes,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->quoteCollection = $quoteCollection;
        $this->quoteDetails = $quoteDetails;
        $this->quotes = $quotes;
        $this->logger = $logger;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $quoteCollection = $this->quoteCollection->create();
        if ($quoteCollection->getSize()) {
            foreach ($quoteCollection as $quoteData) {
                $this->moduleDataSetup->getConnection()->startSetup();
                $connection = $this->moduleDataSetup->getConnection();
                $quoteDetails = $this->quoteDetails->create();
                try {
                    $data = [
                        'entity_id' => $quoteData->getId(),
                        'customer_id' => $quoteData->getCustomerId(),
                        'created_at' => $quoteData->getCreatedAt(),
                        'quote_generate' => 1,
                    ];

                    $connection->insert($this->moduleDataSetup->getTable('wk_quote_details'), $data);
                    $table = 'wk_quotes';
                    $idField = 'entity_id';
                    $rowId = $quoteData->getId();
                    $field = 'quote_id';
                    $value = $quoteData->getId();
                    $this->moduleDataSetup->updateTableRow($table, $idField, $rowId, $field, $value);
                    $this->moduleDataSetup->getConnection()->endSetup();
                } catch (\Exception $e) {
                    $this->logger->info('Save Quote Data Error '.$e->getMessage());
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
