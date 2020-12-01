<?php

declare(strict_types=1);

namespace Vexsoluciones\Credix\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vexsoluciones\Credix\Model\Payment\Credix;

class EnvironmentType implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Credix::ENVIRONMENT_INTEGRATION_CODE,
                'label' => Credix::ENVIRONMENT_INTEGRATION,
            ],
            [
                'value' => Credix::ENVIRONMENT_PRODUCTION_CODE,
                'label' => Credix::ENVIRONMENT_PRODUCTION,
            ],
        ];
    }
}
