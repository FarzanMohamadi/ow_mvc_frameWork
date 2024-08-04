<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsecurefileurl.bol
 * @since 1.0
 */
class FRMSECUREFILEURL_BOL_UrlsDao extends OW_BaseDao
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMSECUREFILEURL_BOL_Urls';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmsecurefileurl_urls';
    }

    /***
     * @param $key
     * @param $hash
     * @param $type
     * @param $path
     * @return FRMSECUREFILEURL_BOL_Urls
     */
    public function addUrl($key, $hash, $type, $path)
    {
        $url = $this->existUrlByKey($key);;
        if($url == null){
            $url = new FRMSECUREFILEURL_BOL_Urls();
        }
        $url->time = time();
        $url->key = $key;
        $url->hash = $hash;
        $url->type = $type;
        $url->path = $path;

        $this->save($url);

        return $url;
    }

    /***
     * @param $id
     * @param $new_hash
     * @return mixed
     */
    public function updateUrl($id, $new_hash)
    {
        $url = $this->findById($id);
        if($url != null){
            $url->hash = $new_hash;
            $url->time = time();
            $this->save($url);
        }

        return $url;
    }

    /***
     * @param $key
     * @return mixed|null
     */
    public function existUrlByKey($key)
    {
        $example = new OW_Example();
        $example->andFieldEqual('key', $key);
        $url = $this->findObjectByExample($example);
        if($url != null){
            return $url;
        }

        return null;
    }

    /***
     * @param $key
     * @return mixed|null
     */
    public function existUrlByKeyList($keyList)
    {
        if (empty($keyList)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray('key', $keyList);
        return $this->findListByExample($example);
    }

    /***
     * @param $hash
     * @return mixed|null
     */
    public function existUrlByHash($hash)
    {
        $example = new OW_Example();
        $example->andFieldEqual('hash', $hash);
        $url = $this->findObjectByExample($example);
        if($url != null){
            return $url;
        }

        return null;
    }

    /***
     * @param $time
     */
    public function deleteExpired( $time )
    {
        $example = new OW_Example();
        $example->andFieldLessThan('time', $time);

        $this->deleteByExample($example);
    }
}
