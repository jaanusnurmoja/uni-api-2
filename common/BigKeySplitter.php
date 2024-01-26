<?php namespace Common;

class BigKeySplitter
{

    public string $field;
    public int $count;
    public ?string $tableAlias = null;
    public ?string $parent = null;
    public ?string $mode = null;
    public ?int $id = null;
    public ?string $table = null;

    public function __construct($bigKey)
    {
        if (!strpos($bigKey,'__')) 
        {
            $this->field = $bigKey;
            $this->count = 1;
        }
        else 
        {
            $tableAliasAndField = explode(':',$bigKey);
            $tableAlias = $tableAliasAndField[0];
            $tableAliasParts = explode('__', $tableAlias);
            $cap = count($tableAliasParts);
            $this->tableAlias = $tableAlias;
            $this->field = $tableAliasAndField[1];
            $this->count = $cap+1;
            $this->parent = $tableAliasParts[0];
            $this->mode = $tableAliasParts[1];
            $this->id = $tableAliasParts[2];
            $this->table = $tableAliasParts[3];
        }

    }
}