<?php

/**
 * https: //www.devbabu.com/what-is-the-php-__get-and-__set-methods/
 */
class Test
{
    private $user = [
        "name" => "John",
        "age" => 22,
        "email" => "john@gmail.com",
        "gender" => "Male",
    ];

    public function __get($var_name)
    {
        if (isset($this->user[$var_name])) {
            return $this->user[$var_name];
        }
        return "\nSorry, the property \"{$var_name}\" does not exist.\n";
    }

    public function __set($var_name, $value)
    {
        echo 'Variable => "' . $var_name . '" & Value => "' . $value . '"';
    }

}

$obj = new Test();
echo $obj->name;
echo $obj->phone;
echo $obj->email;
