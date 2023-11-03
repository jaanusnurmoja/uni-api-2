<?php namespace View\Form;

class EditTable
{
    public $data;
    public function __construct($data = null) {
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
        </tr> <?php foreach ($data->data->fields as $fkey => $field) {?>
        <tr>
            <td><?php echo $fkey ?></td>
            <td>
                <ul>
                    <?php
                    foreach ($field as $k => $v) {
                        echo "<li>$k: $v</li>";
                    }?>
                </ul>
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
                            $rdValue = json_encode($rdValue, JSON_PRETTY_PRINT);
                        }
                        echo "<tr><td>$rdKey</td><td>$rdValue</td></tr>";
                    }
                }
            }
        }
                   }
        ?>
    </table>
    <input type="submit" value="Muuda">Muuda</input>
</form>
<?php }
    }