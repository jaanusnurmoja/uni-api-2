<?php namespace user\Service;

include_once __DIR__ . '/../model/User.php';
include_once __DIR__ . '/../../common/Model/Person.php';
use mysqli;
use stdClass;
use \Common\Helper;
use \Common\Model\Person;
use \user\model\User;
use \user\model\Users;

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
        unset($personVars['name'], $personVars['born'], $personVars['isAlive'], $personVars['deceased']);
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
        $q = $cnn->query($sql);
        while ($row = $q->fetch_assoc()) {
            unset($row["id"]);
            $user = new User($row);
            if (!empty($row['p:id'])) {
                $person = new Person();
                foreach ($row as $col => $val) {
                    if (strpos($col, ':')) {
                        $newKey = str_replace('p:', '', $col);
                        $setField = 'set' . ucfirst($newKey);
                        $person->$setField($val);
                    }
                }
                $user->setPerson($person);

            }
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
        $personData = [];
        $sql = '';
        foreach ($kvs as $k => $v) {
            $k = Helper::uncamelize($k);
            if ($k == 'person' && !empty($v->pno)) {
                if (empty($v->id)) {
                    //$personData = $v;
                    {
                        $perKvs = get_object_vars($v);
                    }

                    foreach ($perKvs as $perK => $perV) {
                        $perK = Helper::uncamelize($perK);
                        $personData[$perK] = $perV;
                    }
                    $pcls = implode(', ', array_keys($personData));
                    $pvals = "'" . implode("','", array_values($personData)) . "'";
                    $sql .= "INSERT INTO persons ($pcls) values ($pvals);";

                    $newKvs['persons_id'] = 'last_insert_id()';
                } else {
                    $newKvs['persons_id'] = $v->id;
                }

            } else {
                $newKvs[$k] = $v;
            }
        }
        $cols = implode(', ', array_keys($newKvs));
        $vals = "'" . implode("','", array_values($newKvs)) . "'";
        $vals = str_replace("'last_insert_id()'", "last_insert_id()", $vals);
        $sql .= "INSERT INTO users ($cols) values ($vals);";
        $sql .= "SELECT last_insert_id() as lastId;";
        $r = new stdClass;
        $r->sql = $cnn->multi_query($sql);
        $res = $cnn->store_result();
        $row = $res->fetch_object();
        $r->lastId = $row->lastId;
        return $r;
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
        print_r($cols);
        print_r($vals);
        $sql = "INSERT INTO persons ($cols) values ($vals)";
        if ($cnn->query($sql)) {
            $personId = $cnn->insert_id;
            echo "<p>addPerson: $sql</p>";
            if (!empty($user)) {
                return $this->addPersonToUser($personId, $user)->person;
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
