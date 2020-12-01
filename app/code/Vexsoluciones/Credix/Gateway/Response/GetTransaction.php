<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Gateway\Response;

final class GetTransaction
{
    /**
     * @var string
     */
    public $numReference;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $message;

    private function __construct(
        string $numReference,
        string $type,
        string $message
    )
    {
        $this->numReference = $numReference;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * @param array $data
     * @return GetTransaction
     */
    public static function create(array $data = [])
    {
        return new self(
            $data['numReference'],
            $data['type'],
            $data['message']
        );
    }
}
