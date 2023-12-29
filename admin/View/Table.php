<?php namespace View;

use Common\Model\DataCreatedModified;
use View\Form\EditTable;

include_once __DIR__ . '/Form/EditTable.php';
include_once __DIR__ . '/../../common/Model/DataCreatedModified.php';

/**
 * Table
 *
 * Hallatavate tabelite loetelu ja üksikasjade vaade
 *
 *     @var mixed $tableSingleOrList readonly vaates kas loetelu või üksikasjad
 *     @var mixed $edit
 *     @var mixed $new

 */
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
    /**
     * tableDetails näitab valitud kirje üksikasju
     *
     * @return void
     */
    public function tableDetails($confirmDelete = false)
    {
        echo '<h1>' . $this->tableSingleOrList->tableName . '</h1>';
        if ($confirmDelete === true) {
            $origin = $_SERVER['HTTP_REFERER'];
            $delId = $this->tableSingleOrList->id;
            ?>
<div class="card w-75">
    <div class="card-body">
        <h5 class="card-title bg-warning">Tabeli kustutamine loetelust</h5>
        <div class="card-text">
            Oled kustutamas tabelit <?=$this->tableSingleOrList->tableName?> loetelust. Tabel ise jääb andmebaasi alles,
            kuid selle andmed on avalikkusele osaliselt nähtavad üksnes juhul, kui mõnel teisel, sisuhaldusesse kaasatud
            tabelil on sellega belongsTo (või hasManyAndBelongsTo) tüüpi andmeseos (tüüpiline näide vormivaates -
            rippmenüü või märkeruudud). Soovi korral võib tabeli hiljem uuesti sisuhaldusesse kaasata, tehes tabeli
            lisamise vormis valiku kaasamata tabelite hulgast.
            <h3>Kas soovid selle tabeli praegu loetelust kustutada?</h3>
        </div>
        <form method="post" action="../../View/Form/Delete.php">
            <input id="remove" type="hidden" name="remove" value="1" disabled />
            <input id="callback" name="callback" type="hidden" value="<?=$origin?>" />
            <input id="delId" name="delId" type="hidden" value="<?=$delId?>" />

            <label for=" decide">Jah, eemaldan <input id="decide" onclick="
            document.getElementById('remove').toggleAttribute('disabled');
            document.getElementById('yes').classList.toggle('d-none');
            document.getElementById('no').classList.toggle('d-none');
            " type="checkbox" value="Jah, eemaldan" /></label>
            <input type="button" id="no" value="Ei, jäta alles" class="btn btn-warning"
                onclick="window.location.href='<?=$_SERVER['HTTP_REFERER']?>'" />
            <input type="submit" id="yes" value="Eemalda" class="btn btn-danger d-none" />
        </form>

    </div>
</div>

<?php
}

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

                        if (is_iterable($v)) {
                            $v = json_encode($v);
                        }
                        echo "<li>$k: $v</li>";
                    }?></ul>
    </td>
</tr>
<?php
}
                    ?>
<tr>
    <td colspan="2">
        <h2>Kes ja millal lisas või muutis</h2>
    </td>
</tr>

<?php
//$cmf= new CreatedModified();
                    $cmf = new DataCreatedModified();
                    $this->tableSingleOrList->data->dataCreatedModified = $cmf;
                    foreach ($cmf as $cmKey => $cmValue) {
                        echo "<tr><td>$cmKey</td><td>" . json_encode($cmValue) . "</td></tr>";
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
                            if (is_object($rdValue) || is_array($rdValue)) {
                                $rdValue = json_encode($rdValue, JSON_PRETTY_PRINT);
                            }
                            echo "<tr><td>$rdKey</td><td>$rdValue</td></tr>";
                        }
                    }
                }
            }
        }
    }

    /**
     * tableList näitab hallatavate tabelite loetelu
     *
     * @return void
     */
    public function tableList()
    {
        if (is_array($this->tableSingleOrList)) {
            ?>

<h1>Tabelid</h1>
<table class="table table-success table-striped">
    <caption class="caption-top"><a class="btn btn-sm btn-success"
            href="<?php echo isset($request[1]) ? '' : 'tables'; ?>/new">
            <i class="bi bi-plus-warning bi-plus-lg"></i> Lisa uus</a></caption>
    <caption class="caption-bottom"><a class="btn btn-sm btn-success"
            href="<?php echo isset($request[1]) ? '' : 'tables'; ?>/new">
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