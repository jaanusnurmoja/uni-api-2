<?php namespace Service;

use mysqli;

class Delete
{
    protected function cnn()
    {
        $cnf = parse_ini_file(__DIR__ . '/../../config/connection.ini');
        return new mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);

    }

    public function removeFromList($list, $idOrName)
    {
        $db = $this->cnn();
        $item = is_numeric($idOrName) ? 'id' : 'table_name';
        $sql = "DELETE from $list WHERE $item = '$idOrName'";
        echo $sql;
        $db->query($sql);
    }
}