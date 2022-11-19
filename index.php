<?php
echo 'Uuri asja lähemalt siit: <a href="README.md">UNI-API RAKENDUS</a>';
$rel = file_get_contents('api/relations.json'); ?>
<ul>
    <?php
foreach (json_decode($rel) as $table => $params) {
    if ($table != 'hasManyAndBelongsTo') {

        echo "<li><span><a href='api/$table'>$table</a></span><span> | $params->description</span></li>";
    }
}
?>
</ul>