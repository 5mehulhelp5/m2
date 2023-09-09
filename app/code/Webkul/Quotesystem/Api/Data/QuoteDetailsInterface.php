<?php
/**
 * Quote Interface
 *
 * @category  Webkul
 * @package   Webkul_Quotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Quotesystem\Api\Data;

interface QuoteDetailsInterface
{
    /**
* #@+
     * Constants for keys of data array.
     */
    const ENTITYID = 'entity_id';

    const QUOTEID = 'quote_id';

    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getEntityId();
    /**
     * Set entity ID
     *
     * @param  int $id [entity id]
     * @return \Webkul\Quotesystem\Api\Data\QuoteDetailsInterface
     */
    public function setEntityId($id);

    /**
     * Get entity Quote ID
     *
     * @return int|null
     */
    public function getQuoteId();
    /**
     * Set entity Quote ID
     *
     * @param  int $quoteId
     * @return \Webkul\Quotesystem\Api\Data\QuoteDetailsInterface
     */
    public function setQuoteId($quoteId);
}
