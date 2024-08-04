<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminstagram
 * @since 1.0
 */
class FRMINSTAGRAM_CLASS_InstagramFetcher
{

    public function __construct()
    {
    }

    /***
     * @param $url
     * @return array|null
     */
    public function fetchFirstItems($url){
        try {
            //retrieve data
            $str = OW::getStorage()->fileGetContent($url, true);
            if(empty($str)){
                return null;
            }

            $internalErrors = libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $loadResult = $dom->loadHTML($str);
            libxml_use_internal_errors($internalErrors);

            if(!isset($loadResult) || !$loadResult){
                return null;
            }

            $data = array();
            $scripts = $dom->getElementsByTagName('script');
            $patten = '/( *window._sharedData *= *(.*);)/u';
            foreach ($scripts as $script) {
                $text = $script->textContent;
                preg_match_all($patten,$text,$matches);
                if(isset($matches) && !empty($matches) && !empty($matches[2])){
                    $data = json_decode($matches[2][0], true);
                    break;
                }
            }

            if (isset($data['entry_data']) && isset($data['entry_data']['ProfilePage']) && !empty($data['entry_data']['ProfilePage'])
                && isset($data['entry_data']['ProfilePage'][0]['graphql']) && isset($data['entry_data']['ProfilePage'][0]['graphql']['user'])) {
                $data = $data['entry_data']['ProfilePage'][0]['graphql']['user'];
            }else{
                return null;
            }

            return $this->get_items_from_userdata($data, $data['id'], $data['username'], $data['profile_pic_url']);
        }catch(Exception $e){
            return null;
        }
    }

    public function fetchMore($id,$url,$username,$loadedItems){
        try {
            //try to retrieve data with cookie
            $ch2 = curl_init($url);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, array('cookie: ig_pr=1;'));
            $str = curl_exec($ch2);

            //$str = OW::getStorage()->fileGetContent($url, true);
            $data = json_decode($str,true);

            if(!isset($data)){
                return array(
                    'error'=>false,
                    'result'=>array('status'=>'error','error_msg'=>OW::getLanguage()->text('base','comment_add_post_error'))
                );
            }

            if (isset($data['data']) && isset($data['data']['user'])) {
                $data = $data['data']['user'];
            }else{
                return array(
                    'error'=>false,
                    'result'=>array('status'=>'error','error_msg'=>OW::getLanguage()->text('base','comment_add_post_error'))
                );
            }

            return $this->get_items_from_userdata($data, $id, $username, isset($_POST['profile_pic'])?$_POST['profile_pic']:'');
        }catch(Exception $e){
            return array(
                'error'=>false,
                'result'=>array('status'=>'error','error_msg'=>OW::getLanguage()->text('base','comment_add_post_error'))
            );
        }
    }

    private function format_social_count($count){
        $count = intval($count);
        if($count>(1000*1000))
            $count = intval($count/(1000*1000))."m";
        else if($count>1000)
            $count = intval($count/(1000))."k";
        return $count;
    }

    private function get_items_from_userdata($data, $id, $username, $profile_pic){
        //check if empty
        if (!isset($data['edge_owner_to_timeline_media']) || !isset($data['edge_owner_to_timeline_media']['edges']) || empty($data['edge_owner_to_timeline_media']['edges'])) {
            return array(
                'error'=>false,
                'result'=>array('status' => 'empty', 'more_available' => false, 'items' => array())
            );
        }

        $ret = array();
        $ret['status'] = 'ok';
        $ret['more_available'] = $data['edge_owner_to_timeline_media']['page_info']['has_next_page'];
        $ret['after'] = $data['edge_owner_to_timeline_media']['page_info']['end_cursor'];
        $ret['user'] = array(
            'id'=> $id,
            'username'=> $username,
            'profile_picture'=> $profile_pic,
            'profile_url'=>OW::getConfig()->getValue('frminstagram', 'instagram_url').$username."/",
        );
        $data = $data['edge_owner_to_timeline_media']['edges'];

        if($ret['more_available']){
            $ret['first'] = count($data);
        }

        $items=array();
        for($i=0;$i<count($data);$i++){
            try {
                $newItem = array();
                $newItem['id'] = $data[$i]['node']['id'];
                $newItem['link'] = OW::getConfig()->getValue('frminstagram', 'instagram_url').'p/'.$data[$i]['node']['shortcode'].'/?taken-by='.$username;
                $newItem['image'] = $data[$i]['node']['thumbnail_src'];
                $newItem['likes'] = isset($data[$i]['node']['edge_liked_by'])?
                    $this->format_social_count($data[$i]['node']['edge_liked_by']['count']):$this->format_social_count($data[$i]['node']['edge_media_preview_like']['count']);
                $newItem['comments'] = $this->format_social_count($data[$i]['node']['edge_media_to_comment']['count']);
                $x = UTIL_DateTime::formatSimpleDate($data[$i]['node']['taken_at_timestamp'],true);
                $newItem['created_time'] = $x;
                $newItem['type'] = $data[$i]['node']['is_video']?'video':'photo';

                $items[]=$newItem;
            }catch(Exception $e){  }
        }
        $ret['items']=$items;
        return array(
            'error'=>false,
            'result'=>$ret
        );
    }
}

