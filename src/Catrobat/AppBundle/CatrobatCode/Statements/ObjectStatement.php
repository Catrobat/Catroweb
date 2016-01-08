<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ObjectStatement extends Statement
{
    private $name;

    public function __construct($statementFactory, $spaces, $name)
    {
        $this->name = $name;
        parent::__construct($statementFactory, null, 0,
            "", "");

    }

    public function execute()
    {
        return $this->name;
    }
}

?>

