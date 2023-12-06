<?php namespace user;

include_once __DIR__ . '/model/Users.php';

use \Common\Helper;
use \Common\Model\Person;
use \user\model\User;
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
            echo '<p>Algab sisselogija kontroll / kasutajaks tegemine</p>';
            if (!empty($this->userData->id)) {
                $this->setConfirmedUser();
            } else {
                $this->setUserData();
            }
        }
        //return $this;
    }

    public function setUserData()
    {
        $this->currentPerson = $_SESSION['currentPerson'];
        if (empty($this->userData)) {
            if (isset($_SESSION['userData'])) {
                $user = new User($_SESSION['userData']);
                echo '<p>Kui sess ütleb userdata, luuakse kasutaja objekt</p>';
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
                echo '<p>Kasutaja objekt on loodud id kaardi andmetest</p>';
            }
            $this->userData = $user;
            $this->checkIfUserExistsAndAdd($user);
        } else {
            echo '<p>Kasutaja objekt on ammu olemas, seega jäi esimene samm vahele</p>';
            if (!empty($this->userData->id)) {
                echo '<p>Isegi id on olemas. Kinnitama!</p>';
                $this->setConfirmedUser();
            }
        }
    }

    public function checkIfUserExistsAndAdd($user)
    {
        echo '<p>Kontrollime, kas kasutaja on olemas</p>';
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
            echo '<p>Vist on, läheme kinnitama!</p>';
            $this->setConfirmedUser();
        } else {
            echo '<p>Vist ei ole, kasutaja tuleb luua</p>';
            $this->addNewIfNotUser();
        }
    }

    public function setConfirmedUser($user = null)
    {
        $this->setIsUser(true);
        echo '<p>Kinnitatud :) aga on veel asju</p>';
        if (empty($this->users->list[0]->person->id)) {
            if (isset($this->userData->person) && ($this->users->list[0]->social == 'eID')) {
                echo '<p>Näiteks kui miskipärast pole id kaardi omanikul isikukirjet küljes</p>';
                $this->users->list[0]->setPerson($this->userData->person);
                $this->checkPersonAndAddIfMissing($this->users->list[0], $this->userData->person);
            }
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
        echo '<p>Sessiooni muutuja loggedin kah tehtud</p>';
    }

    public function addNewIfNotUser()
    {
        $db = new Db();
        //echo '<p>hakkame uut kasutajat lisama. Kui jäime siia toppama, klikka <a href="">siia</a></p>';
        echo file_get_contents(__DIR__ . '/template/welcomeIdPerson.phtml');
        $db->addNewUser($this->userData);
        echo '<p>Kui siin ka midagi näeks, oleks tore</p>';
        print_r($this->users->list[0]);
        $this->setConfirmedUser();
        //header("Refresh:10");

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
            echo '<p>Siinkohal tuleb vaid lisada isik kasutajale</p>';
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