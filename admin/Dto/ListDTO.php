<?php namespace Dto;

/**
 * Universaalne loetelumoodustaja sõltumata andmetüüpidest
 */
class ListDTO
{
    /**
     * @var int count
     */
    public $count;
    /**
     * @var array list
     */
    public $list;

    public function __construct($data = [])
    {
        $this->count = count($data);
        $this->list = $data;
        return $this;
    }
}
