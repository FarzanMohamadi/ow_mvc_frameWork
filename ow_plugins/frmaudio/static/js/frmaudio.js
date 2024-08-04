/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio.js
 * @since 1.0
 */

var mp3WorkerPath;
var recorderWorkerPath;
var audioTimerInterval;

function CreateAudio(){
    audioFloatBox=OW.ajaxFloatBox('FRMAUDIO_CMP_Audio', {} , {width:700, iconClass: 'ow_ic_add'});
}
function MobileCreateAudio(){
    audioFloatBox=OW.ajaxFloatBox('FRMAUDIO_MCMP_Audio', {} , {width:700, iconClass: 'owm_ic_add'});
}
function addAudioComplete($cmp, $name, $audioData, $audioId){
    $cmp.close();
    audioRemove();
    $('#audio_feed_data').val($audioId);
    $('#audio_feed_name').val($name);
    $('.ow_file_attachment_preview').prepend('<div class="audio_item_player"><audio class="audio_item_player" width="100%" height="38px" controls src="'+$audioData+'" type="audio/mp3"></audio><a class="audio_item_delete" onclick="audioRemove()">'+OW.getLanguageText('frmaudio', 'delete_audio_item')+'</a></div>');
    $('.owm_newsfeed_status_update_edit ').append('<div class="audio_item_player" style="display: none"><audio class="audio_item_player" width="100%" height="38px" controls src="'+$audioData+'" type="audio/mp3"></audio><a class="audio_item_delete" onclick="audioRemove()">'+OW.getLanguageText('frmaudio', 'delete_audio_item')+'</a></div>');
    $('audio').mediaelementplayer();
}

function defineMP3Recorder(a){
    recorderWorkerPath = a;
}

function defineMP3Worker(a){
    mp3WorkerPath = a;
}

function __log(e, data) {
    log.innerHTML += "\n" + e + " " + (data || '');
}

function audioRemove() {
    $(".form_auto_click .audio_item_player").remove();
    $(".ow_file_attachment_preview .audio_item_player").remove();
    $(".owm_newsfeed_status_update_edit .audio_item_player").remove();
    $(".owm_forum_topic_bottom .audio_item_player").remove();
    $(".ow_form .audio_item_player").remove();
    $(".audio_item_delete").remove();
    $('#audio_feed_data').val(null);
    $('#audio_feed_name').val(null);
}

function hasRecoredAudio(){
    navigator.getUserMedia = ( navigator.getUserMedia ||
    navigator.webkitGetUserMedia ||
    navigator.mozGetUserMedia ||
    navigator.msGetUserMedia);

    return navigator.getUserMedia!=null && navigator.getUserMedia!= 'undefined';

}

function buttonManager(type){
    if(type=='start'){
        $(".stop").removeClass("disabled");
        $(".start").addClass("disabled");
    }
    if(type=='stop'){
        $(".start").removeClass("disabled");
        $(".stop").addClass("disabled");
    }
}