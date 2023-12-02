<?php namespace Common\Model;

class Person
{
    public $id;
    public $firstName;
    public $middleName;
    public $lastName;
    public $country = 'EE';
    public $pno;
    //private $pnoCode;
    //public $born;
    //public bool $isAlive = true;
    //public $deceased = null;

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }


    /**
     * Get the value of firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     */
    public function setFirstName($firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the value of middleName
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Set the value of middleName
     */
    public function setMiddleName($middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * Get the value of lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     */
    public function setLastName($lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get the value of country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set the value of country
     */
    public function setCountry($country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get the value of pno
     */
 
 /*
     public function getPnoCode()
    {
        return $this->pnoCode;
    }

    /**
     * Set the value of pno
     */
 /*   public function setPnoCode($pnoCode = null): self
    {
        if ($pnoCode == null && !empty($this->pno)) {
            $pnoArr = explode('-', $this->getPno());
            $pnoCode = $pnoArr[1];
            $country = str_replace('PNO', '', $pnoArr[0]);
            if ($this->country != $country) {
                $this->setCountry($country);
            }
        }
        $this->pnoCode = $pnoCode;
        return $this;
    }

    /**
     * Get the value of pnoFull
     */
    public function getPno()
    {
        return $this->pno;
    }

    /**
     * Set the value of pnoFull
     */
    public function setPno($pno=null): self
    {
        $this->pno = $pno;

        return $this;
    }

    /**
     * Get the value of born
     */
/*    public function getBorn()
    {
        return $this->born;
    }

    /**
     * Set the value of born
     */
 /*   public function setBorn($born): self
    {
        $this->born = $born;

        return $this;
    }

    /**
     * Get the value of isAlive
     *
     * @return bool
     */
/*    public function getIsAlive(): bool
    {
        return $this->isAlive;
    }

    /**
     * Set the value of isAlive
     *
     * @param bool $isAlive
     *
     * @return self
     */
  /*  public function setIsAlive(bool $isAlive = true): self
    {
        if ($isAlive === true) {
            $this->setDeceased(null);
        }
        $this->isAlive = $isAlive;

        return $this;
    }

    /**
     * Get the value of deceased
     */
/*    public function getDeceased()
    {
        return $this->deceased;
    }

    /**
     * Set the value of deceased
     */
/*    public function setDeceased($deceased): self
    {
        $this->deceased = $deceased;

        return $this;
    }
    */
}