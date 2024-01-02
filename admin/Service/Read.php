<?php namespace Service;

use Common\Helper;
use Common\Model\DataCreatedModified;
include_once __DIR__ . '/../Model/RelationSettings.php';
include_once __DIR__ . '/../Model/Relation.php';
include_once __DIR__ . '/../Model/Table.php';
include_once __DIR__ . '/../Model/Field.php';
include_once __DIR__ . '/../Dto/TableDTO.php';
include_once __DIR__ . '/../Dto/ListDTO.php';
include_once __DIR__ . '/../../common/Model/CreatedModified.php';
include_once __DIR__ . '/../../common/Model/DataCreatedModified.php';

use mysqli;
use \Common\Model\CreatedModified;
use \Dto\ListDTO;
use \Dto\TableDTO;
use \Model\Data;
use \Model\Field;
use \Model\Relation;
use \Model\RelationSettings;
use \Model\Table;
use \user\model\User;
use Dto\TableItem;

/**
 * Andmete lugemine andmebaasitabelitest
 */
class Read
{
    protected function cnn()
    {
        // require __DIR__ . '/../../api/config.php';
        // return new mysqli($host, $user, $pass, $dbname);
        $cnf = parse_ini_file(__DIR__ . '/../../config/connection.ini');
        return new mysqli($cnf["servername"], $cnf["username"], $cnf["password"], $cnf["dbname"]);

    }

    protected function getCurrentUser()
    {
        if (isset($_SESSION['userData'])) {
            return new User($_SESSION['userData']);
        }
    }

    public function getTables(Table $model = null, $params = [], Relation $rel = null, RelationSettings $relationSettings = null, TableDTO $tableDTO = null)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $where = '';
        if (!empty($params)) {
            $w = [];
            foreach ($params as $key => $value) {
                $w[] = " $key = '$value'";
            }
            $where = ' WHERE' . implode(' AND', $w);
        }

        $query = "SELECT t.id as rowid, t.id as tid, t.*, t.created_by as tc_who, t.created_when as tc_when, t.modified_by as tm_who, t.modified_when as tm_when,
        f.id as fid, f.name as field,
        rd.id as rd_id, rd.role as rd_role, rd.*, rd.created_by as rc_who, rd.created_when as rc_when, rd.modified_by as rm_who, t.modified_when as rm_when,
        r.id as rid, r.*,
        tcu.id as tcu_id, tcu.username as tcu_name, tcu.email as tcu_email, tcu.password as tcu_password, tcu.social as tcu_social, tcu.user_token as tcu_usertoken, tcu.identity_token as tcu_id_token, tcu.role as tcu_role,
        rcu.id as rcu_id, rcu.username as rcu_name, rcu.email as rcu_email, rcu.password as rcu_password, rcu.social as rcu_social, rcu.user_token as rcu_usertoken, rcu.identity_token as rcu_id_token, rcu.role as rcu_role

        FROM uasys_models t
        LEFT JOIN fields f ON f.models_id = t.id
        LEFT JOIN uasys_relation_settings rd 
        ON (rd.many_id = t.id OR rd.one_id = t.id OR rd.any_id = t.id OR JSON_CONTAINS(rd.many_many_ids, t.id))
        LEFT JOIN uasys_relations r ON r.id = rd.relations_id
        LEFT JOIN uasys_users tcu ON tcu.id = t.created_by
        LEFT JOIN uasys_users rcu ON rcu.id = rd.created_by
        $where";
        $q = $db->query($query);

        $rowList = [];
        $rowsDebug = [];
        $single = null;

        while ($row = $q->fetch_assoc()) {
            unset($row['id'], $row['role'], $row['created_by'], $row['created_when'], $row['modified_by'], $row['modified_when']);
            $rowsDebug[] = $row;
            while ($row['rd_id'] != null && (empty($relationSettings) || $relationSettings->getId() != $row['rd_id'])) {
                $relationSettings = new RelationSettings();
                $rel = new Relation();
                $rel->setId($row['rid']);
                $rel->setType($row['type']);
                $rel->setAllowHasMany((bool) $row['allow_has_many']);
                $rel->setIsInner((bool) $row['is_inner']);

                $rcUser = new User;
                $rcUser->setId($row['rcu_id'])->setUsername($row['rcu_name'])->setEmail($row['rcu_email'])->setPassword($row['rcu_password'])->setSocial($row['rcu_social'])->setUserToken($row['rcu_usertoken'])->setIdentityToken($row['rcu_id_token'])->setRole($row['rcu_role']);

                $relDetailsCreMod = new CreatedModified($row['rd_id'], 'relation_settings');
                $relDetailsCreMod->setCreatedBy($rcUser)
                    ->setCreatedWhen($row['rc_when'])
                    ->setModifiedBy($row['rm_who'])
                    ->setModifiedWhen($row['rm_when']);
                $relDetailsCreMod->__construct();
                $relationSettings
                    ->setCreatedModified($relDetailsCreMod)
                    ->setId($row['rd_id'])
                    ->setTableId($row['rowid'])
                    ->setManyId($row['many_id'])
                    ->setAnyId($row['any_id'])
                    ->setOneId($row['one_id'])
                    ->setRelation($rel)
                    ->setRole($row['rd_role'])
                    ->setKeyField($row['key_field'])
                    ->setHasMany((bool) $row['hasMany'])
                    //->setOtherTable($row['other_table'])
                    ->rewriteMode($row['mode'])
                    ->setManyTable($row['many_table'])
                    ->setManyFk($row['many_fk'])
                    ->setManyMany(!empty($row['many_many']) ? json_decode($row['many_many']) :  null)
                    ->setManyManyIds(!empty($row['many_many_ids']) ? json_decode($row['many_many_ids']): null)
                    ->setAnyAny($row['any_any'])
                    ->setAnyTable($row['any_table'])
                    ->setAnyPk($row['any_pk'])
                    ->setOnePk($row['one_pk'])
                    ->setOneTable($row['one_table'])
                    ;
                }
            if (empty($model) || (empty($model->getId()) || $model->getId() != $row['rowid'])) {
                $model = new Table();
                $model->setId($row['rowid']);
                $model->setTableName($row['table_name']);
                $model->setPk($row['pk']);
                $data = new Data();
                $modelItem = new TableItem($model);
                $data->setTable($modelItem);
                $fields = $this->getDefaultFields($row['table_name']);
                if ($row['field_data'] == 'default') {
                    $data->setFields($fields['dataFields']);
                }
                $data->setDataCreatedModified($fields['dataCreatedModified']);

                $tcUser = new User;
                $tcUser->setId($row['tcu_id'])->setUsername($row['tcu_name'])->setEmail($row['tcu_email'])->setPassword($row['tcu_password'])->setSocial($row['tcu_social'])->setUserToken($row['tcu_usertoken'])->setIdentityToken($row['tcu_id_token'])->setRole($row['tcu_role']);

                $tableCreMod = new CreatedModified($row['rowid'], 'uasys_models');
                $tableCreMod->setCreatedBy($tcUser)->setCreatedWhen($row['tc_when'])->setModifiedBy($row['tm_who'])->setModifiedWhen($row['tm_when']);
                $model->setData($data);
                $model->setCreatedModified($tableCreMod);

            }
            if (!empty($relationSettings)) {
                $relationSettings->setTable($modelItem);
                if ($relationSettings->table->id == $row['rowid'] && $relationSettings->getId() == $row['rd_id']) {

                    $model->addRelationSettings($relationSettings);
                }
            }

            $single = new TableDTO($model);
            $rowList[$row['rowid']] = $single;
        }
        if (!empty($params) && count($rowList) == 1) {
            return $single;
        } else {
            return new ListDTO($rowList);
        }

    }

    public function getExistingTables($used = [])
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $query = "SHOW TABLES";
        $q = $db->query($query);
        $r = [];
        while ($row = $q->fetch_assoc()) {
            $r[] = array_pop($row);
        }
        $diff = array_diff($r, $used);
        return $diff;
    }

    public function getDefaultFields($table, $checkField = null)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $query = "SHOW COLUMNS FROM $table";
        $q = $db->query($query);
        $fields = [];
        $fields['indexes'] = [];
        if (!empty($checkField)) {
            $return = false;
        }

        while ($row = $q->fetch_assoc()) {
            if (!empty($checkField)) {
                if ($row['Field'] == $checkField) {
                    $return = true;
                }
            }
            if (empty($row['Key'])) {
                $fieldName = Helper::camelize($row['Field'], true);
                $field = new Field($fieldName, $row['Type']);
                $fields['dataCreatedModified'] = new DataCreatedModified();
                if (in_array($row['Field'], ['created_by', 'created_when', 'modified_by', 'modified_when'])) {
                    $setField = 'set' . Helper::camelize($row['Field']);
                    $fields['dataCreatedModified']->$setField($field);
                } else {
                    $field->setDefOrNull($row['Null'] == 'YES' ? true : false);
                    $field->setDefaultValue($row['Default']);
                    $fields['dataFields'][$fieldName] = $field;
                }
            } else {
                if ($row['Key'] == 'PRI') {
                    $fields['pk'] = $row['Field'];
                } else {
                    array_push($fields['indexes'], $row['Field']);
                }
            }
        }
        if (!empty($checkField)) {
            return $return;
        }
        return $fields;
    }

    public function getRelations()
    {
        $relations = [];
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();

        $query = "SELECT * FROM uasys_relations";
        $q = $db->query($query);

        while ($row = $q->fetch_assoc()) {

            $rel = new Relation();
            $rel->setId($row['id']);
            $rel->setType($row['type']);
            $rel->setAllowHasMany((bool) $row['allow_has_many']);
            $rel->setIsInner($row['is_inner']);
            array_push($relations, $rel);

        }
        return $relations;
    }

    /**See https: //www.barattalo.it/coding/php-to-get-enum-set-values-from-mysql-field/
     */
    public function setAndEnumValues($table, $field)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = $this->cnn();
        $query = "SHOW COLUMNS FROM `$table` LIKE '$field'";
        $result = $db->query($query) or die('Error getting Enum/Set field ' . $db->error);
        $row = $result->fetch_array();
        if (stripos("." . $row[1], "enum(") > 0) {
            $row[1] = str_replace("enum('", "", $row[1]);
        } else {
            $row[1] = str_replace("set('", "", $row[1]);
        }

        $row[1] = str_replace("','", "\n", $row[1]);
        $row[1] = str_replace("')", "", $row[1]);
        $ar = explode("\n", $row[1]);
        for ($i = 0; $i < count($ar); $i++) {
            $arOut[str_replace("''", "'", $ar[$i])] = str_replace("''", "'", $ar[$i]);
        }

        return $arOut;
    }

    public function req($r = [])
    {
        $new = [];
        if (isset($r[1])) {
            $new['type'] = $r[1];
        }

        if (isset($r[2])) {
            $new['item'] = $r[2];
        }

        if (isset($r[3])) {
            $new['subtype'] = $r[3];
        }

        if (isset($r[4])) {
            $new['subitem'] = $r[4];
        }

        $new['debug'] = 'ohoohhooi';
        return $new;
    }

}
