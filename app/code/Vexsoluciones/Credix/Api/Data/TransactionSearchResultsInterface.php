<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface TransactionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return TransactionInterface[]
     */
    public function getItems();

    /**
     * @param TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
