function getAccessToken($url) {
    access_token = -1;
    token_type = -1;
    expires_in = -1;

    var params = {}, queryString = location.hash.substring(1),
        regex = /([^&=]+)=([^&]*)/g, m;
    while (m = regex.exec(queryString)) {
        params[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
    }

    if(params['access_token']){
        access_token = params['access_token'];
    }
    if(params['token_type']){
        token_type = params['token_type'];
    }
    if(params['expires_in']){
        expires_in = params['expires_in'];
    }

    var data = {"access_token":access_token,"token_type":token_type,"expires_in":expires_in};
    $.ajax({
        url: $url,
        type: 'post',
        dataType : "json",
        data: data,
        success: function(result){
            window.location = result.url;
        }
    });
}