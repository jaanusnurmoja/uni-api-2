<?php namespace Api\Service;

class DbRead
{
    public array $tables;
    public array $pks;
    protected function cnn() {
        $cnf = parse_ini_file(__DIR__ . '/../../../config/connection.ini');
        $mysqli = new \mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    }

    public function anySelect($query) {
        $db = $this->cnn();
        $rows = [];
        //$results = new Result();
        $res = $db->query($query);
        $fields = [];
        $tables = [];
        $pks = [];
        foreach($res->fetch_fields() as $field) {
            if ($field->orgname == $this->getPk($field->orgtable)) {
                $pks[$field->orgtable] = $field->name;
                $tables[$field->orgtable]['tableAlias'] = $field->table;
                $tables[$field->orgtable]['pk'] = $field->orgname;
                $parts = explode('__', $field->table);
                    if ($parts[1] == 'belongsTo') {
                        $tables[$parts[0]]['parent'][$parts[2]] = $parts[3];
                    }
                    else {
                        $tables[$parts[0]]['child'][$parts[2]] = $parts[3];
                    }
            }
            $fields[$field->name] = $field;
        }
        while ($row = $res->fetch_object()) {
            if (isset($row->rowid)) {
                $rows[0]['pks'] = $pks;
                $rows[0]['tables'] = $tables;
                $rows[0]['fields'] = $fields;
                foreach ($row as $key => $value) {
                    $table = $fields[$key]->orgtable;
                    $pk = $pks[$table];
                    $rows[$row->rowid][$table][$row->$pk][$fields[$key]->orgname] = $value;
                }
                //$rows[$row->rowid][] = $row;
            } else {
                $rows[] = $row;
            }
        }
        $db->close();
        return $rows;
    }

    public function getPk($table)
    {
        $keys = $this->getColumns($table);
        return $keys->pk;
    }

    public function getColumns($table)
    {
        $db = $this->cnn();
        $cols = new \stdClass();
        $sql = "SHOW COLUMNS FROM `$table`";
        if ($result = $db->query($sql)) {
            while ($column = $result->fetch_object()) {
                if ($column->Key == 'PRI') {
                    $cols->pk = $column->Field;
                } else {
                    $cols->{$column->Field} = $column;
                }
            }
        }
        $db->close();
        return $cols;
    }

    
}