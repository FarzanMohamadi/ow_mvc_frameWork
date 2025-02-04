<?php
/**
 * User: Hamed Tahmooresi
 * Date: 1/3/2016
 * Time: 3:17 PM
 */
class FRMEventManager
{
    /*
     * goal: Overriding term's field in join form.
     * goal: Password validation should be updated to be a valid password.
     * category: security
     * used by plugins: frmchangepasswordinterval,frmterms
     */
    const ON_RENDER_JOIN_FORM = 'frm.on_render_join_form';
    const ON_AFTER_PASSWORD_UPDATE = 'frm.on_after_password_update';

    const ON_USER_AUTH_FAILED = 'frm.on_user_auth_failed';
    const ON_BEFORE_FORM_SIGNIN_RENDER = 'frm.on_before_form_signin_render';
    const ON_CAPTCHA_VALIDATE_FAILED = 'frm.on_captcha_validate_failed';
    const ON_AFTER_SIGNIN_FORM_CREATED = 'frm.on_after_signin_form_created';

    /*
     * goal: Password value should be checked to be a valid password.
     * category: security
     * used by plugins: frmpasswordstrengthmeter
     */
    const ON_PASSWORD_VALIDATION_IN_JOIN_FORM = 'frm.on_password_validation_in_join_form';

    /*
     * goal: We should check user's password validation before reset password form renderer. If users are invalid, we should redirect them to change password from token that sent to their email.
     * category: security
     * used by plugins: frmchangepasswordinterval
     */
    const ON_BEFORE_RESET_PASSWORD_FORM_RENDERER = 'frm.on_before_reset_password_form_renderer';

    /*
     * goal: Remove data backup using timestamp.
     * category: security
     * parameter: timestamp
     * used by plugins: frmdatabackup
     */
    const ON_DATA_BACKUP_DELETE = 'frm.on_manage_data_backup_time';
    /**
     * goal: change date format to jalali according to frmjalali setting
     * category: date format plugin
     * used by plugins: frmjalali
     */
    const ON_AFTER_DEFAULT_DATE_VALUE_SET = 'frm.on_after_default_date_value_set';

    /**
     * goal: if date is jalali format change it to gregorian  for validating
     * category: date format plugin
     * used by plugins: frmjalali
     */
    const ON_BEFORE_VALIDATING_FIELD = 'frm.on_before_validating_field';
    /**
     * goal: show date format specially jalali format according to admin settings (exmp : only month , FULL DATE WITH HOUR etc)
     * category: date format plugin
     * used by plugins: frmjalali
     */
    const ON_RENDER_FORMAT_DATE_FIELD = 'frm.on_render_format_date_field';

    /*
     * goal: All removed data backup in created tables.
     * category: security
     * used by plugins: core
     */
    const ON_AFTER_SQL_IMPORT_IN_INSTALLING = 'frm.on.after.sql.import.in.install';

    /*
     * goal: Do anything after installation completed
     * category: installation
     * used by plugins:
     */
    const ON_AFTER_INSTALLATION_COMPLETED = 'frm.on.after.installation.completed';

    /*
     * goal: Do anything before update activity timestamp of user
     * category: user
     * used by plugins: frm security essential
     */
    const ON_BEFORE_UPDATE_ACTIVITY_TIMESTAMP = 'frm.on.before.update.activity.timestamp';

    /*
     *goal: render each video file properly instead of showing static thumb picture in main page
     * category: video plugin
     * used by plugins: video
     */
    const ON_AFTER_VIDEO_RENDERED = 'frm.on_after_video_rendered';

    /*
     *goal: Add parent email field in registration form
     * category: kids control
     * used by plugins: frmkidscontrol
     */
    const ON_BEFORE_JOIN_FORM_RENDER = 'frm.on_before_join_form_render';


    /*
     * goal: checking privacy of object
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_OBJECT_RENDERER = 'frm.on.before.object.renderer';

    /*
     * goal: add privacy field into status form
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_UPDATE_STATUS_FORM_RENDERER = 'frm.on.before.update.status.form.renderer';

    /*
     * goal: assign privacy attribute into status component
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_AFTER_UPDATE_STATUS_FORM_RENDERER = 'frm.on.after.update.status.form.renderer';

    /*
     * goal: add privacy field into photo upload form
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_PHOTO_UPLOAD_FORM_RENDERER = 'frm.on.before.photo.upload.form.renderer';

    /*
     * goal: assign privacy attribute into photo component
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_PHOTO_UPLOAD_COMPONENT_RENDERER = 'frm.on.before.photo.upload.component.renderer';

    /*
     * goal: add privacy field into video upload form
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_VIDEO_UPLOAD_FORM_RENDERER = 'frm.on.before.video.upload.form.renderer';

    /*
     * goal: assign privacy attribute into video component
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_VIDEO_UPLOAD_COMPONENT_RENDERER = 'frm.on.before.video.upload.component.renderer';

    /*
     * goal: checking privacy
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_PRIVACY_CHECK = 'frm.on.before.privacy.value.check';

    /*
     * goal: decide to show update status form
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_UPDATE_STATUS_FORM_CREATE = 'frm.on.before.update.status.form.create';

    /*
     * goal: decide to show update status form
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_UPDATE_STATUS_FORM_CREATE_IN_PROFILE = 'frm.on.before.update.status.form.create.in.profile';

    /*
     * goal: decide to create feed from activity
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_FEED_ACTIVITY_CREATE = 'frm.on.before.feed.activity.create';

    /*
     * goal: change privacy in query
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_QUERY_FEED_CREATE = 'frm.on.query.feed.create';

    /*
     * goal: decide to show feed to viewer
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_FEED_ITEM_RENDERER = 'frm.on.before.feed.item.renderer';

    /*
     * goal: Show feed privacy
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_FEED_ITEM_RENDERER = 'frm.on.feed.item.renderer';

    /*
     * goal: Show album privacy
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_ALBUMS_RENDERER = 'frm.on.before.albums.renderer';

    /*
     * goal: Show album privacy
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_ALBUM_INFO_RENDERER = 'frm.on.before.album.info.renderer';
    /*
    *goal: render each video file properly instead of showing static thumb picture in main page
    * category: video plugin
    * used by plugins: video
    */
    const ON_BEFORE_FEED_RENDERED = 'frm.on_before_feed_rendered';

    /*
     * goal: checking the last photo of album in moving photos
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_AFTER_LAST_PHOTO_FEED_REMOVED = 'frm.on.after.last.photo.feed.removed';

    /*
     * goal: change name of album
     * category: privacy
     * used by plugin: frm-security-essentials
     */
    const ON_BEFORE_ALBUM_CREATE_FOR_STATUS_UPDATE = 'frm.on.before.album.create.for.status.update';

    /*
     * goal: replace base url of item in render
     * category: privacy
     * used by plugin: base
     */
    const ON_AFTER_NEWSFEED_STATUS_STRING_READ = 'frm.on.after.newsfeed.status.string.read';

    /*
     * goal: replace base url of item in writing to db
     * category: privacy
     * used by plugin: base
     */
    const ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE = 'frm.on.before.newsfeed.status.string.write';


    /*
     * goal: replace base url of item in render
     * category: privacy
     * used by plugin: base
     */
    const ON_AFTER_NOTIFICATION_STRING_READ = 'frm.on.after.notification.string.read';

    /*
     * goal: replace base url of item in writing to db
     * category: privacy
     * used by plugin: base
     */
    const ON_BEFORE_NOTIFICATION_STRING_WRITE = 'frm.on.before.notification.string.write';

    /*
     * goal: add item to video
     * category: video
     * used by plugin: frmvideoplus
     */
    const ADD_LIST_TYPE_TO_VIDEO = 'frm.add.list.type.to.video';

    /*
     * goal: return list of video for some listType
     * category: video
     * used by plugin: frmvideoplus
     */
    const GET_RESULT_FOR_LIST_ITEM_VIDEO = 'frm.get.result.for.list.item.video';

    /*
    * goal: return number of videos for some listType
    * category: video
    * used by plugin: frmvideoplus
    */
    const GET_RESULT_FOR_COUNT_ITEM_VIDEO = 'frm.get.result.for.count.item.video';

    /*
     * goal: set title of page
     * category: video
     * used by plugin: frmvideoplus
     */
    const SET_TILE_HEADER_LIST_ITEM_VIDEO = 'frm.set.title.header.list.item.video';
    
    
        /*
     * goal: add aparat video provider to video providers
     * category: video
     * used by plugin: frmaparatsupport
     */
    const ON_AFTER_VIDEO_PROVIDERS_DEFINED = 'frm.on.after.video.providers.defined';

    /*
    * goal: add privacy field to questions data
    * category: privacy
    * used by plugin: frm-security-essentials
    */
    const ON_BEFORE_QUESTIONS_DATA_PROFILE_RENDER = 'frm.on.before.questions.data.profile.render';


    /*
    * goal: add extra validation on video code
    * category: video
    * used by plugin: frmaparatsupport
    */
    const ON_VIDEO_URL_VALIDATION = 'frm.on.video.url.validation';


    /*
    * goal: add extra validation on video code
    * category: video
    * used by plugin: frmaparatsupport
    */
    const ON_BEFORE_VIDEO_ADD = 'frm.on.before.video.add';

    /*
    * goal: add verify later text into email verify page
    * category: login
    * used by plugin: frm-security-essentials
    */
    const ON_BEFORE_EMAIL_VERIFY_FORM_RENDER = 'frm.before.email.verify.form.render';

    /*
    * goal: check diff of extensions
    * category: install
    * used by plugin:
    */
    const ON_BEFORE_INSTALL_EXTENSIONS_CHECK = 'frm.on.before.install.extensions.check';

    /*
    * goal: set default cover of album
    * category: photo
    * used by plugin:
    */
    const ON_ALBUM_DEFAULT_COVER_SET = 'frm.on.album.default.cover.set';

    /*
    * goal: set error
    * category: error
    * used by plugin:
    */
    const ON_BEFORE_ERROR_RENDER = 'frm.on.before.error.render';

    /*
    * goal: get passwrod requirement password strength label
    * category: security
    * used by plugin: frmpasswordstrengthmeter
    */
    const GET_PASSWORD_REQUIREMENT_PASSWORD_STRENGTH_INFORMATION = 'frm.get.password.requirement.password.strength.information';

    /*
    * goal: alter query must executed for backup tables
    * category: security
    * used by plugin: core
    */
    const BEFORE_ALTER_QUERY_EXECUTED = 'frm.before.alter.query.executed';

    /*
    * goal: after query executed
    * category: performance
    * used by plugin: core
    */
    const AFTER_QUERY_EXECUTED = 'frm.after.query.executed';

    /*
    * goal: after user query executed
    * category: performance
    * used by plugin: core
    */
    const AFTER_USER_QUERY_EXECUTED = 'frm.after.user.query.executed';

    /*
    * goal: set default value for privacy items
    * category: security
    * used by plugin: frm-security-essentials
    */
    const ON_BEFORE_PRIVACY_ITEM_ADD = 'frm.on.before.privacy.item.add';

    /*
    * goal: checking load more functionality
    * category: performance
    * used by plugin:
    */
    const ON_BEFORE_ACTIONS_LIST_RETURN = 'frm.on.before.actions.list.return';

    /*
    * goal: change date format from gregorian to jalali for frmnews plugin and blog
    * category: core
    * used by plugin: frmjalali
    */
    const CHANGE_DATE_FORMAT_TO_JALALI_FOR_BLOG_AND_NEWS = 'frm.change.date.format.to.jalali.for.blog.and.news';

    /*
    * goal: change date format from gregorian to jalali for frmnews plugin and blog
    * category: core
    * used by plugin: frmjalali
    */
    const CHANGE_DATE_FORMAT_TO_GREGORIAN = 'frm.change.date.format.to.gregorian';
    
    /*
    * goal: change birthday range from gregorian format to jalali
    * category: core
    * used by plugin: frmjalali
    */
    const SET_BIRTHDAY_RANGE_TO_JALALI = 'frm.set.birthday.range.to.jalali';

    /*
    * goal: change date range from gregorian format to jalali
    * category: core
    * used by plugin: frmjalali
    */
    const CHANGE_DATE_RANGE_TO_JALALI = 'frm.change.date.range.to.jalali';

    /*
    * goal: show user's information to other users or not
    * category: privacy
    * used by plugin: frm-security-essentials
    */
    const ON_BEFORE_USER_INFORMATION_RENDER = 'frm.on.before.user.information.render';
    
    /*
   * goal: calculate maximum day of a jalali month
   * category: core
   * used by plugin: frmjalali
   */
    const CALCULATE_JALALI_MONTH_LAST_DAY = 'frm.calculate.jalali.month.last.day;';

    /*
    * goal: check if image upload form rendered to upload image ina rich textbox change template to blank.htm
    * category: core
    * used by plugin:
    */
    const CHECK_MASTER_PAGE_BLANK_HTML_FOR_UPLOAD_IMAGE_FORM = 'frm.check.master.page.blank.html.for.upload.image.form';

    /*
    * goal: Decide to show currency field in setting or not
    * category: usability
    * used by plugin:
    */
    const ON_BEFORE_CURRENCY_FIELD_APPEAR = 'frm.on.before.currency.field.appear';

    /*
     * correct sentences contain multiple language alignment
     */
    const CORRECT_MULTIPLE_LANGUAGE_SENTENCE_ALIGNMENT = 'frm.correct.multiple.language.sentence.alignment';

    /*
    * goal: validate html content
    * category: security
    * used by plugin:
    */
    const ON_VALIDATE_HTML_CONTENT = 'frm.on.validate.html.content';

    /*
    * goal: check tidy extension is enabled
    * category: security
    * used by plugin:
    */
    const BEFORE_ALLOW_CUSTOMIZATION_CHANGED = 'frm.on.before.allow.customization.changed';

    /*
    * goal: disabled allow customization when tidy extension is not enabled
    * category: security
    * used by plugin:
    */
    const BEFORE_CUSTOMIZATION_PAGE_RENDERER = 'frm.on.before.customization.page.renderer';

    /*
    * goal: correct display of partial hafspace code do to truncated sentences
    * category: core
    * used by plugin:
    */
    const PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION = 'frm.partial.half.space.code.display.correction';
    /*
    * goal: disable index_status checkbox in admin in newsfeed configuration
    * category: newsfeed
    * used by plugin: frm-security-essentials
    */
    const ON_BEFORE_INDEX_STATUS_ENABLED= 'frm.on.before.index.status.enabled';
    /*
    * goal: add class with name: ow_required_star for required fields
    * category: base
    * used by plugin:
    */
    const DISTINGUISH_REQUIRED_FIELD= 'frm.distinguish.required.field';
    /*
     * goal: add item to photo
     * category: photo
     * used by plugin: frmphotoplus
     */
    const ADD_LIST_TYPE_TO_PHOTO = 'frm.add.list.type.to.photo';
    /*
     * goal: return list of photo for some listType
     * category: photo
     * used by plugin: frmphotoplus
     */
    const GET_RESULT_FOR_LIST_ITEM_PHOTO = 'frm.get.result.for.list.item.photo';
    /*
     * goal: set title of page
     * category: photo
     * used by plugin: frmphotoplus
     */
    const SET_TILE_HEADER_LIST_ITEM_PHOTO = 'frm.set.title.header.list.item.photo';
    /*
     * goal: add friend_photo to valid list
     * category: photo
     * used by plugin: frmphotoplus
     */
    const GET_VALID_LIST_FOR_PHOTO = 'frm.get.valid.list.for.photo';

    /*
    * goal: check if config of frm-security-essentials exist and return it's value
    * category: base
    * used by plugin: frm-security-essentials
    */
    const CHECK_VIEW_USER_COMMENT_WIDGET_STATUS= 'frm.check.view.user.comment.widget.status';
    /*
     * goal: prevent to show plugins that don't have mobile version
     * category: mobile
     * used by plugin: newsfeed
     */
    const ON_AFTER_GET_TPL_DATA = 'frm.on.after.get.tpl.data';
    /*
    * goal: create privacy field
    * category: security
    * used by plugin: frm-security-essentials
    */
    const ON_BEFORE_CREATE_FORM_USING_FIELD_PRIVACY = 'frm.on.before.create.form.using.field.privacy';

    /*
     * goal: show privacy button
     * category: mobile
     * used by plugin: video
     */
    const ON_BEFORE_VIDEO_RENDER = 'on.before.video.render';

    /*
     * goal: show privacy button
     * category: mobile
     * used by plugin: photo
     */
    const ON_BEFORE_PHOTO_RENDER = 'on.before.photo.render';
    /*
     * goal: check if mobile version is used
     * category: mobile
     * used by plugin: base
     */
    const IS_MOBILE_VERSION = 'frm.is.mobile.version';
    /*
    * goal: to add audio massages in forum
    * category: audio
    * used by plugin: audio & forum
    */
    const ON_BEFORE_FORUM_POST_FORM_CREATE = 'frm.on.before.forum.add.post.form.create';

    /*
    * goal: add audio panel to post text
    * category: audio
    * used by plugin: audio & forum
    */
    const ON_BEFORE_FORUM_POST_RENDER = 'frm.on.before.forum.post.create';

    /*
      * goal: add item to event
      * category: event
      * used by plugin: frmeventplus
    */
    const ADD_LIST_TYPE_TO_EVENT = 'frm.add.list.type.to.event';
    /*
      * goal: add item to event
      * category: event
      * used by plugin: frmeventplus
    */
    const GET_RESULT_FOR_LIST_ITEM_EVENT = 'frm.get.result.for.list.item.event';
    /*
      * goal: add item to event
      * category: event
      * used by plugin: frmeventplus
    */
    const SET_TITLE_HEADER_LIST_ITEM_EVENT = 'frm.set.title.header.list.item.event';
    /*
      * goal: add item to event
      * category: event
      * used by plugin: frmeventplus
    */
    const GET_VALID_LIST_FOR_EVENT = 'frm.get.valid.list.for.event';
    /*
      * goal: add item to event
      * category: event
      * used by plugin: frmeventplus
    */
    const ADD_EVENT_FILTER_FORM = 'frm.add.event.filter.form';
    /*
      * goal: add leave button to event
      * category: event
      * used by plugin: frmeventplus
    */
    const ADD_LEAVE_BUTTON = 'frm.add.leave.button';
    /*
      * goal: addcategory filter element
      * category: event
      * used by plugin: frmeventplus
    */
    const ADD_CATEGORY_FILTER_ELEMENT = 'frm.add.category.filter.element';
    /*
      * goal: get selected category for event
      * category: event
      * used by plugin: frmeventplus
    */
    const GET_EVENT_SELECTED_CATEGORY_ID = 'frm.get.event.selected.category.id';
    /*
      * goal: add category to event
      * category: event
      * used by plugin: frmeventplus
    */
    const ADD_CATEGORY_TO_EVENT = 'frm.add_category_to_event';
    /*
      * goal: get event category label
      * category: event
      * used by plugin: frmeventplus
    */
    const GET_EVENT_SELECTED_CATEGORY_LABEL = 'frm.get.event.selected.category.label';

    /*
    * goal: check privacy of forum group
    * category: privacy
    * used by plugin: forum
    */
    const ON_BEFORE_FORUM_SECTIONS_RETURN = 'frm.on.before.forum.sections.return';

    /*
    * goal: search in private sections of forum
    * category: privacy
    * used by plugin: forum
    */
    const ON_BEFORE_FORUM_ADVANCE_SEARCH_QUERY_EXECUTE = 'frm.on.before.forum.advance.search.query.execute';

    /*
    * goal: add privacy condition to query of finding list
    * category: privacy
    * used by plugin: frmsecurityessentials
    */
    const ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE = 'frm.on.before.content.list.query.execute';

    /*
    * goal: check privacy of photo
    * category: privacy
    * used by plugin: photo
    */
    const ON_BEFORE_PHOTO_INIT= 'frm.on.before.photo.init';

    /*
    * goal: add privacy condition to query of finding user feed list
    * category: privacy
    * used by plugin: frmsecurityessentials
    */
    const ON_BEFORE_USER_FEED_LIST_QUERY_EXECUTE = 'frm.on.before.user.feed.list.query.execute';
    /*
    * goal: get group list with filtering
    * category: frmgroupsplus
    * used by plugin: groups
    */
    const GET_RESULT_FOR_LIST_ITEM_GROUP = 'frm.get.result.for.list.item.group';
    /*
    * goal: add filter to group list form
    * category: frmgroupsplus
    * used by plugin: groups
    */
    const ADD_GROUP_FILTER_FORM = 'frm.add.group.filter.form';
    /*
    * goal: create elements for filter form
    * category: frmgroupsplus
    * used by plugin: groups
    */
    const ADD_GROUP_FILTER_ELEMENT = 'frm.add.group.filter.element';
    /*
    * goal: select group category by id
    * category: frmgroupsplus
    * used by plugin: groups
    */
    const GET_GROUP_SELECTED_CATEGORY_ID = 'frm.get.group.selected.category.id';
    /*
    * goal: add category to group
    * category: frmgroupsplus
    * used by plugin: groups
    */
    const ADD_CATEGORY_TO_GROUP = 'frm.add.category.to.group';
    /*
    * goal: aget category label
    * category: frmgroupsplus
    * used by plugin: groups
    */
    const GET_GROUP_SELECTED_CATEGORY_LABEL = 'frm.get.group.selected.category.label';
    /*
    * goal: correct display of partial space code do to truncated sentences
    * category: core
    * used by plugin:
    */
    const PARTIAL_SPACE_CODE_DISPLAY_CORRECTION = 'frm.partial.space.code.display.correction';
    /*
    * goal: delete html entities at the end of truncated sentences
    * used by plugin: notifications
    */
    const HTML_ENTITY_CORRECTION = 'frm.html.entity.correction';
    /*
    * goal: add control kids menu for mobile
    * category: frmcontrollkids
    * used by plugin: frmcontrollkids
    */
    const ON_MOBILE_ADD_ITEM = 'frm.on.mobile.add.item';

    /*
    * goal: support png files
    * category: photo
    * used by plugin: photo
    */
    const ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN = 'frm.on.before.photo.temporary.path.return';
    /*
    * goal: validate uploaded file name
    * category: core
    * used by plugin:
    */
    const VALIDATE_UPLOADED_FILE_NAME = 'frm.validate.uploaded.file.name';

    /*
      * goal: make decision for checking user status
      * category: security
      * used by plugin: frmsecurityessential
    */
    const ON_BEFORE_USER_DISAPPROVE_AFTER_EDIT_PROFILE = 'frm.on.before.user.disapprove_after_edit_profile';

    /*
      * goal: make decision to add image
      * category: security
      * used by plugin: library oembed.php
    */
    const ON_BEFORE_URL_IMAGE_ADD_ON_CHECK_LINK = 'frm.on.before.url.image.add.on.check.link';
    /*
      * goal: enable offline chat for desktop version
      * category: mailbox
    */
    const ENABLE_DESKTOP_OFFLINE_CHAT = 'frm.enable.desktop.offline.chat';
    /*
      * goal: show friendship status in user list
      * category: mailbox
    */
    const USER_LIST_FRIENDSHIP_STATUS = 'frm.user.list.friendship.status';

    /*
      * goal: Improve performance by checking uri requests
      * category: performance
    */
    const BEFORE_CHECK_URI_REQUEST = 'frm.before.check_uri_request';

    /*
      * goal: Operate before group view render
      * category: ui
    */
    const ON_BEFORE_GROUP_VIEW_RENDER = 'frm.on.before.group.view.render';

    /*
  * goal: Operate before group list view render
  * category: ui
*/
    const ON_BEFORE_GROUP_LIST_VIEW_RENDER = 'frm.on.before.group.view.render';

    /*
      * goal: Operate before news view render in mobile for frmwidgetplus
      * category: ui
    */
    const ON_BEFORE_NEWS_VIEW_RENDER = 'frm.on.before.news.view.render';

    /*
      * goal: update question data timestamp
      * category: security
    */
    const ON_BEFORE_CHECK_USER_STATUS = 'frm.on.before.check.user.status';

    /*
      * goal: secure file url
      * category: security
    */
    const ON_BEFORE_GET_FILE_URL = 'frm.on.before.get.file.url';
    /*
    * goal: Enables changing the sign-in located button above the page
    * category: ui
    * used by plugin: frmsso
    */
    const ON_BEFORE_SIGNIN_BUTTON_ADD = 'frm.on.before.signin.button.add';
    /*
    * goal: Triggers after the widget BASE_CMP_UserViewWidget is constructed.
    * category: ui
    * used by plugin: frmsso
    */
    const ON_AFTER_CHANGE_PASSWORD_WIDGET_ADDED = 'on.after.change.password.widget.added';
    /*
    * goal: Enables changing the sign-in page
    * category: ui
    * used by plugin: frmmobileaccount
    */
    const ON_BEFORE_SIGNIN_PAGE_RENDER = 'frm.on.before.signin.page.render';
    /*
    * goal: Enables changing the join page
    * category: ui
    * used by plugin: frmmobileaccount
    */
    const ON_BEFORE_JOIN_PAGE_RENDER = 'frm.on.before.join.page.render';
    /*
    * goal: Enables changing the sign-in page
    * category: ui
    * used by plugin: frmmobileaccount
    */
    const ON_BEFORE_AVATAR_FIELD_JOIN_ADD = 'frm.on.before.avatar.field.join.add';
    /*
    * goal: check cookie update
    * category: ui
    * used by plugin: frmmobileaccount
    */
    const ON_BEFORE_AUTOLOGIN_COOKIE_UPDATE = 'frm_before_autologin_cookie_update';
  /*
   * goal: find owner id of an action
   * category: ui
   * used by plugin: newsfeed
   */
    const CHECK_OWNER_OF_ACTION_ID= 'frm.check.owner.of.action.id';
    /*
     * goal: redirect in some conditions
     * category: auth
     * used by plugin: frmsso
     */
    const ON_BEFORE_CONTROLLERS_INVOKE = 'frm.on.before.controllers.invoke';
    /*
     * goal: redirect in some conditions in join page
     * category: auth
     * used by plugin: frmsso
     */
    const ON_BEFORE_JOIN_CONTROLLER_START = 'frm.on.join.controller.start';
    /*
     * goal: redirect in some conditions in join page
     * category: auth
     * used by plugin: frmsso
     */
    const ON_BEFORE_SEND_VERIFICATION_EMAIL = 'frm.on.before.send.verification.email';
    /*
    * goal: remove some fields in edit form of profile when frmsso is enabled.
    * category: auth
    * used by plugin: frmsso
    */
    const ON_BEFORE_PROFILE_EDIT_FORM_BUILD = 'frm.on.before.profile.edit.form.build';
    /*
     * goal: edit contents of console item
     * category: core
     */
    const ON_BEFORE_CONSOLE_ITEM_RENDER = 'frm.on.before.console.item.render';
    /*
     * goal: handle rabbitmq item
     * category: core
     */
    const ON_AFTER_RABITMQ_QUEUE_RELEASE = 'frm.rabbitmq.queue_release';
    /*
     * goal: Triggers before rendering user inputs in web pages
     * category: core
     * used by base and plugins: frmhashtag, frmmention, frmemoji
     */
    const ON_BEFORE_RENDER_STRING = 'base.before_render_string';

    /*
     * goal: Triggers to check access to manage users
     * category: core
     * used by base and plugins: frmquestionroles
     */
    const HAS_USER_AUTHORIZE_TO_MANAGE_USERS = 'base.has_user_authorize_to_manage_users';

    /*
     * goal: Triggers to find managers for user
     * category: core
     * used by base and plugins: frmquestionroles
     */
    const FIND_MODERATOR_FOR_USER = 'base.find_moderator_for_user';
}