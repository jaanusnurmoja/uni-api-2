<?php namespace View\Form;

class NewTable
{
    public $dto;
    public $relations;
    public $postBody;
    public $tableCtrl;
    private $currentUser;
    
    public function __construct($table = null)
    {
        $tableCtrl = new \Controller\Table();
        $list = new \DTO\ListDTO();
        $this->currentUser = $_SESSION['loggedIn']['userData'];
        $tableCtrl->getRelationsList($list);
        $this->relations = $list->list;
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
    <label> <?php echo $key ?> <input type="text" name="table[<?php echo $key ?>]"
            value="<?php echo $value ?>" /></label>
    <?php } else {
                    if ($key == 'data') {?>
    <h2>Andmeväljad</h2>
    <table class="table table-warning table-striped table-sm wrapper">
        <thead>
            <tr>
                <td width="10%" colspan="3"><span class="add btn btn-success btn-sm">Add</span></td>
            </tr>
        </thead>
        <tbody class="repeatcontainer ui-sortable" data-rf-row-count=1>
            <?php $f0 = new \Model\Field(); ?>
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
                <td class="col"><span class="move btn btn-info btn-sm"><i class="bi bi-arrow-down-up"></i></span></td>
                <td>
                    <input type="hidden"
                        name="<?="table[$key][{{row-count-placeholder}}][createdModified][createdBy][id]"?>"
                        value="<?=$this->currentUser->id?>" />
                    <table>

                        <?php $data->$key = [];
                            $data->$key[0] = new \Model\RelationDetails();
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

<?php
if (!empty($this->postBody)) {
    $this->tableCtrl->addTable($this->postBody['table']);
}
}
}