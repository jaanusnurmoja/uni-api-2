<?php namespace View;

class Table
{
    public $tableSingleOrList;
    public function __construct($tableSingleOrList)
    {
        $this->tableSingleOrList = $tableSingleOrList;
    }
    public function tableDetails()
    {
        echo '<h1>' . $this->tableSingleOrList->name . '</h1>';
        echo '<table class="table-warning table-striped">';

        foreach ($this->tableSingleOrList as $key => $value) {
            if (!is_object($value) && !is_array($value)) {
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            } else {
                if ($key == 'data') {
                    foreach ($this->tableSingleOrList->data->fields as $key => $field) {?>
<tr>
    <td><?php echo $key ?></td>
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
            }
        }
    }

    public function tableList()
    {
        if (is_array($this->tableSingleOrList)) {
            ?>

<h1>Tabelid</h1>
<table class="table table-success table-striped">
    <thead>
        <tr>
            <?php
$thead = get_object_vars($this->tableSingleOrList[1]);
            foreach ($thead as $key => $value) {
                echo "<th>" . $key . "</th>";

            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
foreach ($this->tableSingleOrList as $row) {
                echo "<tr>";
                foreach ($row as $key => $value) {
                    if ($key == 'name') {
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
                        $value = count($value);
                    }
                    echo '<td>' . $value . '</td>';
                }
                echo "</tr>";
            }

            ?>
    </tbody>
    <?php

        }
    }
}