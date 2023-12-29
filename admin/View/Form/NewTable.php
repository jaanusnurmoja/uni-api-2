<?php namespace View\Form;

use Common\Model\DataCreatedModified;

/**
 * NewTable - uue tabeli lisamise vorm
 */
class NewTable
{
    /**
     * @var \Dto\TableDTO dto
     */
    public $dto;
    /**
     * Andmeseoste liikide loetelu rippmenüü jaoks
     *
     * @var array relations
     */
    public $relations;
    /**
     * Andmebaasis olevad, ent kaasamata tabelid
     *
     * @var array unused
     */
    public $unused;
    /**
     * @var array postBody
     */
    public $postBody;
    /**
     * @var mixed tableCtrl
     */
    public $tableCtrl;
    /**
     * @var \user\model\User currentUser
     */
    private $currentUser;

    /**
     * __construct
     *
     * @param mixed table
     *
     * @return void
     */
    public function __construct($table = null)
    {
        $tableCtrl = new \Controller\Table();
        $list = new \DTO\ListDTO();
        $this->currentUser = $_SESSION['loggedIn']['userData'];
        $tableCtrl->getRelationsList($list);
        $this->relations = $list->list;
        $this->unused = $tableCtrl->getUnusedTables();
        $this->tableCtrl = $tableCtrl;

        if (empty($table)) {
            $table = new \Model\Table();
        }
        $this->dto = new \DTO\TableDTO($table);
        $forminput = file_get_contents('php://input');
        parse_str($forminput, $this->postBody);
    }

    public function newTableForm($d = null)
    {
        if (empty($d)) {
            $data = $this->dto;
        }

        ?>
<h1>
    Uus tabel: <?php echo $data->tableName ?>
</h1>

<form id="new-table" name="new-table" class="repeat" method="post" enctype='application/json'>
    <?php
foreach ($data as $key => $value) {
            if ($key != "id") {
                if (!is_object($value) && !is_array($value)) {?>
    <label> <?php echo $key ?> <input type="text" id="table.<?=$key?><?=$key == 'tableName' ? '.new' : ''?>"
            name="table[<?=$key?>]" value="<?=$value?>"
            <?=$key == 'tableName' ? 'onchange="this.value.length>0?this.nextElementSibling.setAttribute(\'disabled\', \'disabled\'):this.nextElementSibling.removeAttribute(\'disabled\')"' : ''?> />
        <?php
if ($key == 'tableName') {?>

        <select name="table[tableName]" id="table.tableName.unused"
            onchange="this.value.length>0?this.previousElementSibling.setAttribute('disabled', 'disabled'):this.previousElementSibling.removeAttribute('disabled');this.value.length>0?this.nextElementSibling.value='':this.nextElementSibling.value='id'">
            <option value=''>või vali olemasolevatest:</option>
            <?php
foreach ($this->unused as $t) {
                    echo "<option value='$t'>$t</option>";
                }
                    ?>
        </select>
        <?php
}?>
    </label>

    <?php
} else {
                    if ($key == 'data') {?>
    <h2>Andmeväljad</h2>
    <table class="table table-warning table-striped table-sm wrapper">
        <thead>
            <tr>
                <td width="10%" colspan="3"><span class="add btn btn-success btn-sm">Add</span></td>
            </tr>
        </thead>
        <tbody class="repeatcontainer ui-sortable" data-rf-row-count=1>
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
                            // echo "<input type='hidden' name='table[data][fields][{{row-count-placeholder}}][$k0]' id='$k0' value='$v0' /> ";
                            // } else {
                            echo "<label for='$k0'>$k0</label> <input name='table[data][fields][{{row-count-placeholder}}][$k0]' id='$k0'";
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
$data->data->fields[] = new \Model\Field();

                        foreach ($data->data->fields as $fkey => $field) {
                            //$field = new \Model\Field();
                            ?>
            <tr class="trow"> <?php
?>
                <td>
                    <span class="move btn btn-info btn-sm"><i class="bi bi-arrow-down-up"></i></span>
                </td>
                <td>
                    <fieldset>
                        <?php foreach ($field as $k => $v) {
                                if ($k != 'id') {

                                    // $fKey
                                    // echo "<input type='hidden' name='table[data][fields][0][$k]' id='$k' value='$v' /> ";
                                    //} else {
                                    echo "<label for='$k'>$k</label> <input name='table[data][fields][0][$k]' id='$k'";
                                    if (is_bool($v)) {
                                        $checked = $v ? ' checked="checked"' : '';
                                        echo " type='checkbox' value=true$checked onclick=this.toggleAttribute('checked') />";
                                    } else {
                                        if (is_iterable($v)) {
                                            $v = json_encode($v);
                                        }

                                        echo " type='text' value='$v' />";
                                    }
                                }
                            }?>
                    </fieldset>
                </td>
                <td width="10%"><span class="remove btn btn-danger btn-sm">Remove</span></td>
            </tr>
            <?php
}
                        ?>

        </tbody>
    </table>
    <table class="table table-warning table-striped table-sm wrapper">
        <?php

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
                                                            <input class='col col-6'name='$elName' id='$elId' readonly";
                                        if (is_bool($cmv)) {
                                            $checked = $cmv ? ' checked="checked"' : '';
                                            echo " type='checkbox' value=true$checked disabled />";
                                            echo "<input type='hidden' name='$elName' value='$cmv'>";
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

    </table>
    <?php
} else {
                        if ($key == 'createdModified') {
                            ?>
    <input type="hidden" name="table[createdModified][createdBy][id]" value="<?=$this->currentUser->id?>" />
    <?php }
                        $roles = ['belongsTo', 'hasMany', 'hasManyAndBelongsTo'];
                        if (in_array($key, $roles)) {
                            echo '<h4>' . $key . '</h4>';?> <table
        class=" table table-warning table-striped table-sm wrapper">
        <thead>
            <tr>
                <td width="10%" colspan="3"><span class="add btn btn-success btn-sm">Add</span></td>
            </tr>
        </thead>
        <tbody class="repeatcontainer ui-sortable" data-rf-row-count="0">
            <tr class="template trow" style="display:none;">
                <td class="col"><span class="move btn btn-info btn-sm"><i class="bi bi-arrow-down-up"></i></span>
                </td>
                <td>
                    <input type="hidden"
                        name="<?="table[$key][{{row-count-placeholder}}][createdModified][createdBy][id]"?>"
                        value="<?=$this->currentUser->id?>" />
                    <table>

                        <?php $data->$key = [];
                            $data->$key[0] = new \Model\RelationSettings();
                            if (!isset($data->$key[0]->relation)) {
                                $data->$key[0]->relation = new \Model\Relation();
                            }

                            foreach ($data->$key[0] as $rdKey => $rdValue) {
                                if ($rdKey == 'relation') {
                                    ?>
                        <tr>
                            <td><?php echo $rdKey ?>
                            </td>
                            <td><select name="table[<?=$key?>][{{row-count-placeholder}}][<?=$rdKey?>]">
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
                                    if ($rdKey != "id") {
                                        //echo "<input type='hidden' name='table[$key][{{row-count-placeholder}}][$rdKey]'>";
                                        //} else {
                                        if (is_bool($rdValue)) {
                                            $checked = $rdValue ? ' checked="checked"' : '';
                                            echo "<tr><td>$rdKey</td><td><input type='checkbox' id='$rdKey' name='table[$key][{{row-count-placeholder}}][$rdKey]' value=true$checked /></td></tr>";
                                        } else {
                                            echo "<tr><td>$rdKey</td><td><input type='text' id='$rdKey' name='table[$key][{{row-count-placeholder}}][$rdKey]' value='$rdValue' /></td></tr>";
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
        </tbody>
    </table>
    <?php
}
                    }
                }
            }
        }
        ?>
    <input type="submit" value="Lisa" />
</form>
<script>
function existingOrNot() {

}
</script>
<?php
if (!empty($this->postBody)) {
            $unused = false;
            if (in_array($this->postBody['table']['tableName'], $this->unused)) {
                $this->postBody['table']['pk'] = $this->tableCtrl->getPk($this->postBody['table']['tableName']);
                $unused = true;
            }
            $this->tableCtrl->addTable($this->postBody['table'], $unused);
        }
    }
}