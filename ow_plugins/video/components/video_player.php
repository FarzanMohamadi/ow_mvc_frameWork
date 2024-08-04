<?php
/**
 * Video player component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.components
 * @since 1.0
 */
class VIDEO_CMP_VideoPlayer extends OW_Component
{
    /**
     * @var VIDEO_BOL_ClipService 
     */
    private $clipService;

    /**
     * Class constructor
     *
     * @param array $params
     */
    public function __construct( array $params )
    {
        parent::__construct();

        $clipId = $params['id'];
        $this->clipService = VIDEO_BOL_ClipService::getInstance();

        $clip = $this->clipService->findClipById($clipId);
        $event = new OW_Event('videplus.on.video.view.render', array('code'=>$clip->code,'videoId'=>$clip->id));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['source'])) {
            $config = OW::getConfig();
            $playerWidth = $config->getValue('video', 'player_width');
            $playerHeight = $config->getValue('video', 'player_height');
            $this->assign('width', $playerWidth);
            $this->assign('height', $playerHeight);
            $this->assign('videoFile', true);
            $this->assign('source', $event->getData()['source']);
            if(isset($event->getData()['thumbUrl'])) {
                $this->assign('thumbUrl', $event->getData()['thumbUrl']);
            }else{
                $this->assign('thumbUrl', OW::getPluginManager()->getPlugin('video')->getStaticUrl(). 'img/video_no_thumbnail.png');
            }
        }else {
            $message = OW_Language::getInstance()->text('video','video_not_valid');
            $ajaxUrl = OW::getRouter()->urlForRoute('video_validate_iframe');
            OW::getDocument()->addOnloadScript(
                "var iframe = $('div.ow_video_player iframe');
if (iframe) {
    iframe.load(function (e) {
        var url = iframe[0].src;
        if (url) {
            UrlExists(url, \"$ajaxUrl\");
        }
    });
    function UrlExists(url, ajaxUrl) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            cache: false,
            data:
                {
                    url: url
                },
            success: function( data )
            {
                if ( !data.valid )
                {
                    iframe[0].style.visibility = \"hidden\";
                    if(typeof OWM === \"undefined\"){
                        OW.error(\"$message\");
                    }else{
                        OWM.error(\"$message\");
                    }
                }
            }
        });
    }
}");

            $code = $this->clipService->validateClipCode($clip->code, $clip->provider);
            $code = $this->clipService->addCodeParam($code, 'wmode', 'transparent');

            $this->assign('video_not_found', false);
            if($clip->provider == "aparat")
            {
                $verifyAparatVideo = OW::getEventManager()->trigger(new OW_Event('video.on_aparat_video_provider', array('clip_code' => $clip->code)));
                if(!$verifyAparatVideo->getData()['video_found'])
                {
                    $this->assign('video_not_found', true);
                }
            }

            $config = OW::getConfig();
            $playerWidth = $config->getValue('video', 'player_width');
            $playerHeight = $config->getValue('video', 'player_height');

            $code = $this->clipService->formatClipDimensions($code, $playerWidth, $playerHeight);

            if ($clip->provider == 'youtube') {
                $code = preg_replace('/src="([^"]+)"/i', 'src="$1?wmode=transparent&origin=http://ow"', $code);
            }

            $this->assign('clipCode', $code);
        }
    }
}