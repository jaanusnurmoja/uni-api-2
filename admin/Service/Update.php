<?php namespace Service;

use Common\Helper;
use mysqli;

class Update
{
    protected function cnn()
    {
        $cnf = parse_ini_file(__DIR__ . '/../../config/connection.ini');
        return new mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);

    }

    public function updateTable($table)
    {
        $whereId = $table['id'];
        $sql = '';

        $sql .= "UPDATE models SET modified_by = " . $table['createdModified']['modifiedBy'] . " WHERE id = $whereId;
        ";
        foreach ($table as $propName => $propContent) {
            if ($propName == 'data' && isset($propContent['fields'])) {
                foreach ($propContent['fields'] as $fieldName => $fieldProps) {
                    if ($this->checkIfFieldExists($table['tableName'], $fieldName) === false) {
                        $fieldName = Helper::uncamelize($fieldName);
                    }

                    $sql .= $this->changeColumn($table['tableName'], $fieldName, $fieldProps);
                }
            }

            if (in_array($propName, ['belongsTo', 'hasMany', 'hasManyAndBelongsTo'])) {
                foreach ($propContent as $relationDetails) {
                    $rdId = $relationDetails['id'];
                    unset($relationDetails['id'], $relationDetails['table']);
                    $sql .= $this->updateRelationDetails($relationDetails, $rdId);
                }
            }
        }
        echo 'uuendame tabelit: ' . $sql . "\n";
        $this->makeQueries($sql);
    }

    public function makeQueries($sql)
    {
        $cnn = $this->cnn();
        try
        {
            $cnn->multi_query($sql);
            do {
                /* store the result set in PHP */
                if ($result = $cnn->store_result()) {
                    while ($row = $result->fetch_row()) {
                        printf("%s\n", $row[0]);
                    }
                }
                /* print divider */
                if ($cnn->more_results()) {
                    printf("-----------------\n");
                }
            } while ($cnn->next_result());

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

    }

    public function changeColumn($tableName, $fieldName, $fieldProps)
    {
        $sql = "ALTER TABLE $tableName CHANGE COLUMN $fieldName ";
        foreach ($fieldProps as $fpName => $fpValue) {
            if ($fpName == 'name') {
                $fpValue = Helper::uncamelize($fpValue);
                $sql .= " $fpValue";
            }
            if ($fpName == 'defOrNull') {
                $sql .= (bool) $fpValue === true ? " NULL" : " NOT NULL";
            }
            if ($fpName == 'defaultValue') {
                if (empty($fpValue)) {
                    $sql .= (bool) $fieldProps['defOrNull'] === true ? " DEFAULT NULL;
                                " : "
                                ";
                } else {
                    if ($fpValue != 'current_timestamp') {
                        $fpValue = "'$fpValue'";
                    }
                    $sql .= " DEFAULT $fpValue;
                                ";
                }
            }
        }
        return $sql;
    }

    public function updateRelationDetails($relationDetails, $rdId)
    {
        $sql = "UPDATE relation_details SET ";
        foreach ($relationDetails as $rdKey => $rdValue) {
            if ($rdKey == 'createdModified') {
                foreach ($rdValue as $cmKey => $cmValue) {
                    $sqlKey = Helper::uncamelize($cmKey);
                    $sql .= "$sqlKey = $cmValue";
                    $sql .= next($relationDetails) ? ', ' : '';
                }
            } else {
                $sqlKey = Helper::uncamelize($rdKey);
                $sql .= "$sqlKey = $rdValue";
                $sql .= next($relationDetails) ? ', ' : '';
            }
        }
        $sql .= " WHERE id = $rdId;
                ";
        return $sql;

    }

    public function checkIfFieldExists($table, $field)
    {
        $read = new Read();
        return $read->getDefaultFields($table, $field);
    }
}