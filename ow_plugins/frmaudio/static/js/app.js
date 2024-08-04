/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio.js
 * @since 1.0
 */

var audio_context;

function initAudioApp() {

  $(function () {

    try {
      // webkit shim
      window.AudioContext = window.AudioContext || window.webkitAudioContext;
      navigator.getUserMedia = ( navigator.getUserMedia ||
      navigator.webkitGetUserMedia ||
      navigator.mozGetUserMedia ||
      navigator.msGetUserMedia);
      window.URL = window.URL || window.webkitURL;
      var audio_context = new AudioContext;
      __log('Audio context set up.');
      __log('navigator.getUserMedia ' + (navigator.getUserMedia ? 'available.' : 'not present!'));
    } catch (e) {
    }

    $('.recorder .start').on('click', function () {
      $this = $(this);
      $recorder = $this.parent();
      navigator.getUserMedia({audio: true}, function (stream) {
        var recorderObject = new MP3Recorder(audio_context, stream, {
          statusContainer: $recorder.find('.status'),
          statusMethod: 'replace'
        });
        $recorder.data('recorderObject', recorderObject);
        recorderObject.start();
      }, function (e) {
      });
    });

    $('.recorder .stop').on('click', function () {
      $this = $(this);

      $recorder = $this.parent();
      recorderObject = $recorder.data('recorderObject');
      if(recorderObject!=null) {
        recorderObject.stop();
        url = this.attributes['data-url'].value;
        recorderObject.exportMP3(function (base64_mp3_data) {
          //make ajax to store data
          $.ajax({
            url: url,
            type: 'POST',
            data: {data: base64_mp3_data},
            success: function(response) {
              data = JSON.parse(response);
              if(data.result){
                $("#audio").attr("src", data.url);
                recorderObject.logStatus('');
                $("#audioId").val(data.id);
                $('audio').mediaelementplayer();
              }else{
                OWM.message('err');
              }
            },
            'error' : function() {
              OWM.message('err');
            }
          });
        });
      }

    });

  });
}