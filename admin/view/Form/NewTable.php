<?php namespace View\Form;

class NewTable
{
    public $dto;
    public $relations;
    public $postBody;
    public function __construct($table = null)
    {
        $readRels = new \Controller\Table();
        $list = new \DTO\ListDTO();
        $readRels->getRelationsList($list);
        $this->relations = $list->list;

        if (empty($table)) {
            $table = new \Model\Table();
        }
        $this->dto = new \DTO\TableDTO($table);
        $this->postBody = file_get_contents('php://input');
    }

    public function newTableForm($data = null)
    {
        if (empty($data)) {
            $data = $this->dto;
        }

        ?>
<h1>
    Uus tabel: <?php echo $data->name ?>
</h1>

<form id="new-table" name="new-table" class="repeat" method="post" enctype='application/json'>
    <?php
foreach ($data as $key => $value) {
            if ($key != "id") {
                if (!is_object($value) && !is_array($value)) {?>
    <div> <?php echo $key ?> </div>
    <div><input type="text" name="table[<?php echo $key ?>]" value="<?php echo $value ?>" /></div>
    <?php } else {
                    if ($key == 'data') {?>
    <h2>Andmeväljad</h2>
    <table class="table table-warning table-striped wrapper">
        <thead>
            <tr>
                <td width="10%" colspan="3"><span class="add btn btn-success btn-sm">Add</span></td>
            </tr>
        </thead>
        <tbody class="container ui-sortable" data-rf-row-count="0"> <?php
//$newField->setName('new');
                        $data->data->fields[] = new \Model\Field();

                        foreach ($data->data->fields as $fkey => $field) {?>
            <tr class="template row" style="display: none;"> <?php
?>
                <td><?php echo $fkey ?>
                    <span class="move btn btn-info btn-sm"><i class="bi bi-arrow-down-up"></i></span>
                </td>
                <td>
                    <fieldset>
                        <?php foreach ($field as $k => $v) {
                            if ($k == 'id') {

                                // $fKey
                                echo "<input type='hidden' name='table[data][fields][{{row-count-placeholder}}][$k]' id='$k' value='$v' /> ";
                            } else {
                                echo "<label for='$k'>$k</label> <input name='table[data][fields][{{row-count-placeholder}}][$k]' id='$k' type='text' value='$v' />";
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
} else {?>
    <?php
$roles = ['belongsTo', 'hasMany', 'hasManyAndBelongsTo'];
                        if (in_array($key, $roles)) {
                            echo '<h4>' . $key . '</h4>';?>
    <table class="table table-warning table-striped wrapper">
        <thead>
            <tr>
                <td width="10%" colspan="3"><span class="add btn btn-success btn-sm">Add</span></td>
            </tr>
        </thead>
        <tbody class="container ui-sortable" data-rf-row-count="0">
            <tr class="template row" style="display:none;">
                <td><span class="move btn btn-info btn-sm"><i class="bi bi-arrow-down-up"></i></span></td>
                <td>
                    <div>
                        <?php $data->$key = [];
                            $data->$key[0] = new \Model\RelationDetails();
                            if (!isset($data->$key[0]->relation)) {
                                $data->$key[0]->relation = new \Model\Relation();
                            }

                            foreach ($data->$key[0] as $rdKey => $rdValue) {
                                if ($rdKey == 'relation') {
                                    ?>
                        <div>
                            <div><?php echo $rdKey ?>
                            </div>
                            <div><select name="table[<?=$key?>][{{row-count-placeholder}}][<?=$rdKey?>]">
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
                            </div>
                        </div>
                        <?php
} else {
                                    if (is_bool($rdValue)) {
                                        $checked = $rdValue ? ' checked="checked"' : '';
                                        echo "<div><div>$rdKey</div><div><input type='checkbox' id='$rdKey' name='table[$key][{{row-count-placeholder}}][$rdKey]' value=true$checked /></div></div>";
                                    } else {
                                        if ($rdKey == "id") {
                                            echo "<input type='hidden' name='table[$key][{{row-count-placeholder}}][$rdKey]'>";
                                        } else {
                                            echo "<div><div>$rdKey</div><div><input type='text' id='$rdKey' name='table[$key][{{row-count-placeholder}}][$rdKey]' value='$rdValue' /></div></div>";
                                        }
                                    }
                                }
                            }
                            ?>
                    </div>
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
}
}