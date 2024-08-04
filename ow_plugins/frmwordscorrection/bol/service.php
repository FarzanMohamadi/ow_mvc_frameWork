<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmwordscorrection.bol
 * @since 1.0
 */
class FRMWORDSCORRECTION_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * @param $tableName
     * @param $columnName
     */
    public function correctAllInTable($tableName, $columnName) {
        $this->correctWord($tableName, $columnName, 'ي', 'ی');
        $this->correctWord($tableName, $columnName, 'ك', 'ک');
    }

    /**
     * @param string $text
     * @return string
     */
    public function correctWordString($text)
    {
        $text = str_replace('ي', 'ی',$text);
        return str_replace('ك', 'ک',$text);
    }

    /***
     * @param $tableName
     * @param $columnName
     * @param $oldWord
     * @param $newWord
     * @param null $whereClause
     * @param null $params\
     */
    private function correctWord($tableName, $columnName, $oldWord, $newWord,$whereClause=null,$params=null){
        if(isset($whereClause) && isset($params)) {
            $query = "update " . OW_DB_PREFIX . $tableName . " set " . $columnName . " = REPLACE(" . $columnName . ",'" . $oldWord . "','" . $newWord . "') " . $whereClause;
            OW::getDbo()->query($query,$params);
        }else {
            $query = "update " . OW_DB_PREFIX . $tableName . " set " . $columnName . " = REPLACE(" . $columnName . ",'" . $oldWord . "','" . $newWord . "')";
            OW::getDbo()->query($query);
        }
    }

    /***
     * @param $tableName
     * @return bool
     */
    private function tableExist($tableName){
        $table = OW::getDbo()->queryForRow('show tables like :tableName', array('tableName' => OW_DB_PREFIX . $tableName));
        return !(empty($table));
    }

    /***
     * Call this to fix all tables
     */
    public function correctAll(){
        //Correct translations
        $this->correctTranslations();

        //Correct frmnews plugin words
        $this->correctNewsWords();

        //Correct groups plugin words
        $this->correctGroupsWords();

        //Correct event plugin words
        $this->correctEventWords();

        //Correct blogs plugin words
        $this->correctBlogsWords();

        //Correct video plugin words
        $this->correctVideoWords();

        //Correct forum plugin words
        $this->correctForumWords();

        //Correct photo plugin words
        $this->correctPhotoWords();

        //Correct newsfeed plugin words
        $this->correctNewsFeedWords();

        //Correct component setting words (ex: User about me field)
        $this->correctComponentSettingWords();

        //Correct all tag words
        $this->correctTagsWords();

        //Correct users realname words
        $this->correctUsersRealName();
    }

    /******************** FUNCTIONS FOR EACH PLUGIN **********************/

    private function correctTranslations(){
        if(!$this->tableExist('base_language_value')){
            return;
        }

        //Correct location
        $this->correctAllInTable('base_language_value', 'value');
    }

    private function correctTagsWords(){
        if(!$this->tableExist('base_tag')){
            return;
        }

        //Correct location
        $this->correctAllInTable('base_tag', 'label');
    }

    private function correctNewsFeedWords(){
        if(!$this->tableExist('newsfeed_status')){
            return;
        }

        //Correct status
        $this->correctAllInTable('newsfeed_status', 'status');
    }

    private function correctComponentSettingWords(){
        if(!$this->tableExist('base_component_entity_setting')){
            return;
        }

        //Correct value
        $this->correctAllInTable('base_component_entity_setting', 'value');
    }

    private function correctPhotoWords(){
        if(!$this->tableExist('photo') || !$this->tableExist('photo_album')){
            return;
        }

        //Correct description of photo
        $this->correctAllInTable('photo', 'description');

        //Correct name of album
        $this->correctAllInTable('photo_album', 'name');

        //Correct description of album
        $this->correctAllInTable('photo_album', 'description');
    }

    private function correctForumWords(){
        if(!$this->tableExist('forum_post') || !$this->tableExist('forum_topic')){
            return;
        }

        //Correct text of post
        $this->correctAllInTable('forum_post', 'text');

        //Correct title of topic
        $this->correctAllInTable('forum_topic', 'title');
    }

    private function correctVideoWords(){
        if(!$this->tableExist('video_clip')){
            return;
        }

        //Correct title
        $this->correctAllInTable('video_clip', 'title');

        //Correct description
        $this->correctAllInTable('video_clip', 'description');
    }

    private function correctEventWords(){
        if(!$this->tableExist('event_item')){
            return;
        }

        //Correct title
        $this->correctAllInTable('event_item', 'title');

        //Correct description
        $this->correctAllInTable('event_item', 'description');

        //Correct location
        $this->correctAllInTable('event_item', 'location');
    }

    private function correctGroupsWords(){
        if(!$this->tableExist('groups_group')){
            return;
        }

        //Correct title
        $this->correctAllInTable('groups_group', 'title');

        //Correct description
        $this->correctAllInTable('groups_group', 'description');
    }

    private function correctBlogsWords(){
        if(!$this->tableExist('blogs_post')){
            return;
        }

        //Correct title
        $this->correctAllInTable('blogs_post', 'title');

        //Correct post
        $this->correctAllInTable('blogs_post', 'post');
    }

    private function correctNewsWords(){
        if(!$this->tableExist('frmnews_entry')){
            return;
        }

        //Correct title
        $this->correctAllInTable('frmnews_entry', 'title');

        //Correct entry
        $this->correctAllInTable('frmnews_entry', 'entry');
    }

    private function correctUsersRealName(){
        if(!$this->tableExist('base_question_data')){
            return;
        }
        $whereClause = ' where `questionName`=:name';
        $params = array('name'=>'realname');
        //Correct title
        $this->correctWord('base_question_data', 'textValue', 'ي', 'ی',$whereClause,$params);
        $this->correctWord('base_question_data', 'textValue', 'ك', 'ک',$whereClause,$params);
    }

}