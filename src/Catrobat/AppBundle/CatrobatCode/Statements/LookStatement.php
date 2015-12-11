<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class LookStatement extends Statement
{

    const BEGIN_STRING = "";
    const END_STRING = "";
    private $value;
    private $fileName;

    public function __construct($statementFactory, $xmlTree, $spaces, $value)
    {
        $this->value = $value;
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $value,
            self::END_STRING);
    }

    public function execute()
    {
        $this->findNames();
        //$code = $this->value . $this->executeChildren();

        $code = '';

        if($this->value != null)
        {
            $code .= $this->value ;
        }
        if ($this->fileName != null) {
            $code .= ' (filename: ' . $this->fileName->execute() . ')';
        }
        return $code;
    }


    private function findNames()
    {
        $tmpStatements = parent::getStatements();
        foreach ($tmpStatements as $statement) {
            if ($statement != null) {
                if ($statement instanceof FileNameStatement) {
                    $this->fileName = $statement;
                }
            }
        }
    }
}

?>