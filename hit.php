<?php

require_once __DIR__ . '/record.php';

class Hit extends \Record{

    const DRIVER = 'mssql';
    const DATABASE = 'Sandbox';
    const TABLE = 'tbl_hit_count';
    const PRIMARYKEY = 'id';

    const CONPATTERN = '/constraint=(.*)/';
    const TASKPATTERN = '/&task=(.*)&/';

    public $id;
    public $link;
    public $server;
    public $dirname;
    public $filename;
    public $extension;
    public $task;
    public $app_constraint;
    public $hits;
    public $created_date;
    public $created_by;
    public $updated_date;
    public $updated_by;
    public $status_id;

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DATABASE,self::TABLE,$id);
    }
    public static function parseDir($requestUri){
        $pieces = explode('/',$requestUri);
        return $pieces[1];
    }
    public static function parseConstraint($requestUri){
        if(preg_match(self::CONPATTERN,$requestUri,$mathes)){
            return $mathes[1];
        }
        return false;
    }
    public static function parseTask($requestUri){
        if(preg_match(self::TASKPATTERN,$requestUri,$matches)){
            return $matches[1];
        }
        return false;
    }
    public static function isDuplicate($hit){
        $data = null;
        $results = $GLOBALS['db']
//            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DATABASE)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("task = '$hit->task'")
            ->andWhere("filename = '$hit->filename'")
            ->andWhere("app_constraint = '$hit->app_constraint'")
            ->andWhere("dirname = '$hit->dirname'")
            ->andWhere("server = '$hit->server'")
            ->get();
        if(!mssql_num_rows($results)){
            return false;
        }
        while($row = mssql_fetch_assoc($results)){
            $data = new self($row[self::PRIMARYKEY]);
        }
        return $data;
    }
}