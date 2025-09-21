<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\GoogleTagManagerPlus\Model;

use Magefan\GoogleTagManagerPlus\Api\SessionManagerInterface;
use Magento\Framework\Session\SessionManager as MagentoSessionManager;

/**
 * Abstract management model
 */
class SessionManager implements SessionManagerInterface
{
    /**
     * @param MagentoSessionManager $session
     * @param array $data
     * @return void
     */
    public function push(MagentoSessionManager $session, array $data): void
    {
        if ($data) {
            $dataLayers = $session->getMfDataLayers() ?: [];
            $dataLayers[] = $data;
            $session->setMfDataLayers($dataLayers);
        }
    }

    /**
     * @param MagentoSessionManager $order
     * @return array
     */
    public function get(MagentoSessionManager $session): array
    {
        $data = $session->getMfDataLayers() ?: [];
        $session->setMfDataLayers(null);
        return $data;
    }
}
