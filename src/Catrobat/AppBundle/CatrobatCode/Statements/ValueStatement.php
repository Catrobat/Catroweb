<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ValueStatement extends Statement
{
    private $value;

    public function __construct($statementFactory, $xmlTree, $spaces, $value)
    {
        $this->value = $value;
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $value,
            "");
    }

    public function execute()
    {
        $code = $this->value . $this->executeChildren();
        return $code;
    }
}

?>
