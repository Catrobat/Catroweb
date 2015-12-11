<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ValueStatement extends Statement
{

    const BEGIN_STRING = "";
    const END_STRING = "";
    private $value;

    public function __construct($statementFactory, $xmlTree, $spaces, $value)
    {
        $this->value = $value;
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $value,
            self::END_STRING);
    }

    public function execute()
    {
        $code = $this->value . $this->executeChildren();
        return $code;
    }
}

?>