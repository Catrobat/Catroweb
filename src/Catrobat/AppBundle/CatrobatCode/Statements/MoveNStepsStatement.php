<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class MoveNStepsStatement extends Statement
{
	const BEGIN_STRING = "move (";
	const END_STRING = ") steps<br/>";
	
	public function __construct($statementFactory, $xmlTree, $spaces)
	{
		parent::__construct($statementFactory, $xmlTree, $spaces,
							self::BEGIN_STRING,
							self::END_STRING);
	}
	
}
?>
