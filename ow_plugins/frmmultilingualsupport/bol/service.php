<?php
class FRMMULTILINGUALSUPPORT_BOL_Service
{
    const CREATE_MULTILINGUAL_FIELD = 'frmmultilingualsupport.create.multilingual.field';
    const STORE_MULTILINGUAL_DATA = 'frmmultilingualsupport.store.multilingual.data';
    const SHOW_DATA_IN_MULTILINGUAL= 'frmmultilingualsupport.show.data.in.multilingual';
    const SHOW_STATIC_PAGE_NAME_IN_MULTILINGUAL= 'frmmultilingualsupport.show.static.page.name.in.multilingual';

    const CREATE_MULTILINGUAL_FIELD_WIDGET_PAGE = 'frmmultilingualsupport.create.multilingual.field.widget.page';
    const STORE_MULTILINGUAL_DATA_WIDGET_PAGE = 'frmmultilingualsupport.store.multilingual.data.widget.page';

    const FIND_MULTI_VALUE_BY_WIDGET_UNIQUE_NAME = 'frmmultilingualsupport.find.multi.value.by.widget.unique.name';

    const ENGLISH_FILD_LABEL_POSTFIX = '_frmEnglishSupport';
    const FARSI_FILD_LABEL_POSTFIX = '_frmFarsiSupport';
    const NEWS_ENTITY_TYPE='news';
    const PAGE_ENTITY_TYPE='page';
    const CONTACTUS_ENTITY_TYPE='frmcontactus';
    const WIDGET_PAGE_ENTITY_TYPE='widget_page';

    const ESCAPE_STRIP_JS='escapeStripJs';
    const ESCAPES_ALL='escapeAll';
    const ESCAPES_IGNORE='escapeIgnore';
    const ESCAPES_SANITIZE='escapeSanitize';

    const PRESENTATION_TEXT = 'text';
    const PRESENTATION_TEXTAREA = 'textarea';

    private static $classInstance;
    private $multilingualDao;
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    private function __construct()
    {
        $this->multilingualDao = FRMMULTILINGUALSUPPORT_BOL_DataDao::getInstance();

    }
    /*
     * get custom buttons to Wysiwyg entry for news
     */

    public function getNewsEntryWysiwygButtons()
    {
        $buttons = array(
            BOL_TextFormatService::WS_BTN_BOLD,
            BOL_TextFormatService::WS_BTN_ITALIC,
            BOL_TextFormatService::WS_BTN_UNDERLINE,
            BOL_TextFormatService::WS_BTN_LINK,
            BOL_TextFormatService::WS_BTN_IMAGE,
            BOL_TextFormatService::WS_BTN_ORDERED_LIST,
            BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            BOL_TextFormatService::WS_BTN_MORE,
            BOL_TextFormatService::WS_BTN_SWITCH_HTML,
            BOL_TextFormatService::WS_BTN_HTML,
            //BOL_TextFormatService::WS_BTN_VIDEO
        );
        return $buttons;
    }

    /*
     * don't create multilingual field for these instances
     */
    public function ignoreInstanceFields($field)
    {
        $InIgnoreClassType = array("MAILBOX_CLASS_SearchField","MAILBOX_CLASS_Textarea");
        if(in_array(get_class($field),$InIgnoreClassType)){
            return true;
        }
        return false;
    }

    /*
     * don't create multilingual field for the field with this attribute
     */
    public function ignoreAttributeFields($field)
    {
        $ignoreAttribute = array("identity");
        if($field->getAttribute('name')!=null && in_array($field->getAttribute('name'),$ignoreAttribute)){
            return true;
        }
        return false;
    }

    /*
     * don't create multilingual field for these fields for static html page's form
     */
    public function isInIgnoreFieldsForStaticPages($fieldName)
    {
        $createField = true;
        $ignoreFieldsForStaticPages = array('address','type','external-url','local-url','url');
        if(in_array($fieldName,$ignoreFieldsForStaticPages)) {
            $createField=false;
        }
        return $createField;
    }

    /*
     * don't create multilingual field for these fields in frmcontact us admin
     */
    public function isInIgnoreFieldsForContactUsAdmin($fieldName)
    {
        $createField = true;
        $ignoreFieldsForContactUsAdmin= array('email','label');
        if(in_array($fieldName,$ignoreFieldsForContactUsAdmin)) {
            $createField=false;
        }
        return $createField;
    }

    public function isInMultiLingualControllerAndActionList($fieldName,$ctrl,$action){
        $InMultiLingualControllerList = array('FRMNEWS_CTRL_Save','FRMNEWS_MCTRL_Save','ADMIN_CTRL_Pages','ADMIN_CTRL_PagesEditLocal','BASE_CTRL_ComponentPanel','FRMCONTACTUS_CTRL_Admin');
        $InMultiLingualActionList = array('index','dept');
        if(in_array($ctrl,$InMultiLingualControllerList) && in_array($action,$InMultiLingualActionList)){
            if(($ctrl=='ADMIN_CTRL_Pages' || $ctrl=='ADMIN_CTRL_PagesEditLocal') && $action=='index'){
                return $this->isInIgnoreFieldsForStaticPages($fieldName);
            }
            if($ctrl=='FRMCONTACTUS_CTRL_Admin' && $action=='dept'){
                return $this->isInIgnoreFieldsForContactUsAdmin($fieldName);
            }
            return true;
        }
        return false;
    }
    public function createMultilingualField(OW_Event $event){
        $params = $event->getParams();

        if (isset($params['field']))
        {
            $field = $params['field'];
            if ($this->ignoreAttributeFields($field)) {
                return;
            }
            $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
            $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
            if (BOL_LanguageService::getInstance()->getCurrent()->getTag()==='en')
            {
                if(strpos($field->getName(), $farsiPostfixLabel) !== false){
                    return;
                }
                $multiLabelDesc= OW::getLanguage()->text('frmmultilingualsupport', 'type_in_persian');
                $htmlMultiDesc = '<label style="direction: ltr;display: block;">'.$multiLabelDesc.'</label>';

            }
            if (BOL_LanguageService::getInstance()->getCurrent()->getTag()==='fa-IR')
            {
                if(strpos($field->getName(), $englishPostfixLabel) !== false){
                    return;
                }
                $multiLabelDesc = OW::getLanguage()->text('frmmultilingualsupport', 'type_in_english');
                $htmlMultiDesc = '<label style="direction: rtl;display: block;">'.$multiLabelDesc.'</label>';
            }
            $attr = OW::getRequestHandler()->getHandlerAttributes();
            if (!$this->isInMultiLingualControllerAndActionList($field->getName(), $attr[OW_RequestHandler::ATTRS_KEY_CTRL], $attr[OW_RequestHandler::ATTRS_KEY_ACTION]) || $this->ignoreInstanceFields($field))
            {
                return false;
            }
            $isFarsi = false;
            if ($field->getValue() != null)
            {
                $isFarsi = $this->detectPersianCharacter($field->getValue());
            }

            $fieldFaName = $field->getName() . $farsiPostfixLabel;
            $fieldEnName = $field->getName() . $englishPostfixLabel;

            $enFieldValue = $this->getEnMultilingualEntityData($attr, $fieldEnName);
            $faFieldValue = $this->getFaMultilingualEntityData($attr, $fieldFaName);

            if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='en')
            {
                $complementName = $fieldFaName;
            }else if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='fa-IR')
            {
                $complementName = $fieldEnName;
            }
            $complementField = null;
            if ($field instanceof TextField) {
                $complementField = new TextField($complementName);
            } else if ($field instanceof Textarea)
            {
                /*
                 * when original field is MobileWysiwygTextarea it's instance of both: MobileWysiwygTextarea and Textarea
                 */
                if ($field instanceof MobileWysiwygTextarea)
                {
                    if($field->getName()=='entry')
                    {
                        $complementField = new MobileWysiwygTextarea($complementName,'frmnews',$this->getNewsEntryWysiwygButtons());

                    }else {
                        $complementField = new MobileWysiwygTextarea($complementName,'frmnews');
                    }
                    OW::getRegistry()->set('baseMWsInit',null);
                }else {
                    $complementField = new Textarea($complementName);
                }
            } else if ($field instanceof WysiwygTextarea)
            {
                if($field->getName()=='entry')
                {
                    $complementField = new WysiwygTextarea($complementName,'frmnews',$this->getNewsEntryWysiwygButtons());
                    $complementField->setSize(WysiwygTextarea::SIZE_L);
                }else {
                    $complementField = new WysiwygTextarea($complementName,'frmnews');
                }
                OW::getRegistry()->set('baseWsInit', null);
            }
            if (isset($complementField))
            {
                OW::getDocument()->addScriptDeclaration($complementField->getElementJs());
                if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='en')
                {
                    if(isset($enFieldValue))
                    {
                        if($isFarsi)
                        {
                            $field->setValue($enFieldValue);
                            if($field->getAttribute('value')!==null)
                            {
                                $field->removeAttribute('value');
                                $field->addAttribute('value',$enFieldValue);
                            }
                        }
                        $complementField->setValue($faFieldValue);
                        if($field->getAttribute('value')!==null)
                        {
                            $field->removeAttribute('value');
                            $field->addAttribute('value',$enFieldValue);
                        }
                        $field->setValue($enFieldValue);
                    }else {
                        $complementField->addAttribute("placeholder", OW::getLanguage()->text('frmmultilingualsupport', 'type_in_persian'));
                    }

                }else if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='fa-IR')
                {
                    if(isset($faFieldValue))
                    {
                        if(!$isFarsi)
                        {
                            $field->setValue($faFieldValue);
                            if($field->getAttribute('value')!==null){
                                $field->removeAttribute('value');
                                $field->addAttribute('value',$faFieldValue);
                            }
                        }
                        $complementField->setValue($enFieldValue);
                        if($field->getAttribute('value')!==null)
                        {
                            $field->removeAttribute('value');
                            $field->addAttribute('value',$faFieldValue);
                        }
                        $field->setValue($faFieldValue);
                    }
                }

                $event->setData(array('multilingualField' => '<br><br>'.$htmlMultiDesc . $complementField->renderInput() . '<br>'));
            }
        }

    }

    public function isOnlyStripJsList($key,$entityType){
        if($entityType==self::NEWS_ENTITY_TYPE) {
            $sanitizeList = array('entry');
            if (in_array($key, $sanitizeList)) {
                return self::ESCAPES_SANITIZE;
            }
            return self::ESCAPES_ALL;
        }else if($entityType==self::PAGE_ENTITY_TYPE) {
            $escapeIgnoreList = array('content');
            if (in_array($key, $escapeIgnoreList)) {
                return self::ESCAPES_IGNORE;
            }
            return self::ESCAPES_ALL;
        }else if($entityType==self::CONTACTUS_ENTITY_TYPE) {
            $sanitizeList = array('comment');
            if (in_array($key, $sanitizeList)) {
                return self::ESCAPES_SANITIZE;
            }
            return self::ESCAPES_ALL;
        }
    }

    public function storeMultilingualData(OW_Event $event){
        $params = $event->getParams();
        if (isset($params['entityId']) && isset($params['entityType']))
        {
            $attr = OW::getRequestHandler()->getHandlerAttributes();
            if ($attr[OW_RequestHandler::ATTRS_KEY_CTRL] === 'FRMNEWS_CTRL_Save' || $attr[OW_RequestHandler::ATTRS_KEY_CTRL] === 'FRMNEWS_MCTRL_Save')
            {
                $this->saveData($params['entityId'], $params['entityType']);
            } elseif ($attr[OW_RequestHandler::ATTRS_KEY_CTRL] === 'ADMIN_CTRL_PagesEditLocal' || $attr[OW_RequestHandler::ATTRS_KEY_CTRL] === 'ADMIN_CTRL_Pages')
            {
                $this->saveData($params['entityId'], $params['entityType']);
            }elseif ($attr[OW_RequestHandler::ATTRS_KEY_CTRL] === 'FRMCONTACTUS_CTRL_Admin')
            {
                $this->saveData($params['entityId'], $params['entityType']);
            }
        }
    }


    public function saveData($entityId,$entityType){
        $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
        $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
        $enData = array();
        $faData = array();
        foreach($_POST as $key => $value)
        {
            if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='fa-IR')
            {
                if (strpos($key, $englishPostfixLabel) !== false && isset($value)) {
                    $length = strpos($key, $englishPostfixLabel);
                    $originalKey = substr($key, 0, $length);
                    $escapeMethod = $this->isOnlyStripJsList($originalKey, $entityType);
                    if ($escapeMethod === self::ESCAPES_SANITIZE) {
                        $value = UTIL_HtmlTag::sanitize($value);
                        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $value)));
                        if(isset($stringRenderer->getData()['string'])){
                            $value = $stringRenderer->getData()['string'];
                        }
                        $enData[$key] = $value;


                        $value = UTIL_HtmlTag::sanitize($_POST[$originalKey]);
                        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $value)));
                        if(isset($stringRenderer->getData()['string'])){
                            $value = $stringRenderer->getData()['string'];
                        }
                        $faData[$originalKey . $farsiPostfixLabel] = $value;
                    }
                    else if ($escapeMethod === self::ESCAPE_STRIP_JS) {
                        $enData[$key] = UTIL_HtmlTag::stripJs($value);
                        $faData[$originalKey . $farsiPostfixLabel] = UTIL_HtmlTag::stripJs($_POST[$originalKey]);
                    } else if ($escapeMethod === self::ESCAPES_ALL) {
                        $enData[$key] =UTIL_HtmlTag::stripTagsAndJs($value);
                        $faData[$originalKey . $farsiPostfixLabel] =UTIL_HtmlTag::stripTagsAndJs($_POST[$originalKey]);
                    } else if ($escapeMethod === self::ESCAPES_IGNORE) {
                        $enData[$key] = $value;
                        $faData[$originalKey . $farsiPostfixLabel] = $_POST[$originalKey];
                    }
                }
            }else if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='en')
            {
                if (strpos($key, $farsiPostfixLabel) !== false && isset($value))
                {
                    $length = strpos($key, $farsiPostfixLabel);
                    $originalKey = substr($key, 0, $length);
                    $escapeMethod = $this->isOnlyStripJsList($originalKey, $entityType);
                    if ($escapeMethod === self::ESCAPES_SANITIZE) {
                        $value = UTIL_HtmlTag::sanitize($value);
                        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $value)));
                        if(isset($stringRenderer->getData()['string'])){
                            $value = $stringRenderer->getData()['string'];
                        }
                        $faData[$key] = $value;


                        $value = UTIL_HtmlTag::sanitize($_POST[$originalKey]);
                        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $value)));
                        if(isset($stringRenderer->getData()['string'])){
                            $value = $stringRenderer->getData()['string'];
                        }
                        $enData[$originalKey . $englishPostfixLabel] = $value;
                    }
                    else if ($escapeMethod === self::ESCAPE_STRIP_JS) {
                        $faData[$key] = UTIL_HtmlTag::stripJs($value);
                        $enData[$originalKey . $englishPostfixLabel] = UTIL_HtmlTag::stripJs($_POST[$originalKey]);
                    } else if ($escapeMethod === self::ESCAPES_ALL) {
                        $faData[$key] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($value));
                        $enData[$originalKey . $englishPostfixLabel] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($_POST[$originalKey]));
                    } else if ($escapeMethod === self::ESCAPES_IGNORE) {
                        $faData[$key] = $value;
                        $enData[$originalKey . $englishPostfixLabel] = $_POST[$originalKey];
                    }
                }
            }
        }
        $enJsonData=json_encode($enData);
        $faJsonData=json_encode($faData);
        $this->multilingualDao->saveData($entityId,$entityType,'en',$enJsonData);
        $this->multilingualDao->saveData($entityId,$entityType,'fa-IR',$faJsonData);
    }

    public function findEnDataByEntityIdAndType($entityId,$entityType){
        return  $this->multilingualDao->findEnDataByEntityIdAndType($entityId,$entityType);
    }

    public function findFaDataByEntityIdAndType($entityId,$entityType){
        return  $this->multilingualDao->findFaDataByEntityIdAndType($entityId,$entityType);
    }

    public function getEnMultilingualEntityData ($attr,$fieldEnName){
        if($attr[OW_RequestHandler::ATTRS_KEY_CTRL]==="FRMNEWS_CTRL_Save" || $attr[OW_RequestHandler::ATTRS_KEY_CTRL]==="FRMNEWS_MCTRL_Save") {
            if(!isset($attr['params']['id'])){
                return;
            }
            $entityType = self::NEWS_ENTITY_TYPE;
            $entityId = $attr['params']['id'];
            $enData= $this->findEnDataByEntityIdAndType($entityId,$entityType);
            if(isset($enData)){
                $enJsonData= json_decode($enData->entityData,true);
                if(isset($enJsonData[$fieldEnName])){
                    $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' =>  $enJsonData[$fieldEnName])));
                    if(isset($stringRenderer->getData()['string'])){
                        $enJsonData[$fieldEnName] = $stringRenderer->getData()['string'];
                    }
                    return $enJsonData[$fieldEnName];
                }
            }
        }elseif($attr[OW_RequestHandler::ATTRS_KEY_CTRL]==="ADMIN_CTRL_PagesEditLocal") {
            if(!isset($attr['params']['id'])){
                return;
            }
            $entityType = self::PAGE_ENTITY_TYPE;
            $entityId = $attr['params']['id'];
            $enData= $this->findEnDataByEntityIdAndType($entityId,$entityType);
            if(isset($enData)){
                $enJsonData= json_decode($enData->entityData,true);
                if(isset($enJsonData[$fieldEnName])){
                    return $enJsonData[$fieldEnName];
                }
            }
        }elseif($attr[OW_RequestHandler::ATTRS_KEY_CTRL]==="FRMCONTACTUS_CTRL_Admin") {
            if(!isset($attr['params']['sectionId'])){
                return;
            }
            $entityType = self::CONTACTUS_ENTITY_TYPE;
            $entityId =1;
            $enData= $this->findEnDataByEntityIdAndType($entityId,$entityType);
            if(isset($enData)){
                $enJsonData= json_decode($enData->entityData,true);
                if(isset($enJsonData[$fieldEnName])){
                    return $enJsonData[$fieldEnName];
                }
            }
        }
    }

    public function getFaMultilingualEntityData ($attr,$fieldFaName){
        if($attr[OW_RequestHandler::ATTRS_KEY_CTRL]==="FRMNEWS_CTRL_Save" || $attr[OW_RequestHandler::ATTRS_KEY_CTRL]==="FRMNEWS_MCTRL_Save") {
            if(!isset($attr['params']['id'])){
                return;
            }
            $entityType = self::NEWS_ENTITY_TYPE;
            $entityId = $attr['params']['id'];
            $faData= $this->findFaDataByEntityIdAndType($entityId,$entityType);
            if(isset($faData)){
                $faJsonData= json_decode($faData->entityData,true);
                if(isset($faJsonData[$fieldFaName])){
                    $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' =>  $faJsonData[$fieldFaName])));
                    if(isset($stringRenderer->getData()['string'])){
                        $faJsonData[$fieldFaName] = $stringRenderer->getData()['string'];
                    }
                    return $faJsonData[$fieldFaName];
                }
            }
        }elseif($attr[OW_RequestHandler::ATTRS_KEY_CTRL]==="ADMIN_CTRL_PagesEditLocal") {
            if(!isset($attr['params']['id'])){
                return;
            }
            $entityType = self::PAGE_ENTITY_TYPE;
            $entityId = $attr['params']['id'];
            $faData= $this->findFaDataByEntityIdAndType($entityId,$entityType);
            if(isset($faData)){
                $faJsonData= json_decode($faData->entityData,true);
                if(isset($faJsonData[$fieldFaName])){
                    return $faJsonData[$fieldFaName];
                }
            }
        }elseif($attr[OW_RequestHandler::ATTRS_KEY_CTRL]==="FRMCONTACTUS_CTRL_Admin") {
            if(!isset($attr['params']['sectionId'])){
                return;
            }
            $entityType = self::CONTACTUS_ENTITY_TYPE;
            $entityId =1;
            $faData= $this->findFaDataByEntityIdAndType($entityId,$entityType);
            if(isset($faData)){
                $faJsonData= json_decode($faData->entityData,true);
                if(isset($faJsonData[$fieldFaName])){
                    return $faJsonData[$fieldFaName];
                }
            }
        }
    }

    public function showDataInMultilingual(OW_Event $event)
    {
        $tagLang = BOL_LanguageService::getInstance()->getCurrent()->getTag();
        if($tagLang!=='fa-IR' && $tagLang!=='en'){
            return;
        }
        $params = $event->getParams();
        if(isset($params['display']) && isset($params['entityType']))
        {
            $entityType = $params['entityType'];
            $display = $params['display'];
            /*
             * check english data for news
             */
            if($entityType==self::NEWS_ENTITY_TYPE)
            {
                $this->showNewsMultiData($display,$params,$entityType,$event);
            }else if($entityType==self::PAGE_ENTITY_TYPE)
            {
                $this->showStaticPageMultiData($display,$params,$entityType,$event);
            }else if($entityType==self::CONTACTUS_ENTITY_TYPE)
            {
                $this->showContactUsMultiData($display,$params,$entityType,$event);
            }
        }
    }

    public function showContactUsMultiData($display,$params,$entityType,&$event)
    {
        $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
        $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
        $tagLang = BOL_LanguageService::getInstance()->getCurrent()->getTag();
        if($display=='showAdminComment')
        {
            if($tagLang==='fa-IR')
            {
                /*
                 * entity Id is set constant 1 for admin comment
                 */
                $multiData = $this->findFaDataByEntityIdAndType(1, $entityType);
                $multiPostfix = $farsiPostfixLabel;
            } else if($tagLang==='en')
            {
                $multiData = $this->findEnDataByEntityIdAndType(1, $entityType);
                $multiPostfix = $englishPostfixLabel;
            }
            if($multiData!=null) {
                $multiJsonDataArr = json_decode($multiData->entityData, true);
                if (isset($multiJsonDataArr)) {
                    $event->setData(array('multiData' => $multiJsonDataArr['comment' . $multiPostfix]));
                }
            }
        }
    }
    public function showStaticPageMultiData($display,&$params,$entityType,&$event)
    {
        $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
        $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
        $tagLang = BOL_LanguageService::getInstance()->getCurrent()->getTag();
        if($display=='Content' && isset($params['pageController']) &&  isset($params['entityId']))
        {
            $pageController = $params['pageController'];
            $entityId = $params['entityId'];
            if($tagLang==='fa-IR')
            {
                $multiData = $this->findFaDataByEntityIdAndType($entityId, $entityType);
                $multiPostfix = $farsiPostfixLabel;
            } else if($tagLang==='en')
            {
                $multiData = $this->findEnDataByEntityIdAndType($entityId, $entityType);
                $multiPostfix = $englishPostfixLabel;
            }
            if(isset($multiData) && isset($multiData->entityData)) {
                $multiJsonDataArr = json_decode($multiData->entityData, true);
                $pageController->assign('content', $multiJsonDataArr['content' . $multiPostfix]);
                $pageController->setPageHeading($multiJsonDataArr['title' . $multiPostfix]);
                $pageController->setPageTitle($multiJsonDataArr['title' . $multiPostfix]);
                OW::getDocument()->setDescription($multiJsonDataArr['meta_desc' . $multiPostfix]);
                OW::getDocument()->setKeywords($multiJsonDataArr['meta_keywords' . $multiPostfix]);
                $pageController->documentKey = $multiJsonDataArr['name' . $multiPostfix] != null ? $multiJsonDataArr['name' . $multiPostfix] : $pageController->documentKey;
            }
        }
    }

    public function showNewsMultiData($display,&$params,$entityType,&$event){
        $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
        $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
        $tagLang = BOL_LanguageService::getInstance()->getCurrent()->getTag();
        if($display=='list' && isset($params['list']))
        {
            $forWidget = false;
            $multiItemDataCount=0;
            $multiItemData=array();
            if(isset($params['forWidget'])){
                $forWidget=true;
            }
            $list = $params['list'];
            foreach ($list as &$item)
            {
                if($forWidget)
                {
                    $dto =$item;
                }else {
                    $dto = &$item['dto'];
                }
                $unchangedDto=$dto;
                if($tagLang==='en')
                {
                    $multiData = $this->findEnDataByEntityIdAndType($dto->id, $entityType);
                    $multiPostfix = $englishPostfixLabel;
                }else if($tagLang==='fa-IR')
                {
                    $multiData = $this->findFaDataByEntityIdAndType($dto->id, $entityType);
                    $multiPostfix = $farsiPostfixLabel;
                }

                if (!isset($multiData))
                {
                    $multiItemData[]=$item;
                    $multiItemDataCount++;
                    continue;
                }
                else if (isset($multiData)) {
                    $multiJsonDataArr = json_decode($multiData->entityData, true);
                    $isPrimaryFieldFilled=false;
                    foreach ($dto as $key => &$value)
                    {
                        if (isset($multiJsonDataArr[$key . $multiPostfix]))
                        {
                            $multiValue = $multiJsonDataArr[$key . $multiPostfix];
                            /*
                             * check if primary field is filled to this news can be added to news list
                             */
                            if($key=='title' && isset($multiValue) && trim($multiValue)!=''){
                                $isPrimaryFieldFilled=true;
                                $value=$multiValue;
                            }
                            /*
                             * primary value cannot be null or empty
                             */
                             else if($key=='title' && (!isset($multiValue) || trim($multiValue)==''))
                            {
                                $isPrimaryFieldFilled=false;
                            }

                            else if($key=='entry' && isset($multiValue) && trim($multiValue)!='')
                            {
                                $value = htmlspecialchars_decode($multiValue);
                            }
                        }
                    }
                    if($isPrimaryFieldFilled) {
                        $multiItemData[] = $item;
                        $multiItemDataCount++;
                    }
                    else{
                        /*
                         * return to unchanged data
                         */
                        $dto = $unchangedDto;
                        $multiItemData[] = $item;
                        $multiItemDataCount++;
                    }
                }
            }


            $event->setData(array('multiData' => $multiItemData,'multiDataCount'=>$multiItemDataCount ));
        }
        elseif($display=='view' && isset($params['entity']))
        {
            $entity = $params['entity'];
            $enMultiData = $this->findEnDataByEntityIdAndType($entity->id, $entityType);
            $faMultiData = $this->findFaDataByEntityIdAndType($entity->id, $entityType);

            /*
             * this news is Farsi originaly but not existed in multilingual tdata table, perhaps It's created before frmmultilingualsupport installed.
             */
            if (!isset($faMultiData) && $tagLang==='fa-IR' && $this->detectPersianCharacter($entity->title))
            {
                return;
            }
            /*
            * this news is English originaly but not existed in multilingual tdata table, perhaps It's created before frmmultilingualsupport installed.
            */
            if (!isset($enMultiData) && $tagLang==='en' && !$this->detectPersianCharacter($entity->title))
            {
                return;
            }
            if($tagLang==='fa-IR'){
                $this->data_purging($faMultiData, $enMultiData, $farsiPostfixLabel, $englishPostfixLabel, $entity);
            }else{
                $this->data_purging( $enMultiData, $faMultiData, $englishPostfixLabel,$farsiPostfixLabel, $entity);
            }
        }
    }

    public function data_purging($firstMultiData, $secondMultiData, $firstMultiPostfix,$secondMultiPostfix, $entity){
        $multiJsonDataArrFirst = array();
        $multiJsonDataArrSecond = array();
        if(isset($firstMultiData)){
            $multiJsonDataArrFirst = json_decode($firstMultiData->entityData, true);
        }
        if(isset($secondMultiData)){
            $multiJsonDataArrSecond = json_decode($secondMultiData->entityData, true);
        }
        foreach ($entity as $key => &$value) {
            if (isset($multiJsonDataArrFirst[$key . $firstMultiPostfix])) {
                $value = $multiJsonDataArrFirst[$key . $firstMultiPostfix];
                if($value===""){
                    $value = $multiJsonDataArrSecond[$key . $secondMultiPostfix];
                }
                if($key=='entry')
                {
                    $value = htmlspecialchars_decode($value);
                }
            }
        }
    }

    public function showStaticPageNameInMultilingual(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['menuItem'])){
            $menuItem = $params['menuItem'];
        }
        if(isset($menuItem) && strpos($menuItem->getKey(), 'page_')!==0)
        {
            return;
        }
        $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
        $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
        $tagLang = BOL_LanguageService::getInstance()->getCurrent()->getTag();
        if($tagLang!=='en' && $tagLang!=='fa-IR'){
            return;
        }
        if (isset($params['entityId']) && isset($params['menuItem']))
        {
            $menuItem = $params['menuItem'];
            $entityType = self::PAGE_ENTITY_TYPE;
            if($tagLang==='en') {
                $multiData = $this->findEnDataByEntityIdAndType($params['entityId'], $entityType);
                $multiPostfixLabel = $englishPostfixLabel;
            }else if($tagLang==='fa-IR') {
                $multiData = $this->findFaDataByEntityIdAndType($params['entityId'], $entityType);
                $multiPostfixLabel = $farsiPostfixLabel;
            }
            if (isset($multiData))
            {
                $multiJsonDataArr = json_decode($multiData->entityData, true);
                if (isset($multiJsonDataArr['name' . $multiPostfixLabel])) {
                    $menuItem->setLabel($multiJsonDataArr['name' . $multiPostfixLabel]);
                }
            }
        }else if ( isset($params['menuItems']))
        {
            $menuItems = $params['menuItems'];
            $entityType = self::PAGE_ENTITY_TYPE;
            foreach ($menuItems as &$menuItem)
            {
                if($tagLang==='en')
                {
                    $multiData = $this->findEnDataByEntityIdAndType($menuItem['id'], $entityType);
                    $multiPostfixLabel = $englishPostfixLabel;
                }else if($tagLang==='fa-IR')
                {
                    $multiData = $this->findFaDataByEntityIdAndType($menuItem['id'], $entityType);
                    $multiPostfixLabel = $farsiPostfixLabel;
                }
                if (isset($multiData)) {
                    $multiJsonDataArr = json_decode($multiData->entityData, true);
                    $menuItem['enLabel']=$multiJsonDataArr['name' . $multiPostfixLabel];
                }
            }
            $event->setData(array('menuItems'=>$menuItems));
        }
    }

    public function detectPersianCharacter($value)
    {
        if(!is_array($value))
        {
            if (preg_match('/[آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهی]/', $value)) {
                return true;
            }
            return false;
        }
        return false;
    }


    public function createMultilingualFieldWidgetPage(OW_Event $event)
    {
        $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
        $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
        $tagLang = BOL_LanguageService::getInstance()->getCurrent()->getTag();
        if ($tagLang !== 'en' && $tagLang !== 'fa-IR') {
            return;
        }
        $params = $event->getParams();
        if(isset($params['componentId']) && isset($params['componentSetting']) && isset($params['componentUniqName']))
        {
            $componentSetting = $params['componentSetting'];
            $componentId = $params['componentId'];
            $componentUniqName = $params['componentUniqName'];

            $attr = OW::getRequestHandler()->getHandlerAttributes();
            if ($attr[OW_RequestHandler::ATTRS_KEY_CTRL] == "BASE_CTRL_AjaxComponentAdminPanel" && $attr[OW_RequestHandler::ATTRS_KEY_ACTION] == "processQueue")
            {
                if(isset($componentSetting['content']))
                {
                    $multiContentValue='';
                    $enContentMultiLingualValue = $this->getEnMultiValueofFieldWidgetPage($componentId,'content',$componentUniqName);
                    $faContentMultiLingualValue = $this->getfaMultiValueofFieldWidgetPage($componentId,'content',$componentUniqName);

                    $multiTitleValue='';
                    $enTitleMultiLingualValue = $this->getEnMultiValueofFieldWidgetPage($componentId,'title',$componentUniqName);
                    $faTitleMultiLingualValue = $this->getfaMultiValueofFieldWidgetPage($componentId,'title',$componentUniqName);

                    $replaceInOriginalTitle='';
                    if($tagLang==='en')
                    {
                        $multiLangPostfix = $farsiPostfixLabel;
                        $lablePrefix = 'fa';
                        $componentSetting['content']['value']=$enContentMultiLingualValue;
                        $multiContentValue=$faContentMultiLingualValue;
                        $multiTitleValue = $faTitleMultiLingualValue;
                        $replaceInOriginalTitle=$enTitleMultiLingualValue;

                    }else if($tagLang==='fa-IR')
                    {
                        $multiLangPostfix = $englishPostfixLabel;
                        $lablePrefix = 'en';
                        $componentSetting['content']['value']=$faContentMultiLingualValue;
                        $multiContentValue=$enContentMultiLingualValue;
                        $multiTitleValue = $enTitleMultiLingualValue;
                        $replaceInOriginalTitle=$faTitleMultiLingualValue;
                    }
                    $multiName = 'content'.$multiLangPostfix;
                    $componentSetting[$multiName] = array(
                        'presentation' => self::PRESENTATION_TEXTAREA,
                        'label' => OW::getLanguage()->text('frmmultilingualsupport', $lablePrefix.'_custom_html_widget_content_label'),
                        'value' => $multiContentValue
                    );
                    $multiName = 'title'.$multiLangPostfix;
                    $componentSetting[$multiName] = array(
                        'presentation' => self::PRESENTATION_TEXT,
                        'label' => OW::getLanguage()->text('frmmultilingualsupport', $lablePrefix.'_custom_html_widget_title_label'),
                        'value' => $multiTitleValue
                    );
                    $event->setData(array('multiLingualSetting'=>$componentSetting,'replaceInOriginalTitle'=>$replaceInOriginalTitle,'replaceInOriginalContent'=>$componentSetting['content']['value']));
                }else if(isset($componentSetting['all_in_one']))
                {
                    $multiTitleValue='';
                    $enTitleMultiLingualValue = $this->getEnMultiValueofFieldWidgetPage($componentId,'title',$componentUniqName);
                    $faTitleMultiLingualValue = $this->getfaMultiValueofFieldWidgetPage($componentId,'title',$componentUniqName);
                    $replaceInOriginalTitle=null;
                    if($tagLang==='en')
                    {
                        $multiLangPostfix = $farsiPostfixLabel;
                        $lablePrefix = 'fa';
                        $multiTitleValue = $faTitleMultiLingualValue;
                        $replaceInOriginalTitle=$enTitleMultiLingualValue;

                    }else if($tagLang==='fa-IR')
                    {
                        $multiLangPostfix = $englishPostfixLabel;
                        $lablePrefix = 'en';
                        $multiTitleValue = $enTitleMultiLingualValue;
                        $replaceInOriginalTitle=$faTitleMultiLingualValue;
                    }
                    $multiName = 'title'.$multiLangPostfix;
                    $componentSetting[$multiName] = array(
                        'presentation' => self::PRESENTATION_TEXT,
                        'label' => OW::getLanguage()->text('frmmultilingualsupport', $lablePrefix.'_custom_html_widget_title_label'),
                        'value' => $multiTitleValue
                    );
                    $event->setData(array('multiLingualSetting'=>$componentSetting,'replaceInOriginalTitle'=>$replaceInOriginalTitle));
                }
            }
        }
    }

    public function storeMultilingualDataWidgetPage(OW_Event $event)
    {
        $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
        $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
        $enData = array();
        $faData = array();
        $params = $event->getParams();
        $isTitleDefaultSetting = true;
        if(isset($params['componentId']) && isset($params['settings']) && isset($params['componentUniqName']))
        {
            $settings = $params['settings'];
            $componentId = $params['componentId'];
            $componentUniqName = $params['componentUniqName'];
            $componentTitleSetting = BOL_ComponentAdminService::getInstance()->findSettingList($componentUniqName);
            if(!isset($settings['title']) && isset($componentTitleSetting['title']))
            {
                $settings['title'] = $componentTitleSetting['title'];
                $isTitleDefaultSetting=false;
            }
            $entityType=self::WIDGET_PAGE_ENTITY_TYPE;
            foreach($settings as $key => $value)
            {
                if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='fa-IR')
                {
                    if (strpos($key, $englishPostfixLabel) !== false && isset($value))
                    {
                        $length = strpos($key, $englishPostfixLabel);
                        $originalKey = substr($key, 0, $length);
                        $enData[$key] = $value;
                        $faData[$originalKey . $farsiPostfixLabel] = $settings[$originalKey];
                        unset($settings[$key]);
                    }
                }else if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='en')
                {
                    if (strpos($key, $farsiPostfixLabel) !== false && isset($value)) {
                        $length = strpos($key, $farsiPostfixLabel);
                        $originalKey = substr($key, 0, $length);

                        $faData[$key] = $value;
                        $enData[$originalKey . $englishPostfixLabel] = $settings[$originalKey];
                        unset($settings[$key]);
                    }
                }
            }
            $enJsonData=json_encode($enData);
            $faJsonData=json_encode($faData);
            $this->multilingualDao->saveData($componentId,$componentUniqName,'en',$enJsonData);
            $this->multilingualDao->saveData($componentId,$componentUniqName,'fa-IR',$faJsonData);
            if(!$isTitleDefaultSetting) {
                unset($settings['title']);
            }
            $event->setData(array('orginalSettings'=>$settings));
        }
    }

    public function getEnMultiValueofFieldWidgetPage($componentId,$fieldName,$componentUniqName)
    {
        $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
        $entityType=self::WIDGET_PAGE_ENTITY_TYPE;
        $value=null;
        $multiData = $this->findEnDataByEntityIdAndType($componentId, $componentUniqName);
        if (isset($multiData)) {
            $multiJsonDataArr = json_decode($multiData->entityData, true);
            if(isset($multiJsonDataArr[$fieldName.$englishPostfixLabel])){
                $value = $multiJsonDataArr[$fieldName.$englishPostfixLabel];
            }
        }
        return $value;
    }

    public function getFaMultiValueofFieldWidgetPage($componentId,$fieldName,$componentUniqName)
    {
        $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
        $entityType=self::WIDGET_PAGE_ENTITY_TYPE;
        $value=null;
        $multiData = $this->findFaDataByEntityIdAndType($componentId, $componentUniqName);
        if (isset($multiData)) {
            $multiJsonDataArr = json_decode($multiData->entityData, true);
            if(isset($multiJsonDataArr[$fieldName.$farsiPostfixLabel])){
                $value = $multiJsonDataArr[$fieldName.$farsiPostfixLabel];
            }
        }
        return $value;
    }

    public function findMultiValueByWidgetUniqueName(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['uniqName']) && strpos($params['uniqName'],'admin-')!==0)
        {
            return;
        }
        $farsiPostfixLabel = self::FARSI_FILD_LABEL_POSTFIX;
        $englishPostfixLabel = self::ENGLISH_FILD_LABEL_POSTFIX;
        if(isset($params['settings']) && !empty($params['settings']) && isset($params['uniqName']))
        {
            $settings = $params['settings'];
            $component = BOL_ComponentPlaceDao::getInstance()->findByUniqName($params['uniqName']);
            if (!isset($component))
            {
                return;
            }
            if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='fa-IR')
            {
                $multiLangPostfix = $farsiPostfixLabel;
                $multiData = $this->findFaDataByEntityIdAndType($component->componentId, $params['uniqName']);
            }else if(BOL_LanguageService::getInstance()->getCurrent()->getTag()==='en')
            {
                $multiLangPostfix = $englishPostfixLabel;
                $multiData = $this->findenDataByEntityIdAndType($component->componentId, $params['uniqName']);
            }

            if(!isset($multiData))
            {
                return;
            }

            $multiJsonDataArr = json_decode($multiData->entityData, true);
            foreach($settings as $key => $value)
            {
                if (isset($multiJsonDataArr[$key . $multiLangPostfix]))
                {
                    $settings[$key] = $multiJsonDataArr[$key . $multiLangPostfix];
                }
            }
            $event->setData(array('multiSettings'=>$settings));
        }
    }
}