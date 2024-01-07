<?php namespace Api\Model;;

/**
 * Universaalne loetelumoodustaja sõltumata andmetüüpidest
 */
class EntityList
{
    /**
     * @var int count
     */
    public $count;
    /**
     * @var array items
     */
    public array $items;

    public function __construct($data = [])
    {
        $this->count = count($data);
        $this->items = $data;
        return $this;
    }
}
