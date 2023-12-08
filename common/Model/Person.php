<?php namespace Common\Model;

/**
 * Põhilised isikuandmed. Tabelisse persons kantakse automaatselt need, kes sisenevad esmakordselt süsteemi id-kaardiga.
 *
 * @todo Laiendusklass detailsemate isikuandmetega, nt sünnikuupäev (isikukoodi põhjal)
 */
class Person
{
    public $id;
    public $firstName;
    public $middleName;
    public $lastName;
    /**
     * Riik, vaikimisi Eesti
     *
     * @var mixed country
     */
    public $country = 'EE';
    public $pno;

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
     * Get  the value of pno
     */
    public function getPno()
    {
        return $this->pno;
    }

    /**
     * Set the value of pno
     */
    public function setPno($pno = null): self
    {
        $this->pno = $pno;

        return $this;
    }

}
