<?php namespace View\Form;

class NewTable
{
    public $dto;
    public $relations;
    public function __construct($table = null) {
        $readRels = new \Controller\Table();
        $list = new \DTO\ListDTO();
        $readRels->getRelationsList($list);
        $this->relations = $list->list;

        if (empty($table)) {
            $table = new \Model\Table();
        }
        $this->dto = new \DTO\TableDTO($table);
    }

    public function newTableForm($data = null)
    {
        if (empty($data)) $data = $this->dto;
        ?>
<h1>
    Uus tabel: <?php echo $data->name ?>
</h1>

<form id="new-table" name="new-table">
    <table class="table table-warning table-striped">
        <?php
foreach ($data as $key => $value) {
    if ($key != "id") {
        if (!is_object($value) && !is_array($value)) { ?>
        <tr>
            <td> <?php echo $key?> </td>
            <td><input type="text" name="<?php echo $key?>" value="<?php echo $value?>" /></td>
        </tr>
        <?php } else 
        {
    if ($key == 'data') {?>
        <tr>
            <td colspan=" 2">
                <h2>Andmeväljad</h2>
            </td>
        </tr> <?php
    //$newField->setName('new');
    $data->data->fields['new'] = new \Model\Field();

        foreach ($data->data->fields as $fkey => $field) {?>
        <tr>
            <td><?php echo $fkey ?></td>
            <td>
                <fieldset>
                    <?php
                        foreach ($field as $k => $v) {
                            if ($k == 'id') {
                                echo "<input type='hidden' name='$k' id='$k' value='$v' /> ";
                            } else {
                                echo "<label for='$k'>$k</label> <input name='$k' id='$k' type='text' value='$v' />";
                            }
                        }?>
                </fieldset>
            </td>
        </tr>
        <?php
        }
    } else {?>
        <?php
        $roles = ['belongsTo', 'hasMany', 'hasManyAndBelongsTo'];
        if (in_array($key, $roles)) {
            echo '<tr>
            <td colspan="2" class="h4">' . $key . '</td>
        </tr>';
        $data->$key = [];
            $data->$key[0] = new \Model\RelationDetails();
                    if (!isset($data->$key[0]->relation)) {
                        $data->$key[0]->relation = new \Model\Relation();
                    }

                foreach ($data->$key[0] as $rdKey => $rdValue) {
                    if ($rdKey == 'relation') {
                        ?>
        <tr>
            <td><?php echo $rdKey?></td>
            <td><select name="<?php echo $rdKey?>">
                    <?php
                                    foreach ($this->relations as $r) {
                                        $selected = $rdValue == $r ? " selected='selected'" : '';
                                        echo "<option value='$r->id'$selected'>{$r->type}</option>\n";
                                    }
                        ?>

                </select>
                <span><?php foreach($rdValue as $attr => $val) {
                    echo "$attr: $val; ";
                }?></span>
            </td>
        </tr>
        <?php
                    } else {
                        if (is_bool($rdValue)) {
                            $checked = $rdValue ? ' checked="checked"' : '';
                            echo "<tr><td>$rdKey</td><td><input type='checkbox' id='$rdKey' name='$rdKey' value=true$checked /></td></tr>";
                        } else {
                        if ($rdKey == "id") {
                            echo "<input type='hidden' name='$key\[$rdKey\]\[$rdValue\]'>";
                        } else {
                            echo "<tr><td>$rdKey</td><td><input type='text' id='$rdKey' name='$rdKey' value='$rdValue' /></td></tr>";
                       }
                        }
                    }
                }

        }
    }
}
    }
}
        ?>
    </table>
    <input type="submit" value="Lisa" />
</form>
<?php }
    }