<?php
  //var_dump(password_hash("admin", PASSWORD_BCRYPT));
  $example = json_decode(file_get_contents('example.json'), true);

/**
 * Function to restructure the resultset of a MySQL query with multiple joins and repeated data.
 *
 * @param array $resultset The original resultset from the MySQL query.
 * @param string $primaryKey The primary key column name in the resultset.
 *
 * @return array The restructured resultset with unique rows based on the primary key.
 */
function restructureResultset($resultset, $primaryKey) {
    // Create an empty array to store the restructured resultset.
    $restructuredResultset = array();

    // Iterate through each row in the original resultset.
    foreach ($resultset as $row) {
        // Get the primary key value for the current row.
        $primaryKeyValue = $row[$primaryKey];

        // Check if the primary key value already exists in the restructured resultset.
        if (!isset($restructuredResultset[$primaryKeyValue])) {
            // If the primary key value does not exist, add the row to the restructured resultset.
            $restructuredResultset[$primaryKeyValue] = $row;
        } else {
            // If the primary key value already exists, merge the current row with the existing row.
            $restructuredResultset[$primaryKeyValue] = array_merge($restructuredResultset[$primaryKeyValue], $row);
        }
    }

    // Return the restructured resultset.
    return $restructuredResultset;
}


// Usage demonstration of the restructureResultset function

// Example 1: Restructuring a resultset with multiple joins and repeated data
$resultset1 = [
    ['id' => 1, 'name' => 'John', 'age' => 25, 'city' => 'New York'],
    ['id' => 2, 'name' => 'Jane', 'age' => 30, 'city' => 'Los Angeles'],
    ['id' => 1, 'name' => 'John', 'age' => 25, 'city' => 'Chicago'],
    ['id' => 3, 'name' => 'Mike', 'age' => 35, 'city' => 'San Francisco'],
];

$restructuredResultset1 = restructureResultset($resultset1, 'id');

$examperesultset = restructureResultset($example[1]['res'][1], 'entity__orchestras:id');
echo '<pre>';
print_r($examperesultset);
echo '</pre>';

// Example 2: Restructuring a resultset with a different primary key
$resultset2 = [
    ['code' => 'A001', 'name' => 'Product 1', 'price' => 10.99],
    ['code' => 'A002', 'name' => 'Product 2', 'price' => 19.99],
    ['code' => 'A001', 'name' => 'Product 1', 'price' => 15.99],
    ['code' => 'A003', 'name' => 'Product 3', 'price' => 24.99],
];

$restructuredResultset2 = restructureResultset($resultset2, 'code');


?>
  