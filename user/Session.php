<?php namespace user;

include_once __DIR__ . '/model/Users.php';

use \Common\Helper;
use \Common\Model\Person;
use \user\model\User;
use \user\model\Users;
include_once __DIR__ . '/Service/Db.php';
use \user\Service\Db;

/**
 * Sisselogija sotsiaalkonto / Id-kaardi andmete kokkuviimine olemasoleva kasutajakontoga
 * Esmase sisselogija automaatne registreerimine kasutajana (users)
 * Id-kaardiga esmase sisselogija kandmine isikute tabelisse (persons)
 * Isikukirje ja kasutajatunnuse vahelise seose loomine (Id-kaardiga  automaatselt esmasel sisselogimisel)
 * Sotsiaalkonto / ID-kaardiga sisselogija kasutajakonto olemasolu kinnitamine ning tema tunnistamine sisseloginud kasutajaks (vastava sessioonimuutuja loomine)
 * @todo Id-kaardi andmete kokkuviimine isikukirjega juhul, kui isikukirje on juba varem loodud
 */
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

    /**
     * Sisselogija andmed sobitatakse kasutaja objekti
     * Id-kaart: luuakse ka objekt Person
     */
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
/**
 * Kontrollib, kas kasutaja on olemas, et see puudumise korral lisada
 * @param User $user
 */
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
            /**
             *  Kasutaja on olemas, läheb kinnitamisele
             */
            $this->setConfirmedUser();
        } else {
            echo '<p>Seda kasutajat vist veel ei ole, järelikult tuleb luua</p>';
            /**
             * Kasutajat pole, läheb loomisele
             */
            $this->addNewIfNotUser();
        }
    }

    /**
     * Kasutajakonto olemasolu kinnitamine ja vastava sessioonimuutuja loomine
     * 
     * @param type $user
     */
    public function setConfirmedUser($user = null)
    {
        $this->setIsUser(true);
        if (empty($this->users->list[0]->person->id)) {
            if (isset($this->userData->person) && ($this->users->list[0]->social == 'eID')) {
                echo '<p>Näiteks kui miskipärast pole id kaardi omanikul isikukirjet küljes</p>';
                $this->users->list[0]->setPerson($this->userData->person);
                $this->checkPersonAndAddIfMissing($this->users->list[0], $this->userData->person);
            }
        }

        //print_r($this->users->list[0]);
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
/**
 * Lisa uus kasutaja, kui sisselogija pole kasutaja
 * 
 * @todo BUG: ID-kaardi omaniku kasutajaks registreerimine jääb siin seisma. Soovimatu funktsionaalsus - jätkamiseks tuleb leht uuesti laadida. Dialog element on ajutine lahendus kuni probleemi lahenemiseni.
 * 
 */
    public function addNewIfNotUser()
    {
        echo '<dialog open><h1>Hea ' . $this->userData->person->firstName . ', hakkame lisama teie kasutajakontot ja isikuprofiili. <a href="">Jätka</a></h1></dialog>';
        echo str_replace(['"', '{', '}', '[', ']'], '', json_encode($this->userData));
        $db = new Db();
        /**
         * Pöördumine kasutaja sisestamise päringuga funktsiooni poole.
         * Kui kasutaja lisamine toimus, PEAKS $addUser->lastId olema äsja lisatud kasutaja id
         * ning pärast kasutaja andmete värskendamist suunatama tegevus funktsiooni setConfirmedUser()
         * AGA praegu sunnitakse kasutajat lehte uuesti laadima ning siis toimub kasutaja olemasolu kontroll otsast peale,
         * alates funktsioonist setUserData(), kus kasutaja olemasolu saab kinnitust ning ta viiakse edasi funktsiooni setConfirmedUser()
         */
        $addUser = $db->addNewUser($this->userData);
        if ($addUser->sql) {
            $this->users->list[0] = $db->getAllUsersOrFindByProps(['u.id' => $addUser->lastId]);
            echo '<p>Lisasime teid uue kasutajana ja asume nüüd seda kinnitama</p>';
            $this->setConfirmedUser();
        } else {
            echo '<p>Kahjuks jäi uus kasutaja lisamata, aga ei tea, miks</p>';
        }
    }

    /**
     * Algselt mõeldud isikukirje kontrolliks ja selle lisamiseks puudumise korral
     * @todo Funktsiooni saab kasutada isikuprofiili sidumisel teiste kasutajakontodega, mis on loodud sotsiaalkontodega sisselogimisel
     * @param type $user
     * @param type $person
     */
    public function checkPersonAndAddIfMissing($user, $person)
    {
        $db = new Db();
        $checkPerson = $db->findPerson(['PNO' => $person->pno]);
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
     * @return bool kas on kasutaja või ei (vaikimisi mitte)
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
     * Sotsiaalkonto või id-kaardi andmed on olemas, kuid ei pruugi veel olla kasutaja
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
     * loggedIn muutuja sisaldab kinnitatud sisseloginud kasutaja andmeid
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