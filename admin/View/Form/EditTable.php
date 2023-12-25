<?php namespace View\Form;

class EditTable
{
    public $relations;
    public $data;
    
    public function __construct($data = null) {
        $readRels = new \Controller\Table();
        $list = new \DTO\ListDTO();
        $readRels->getRelationsList($list);
        $this->relations = $list->list;
        $this->data = $data;
    }

    public function editTableForm($data = null)
    {
        if (empty($data)) $data = $this->data;
        ?>
<h1>
    <?php echo $data->name ?>
</h1>

<form id="edit-table" name="edit-table">
    <input type="hidden" name="id" value="<?php echo $data->id ?>" id="id" />
    <table class="table table-warning table-striped">
        <?php

            foreach ($data as $key => $value) {
                if (!is_object($value) && !is_array($value) && $key != 'id') { ?>
        <tr>
            <td> <?php echo $key?> </td>
            <td><input type="text" name="<?php echo $key?>" value="<?php echo $value?>" /></td>
        </tr>
        <?php } else {
                        if ($key == 'data') {?>
        <tr>
            <td colspan=" 2">
                <h2>Andmeväljad</h2>
            </td>
        </tr> <?php 
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
                }
                if (in_array($key, ['belongsTo', 'hasMany', 'hasManyAndBelongsTo']) && !empty($value)) {
                    echo '<tr><td colspan="2" class="h4">' . $key . '</td></tr>';
                    foreach ($value as $av) {
                        foreach ($av as $rdKey => $rdValue) {
                            if (is_object($rdValue)) {
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
                                echo "<tr><td>$rdKey</td><td><input type='text' id='$rdKey' name='$rdKey' value='$rdValue' /></td></tr>";
                            }
                        }
                    }
                }
            }
        }
    }
?>
    </table>
    <input type="submit" value="Salvesta muudatused" />
</form>
<?php }
    }