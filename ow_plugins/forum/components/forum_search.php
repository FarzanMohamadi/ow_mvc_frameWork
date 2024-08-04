<?php
/**
 * Forum search form class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.components
 * @since 1.0
 */
class FORUM_CMP_ForumSearch extends OW_Component
{
    private $scope;
    
    public function __construct( array $params )
    {
        parent::__construct();
        
        $this->scope = $params['scope'];
        
        $value = isset($params['token']) ? trim(htmlspecialchars($params['token'])) : null;
        $userValue = isset($params['userToken']) ? trim(htmlspecialchars($params['userToken'])) : null;
        
        $inputParams = array(
            'type' => 'text',
            'class' => !mb_strlen($value) ? 'invitation' : '',
            'value' => mb_strlen($value) ? $value : null,
            'id' => UTIL_HtmlTag::generateAutoId('input'),
            'placeholder' => OW::getLanguage()->text('forum','search_invitation_topic')
        );
        $this->assign('input', UTIL_HtmlTag::generateTag('input', $inputParams));
        
        $userInputParams = array(
            'type' => 'text',
            'value' => $userValue,
            'id' => $inputParams['id'] . '_user',
            'placeholder' => OW::getLanguage()->text('forum','enter_username')
        );
        $this->assign('userInput', UTIL_HtmlTag::generateTag('input', $userInputParams));
        $this->assign('themeUrl', OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl());
        $this->assign('userToken', $userValue);
        $this->assign('advancedSearchURL', OW::getRouter()->urlForRoute('forum_advanced_search'));

        switch ( $this->scope )
        {
            case 'topic':
                $location = json_encode(OW::getRouter()->urlForRoute('forum_search_topic', array('topicId' => $params['topicId'])));
                break;
                
            case 'group':
                $location = json_encode(OW::getRouter()->urlForRoute('forum_search_group', array('groupId' => $params['groupId'])));
                break;

            case 'section':
                $location = json_encode(OW::getRouter()->urlForRoute('forum_search_section', array('sectionId' => $params['sectionId'])));
                break;

            default:
                $location = json_encode(OW::getRouter()->urlForRoute('forum_search'));
                break;
        }
        
        $userInvitation = OW::getLanguage()->text('forum', 'enter_username');

        $script =
        'var invitation = '.json_encode(OW::getLanguage()->text('forum','search_invitation_topic')).';
        var input = '.json_encode($inputParams['id']).';
        var userInvitation = '.json_encode($userInvitation).';
        var userInput = '.json_encode($userInputParams['id']).';

        $("#" + userInput).focus(function() {
            if ( $(this).val() == userInvitation ) {
                $(this).removeClass("invitation").val("");
            }
        });
        $("#" + userInput).blur(function() {
            if ( $(this).val() == "" ) {
                $(this).addClass("invitation").val(userInvitation);
            }
        });
        ';
        
        if ( !mb_strlen($value) )
        {
            $script .=
            '$("#" + input).focus(function() {
                if ( $(this).val() == invitation ) {
                    $(this).removeClass("invitation").val("");
                }
            });
            $("#" + input).blur(function() {
                if ( $(this).val() == "" ) {
                    $(this).addClass("invitation").val(invitation);
                }
            });
            ';
        }

        $script .= 
        'var $form = $("form#forum_search");
        $(".ow_miniic_delete", $form).click(function() {
            $(".forum_search_tag_input", $form).css({visibility : "hidden", height: "0px", padding: "0px"}).hide();
            $(".add_filter", $form).show();
            $("#forum_search_cont").removeClass("forum_search_inputs");
            $("#" + userInput).val("").removeClass("invitation");
        });

        $("#" + userInput).addClass("invitation").val(userInvitation);
        
         $(document).on("click", "span.submit_forum_search_button", function(e){
            $(e.target).closest("form").submit();
         });   

        $("#'.$inputParams['id'].', #'.$userInputParams['id'].'").keydown(function(e){
            if (e.keyCode == 13) {
                $(this).parents("form").submit();
                return false;
            }
        });
            
        $form.submit(function() {
            var value = $("#" + input).val();
            var userValue = $("#" + userInput).val();
            if(value.length<=0)
            {
               $("#forum-topic-search-empty-input-error").fadeIn();
                setTimeout(function(){
                    $("#forum-topic-search-empty-input-error").fadeOut();
                }, 2500);
                event.preventDefault();
                return false;
            }

            if ( value == invitation && !userValue.length || userValue == userInvitation && !value.length ) {
                return false;
            }

            if ( value == invitation ) {
                value = ""; $("#" + input).val(value);
            }

            if ( userValue == userInvitation ) {
                userValue = ""; $("#" + userInput).val(userValue);
            }

            var search = encodeURIComponent(value);
            userSearch = encodeURIComponent(userValue);
            document.location.href = '.$location.' + "?"
                + (search.length ? "&q=" + search : "")
                + (userSearch.length ? "&u=" + userSearch : "");

            return false;
        });
        ';

        OW::getDocument()->addOnloadScript($script);
    }
}