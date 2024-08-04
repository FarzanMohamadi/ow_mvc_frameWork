<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminstagram
 * @since 1.0
 */
class FRMINSTAGRAM_CTRL_Instagram extends OW_ActionController
{

    private $instagramFetcher;

    public function __construct()
    {
        $this->instagramFetcher = new FRMINSTAGRAM_CLASS_InstagramFetcher();
    }

    public function widgetLoadJson($params){

        $un = $params['username'];
        $url = OW::getConfig()->getValue('frminstagram', 'instagram_url').$un;

        OW::getLanguage()->addKeyForJs('base', 'comment_add_post_error');
        $data = $this->instagramFetcher->fetchFirstItems($url);
        if(isset($data) && isset($data['result'])){
            exit(json_encode($data['result']));
        }
        exit(json_encode(array('status'=>'error','error_msg'=>OW::getLanguage()->text('base','comment_add_post_error'))));
    }

    public function loadMore($params){
        if(empty($_POST['id'])||empty($_POST['after'])||empty($_POST['first'])){
            exit(json_encode(array('status'=>'error','error_msg'=>'empty parameters!')));
        }
        $id = $_POST['id'];
        $after = $_POST['after'];
        $first = $_POST['first'];
        $url = sprintf( OW::getConfig()->getValue('frminstagram', 'instagram_load_more_url'),$id,$first,$after);

        OW::getLanguage()->addKeyForJs('base', 'comment_add_post_error');
        $data = $this->instagramFetcher->fetchMore($id,$url,$params['username'],$first);
        if(isset($data) && isset($data['result'])){
            exit(json_encode($data['result']));
        }
        exit(json_encode(array('status'=>'error','error_msg'=>OW::getLanguage()->text('base','comment_add_post_error'))));
    }
}

