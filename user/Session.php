<?php namespace user;

include_once __DIR__ . '/model/Users.php';

use Common\Helper;
use Common\Model\Person;
use user\model\User;
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

    public function setUserData()
    {
        $this->currentPerson = $_SESSION['currentPerson'];
        if (isset($_SESSION['userData'])) {
            $user = new User($_SESSION['userData']);
            $this->userData = $user;
        }
        /*Array ( [serialNumber] => PNOEE-36706230305
        [GN] => JAANUS [SN] => NURMOJA [CN] => NURMOJA\
        [C] => EE [email] => 36706230305@eesti.ee )*/

        if (isset($_SESSION['idCardData'])) {
            $idCardData = (object) $_SESSION['idCardData'];
            $user = new User();
            $user->setUsername($_SESSION['currentPerson']);
            $user->setEmail($idCardData->email);
            $user->setSocial('eID');

            $person = new Person;
            //$person->name = "$idCardData->GN $idCardData->SN";
            $gnparts = Helper::givenNamesIntoFirstAndMiddle($idCardData->GN);
            $person->setFirstName($gnparts->firstName);
            if (isset($gnparts->middleName)) {
                $person->setMiddleName($gnparts->middleName);
            }

            $person->setLastName($idCardData->SN);
            $person->setCountry($idCardData->C);
            //$person->pnoCode;
            $person->setPno($idCardData->serialNumber);
            //$person->born;
            $user->setPerson($person);
            $this->userData = $user;
        }
        $this->checkIfUserExistsAndAdd($user);
    }

    public function checkIfUserExistsAndAdd($user)
    {
        $db = new Db();
        $this->users = $db->getAllUsersOrFindByProps(
            [
                'username' => $user->username,
                'email' => $user->email,
                'social' => $user->social,
            ]
        );
        if ($this->users->count > 0) {
            if ($this->users->count > 1) {
                echo '<div class="bg-warning">Nende tunnustega on rohkem kui üks kasutajakonto. Seda ei tohiks olla, teavitage saidi haldajaid. Loeme teid selle loetelu esimeseks kasutajaks.</div>';
            }
            $this->setConfirmedUser();
        } else {
            $this->addNewIfNotUser();
        }
    }

    public function setConfirmedUser($user = null)
    {
        $this->setIsUser(true);
        if (isset($this->userData->person) && ($this->users->list[0]->social == 'eID' && empty($this->users->list[0]->person->id))) {
            $this->users->list[0]->setPerson($this->userData->person);
            $this->checkPersonAndAddIfMissing($this->users->list[0], $this->userData->person);
        }
        print_r($this->users->list[0]);
        if (!isset($user)) {
            $user = $this->users->list[0];
        }
        $this->userData = $user;
        if ($this->userData->role == 'ADMIN') {
            $this->setIsAdmin(true);
        }

        $this->loggedIn = [];
        $this->loggedIn['userData'] = $this->userData;
        $this->loggedIn['currentPerson'] = $this->currentPerson;
        $_SESSION['loggedIn'] = $this->loggedIn;
    }

    public function addNewIfNotUser()
    {
        echo '<p>hakkame uut kasutajat lisama</p>';
        print_r($this->userData);
        $db = new Db();
        $addNew = $db->addNewUser($this->userData);
        //unset($db); // vist pole vaja
        if ($addNew !== false) {
            /*
            $this->setIsUser(true);
            $this->userData = $addNew;
            $this->loggedIn['userData'] = $this->userData;
            $this->loggedIn['currentPerson'] = $this->currentPerson;
            $_SESSION['loggedIn'] = $this->loggedIn;
             */
            //$this->searchedUser = $this->userData;
            echo 'Lisasime teid uue kasutajana ja asume nüüd seda kinnitama';
            $this->setConfirmedUser($addNew);
        } else {
            echo 'Kahjuks jäi uus kasutaja lisamata';
        }
    }

    public function checkPersonAndAddIfMissing($user, $person)
    {
        $db = new Db();
        $checkPerson = $db->findPerson(['PNO' => $person->pno]);
        print_r($checkPerson);
        if (!$checkPerson) {
            echo '<div class="bg-success">Kuna olete sisenenud ID-kaardiga, siis on teie andmed nüüd talletatud ka isikuprofiilide loetellu. Kui mitte juba praegu, siis tulevikus annab kasutajakonto sidumine tuvasatatud isiku profiiliga eeliseid süsteemi kasutamisel.</div>';
            $db->addPerson($person, $user);
        } else {
            $db->addPersonToUser($checkPerson->id, $user);
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
