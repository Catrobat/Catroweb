<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ObjectStatement extends Statement
{
    const BEGIN_STRING = "";
    const END_STRING = "";
    private $name;

    public function __construct($statementFactory, $spaces, $name)
    {
        $this->name = $name;
        parent::__construct($statementFactory, null, 0,
            self::BEGIN_STRING,
            self::END_STRING);

    }

    public function execute()
    {
        return $this->name;
    }
}

?>