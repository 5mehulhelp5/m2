<?php

declare(strict_types=1);

namespace Vexsoluciones\Credix\Plugin;

use Magento\Config\Model\Config;
use Vexsoluciones\Credix\Helper\Util;
use Vexsoluciones\Credix\Logger\Logger;

class ConfigPlugin
{
    protected $util;
    protected $logger;

    public function __construct(
        Util $util,
        Logger $logger
    ) {
        $this->util = $util;
        $this->logger = $logger;
    }

    public function afterSave(Config $subject, $result)
    {
        $this->util->verify();

        return $result;
    }
}
