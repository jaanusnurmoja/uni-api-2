<?php

namespace Common;

class Helper
{
    public static function camelize(string $string, bool $lcfirst = false): string
    {
        $output = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if ($lcfirst) {
            $output = lcfirst($output);
        }
        return $output;
    }
    public static function uncamelize(string $string)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    public static function setAndEnumValues($table, $field) {
        $read = new \Service\Read();
        return $read->setAndEnumValues($table, $field);
    }

    public static function checkMultiDiff($array1, $array2, $result = []){
    //$result = array();
    foreach($array1 as $key => $val) {
         if(isset($array2[$key])){
           if(is_array($val) && $array2[$key]){
               $result[$key] = self::checkMultiDiff($val, $array2[$key]);
           }
       } else {
           $result[$key] = $val;
       }
    }

    return $result;
}

    public static function toArray($obj = null)
    {
        $orig_obj = (object) $obj;

        // We want to preserve the object name to the array
        // So we get the object name in case it is an object before we convert to an array (which we lose the object name)
        if (is_object($obj)) {
            $obj = (array)$obj;
        }

        // If obj is now an array, we do a recursion
        // If obj is not, just return the value
        if (is_array($obj)) {
            $new = [];
            //initiate the recursion
            foreach ($obj as $key => $val) {
                // Remove full class name from the key
                $key = str_replace(get_class($orig_obj), '', $key);
                // We don't want those * infront of our keys due to protected methods
                $new[$key] = self::toArray($val);
            }
        } else {
            $new = $obj;
        }

        return $new;
    }

    public static function givenNamesIntoFirstAndMiddle($gn) {
        $nameParts = explode(' ', $gn);
        $result = new \stdClass;
        $result->firstName = array_shift($nameParts);
        if(!empty($nameParts)) {
            $result->middleName = implode(' ', $nameParts);
        }

        return $result;
    }

}