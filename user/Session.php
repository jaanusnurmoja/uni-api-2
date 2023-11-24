<?php namespace user;

include_once __DIR__ . '/model/Users.php';

use stdClass;
use \user\model\Users;
include_once __DIR__ . '/Service/Db.php';
use \user\Service\Db;

class Session
{
    public $isUser = false;
    public $isLoggedIn = false;
    public $isAdmin = false;
    public $loggedIn;
    public $searchedUser;
    public $currentPerson;
    public $userData;
    public $users;


    public function __construct()
    {
        $db = new Db();
        $this->users = $db->getAllUsersOrFindByProps();
        if (isset($_SESSION['currentPerson']) && !empty($_SESSION['currentPerson'])) {
            $this->setIsLoggedIn(true);
            $this->setUserData();
        }
        //return $this;
    }

    

    public function setUserData() {
        $this->currentPerson = $_SESSION['currentPerson'];
        if (isset($_SESSION['userData'])) {
            $this->userData = (object) $_SESSION['userData'];
        }
        /*Array ( [serialNumber] => PNOEE-36706230305 
        [GN] => JAANUS [SN] => NURMOJA [CN] => NURMOJA\ 
        [C] => EE [email] => 36706230305@eesti.ee )*/
        if (isset($_SESSION['idCardData'])) {
            $idCardData = (object) $_SESSION['idCardData'];
            $this->userData = new stdClass;
            $this->userData->firstName = $idCardData->GN;
            $this->userData->lastName = $idCardData->SN;
            $this->userData->username = $_SESSION['currentPerson'];
            $this->userData->email = $idCardData->email;
            $this->userData->social = 'eID';
        }
        $this->checkIfUserExistsAndAdd();
    }

    public function checkIfUserExistsAndAdd() {
        $db = new Db();
        if (!empty($this->userData)) {
            $this->users = $db->getAllUsersOrFindByProps(
                [
                    'username' => $this->userData->username,
                    'email' => $this->userData->email,
                    'social' => $this->userData->social
                ]
            );
            if (
                $this->users->count > 0
            ) {
                $this->setConfirmedUser();
                if ($this->users->count > 1) {
                    echo '<div class="bg-warning">Nende tunnustega on rohkem kui üks kasutajakonto. Seda ei tohiks olla, teavitage saidi haldajaid. Loeme teid selle loetelu esimeseks kasutajaks.</div>';
                }
            } else {
                $this->addNewIfNotUser();
            }
        }
    }

    public function setConfirmedUser() {
        $this->setIsUser(true);
        $this->userData = $this->users->list[0];
        if ($this->userData->role == 'ADMIN') {
            $this->setIsAdmin(true);
        }
        $this->loggedIn = [];
        $this->loggedIn['userData'] = $this->userData;
        $this->loggedIn['currentPerson'] = $this->currentPerson;
        $_SESSION['loggedIn'] = $this->loggedIn;
    }

    public function addNewIfNotUser() {
        $db = new Db();
        if ($this->isLoggedIn() && !$this->isUser()) {
            $addNew = $db->addNewUser($this->userData);
            if ($addNew !== false) {
                $this->setIsUser(true);
                $this->userData = $addNew;
                $this->loggedIn['userData'] = $this->userData;
                $this->loggedIn['currentPerson'] = $this->currentPerson;
                $_SESSION['loggedIn'] = $this->loggedIn;
                //$this->searchedUser = $this->userData;
                echo 'Lisasime teid uue kasutajana';
            } else {
                echo 'Kahjuks jäi uus kasutaja lisamata';
            }
        }
    }

    /**
     * Get the value of isUser
     */
    public function isUser(): bool
    {
        return $this->isUser;
    }

    /**
     * Set the value of isUser
     */
    public function setIsUser(bool $isUser): self
    {
        $this->isUser = $isUser;

        return $this;
    }

    /**
     * Get the value of isLoggedIn
     */
    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    /**
     * Set the value of isLoggedIn
     */
    public function setIsLoggedIn(bool $isLoggedIn): self
    {
        $this->isLoggedIn = $isLoggedIn;

        return $this;
    }

    /**
     * Get the value of isAdmin
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * Set the value of isAdmin
     */
    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * Get the value of loggedIn
     */
    public function getLoggedIn()
    {
        return $this->loggedIn;
    }

    /**
     * Set the value of loggedIn
     */
    public function setLoggedIn($loggedIn): self
    {
        $this->loggedIn = $loggedIn;

        return $this;
    }


}