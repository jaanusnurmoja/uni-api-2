<?php namespace user\model;

include_once __DIR__ . '/../../common/Helper.php';
include_once __DIR__ . '/../../common/Model/Person.php';
use \Common\Helper;
use \Common\Model\Person;

class User
{
    // User class
    public $id;
    public $username;
    public $email;
    private $password;
    public $social;
    private $userToken;
    private $identityToken;
    public $role = 'USER';
    public \Common\Model\Person $person;

    public function __construct($userData = [])
    {
        if (!empty($userData)) {
            foreach ($userData as $key => $value) {
                if ($key == 'ID') {
                    $key = 'id';
                }

                $key = Helper::camelize($key, true);
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }
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
     * Get the value of username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     */
    public function setUsername($username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     */
    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     */
    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of social
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Set the value of social
     */
    public function setSocial($social): self
    {
        $this->social = $social;

        return $this;
    }

    /**
     * Get the value of userToken
     */
    public function getUserToken()
    {
        return $this->userToken;
    }

    /**
     * Set the value of userToken
     */
    public function setUserToken($userToken): self
    {
        $this->userToken = $userToken;

        return $this;
    }

    /**
     * Get the value of identityToken
     */
    public function getIdentityToken()
    {
        return $this->identityToken;
    }

    /**
     * Set the value of identityToken
     */
    public function setIdentityToken($identityToken): self
    {
        $this->identityToken = $identityToken;

        return $this;
    }

    /**
     * Get the value of role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the value of role
     */
    public function setRole($role): self
    {
        $this->role = $role;

        return $this;
    }

/**
 * Get the value of person
 *
 * @return \Person
 */
    public function getPerson(): \Common\Model\Person
    {
        return $this->person;
    }

    /**
     * Set the value of person
     *
     * @param \Person $person
     *
     * @return self
     */

    public function setPerson(\Common\Model\Person $person): self
    {
        $this->person = $person;

        return $this;
    }

}
