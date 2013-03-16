if(typeof(facebook_commenting_api)!='undefined'){
	FB.init({
		appId  : facebook_commenting_api,
		status : true, // check login status
		cookie : true, // enable cookies to allow the server to access the session
		xfbml  : true  // parse XFBML
	});

    FB.login(function(response){
        if (!response.authResponse) {
            return false;
        }

    }, {scope: 'offline_access,email,publish_stream'});
}