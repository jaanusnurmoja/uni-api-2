<?php namespace View;

use View\Form\EditTable;
include_once __DIR__.'/Form/EditTable.php';

class Table
{
    public $tableSingleOrList;
    public $edit;
    public $new;
    
    public function __construct($tableSingleOrList)
    {
        $this->tableSingleOrList = $tableSingleOrList;
        $this->edit = new EditTable($tableSingleOrList);
    }
    public function tableDetails()
    {
    echo '<h1>' . $this->tableSingleOrList->tableName . '</h1>';
    echo '<table class="table table-warning table-striped">';

    foreach ($this->tableSingleOrList as $key => $value) {
        if (!is_object($value) && !is_array($value)) {
            echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
        } else {
                if ($key == 'data') {
                    echo '<tr><td colspan = "2"><h2>Andmeväljad</h2></td></tr>';
                    foreach ($this->tableSingleOrList->data->fields as $fkey => $field) {?>
<tr>
    <td><?php echo $fkey ?></td>
    <td>
        <ul>
            <?php
                    foreach ($field as $k => $v) {
                        echo "<li>$k: $v</li>";
                    }?></ul>
    </td>
</tr>
<?php
                    }
                }

            if ($key == 'createdModified') {
                echo "<tr><td colspan='2' class='h4'>$key</td></tr>";
                foreach ($value as $subKey => $subValue) {
                    if (is_object($subValue) || is_array($subValue)) {
                        $subValue = json_encode($subValue);
                    }
                        echo '<tr><td>' . $subKey . '</td><td>' . $subValue . '</td></tr>';
                    }
                }
                
                if (in_array($key, ['belongsTo', 'hasMany', 'hasManyAndBelongsTo']) && !empty($value)) {
                    echo '<tr><td colspan="2" class="h4">' . $key . '</td></tr>';
                    foreach ($value as $ak => $av) {
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
    }


    public function tableList()
    {
        if (is_array($this->tableSingleOrList)) {
            ?>

<h1>Tabelid</h1>
<table class="table table-success table-striped">
    <caption class="caption-top"><a class="btn btn-sm btn-success"
            href="<?php echo isset($request[1]) ? '' : 'tables';?>/new">
            <i class="bi bi-plus-warning bi-plus-lg"></i> Lisa uus</a></caption>
    <caption class="caption-bottom"><a class="btn btn-sm btn-success"
            href="<?php echo isset($request[1]) ? '' : 'tables';?>/new">
            <i class="bi bi-plus-warning bi-plus-lg"></i> Lisa uus
        </a>
    </caption>
    <thead>
        <tr>
            <?php
$thead = get_object_vars($this->tableSingleOrList[1]);
            foreach ($thead as $key => $value) {
                echo "<th>" . $key . "</th>";

            }
            ?>
            <th>Muuda / Kustuta</th>
        </tr>
    </thead>
    <tbody>
        <?php
foreach ($this->tableSingleOrList as $row) {
                echo "<tr>";
                $url = '';
                foreach ($row as $key => $value) {
                    if ($key == 'tableName') {
                        $url = isset($request[1]) ? $key : "tables/$value";
                        $value = "<a href='$url' class='link-dark'>$value</a>";
                    }
                    if ($key == "data") {
                        $value = $value->fields;
                    }
                    if (is_array($value)) {
                        $value = count($value);
                    }
                    if (is_object($value)) {
                        $value = count((array) $value);
                    }
                    echo '<td>' . $value . '</td>';
                }
                echo "<td>
                <a href='$url/edit' class='btn btn-sm btn-warning'>Muuda</a> | <a href='$url/delete' class='btn btn-sm btn-danger'>Kustuta</a>               </td>";
                echo "</tr>";
            }

            ?>
    </tbody>
    <?php

        }
    }
}