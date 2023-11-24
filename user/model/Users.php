<?php namespace user\model;
class Users
{
    public $count = 0;
    public $list = [];


    /**
     * Get the value of count
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set the value of count
     */
    public function setCount($count = 0): self
    {
        if ($count > 0) {
            $count = count($this->list);
        }
        $this->count = $count;

        return $this;
    }

    /**
     * Get the value of list
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Set the value of list
     */
    public function setList($list): self
    {
        $this->list = $list;

        return $this;
    }

    public function addUserToList(User $user): void {
        array_push($this->list, $user);
        $this->setCount(count($this->list));
    }
}