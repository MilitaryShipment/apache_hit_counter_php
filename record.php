<?php

require_once __DIR__ . '/../db.php';

if(!isset($GLOBALS['db'])){
    $db = new DB();
}

interface RecordBehavior{
    public function create();
    public function update();
    public function setFields($updateObj);
}

abstract class Record implements RecordBehavior{

    const MSSQL = 'mssql';
    const MYSQL = 'mysql';

    public $id;

    protected $driver;
    protected $database;
    protected $table;

    public function __construct($driver,$database,$table,$id)
    {
        $this->driver = $driver;
        $this->database = $database;
        $this->table = $table;
        if(!is_null($id)){
            $this->id = $id;
            $this->_build();
        }
    }
    protected function _build(){
        $results = $GLOBALS['db']
//            ->suite($this->driver)
            ->driver($this->driver)
            ->database($this->database)
            ->table($this->table)
            ->select("*")
            ->where("id = '$this->id'")
            ->get();
        if($this->driver == self::MSSQL){
            if(!mssql_num_rows($results)){
                throw new Exception('Invalid Record ID');
            }
            while($row = mssql_fetch_assoc($results)){
                foreach($row as $key=>$value){
                    if($key == 'guid'){
                        $this->$key = mssql_guid_string($value);
                    }else{
                        $this->$key = $value;
                    }
                }
            }
        }elseif($this->driver == self::MYSQL){
            if(!mysql_num_rows($results)){
                throw new Exception('Invalid Record ID');
            }
            while($row = mysql_fetch_assoc($results)){
                foreach($row as $key=>$value){
                    $this->$key = $value;
                }
            }
        }
        return $this;
    }
    protected function _buildId(){
        $results = $GLOBALS['db']
//            ->suite($this->driver)
            ->database($this->database)
            ->table($this->table)
            ->select("id")
            ->orderBy("id desc")
            ->take(1)
            ->get("value");
        $this->id = $results;
        return $this;
    }
    public function create(){
        $reflection = new \ReflectionObject($this);
        $data = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $upData = array();
        foreach($data as $obj){
            $key = $obj->name;
            if($key == 'created_date' || $key == 'updated_date'){
                $upData[$key] = date("m/d/Y H:i:s");
            }elseif(!is_null($this->$key) && !empty($this->$key)){
                $upData[$key] = $this->$key;
            }
        }
        unset($upData['id']);
        $results = $GLOBALS['db']
//            ->suite($this->driver)
            ->driver($this->driver)
            ->database($this->database)
            ->table($this->table)
            ->data($upData)
            ->insert()
            ->put();
        $this->_buildId()->_build();
        return $this;
    }
    public function update(){
        $reflection = new \ReflectionObject($this);
        $data = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $upData = array();
        foreach($data as $obj){
            $key = $obj->name;
            if($key == 'updated_date'){
                $upData[$key] = date("m/d/Y H:i:s");
            }elseif(!is_null($this->$key) && !empty($this->$key)){
                $upData[$key] = $this->$key;
            }
        }
        if(isset($upData['created_date'])){
            unset($upData['created_date']);
        }
        unset($upData['id']);
        unset($upData['guid']);
        $results = $GLOBALS['db']
//            ->suite($this->driver)
            ->driver($this->driver)
            ->database($this->database)
            ->table($this->table)
            ->data($upData)
            ->update()
            ->where("id = '$this->id'")
            ->put();
        return $this;
    }
    public function setFields($updateObj){
        if(!is_object($updateObj)){
            throw new Exception('Trying to perform object method on non object.');
        }
        foreach($updateObj as $key=>$value){
            $this->$key = $value;
        }
        return $this;
    }
}