<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Logger;

class Logger extends \Monolog\Logger
{
    const DEBUG_KEYS_MASK = '****';

    /**
     * @var string[]
     */
    private $obscureFields = [
        'cardNum',
        'expDate',
        'cvv',
    ];

    /**
     * @param $message
     * @param array $context
     * @return bool
     */
    public function obscureAndInfo($message, array $context = array())
    {
        $data = [];

        foreach ($context as $key => $value) {
            if (in_array($key, $this->obscureFields)) {

                $data[$key] = preg_replace('/.*/', self::DEBUG_KEYS_MASK, $value);

                continue;
            }

            $data[$key] = $value;
        }

        return $this->info($message, $data);
    }
}
