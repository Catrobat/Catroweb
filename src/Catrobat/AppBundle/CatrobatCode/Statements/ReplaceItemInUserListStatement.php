<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ReplaceItemInUserListStatement extends BaseUserListStatement
{
    const BEGIN_STRING = "replace item in userlist ";
    const MIDDLE_STRING = "(";
    const END_STRING = ")<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::MIDDLE_STRING,
            self::END_STRING);
    }

    public function getBrickText()
    {
        $list_variable_name = $this->xmlTree->userList->name;
        $formula_string_index = null;
        $formula_string_value = null;

        foreach ($this->getFormulaListChildStatement()->getStatements() as $statement) {
            if ($statement instanceof FormulaStatement) {
                if ($statement->getCategory() === 'REPLACE_ITEM_IN_USERLIST_INDEX')
                    $formula_string_index = $statement->execute();
                else
                    $formula_string_value = $statement->execute();
            }
        }

        $formula_str_index_no_markup = $formula_string_without_markup = preg_replace("#<[^>]*>#", '', $formula_string_index);
        $formula_str_value_no_markup = $formula_string_without_markup = preg_replace("#<[^>]*>#", '', $formula_string_value);

        return "Replace item in list " . $list_variable_name . " at position " . $formula_str_index_no_markup . " with " . $formula_str_value_no_markup;
    }

    public function getBrickColor()
    {
        return "1h_brick_red.png";
    }

}

?>
