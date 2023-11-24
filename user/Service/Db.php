<?php namespace user\Service;
include_once __DIR__ .'/../model/User.php';
use \user\model\User;
use \user\model\Users;
use \Common\Helper;
use mysqli;

class Db {
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
    
    public function getAllUsersOrFindByProps($props = []) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cnn = $this->cnn();
        $users = new Users();
        $where = '';
        $singleRow = false;
        $ws = [];
        if (!empty($props)) {
            foreach ($props as $name => $value) {
                $ws[] = " $name = '$value'";
            }
            $singleRow = in_array('id', array_keys($props));
            $where = " WHERE". implode(" AND ", $ws);
        }
        $sql = "SELECT u.id as ID, u.*, p.id as pid, p.* FROM users u 
        LEFT JOIN persons p ON p.id = u.persons_id$where;";

        $q = $cnn->query($sql);
        while ($row = $q->fetch_assoc()) {
            unset($row["id"]);
            $user = new User($row);
            $users->addUserToList($user);
        }
        return $singleRow ? $user : $users;
    }

    public function addNewUser($userData) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cnn = $this->cnn();
        $user = new User($userData);
        $kvs = get_object_vars($user);
        $newKvs = [];
        foreach ($kvs as $k => $v) {
            $k = Helper::uncamelize($k);
            $newKvs[$k] = $v;
        }
        $cols = implode(', ', array_keys($newKvs));
        $vals = "'" . implode("','", array_values($kvs)) . "'";
        $sql = "INSERT INTO users ($cols) values ($vals)";
        if ($cnn->query($sql)) {
            $return = $this->getAllUsersOrFindByProps(['id' => $this->cnn()->insert_id]);
            return $return;
        } else {
            return false;
        }
    }
}