<?php namespace Dto;

class ListDTO
{
    public $count;
    public $list;

    public function __construct($data = [])
    {
        $this->count = count($data);
        $this->list = $data;
        return $this;
    }
}