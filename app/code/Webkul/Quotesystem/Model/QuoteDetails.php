<?php
/**
 * Quotes Model
 *
 * @category  Webkul
 * @package   Webkul_Quotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Quotesystem\Model;

use Webkul\Quotesystem\Api\Data\QuoteDetailsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class QuoteDetails extends AbstractModel implements QuoteDetailsInterface, IdentityInterface
{
    const CACHE_TAG = 'wk_quote_details';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_quote_details';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_quote_details';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Quotesystem\Model\ResourceModel\QuoteDetails ::class);
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITYID);
    }
    /**
     * Set ID
     *
     * @return \Webkul\Quotesystem\Model\QuoteDetails
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITYID, $entityId);
    }

    /**
     * Get Quote ID
     *
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTEID);
    }
    /**
     * Set Quote ID
     *
     * @return \Webkul\Quotesystem\Model\Quotes
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTEID, $quoteId);
    }
}
