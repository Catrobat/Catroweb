<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class LookStatement extends Statement
{

    private $value;
    private $fileName;

    public function __construct($statementFactory, $xmlTree, $spaces, $value)
    {
        $this->value = $value;
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $value,
            "");
    }

    public function execute()
    {
        $this->findNames();

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

