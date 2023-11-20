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


}