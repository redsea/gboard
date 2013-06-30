var gboard;
if(!gboard) { gboard = {}; }


//--------------------------------------------------
// 서버와의 ajax 처리
//--------------------------------------------------
if(!gboard.ajax) {gboard.ajax = {

url: {
	authorize:		'http://gboard.org/oauth/authorize',
	access_token:	'http://gboard.org/oauth/access_token',
	login:			'http://gboard.org/member/login'
},

// oauth authorize
authorize: function(cb) {
	$.ajax({
			type: 'POST',
			url: gboard.ajax.url.authorize,
			success: function(data, textStatus, jqXHR) {
				try {
					if(typeof data == 'string') { data = eval('('+data+')'); }
				} catch(exception) {
					(cb)(false, jqXHR, null, 'error', exception);
					return;
				}
				gboard.ajax_adapter.authorize(data);
				(cb)(true, jqXHR, data, textStatus);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				(cb)(false, jqXHR, null, textStatus, errorThrown);
			}
		});
},

// oauth access_token
access_token: function(cb, code) {
	$.ajax({
			type: 'POST',
			url: gboard.ajax.url.access_token,
			data: {code:code},
			//headers: {'X-authorization':code},
			success: function(data, textStatus, jqXHR) {
				try {
					if(typeof data == 'string') { data = eval('('+data+')'); }
				} catch(exception) {
					(cb)(false, jqXHR, null, 'error', exception);
					return;
				}
				gboard.ajax_adapter.access_token(data);
				(cb)(true, jqXHR, data, textStatus);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				(cb)(false, jqXHR, null, textStatus, errorThrown);
			}
		});
},

login: function(cb, access_token, user_id, user_passowrd) {
	$.ajax({
			type: 'POST',
			url: gboard.ajax.url.login,
			headers: {'X-authorization':access_token},
			data: {
				user_id: user_id,
				password: user_passowrd
			},
			success: function(data, textStatus, jqXHR) {
				try {
					if(typeof data == 'string') { data = eval('('+data+')'); }
				} catch(exception) {
					(cb)(false, jqXHR, null, 'error', exception);
					return;
				}
				gboard.ajax_adapter.login(data);
				(cb)(true, jqXHR, data, textStatus);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				(cb)(false, jqXHR, null, textStatus, errorThrown);
			}
		});
}

};}


//--------------------------------------------------
// Network response 의 adapter
// ajax response 값이 바뀔 수 있으므로 공통으로 처리 하기 위해
//--------------------------------------------------
if(!gboard.ajax_adapter) {gboard.ajax_adapter = {

// oauth authorize adapter
authorize: function(input) {
	// XXX 현재는 adapter 로직이 필요 없음.
	// XXX 추후 필요하면 input 에 구겨 넣자.
	return input;
},

// oauth access_token adapter
access_token: function(input) {
	// XXX 현재는 adapter 로직이 필요 없음.
	// XXX 추후 필요하면 input 에 구겨 넣자.
	return input;
},

// member login adapter
login: function(input) {
	// XXX 현재는 adapter 로직이 필요 없음.
	// XXX 추후 필요하면 input 에 구겨 넣자.
	return input;
}

};}
