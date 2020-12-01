<?php

declare(strict_types=1);

namespace Vexsoluciones\Credix\Gateway\Request\Form;

class Card
{
    public $cardNumber;
    public $expDate;
    public $cvv;
    public $quota;

    public $cashUser;
    public $channel;
    public $pin;
}
