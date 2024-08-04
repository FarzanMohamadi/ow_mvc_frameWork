/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio.js
 * @since 1.0
 */
var audioRecorder2;
var audioCtx;
var audioInLevel, audioIn, mixer;

var $timeDisplay;

var loadAudioRecorder = function(audioJsDir){
    if ((navigator.mediaDevices != null) && (navigator.mediaDevices.enumerateDevices != null)) {
        navigator.mediaDevices.enumerateDevices().then(onGotDevices)["catch"](function(err) {
            return onError("Could not enumerate audio devices: " + err);
        });
    } else {
    }
    $('.recorder .start').on('click', function () {
        audioInLevel.gain.value = 0.4;
        onChangeAudioIn();
        startRecording2();
    });

    $('.recorder .stop').on('click', function () {
        temp_upload_url = this.attributes['data-blob-url'].value;
        stopRecording2(true);
        onStopAudioIn();
        audioInLevel.gain.value = 0;
    });

    //---time
    $timeDisplay = $('#time-display');
    window.setInterval(updateDateTime, 200);

    if(audioRecorder2!=null)
    {
        return;
    }

    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
    //var AudioContext1 = window.AudioContext || window.webkitAudioContext;
    audioCtx = new AudioContext();
    if (audioCtx.createScriptProcessor == null) {
        audioCtx.createScriptProcessor = audioCtx.createJavaScriptNode;
    }

    //*----INPUT
    audioInLevel = audioCtx.createGain();
    audioInLevel.gain.value = 0;
    mixer = audioCtx.createGain();

    audioIn = void 0;
    audioInLevel.connect(mixer);
    //mixer.connect(audioCtx.destination);
    //------*/
    audioRecorder2 = new WebAudioRecorder(mixer, {
        workerDir: audioJsDir + 'js2/',
        onEncoderLoading: function(recorder, encoding) {
            //console.log("Loading " + (encoding.toUpperCase()) + " encoder ...");
        }
    });
    audioRecorder2.onEncoderLoaded = function() {
    };
    audioRecorder2.onTimeout = function(recorder) {
        stopRecording2(true);
    };

    audioRecorder2.onEncodingProgress = function(recorder, progress) {
        setProgress(progress);
    };

    audioRecorder2.onComplete = function(recorder, blob) {
        if (recorder.options.encodeAfterRecord) {
        }
        saveRecording(blob, recorder.encoding);
    };
    audioRecorder2.onError = function(recorder, message) {
        onError(message);
    };
};

var deviceIdent;
onGotDevices = function(devInfos) {
    deviceId = void 0;
    for (_i = 0, _len = devInfos.length; _i < _len; _i++) {
        info = devInfos[_i];
        if (info.kind !== 'audioinput') {
            continue;
        }
        deviceIdent = info.deviceId;
        html = info.label || ("Audio in " + (++index));
        html = "<p>F:"+html+"</p>";
        //console.log(html);
        break;
    }
};


onGotAudioIn = function(stream) {
    if (audioIn != null) {
        audioIn.disconnect();
    }
    audioIn = audioCtx.createMediaStreamSource(stream);
    audioIn.connect(audioInLevel);
    return true;
};

onChangeAudioIn = function() {
    constraint = {
        audio: {
            deviceId: deviceIdent,
            echoCancellation: false
        }
    };
    if ((navigator.mediaDevices != null) && (navigator.mediaDevices.getUserMedia != null)) {
        navigator.mediaDevices.getUserMedia(constraint).then(onGotAudioIn)["catch"](function(err) {
            return onError("Could not get audio media device1 #"+deviceIdent+": " + err);
        });
    } else {
        navigator.getUserMedia(constraint, onGotAudioIn, function() {
            return onError("Could not get audio media device: " + err);
        });
    }
};

onStopAudioIn = function() {
    if (audioIn != null) {
        audioIn.disconnect();
    }
    audioIn = void 0;
    audioInLevel.connect(mixer);
};
//--------------*/

startRecording2 = function() {
    audioRecorder2.setEncoding('mp3');
    audioRecorder2.setOptions({
        timeLimit: 60, //seconds
        encodeAfterRecord: false,
        progressInterval: 1000,
        bufferSize: 16384,
        mp3: {
            mimeType: "audio/mpeg",
            bitRate: 64 //64
        }
    });
    audioRecorder2.startRecording();
};

stopRecording2 = function(finish) {
    if (finish) {
        audioRecorder2.finishRecording();
        if (audioRecorder2.options.encodeAfterRecord) {
            //console.log("Encoding " + (audioRecorder2.encoding.toUpperCase()));
        }
    } else {
        audioRecorder2.cancelRecording();
    }
};

var temp_upload_url;
saveRecording = function(blob, enc) {
    var url;
    url = URL.createObjectURL(blob);

    //-------------//make ajax to store data
    var data = new FormData();
    data.append('file', blob);
    //console.log(data);
    //console.log('url:'+temp_upload_url);

    $.ajax({
        url: temp_upload_url,
        type: 'POST',
        data: data,
        contentType: false,
        processData: false,
        success: function (response) {
            console.log(response);
            data = JSON.parse(response);
            if (data.result) {
                $("#audioId").val(data.id);
                var html_audio = '<audio style="display: none;" class="audio_item_player" width="100%" height="38px" controls src="" type="audio/mp3"></audio>';
                $('#audio_item_player_container').html(html_audio);
                $('#audio_item_player_container audio').attr("src", data.url).mediaelementplayer();
            } else {
                OWM.message('err');
            }
        },
        'error': function () {
            OWM.message('err');
        }
    });
};

onError = function(msg) {
    console.log('frmaudio onError:'+msg);
};

//----time
var minSecStr = function(n) {
    return (n < 10 ? "0" : "") + n;
};

var updateDateTime = function() {
    var sec;
    sec = audioRecorder2.recordingTime() | 0;
    $timeDisplay.html("" + (minSecStr(sec / 60 | 0)) + ":" + (minSecStr(sec % 60)));
};
//-------