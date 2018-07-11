<?php

require_once __DIR__ . '/hit.php';

class HitCounter{

    public function __construct(){
        $hitObj = $this->_buildHit();
        $result = Hit::isDuplicate($hitObj);
        if(!$result){
            $hitObj->hits = 1;
            $hitObj->create();
        }else{
            $result->hits += 1;
            $result->update();
        }
    }
    protected function _buildHit(){
        $pathInfo = pathinfo($_SERVER['SCRIPT_FILENAME']);
        $hit = new Hit();
        $hit->link = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $hit->server = $_SERVER['SERVER_NAME'];
        $hit->dirname = Hit::parseDir($_SERVER['REQUEST_URI']);
        $hit->filename = $pathInfo['filename'];
        $hit->extension = $pathInfo['extension'];
        $hit->created_date = date("Y-m-d H:i:s");
        $hit->updated_date = date("Y-m-d H:i:s");
        $hit->task = Hit::parseTask($_SERVER['REQUEST_URI']);
        $hit->app_constraint = Hit::parseConstraint($_SERVER['REQUEST_URI']);
        $hit->status_id = 1;
        $hit->created_by = isset($_SESSION['user_tid']) ? $_SESSION['user_tid'] : 'session_empty';
        $hit->updated_by = isset($_SESSION['user_tid']) ? $_SESSION['user_tid'] : 'session_empty';
        return $hit;
    }
}

$h = new HitCounter();