<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class SoundStatement extends Statement
{
    private $value;
    private $fileName;
    private $name;

    public function __construct($statementFactory, $xmlTree, $spaces, $value)
    {
        $this->value = $value;
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $value,
            "");
    }

    public function execute()
    {
        $code = $this->value;
        $this->findNames();

        if ($this->name != null) {
            $code .= $this->name->execute();
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
                if ($statement instanceof ValueStatement) {
                    $this->name = $statement;
                } else if ($statement instanceof FileNameStatement) {
                    $this->fileName = $statement;
                }
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }
}

?>
