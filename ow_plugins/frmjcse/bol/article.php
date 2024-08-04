<?php

/***
 * Class FRMJCSE_BOL_Article
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMJCSE_BOL_Article extends OW_Entity
{
    /**
     * @var string
     */
    public $title;
    public $abstract;
    public $citation;
    public $file;
    public $active;
    public $issueid;
    public $startPage;
    public $endPage;
    public $ts;
    public $dltimes;
    public $extra;
    public $views;

    public function getExtra($key){
        $extra = json_decode($this->extra, true);
        if (empty($extra) || !isset($extra[$key])){
            return null;
        }
        return $extra[$key];
    }
    public function setExtra($key, $value){
        $extra = json_decode($this->extra, true);
        if (empty($extra)){
            $extra = [];
        }
        $extra[$key]= $value;
        $this->extra = json_encode($extra);
    }
}