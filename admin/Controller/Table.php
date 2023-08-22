<?php namespace Controller;

use \Service\Read;

class Table
{
    public function getTables($r = null)
    {
        $read = new Read;
        return $read->getTables($r);
        //return 'Siin pole midagi';
    }
    public function pathParams($r = null)
    {
        $read = new Read;
        return $read->req($r);
    }

}