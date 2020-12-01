<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Exceptions;

use Exception;

class ClientException extends Exception
{
    /**
     * @param string $message
     * @return ClientException
     */
    public static function generalException(string $message)
    {
        return new self($message);
    }

    /**
     * @param string $message
     * @return ClientException
     */
    public static function transactionException(string $message)
    {
        return new self($message);
    }
}
