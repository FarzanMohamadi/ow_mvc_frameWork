<?php
/**
 * Video add action controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.controllers
 * @since 1.0
 */
class VIDEO_CTRL_Add extends OW_ActionController
{
    /**
     * Default action
     */
    public function index()
    {
        $language = OW::getLanguage();
        OW::getDocument()->setHeading($language->text('video', 'page_title_add_video'));
        OW::getDocument()->setHeadingIconClass('ow_ic_video');
        OW::getDocument()->setTitle($language->text('video', 'meta_title_video_add'));
        OW::getDocument()->setDescription($language->text('video', 'meta_description_video_add'));

        $clipService = VIDEO_BOL_ClipService::getInstance();
        $userId = OW::getUser()->getId();

        if ( !OW::getUser()->isAuthorized('video', 'add') && !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('video'))
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('video', 'add');
            throw new AuthorizationException($status['msg']);
        }

        if ( !($clipService->findUserClipsCount($userId) <= $clipService->getUserQuotaConfig()) )
        {
            $this->assign('auth_msg', $language->text('video', 'quota_exceeded', array('limit' => $clipService->getUserQuotaConfig())));
        }
        else
        {
            $this->assign('auth_msg', null);

            $videoAddForm = new videoAddForm();
            $this->addForm($videoAddForm);
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_VIDEO_UPLOAD_COMPONENT_RENDERER,array('form' => $videoAddForm, 'component' => $this)));
            if(isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH']>0 && $videoAddForm->getElement('videoUpload')!=null) {
                $maxUploadSize = OW::getConfig()->getValue('base', 'attch_file_max_size_mb');
                $bundleSize = floor($_SERVER['CONTENT_LENGTH'] / 1024);

                if ($maxUploadSize > 0 && $bundleSize > ($maxUploadSize * 1024)) {
                    OW::getFeedback()->error(OW::getLanguage()->text('frmvideoplus', 'upload_file_max_upload_filesize_error', array('videofilesize' => $maxUploadSize)));
                    return false;
                }
            }
            if ( OW::getRequest()->isPost())
            {
                $videoAddForm->getElement('code')->addAttribute(Form::SAFE_RENDERING,true);
                if(isset($_POST['input_type'])){
                    $this->assign('input_type', $_POST['input_type']);
                }
                if($videoAddForm->isValid($_POST)) {
                    $values = $videoAddForm->getValues();
                    if ( (isset($_POST['input_type']) && $_POST['input_type']=="aparat") && !empty($values['aparatURL'])) {
                        $aparat_video_ID = explode('/', $values['aparatURL'])[4];
                        $aparat_video_ID = preg_replace('[\?sid=[a-zA-Z0-9]*]', '', $aparat_video_ID);
                        if (preg_match('[[a-zA-Z0-9\.\_\%\+\-]{4,6}]', $aparat_video_ID)) {
                            $aparat_code = '
<style>.h_iframe-aparat_embed_frame{position:relative;} .h_iframe-aparat_embed_frame .ratio {display:block;width:100%;height:auto;} .h_iframe-aparat_embed_frame iframe {position:absolute;top:0;left:0;width:100%; height:100%;}</style>
<div class="h_iframe-aparat_embed_frame"> <span style="display: block;padding-top: 57%"></span>
<iframe src="https://www.aparat.com/video/video/embed/videohash/' . $aparat_video_ID . '/vt/frame" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" ></iframe></div>';
                            $values['code'] = $aparat_code;
                        } else {
                            OW::getFeedback()->warning($language->text('video', 'resource_not_allowed'));
                            return false;
                        }
                    }
                    if ( (isset($_POST['input_type']) && $_POST['input_type']=="code") || !isset($values['videoUpload'])) {
                        $code = $clipService->validateClipCode($values['code']);
                        if (!mb_strlen($code)) {
                            OW::getFeedback()->warning($language->text('video', 'resource_not_allowed'));
                            return false;
                        }
                        $videoAddForm->setValues($values);
                    }
                    $res = $videoAddForm->process();
                    OW::getFeedback()->info($language->text('video', 'clip_added'));
                    $this->redirect(OW::getRouter()->urlForRoute('view_clip', array('id' => $res['id'])));
                }
            }
        }

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'video', 'video');
        }
        $this->setDocumentKey("video_add");
    }
}

/**
 * Video add form class
 */
class videoAddForm extends Form
{

    /**
     * Class constructor
     *
     */
    CONST Min_Number_of_Chars = 1;
    CONST Max_Number_of_Chars = 128;
    public function __construct()
    {
        parent::__construct('videoAddForm');

        $language = OW::getLanguage();

        // title Field
        $titleField = new TextField('title');
        $titleField->addValidator(new StringValidator(self::Min_Number_of_Chars, self::Max_Number_of_Chars));
        $titleField->setRequired(true);
        $this->addElement($titleField->setLabel($language->text('video', 'title')));

        // description Field
        $descField = new WysiwygTextarea('description','video');
        $this->addElement($descField->setLabel($language->text('video', 'description')));

        // code Field
        $codeField = new Textarea('code');
        $codeField->setRequired(true);
        $this->addElement($codeField->setLabel($language->text('video', 'code')));

        $tagsField = new TagsInputField('tags');
        $this->addElement($tagsField->setLabel($language->text('video', 'tags')));

        $submit = new Submit('add');
        $submit->setValue($language->text('video', 'btn_add'));
        $this->addElement($submit);

        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_VIDEO_UPLOAD_FORM_RENDERER,array('form' => $this)));
    }

    /**
     * Adds video clip
     *
     * @return boolean
     */
    public function process()
    {
        $values = $this->getValues();
        $addClipParams = array(
            'userId' => OW::getUser()->getId(),
            'title' => UTIL_HtmlTag::stripTagsAndJs($values['title']),
            'description' => $values['description'],
            'code' => $values['code'],
            'tags' => $values['tags']
        );
        if(isset($values['videoUpload']) ) {
            $addClipParams['videoUpload']=$values['videoUpload'];
            $addClipParams['code']='videoUpload';
        }

        $event = new OW_Event(VIDEO_CLASS_EventHandler::EVENT_VIDEO_ADD, $addClipParams);
        OW::getEventManager()->trigger($event);

        $addClipData = $event->getData();

        if ( !empty($addClipData['id']) )
        {
            if(isset($values['videoUpload']) ) {
                $event = new OW_Event('videoplus.after_add', array('videoUpload'=>$values['videoUpload'],'videoUploadThumbnail'=>$values['videoUploadThumbnail'],'videoId'=>$addClipData['id'] ));
                OW::getEventManager()->trigger($event);
            }
            return array('result' => true, 'id' => $addClipData['id']);
        }

        return false;
    }
}