<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Gateway\Response;

final class AuthTransaction
{
    /**
     * @var string
     */
    public $authorizationNumber;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $message;

    private function __construct(
        string $authorizationNumber,
        string $type,
        string $message
    )
    {
        $this->authorizationNumber = $authorizationNumber;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * @param array $data
     * @return AuthTransaction
     */
    public static function create(array $data = [])
    {
        return new self(
            $data['authorizationNumber'],
            $data['type'],
            $data['message']
        );
    }
}
