<?php namespace View\Form;

use Common\Model\DataCreatedModified;

//use Model\Data;

class EditTable
{
    /**
     * @var \user\model\User currentUser
     */
    public $currentUser;
    /**
     * @var \Controller\Table tableController
     */
    public $tableController;
    /**
     * @var array relations
     */

    public $relations;
    /**
     * @var \DTO\TableDTO dto
     */
    public $dto;
    /**
     * @var mixed data
     */
    public $data;
    /**
     * @var mixed postBody
     */
    public $postBody;

    /**
     * __construct
     *
     * @param mixed data
     *
     * @return void
     */
    public function __construct($data = null)
    {
        $readRels = new \Controller\Table();
        $this->tableController = $readRels;
        $list = new \DTO\ListDTO();
        $this->currentUser = $_SESSION['loggedIn']['userData'];
        $readRels->getRelationsList($list);
        $this->relations = $list->list;
        $this->data = $data;
        $forminput = file_get_contents('php://input');
        parse_str($forminput, $this->postBody);
        $table = new \Model\Table();
        $this->dto = new \DTO\TableDTO($table);

    }

    public function editTableForm($data = null)
    {
        if (empty($data)) {
            $data = $this->data;
        }
        //echo '<pre>';
        //print_r($data);
        //echo '</pre>';
        ?>
<h1>
    <?php echo $data->tableName ?>
</h1>
<p>
    Seisuga 29.11.2023 on autori fookuses sisuhaldusse kaasatavate tabelite ja nende juurde kuuluva haldamine (st
    muutmine) üheainsa vormi vahendusel sarnaselt uue tabeli sisestamisega. Tõenäoliselt ei jää see ainsaks variandiks,
    kuid tekib esimesena.
</p>
<p>
    Oluline väljakutse - muudatuste sisestamise korral peavad $_POST andmetest käiku minema vaid need, mida tegelikult
    soovitakse muuta (või ka lisada). Seda püüab autor lahendada javascripti abil - et vormivälja muuta, tuleb see
    kõigepealt aktiivseks teha, sest muutmisvormis on iga väli vaikimisi 'disabled'. Sel moel kaasatakse $_POST
    muutujasse tõepoolest vaid vajalikud, st muudetavad või lisatavad väljad. Iseküsimus, kuidas talitada vormis
    toimetava kasutaja id-ga, sest selle jaoks ette nähtud väli on loomulikult peidetud, kuid peab samuti muutuma
    aktiivseks, kui vähemalt üks sama tabeli väli on aktiivne.
</p>
<form id="edit-table" name="edit-table" class="repeat" method="post" enctype="application/json">
    <input type="hidden" name="table[id]" id="table.id" value="<?php echo $data->id ?>" />
    <input type="hidden" name="new[id]" id="new.id" value="<?php echo $data->id ?>" />
    <input type="hidden" name="table[createdModified][modifiedBy]" id="table.createdModified.modifiedBy"
        value="<?=$this->currentUser->id?>" />
    <?php

        foreach ($data as $key => $value) {
            if (!is_object($value) && !is_array($value) && $key != 'id') {?>
    <label>
        <?php echo $key ?>
        <input type="checkbox" onclick="this.nextElementSibling.toggleAttribute('disabled')">
        <input type="text" name="table[<?php echo $key ?>]" value="<?php echo $value ?>" disabled />
    </label>
    <?php } else {
                if ($key == 'data') {?>
    <h2>Andmeväljad</h2>
    <table class="table table-warning table-striped table-sm wrapper">
        <thead>
            <tr>
                <td width="10%" colspan="3"><span class="add btn btn-success btn-sm">Add</span></td>
            </tr>
        </thead>
        <tbody class="repeatcontainer ui-sortable" data-rf-row-count>
            <?php $f0 = new \Model\Field();?>
            <tr class="template trow"> <?php
?>
                <td>
                    <span class="move btn btn-info btn-sm"><i class="bi bi-arrow-down-up"></i></span>
                </td>
                <td>
                    <fieldset>
                        <?php foreach ($f0 as $k0 => $v0) {
                    if ($k0 != 'id') {

                        // $fKey
                        //echo "<input type='hidden' name='new[data][fields][{{row-count-placeholder}}][$k0]' id='$k0' value='$v0' /> ";
                        //} else {
                        echo "<label for='$k0'>$k0</label> <input name='new[data][fields][{{row-count-placeholder}}][$k0]' id='$k0'";
                        if (is_bool($v0)) {
                            $checked = $v0 ? ' checked="checked"' : '';
                            echo " type='checkbox' value=true$checked onclick=this.toggleAttribute('checked') />";
                        } else {
                            if (is_iterable($v0)) {
                                $v0 = json_encode($v0);
                            }
                            echo " type='text' value='$v0' />";
                        }
                    }
                }?>
                    </fieldset>
                </td>
                <td width="10%"><span class="remove btn btn-danger btn-sm">Remove</span></td>
            </tr>
            <?php
foreach ($data->data->fields as $fkey => $field) {
                    ?>
            <tr class="trow">
                <td>
                    <span class="move btn btn-info btn-sm"><i class="bi bi-arrow-down-up"></i></span>
                </td>
                <td>
                    <input type="hidden" name="del[data][fields][<?=$fkey?>]" value="1">
                    <fieldset>
                        <?php
foreach ($field as $k => $v) {
                        $elName = "table[data][fields][$fkey][$k]";
                        $elId = "table.data.fields.$fkey.$k";
                        if ($k == 'id') {
                            echo "<input type='hidden' name='$elName' id='$elId' value='$v' disabled /> ";
                        } else {
                            echo "<label for='$elId' class='row col-10 mt-1'>
                                <div class='col col-2'>$k</div>
                                <input class='form-switch col-1' type='checkbox' onclick=this.nextElementSibling.toggleAttribute('disabled')>
                                <input class='col col-6'name='$elName' id='$elId' disabled";
                            if (is_bool($v)) {
                                $checked = $v ? ' checked="checked"' : '';
                                echo " type='checkbox' value=true$checked onclick=this.toggleAttribute('checked') />";
                            } else {
                                if (is_iterable($v)) {
                                    $v = json_encode($v);
                                }

                                echo " type='text' value='$v' />";
                            }
                            echo '</label>';
                        }
                    }?>
                    </fieldset>
                </td>
                <td width="10%"><span class="remove btn btn-danger btn-sm">Remove</span></td>
            </tr>
            <?php
}
//drop
                    $a1 = array_keys($data->data->fields);
                    if (!empty($this->postBody) && isset($this->postBody['del']['data']['fields'])) {
                        $a2 = array_keys($this->postBody['del']['data']['fields']);
                        $diff = array_diff($a1, $a2);
                        echo '<hr>väljadiff: <hr>';
                        foreach ($diff as $stmtval) {
                            echo "ALTER TABLE $data->tableName DROP COLUMN $stmtval;";
                            $this->tableController->dropColumn($data->tableName, $stmtval);
                        }
                    }
//end drop
                    $data->data->dataCreatedModified = new DataCreatedModified($data->tableName);?>

            <tr>
                <td colspan="2">Lisamine ja muutmine (andmetabelid)</td>
            </tr>
            <?php
foreach ($data->data->dataCreatedModified as $cmKey => $cmField) {
                        if (is_object($cmField)) {
                            ?>
            <tr class="trow">
                <td>
                    <?=$cmKey?>
                </td>
                <td colspan="2">
                    <fieldset>
                        <?php
foreach ($cmField as $cmk => $cmv) {
                                $elName = "table[data][dataCreatedModified][$cmKey][$cmk]";
                                $elId = "table.data.dataCreatedModified.$cmKey.$cmk";
                                if ($cmk == 'id') {
                                    echo "<input type='hidden' name='$elName' id='$elId' value='$cmv' disabled /> ";
                                } else {
                                    echo "<label for='$elId' class='row col-10 mt-1'>
                                                            <div class='col col-2'>$cmk</div>
                                                            <input class='col col-6'name='$elName' id='$elId' disabled";
                                    if (is_bool($cmv)) {
                                        $checked = $cmv ? ' checked="checked"' : '';
                                        echo " type='checkbox' value=true$checked disabled/>";
                                    } else {
                                        if (is_iterable($cmv)) {
                                            $cmv = json_encode($cmv);
                                        }

                                        echo " type='text' value='$cmv' />";
                                    }
                                    echo '</label>';
                                }
                            }

                            ?>
                    </fieldset>
                </td>
            </tr>
            <?php
}

                    }
                    ?>
        </tbody>
    </table>
    <?php
} else {
                    if ($key == 'createdModified') {?>
    <h4><?=$key?> (admin)</h4>
    <?php
$this->createdModified($value);
                        ?>
    </td>
    </tr>

    <?php }

                    $roles = ['belongsTo', 'hasMany', 'hasManyAndBelongsTo', 'hasAny'];

                    if (in_array($key, $roles)) {

                        echo '<h4>' . $key . '</h4>';?>
    <table class="table table-warning table-striped table-sm wrapper">
        <thead>
            <tr>
                <td width="10%" colspan="3"><span class="add btn btn-success btn-sm">Add</span></td>
            </tr>
        </thead>
        <tbody class="repeatcontainer ui-sortable" data-rf-row-count>
            <tr class="template trow">
                <td class="col"><span class="move btn btn-info btn-sm"><i class="bi bi-arrow-down-up"></i></span>
                </td>
                <td>
                    <input type="hidden" name="new[<?=$key?>][{{row-count-placeholder}}][table][id]"
                        id="new.<?=$key?>.{{row-count-placeholder}}.table.id" value="<?=$data->id?>" />
                    <input type="hidden"
                        name="new[<?=$key?>][{{row-count-placeholder}}][createdModified][createdBy][id]"
                        id="new.<?=$key?>.{{row-count-placeholder}}.createdModified.createdBy.id"
                        value="<?=$this->currentUser->id?>" />
                    <table>

                        <?php $data->$key = [];
                        $data->$key[0] = new \Model\RelationSettings();
                        /*
                        if (!isset($data->$key[0]->relation)) {
                            $data->$key[0]->relation = new \Model\Relation();
                        }
                        */

                        foreach ($data->$key[0] as $rdKey => $rdValue) {
                            if ($rdKey == 'relation') {
                                ?>
                        <tr>
                            <td><?php echo $rdKey ?>
                            </td>
                            <td><select name="new[<?=$key?>][{{row-count-placeholder}}][<?=$rdKey?>]"
                                    id="new.<?=$key?>.{{row-count-placeholder}}.<?=$rdKey?>">
                                    <option value=''></option>
                                    <?php echo "\n";
                                foreach ($this->relations as $r) {
                                    $selected = $r->type == $key ? " selected='selected'" : '';
                                    echo "<option value='$r->id'$selected'>{$r->type}</option>\n";
                                }
                                ?>

                                </select>

                                <span><?php foreach ($rdValue as $attr => $val) {
                                    echo "$attr: $val; ";
                                }?></span>
                            </td>
                        </tr>
                        <?php
} else {
                                if (is_bool($rdValue)) {
                                    $checked = $rdValue ? ' checked="checked"' : '';
                                    echo "<tr><td>$rdKey</td><td><input type='checkbox' id='new.$key.{{row-count-placeholder}}.$rdKey' name='new[$key][{{row-count-placeholder}}][$rdKey]' value=true$checked /></td></tr>";
                                } else {
                                    if ($rdKey != "id") {
                                        //echo "<input type='hidden' name='new[$key][{{row-count-placeholder}}][$rdKey]'>";
                                        //} else 
                                        //if ($rdKey == 'table') echo json_encode($rdValue);
                                        echo "<tr><td>$rdKey</td><td><input type='text' id='new.$key.{{row-count-placeholder}}.$rdKey' name='new[$key][{{row-count-placeholder}}][$rdKey]' value='$rdValue' /></td></tr>";
                                    }
                                }
                            }
                        }
                        ?>
                    </table>
                </td>
                <td width="10%"><span class="remove btn btn-danger btn-sm">Remove</span></td>
                </td>
            </tr>

            <?php $relDiff = [];
                        if (!empty($value)) {
                            foreach ($value as $i => $av) {
                                
                                ?>

            <tr class="trow">
                <td class="col"><span class="move btn btn-info btn-sm"><i class="bi bi-arrow-down-up"></i></span>
                </td>
                <td>
                    <input type="hidden" name="table[<?=$key?>][<?=$i?>][table][id]" value="<?=$data->id?>"
                        id="table.<?=$key?>.<?=$i?>.table.id" />
                    <input type="hidden" name="table[<?=$key?>][<?=$i?>][createdModified][modifiedBy]"
                        id="table.<?=$key?>.<?=$i?>.createdModified.modifiedBy" value="<?=$this->currentUser->id?>" />
                    <input type="hidden" name="del[<?=$key?>][<?=$i?>][id]" id="del.<?=$key?>.<?=$i?>.id"
                        value="<?=$av->id?>">
                    <table id="<?=$key?>_<?=$i?>">
                        <?php foreach ($av as $rdKey => $rdValue) {
                                    $rdName = "table[$key][$i][$rdKey]";
                                    $rdId = "table.$key.$i.$rdKey";
/*
                                    if ($rdKey == 'relation') {?>
                        <tr>
                            <td class="col col-2"><?=$rdKey?></td>
                            <td>
                                <input type="checkbox" class="col"
                                    onclick="this.nextElementSibling.toggleAttribute('disabled')">
                                <select name="<?=$rdName?>" id="<?=$rdId?>" disabled>

                                    <?php
foreach ($this->relations as $r) {
                                        $selected = $rdValue == $r ? " selected='selected'" : '';
                                        echo "<option value='$r->id'$selected'>{$r->type}</option>\n";
                                    }
                                        ?>

                                </select>

                                <span><?php foreach ($rdValue as $attr => $val) {
                                            echo "$attr: " . json_encode($val);
                                        }?></span>
                            </td>
                        </tr>
                        <?php
} else { */
?>
                        <tr>
                            <td class="col col-2"><?=$rdKey?></td>
                            <td>
                                <?php if (!in_array($rdKey, ['id', 'createdModified'])) {?>
                                <input type="checkbox" class="col"
                                    onclick="this.nextElementSibling.toggleAttribute('disabled')">
                                <?php }
                                        if (is_bool($rdValue)) {
                                            $checked = $rdValue ? ' checked="checked"' : '';
                                            echo "<input type='checkbox' id='$rdId' name='$rdName' value=true$checked disabled/>";
                                        } else {
                                            if ($rdKey == "id") {
                                                echo "$rdValue <input type='hidden' id='$rdId' name='$rdName' value='$rdValue'>";
                                            } else {
                                                if ($rdKey != 'createdModified') {
                                                    if (is_array($rdValue) || is_object($rdValue)) $rdValue = json_encode($rdValue);
                                                    echo "<input type='text' id='$rdName' name='$rdName' value='$rdValue' disabled/>";
                                                } else {
                                                    echo '<h4>Created & modified</h4>';
                                                    $this->createdModified($rdValue);
                                                }
                                            }
                                        }?>
                            </td>
                        </tr>
                        <?php
//}
                                }
                                ?>

                    </table>
                </td>
                <td width="10%"><span class="remove btn btn-danger btn-sm">Kustuta</span></td>
                </td>
            </tr>
            <?php
}
                            $ac1 = array_column($value, 'id');
                            if (!empty($this->postBody)) {
                                if (!isset($this->postBody['del'][$key])) {
                                    $this->postBody['del'][$key] = [];
                                }
                                $ac2 = array_column($this->postBody['del'][$key], 'id');
                                $acDiff = array_diff($ac1, $ac2);
                                if (!empty($acDiff)) {
                                    foreach ($acDiff as $d) {
                                        echo "DELETE FROM uasys_relation_settings WHERE id=$d;";
                                        $this->tableController->removeFromList('relation_settings', $d);
                                    }
                                    echo '<hr>see on reldiff: <hr>';}
                            }
                        }

                    }
                    ?>
        </tbody>
    </table>
    <?php

                }
            }
        }?>
    <input type="submit" value="Salvesta muudatused" />
</form>
<?php
//$update = new Update();
        //$create = new Create();
        if (!empty($this->postBody)) {

            if (isset($this->postBody['table'])) {
                $this->postBody['table']['tableName'] = $data->tableName;
                //$update->updateTable($_POST['table']);
                $this->tableController->updateTable($_POST['table']);
            }
            if (isset($this->postBody['new'])) {
                $this->postBody['new']['tableName'] = $data->tableName;
                $this->tableController->addTable($this->postBody['new'], true);
            }
        }
    }
    public function createdModified($value, $inner = false)
    {
        echo '<dl class="row bg-light border mb-0">';
        foreach ($value as $subKey => $subValue) {
            if (!in_array($subKey, ['tableId', 'tableName', 'id', 'email', 'social', 'role'])) {
                echo "<dt class='col-sm-4 border'>$subKey</dt>";
                echo "<dd class='col-sm-8 border mb-0'>";
                if ($subKey == 'createdBy') {
                    $this->createdModified($subValue, true);
                } else {
                    echo $subValue;
                }
                echo "</dd>";
            }
        }
        if ($inner === true) {
            echo '</dl>';
        } else {
            echo "<dt class='col-sm-4 border'>Current user</dt>
<dd class='col-sm-8 border mb-0'>" . $this->currentUser->id . "</dd>";
            echo "</dl>";
        }

    }
}