<?php namespace user\Service;
include_once __DIR__ .'/../model/User.php';
include_once __DIR__ .'/../../common/Model/Person.php';
use \user\model\User;
use \user\model\Users;
use \Common\Helper;
use mysqli;
use \Common\Model\Person;

class Db
{
    /*
    protected function db() {
        $cnf = parse_ini_file(__DIR__ . '/../../config/connection.ini');
        return new \mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);
    }
    */
    protected function cnn()
    {
        $cnf = parse_ini_file(__DIR__ . '/../../config/connection.ini');
        return new mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);
    }

    public function getAllUsersOrFindByProps($props = [])
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cnn = $this->cnn();
        $users = new Users();
        $person = new Person();
        $personVars = get_object_vars($person);
        unset($personVars['name'],$personVars['born'],$personVars['isAlive'],$personVars['deceased']);
        $pQueryVars = [];

        foreach ($personVars as $pKey => $pValue) {
            $pQueryValue = 'p.' . Helper::uncamelize($pKey) . ' as `p:' . $pKey . '`';
            $pValue = 'p:' . $pKey;
            $personVars[$pKey] = $pValue;
            $pQueryVars[$pKey] = $pQueryValue;
        }
        $pQuery = implode(', ', $pQueryVars);
        $where = '';
        $singleRow = false;
        $ws = [];
        $debug = '';
        if (!empty($props)) {
            foreach ($props as $name => $value) {
                $ws[] = " $name = '$value'";
            }
            $debug = 'props:';
            $singleRow = in_array('id', array_keys($props));
            $where = " WHERE" . implode(" AND ", $ws);
        }
        $sql = "SELECT u.id as ID, u.*, $pQuery FROM users u 
        LEFT JOIN persons p ON p.id = u.persons_id$where;";
        echo "<p>$debug getAllUsersOrFindByProps: $sql</p>";
        $q = $cnn->query($sql);
        while ($row = $q->fetch_assoc()) {
            unset($row["id"]);
            if (!empty($row['p:id'])) {
                while(strpos(key($row), ':')) {
                    $newKey = str_replace('p:', '', key($row));
                    $person->{'set' . ucfirst($newKey)}(current($row));
                }
            }
            $user = new User($row);
            $user->setPerson($person);
            $users->addUserToList($user);
        }
        return $singleRow ? $user : $users;
    }

    public function addNewUser($userData)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cnn = $this->cnn();
        $user = new User($userData);
        $kvs = get_object_vars($user);
        $newKvs = [];
        $personData = null;
        foreach ($kvs as $k => $v) {
            $k = Helper::uncamelize($k);
            if ($k == 'person') {
                if (!empty($v)) {
                    $personData = $v;
                }
            } else {
                $newKvs[$k] = $v;
            }
        }
        $cols = implode(', ', array_keys($newKvs));
        $vals = "'" . implode("','", array_values($kvs)) . "'";
        $sql = "INSERT INTO users ($cols) values ($vals)";
        if ($cnn->query($sql)) {
            echo "<p>addNewUser: $sql</p>";
            $newUser = $this->getAllUsersOrFindByProps(['id' => $cnn->insert_id]);
            if (!empty($personData)) {
                return $this->addPerson($personData, $newUser['id']);
            } else {
                return $newUser;
            }
        } else {
            return false;
        }
    }

    public function addPerson($personData, $user)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cnn = $this->cnn();
        $kvs = get_object_vars($personData);
        $newKvs = [];
        foreach ($kvs as $k => $v) {
            $k = Helper::uncamelize($k);
            $newKvs[$k] = $v;
        }
        $cols = implode(', ', array_keys($newKvs));
        $vals = "'" . implode("','", array_values($kvs)) . "'";
        $sql = "INSERT INTO persons ($cols) values ($vals)";
        if ($cnn->query($sql)) {
            $personId = $cnn->insert_id;
            echo "<p>addPerson: $sql</p>";
            if (!empty($user)) {
                $this->addPersonToUser($personId, $user);
            }
            return $this->findPerson(['id' => $personId]);
        } else {
            return false;
        }

    }

    public function findPerson($props)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cnn = $this->cnn();
        $ws = [];
        foreach ($props as $name => $value) {
            $ws[] = " $name = '$value'";
        }
        $where = " WHERE" . implode(" AND ", $ws);
        $sql = "SELECT * FROM persons $where";
        $q = $cnn->query($sql);
        echo "<p>findPerson: $sql</p>";
        $person = new Person();
        while ($row = $q->fetch_assoc()) {
            foreach ($row as $dbKey => $dbValue) {
                $setKey = 'set' . Helper::camelize($dbKey, true);
                $person->$setKey($dbValue);
            }
        }
        if (!empty($row)) {
            return $person;
        } else {
            return false;
        }
    }

    public function addPersonToUser($p, $u)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cnn = $this->cnn();
        $sql = "UPDATE users SET persons_id = $p WHERE id = $u->id";
        if ($cnn->query($sql)) {
            echo "<p>findPerson: $sql</p>";
        }
        return $u;
    }
}