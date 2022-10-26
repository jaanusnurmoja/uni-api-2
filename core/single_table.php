<?php
// retrieve the table and key from the path
$table = preg_replace('/[^a-z0-9_]+/i', '', $request[1]);
header('Content-Type: application/json');
if ($table == "") {
    echo (json_encode(array('error' => 'Invalid Endpoint!')));
    http_response_code(501);
    return;
}

if (!empty($request[2])) {
    $key = $request[2] ? $request[2] : "";
}

// escape the columns and values from the input object
$columns = preg_replace('/[^a-z0-9_]+/i', '', array_keys($input));
$values = array_map(function ($value) use ($link) {
    if ($value === null) {
        return null;
    }
    return mysqli_real_escape_string($link, (string) $value);
}, array_values($input));

// build the SET part of the SQL command
$set = '';
for ($i = 0; $i < count($columns); $i++) {
    if ($columns[$i] == "id") {
        continue;
    }

    $set .= ($i > 0 ? ',' : '') . '`' . $columns[$i] . '`=';
    $set .= ($values[$i] === null ? 'NULL' : '"' . $values[$i] . '"');
}

// create SQL based on HTTP method
switch ($table) {
    case 'user':
        switch ($method) {
            case 'POST':
                $temp_res = mysqli_query(
                    $link,
                    "SELECT password"
                    . " FROM user"
                    . " WHERE `username` = '" . $input["username"] . "'"
                );

                $sql = "SELECT username, id, token
                FROM `$table`
                WHERE `username`='" . $input["username"] . "'
                  AND '" . password_verify($input["password"], mysqli_fetch_object($temp_res)->password) . "' = '1'";
                break;

            case 'PATCH':
                $sql = "UPDATE `$table`
                SET" . ($input["username"] ? "`username` = '" . $input["username"] . "'" : '')
                . ($input["username"] && $input["password"] ? ", " : "") // add the comma if needed
                 . ($input["password"] ? "`password` = '" . password_hash($input["password"], PASSWORD_BCRYPT) . "'" : '') // re-hash the new password
                 . " WHERE `id`='$key'";
                break;

            default:
                error_response(405, 'Only POST and PATCH methods are allowed!');
                break;
        }
        break;

    default:
        switch ($method) {
            case 'GET':
/*         $sql = "SELECT *
FROM `$table`"
.($key ? " WHERE `id`='$key'" : '');
 */
                $sql = buildQuery();
                break;

            case 'PUT':
                if (check_token()) {
                    $sql = "UPDATE `$table`
                  SET $set
                  WHERE `id`='$key'";
                }
                break;

            case 'POST':
                if (check_token()) {
                    $sql = "INSERT INTO `$table`
                  SET $set";
                }
                break;

            case 'DELETE':
                if (check_token()) {
                    $sql = "DELETE FROM `$table`
                  WHERE `id`='$key'";
                }
                break;

            default:
                error_response(405, 'Only GET, PUT, POST, DELETE methods are allowed!');
                break;
        }
        break;
}

// excecute SQL statement
$result = mysqli_query($link, $sql);

// print results, insert id or affected row count
switch ($table) {
    case 'user':
        switch ($method) {
            case 'POST':
                if ($result->num_rows == 0) {
                    error_response(403, 'No account founded or invalid username/password');
                } else {
                    $token = md5($input["username"] . ":" . $input["password"] . time());
                    mysqli_query($link,
                        "UPDATE `$table`
              SET `token` = '" . $token .
                        "' WHERE `username` = '" . $input["username"] . "'"
                    );
                    $res = mysqli_fetch_object($result);
                    $res->token = $token;
                    echo (json_encode($res));
                }
                break;

            case 'PATCH':
                echo (json_encode(array('count' => mysqli_affected_rows($link))));
                break;
            default:
                break;
        }
        break;

    default:
        if (!$result) {
            error_response(501, 'Invalid Endpoint! No table founded with this name: ' . $table);
            return;
        } else {
            switch ($method) {
                case 'GET':
                    if (mysqli_affected_rows($link) == 0) {
                        if ($key == null) {
                            echo '[]';
                            http_response_code(200);
                        } else {
                            error_response(404, 'No element founded with this ID: ' . $key);
                        }
                    }
/*             elseif(mysqli_affected_rows($link) == 1 && $key) {
echo(json_encode(array_unique(mysqli_fetch_object($result))));
}
 */
                    else {
                        /* if (!$key) */
                        $x = 0;
                        $resData = [];
                        $joinAlias = [];
                        //echo '[';
                        $d = [];
                        for ($i = 0; $i < mysqli_num_rows($result); $i++) {
                            $row = mysqli_fetch_assoc($result);
                            $newVar = [];
                            // $resData[$row['rowid']] = splitProperties($row);
                            $rowid = $row['rowid'];
/*
foreach ($row as $key => $val) {
$keys = explode(':', $key);
$newVar = get_recursive_var($keys, [], $val);
array_push($row, $newVar[key($newVar)]);
}
 */
                            $resData[$rowid][] = $row;
                        }
                        echo json_encode(buildQueryResults($resData));
                        //echo ($i > 0 ? ',' : '') . json_encode($row);
                        /* if (!$key) */
                        //echo ']';
                        //echo json_encode($resData);
                    }
                    break;
                case 'PUT':
                    $last_row = mysqli_query(
                        $link,
                        "SELECT * FROM `$table`"
                        . "WHERE `id` = " . $key . "\n"
                    );
                    $res = mysqli_fetch_object($last_row);
                    if (!$res) {
                        error_response(404, 'No element founded with this ID: ' . $key);
                    } else {
                        echo (json_encode($res));
                    }
                    break;

                case 'POST':
                    // echo mysqli_insert_id($link);
                    $last_row = mysqli_query(
                        $link,
                        "SELECT * FROM `$table`"
                        . "WHERE `id` = " . mysqli_insert_id($link) . "\n"
                    );
                    echo (json_encode(mysqli_fetch_object($last_row)));
                    break;
                default:
                    echo (json_encode(array('count' => mysqli_affected_rows($link))));
                    break;
            }
        }
        break;
}