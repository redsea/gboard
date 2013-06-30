var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.admin) { gboard.admin = {}; }
if(!gboard.admin.login) { gboard.admin.login = {}; }


//--------------------------------------------------
// admin login 에서 사용하는 언어 데이터
//--------------------------------------------------
if(!gboard.admin.login.lang){gboard.admin.login.lang={

// 한글
ko: {
	type_user_id: '사용자 아이디',
	type_user_password: '암호',
	button_login: '로그인',
	try_login: '로그인 진행중 ',
	progress: '',
	end_login: '로그인 완료',
	error_message: '서비스 점검중, 잠시 후 다시 시도 하세요',
	no_user_id: '아이디를 입력하세요',
	no_user_password: '암호를 입력하세요'
},
en : {
	type_user_id: 'User ID',
	type_user_password: 'Password',
	button_login: 'Login',
	try_login: 'Trying Login ',
	progress: '',
	end_login: '로그인 완료',
	error_message: '서비스 점검중, 잠시 후 다시 시도 하세요',
	no_user_id: '아이디를 입력하세요',
	no_user_password: '암호를 입력하세요'
}
	
};}


//--------------------------------------------------
// Login 에서의 actions
//--------------------------------------------------
if(!gboard.admin.login.model){gboard.admin.login.action={

user_id: null,
user_password: null,

// 로그인시 animation setInterval 의 id
interval: null,

// 로그인시 dot 글자가 증가 하는 것 처리
progress: function() {
	var txt = gboard.admin.login.model.data.progress();
	var length = 0;
	
	if(!txt){ length = 0; }
	else	{ length = txt.length; }
	
	if(length != 0 && length < 30)	{ txt += '.'; }
	else							{ txt = '.'; }
	
	gboard.admin.login.model.data.progress(txt);
},

// ajax 리턴되는 값의 valid 체크 공통
valid_error: function(result, data) {
	if(!result || !data || !data.error) {
		// animation 을 멈춘다.
		clearInterval(gboard.admin.login.action.interval);
		gboard.admin.login.action.interval = null;
	
		gboard.admin.login.model.data.try_login(
			gboard.admin.login.lang[gboard.admin.login.model.language].error_message);
			
		gboard.admin.login.model.data.button_enable('yes');
		gboard.admin.login.model.data.input_enable('yes');
		return false;
	}
	
	if(data.error != 'S000001') {
		// animation 을 멈춘다.
		clearInterval(gboard.admin.login.action.interval);
		gboard.admin.login.action.interval = null;
	
		gboard.admin.login.model.data.try_login(data.message);
		
		gboard.admin.login.model.data.button_enable('yes');
		gboard.admin.login.model.data.input_enable('yes');
		return false;
	}
	
	return true;
},

// login 결과 받는 callback function
// XXX 왠만하면 이벤트로 빼고 싶은데, 그냥 하자 ㅜㅜ
cb_login: function(result, jqXHR, data, textStatus, errorThrown) {
	if(!gboard.admin.login.action.valid_error(result, data)) { return; }
	
	if(!data.request_url) {
		clearInterval(gboard.admin.login.action.interval);
		gboard.admin.login.action.interval = null;
		
		gboard.admin.login.model.data.try_login(
			gboard.admin.login.lang[gboard.admin.login.model.language].error_message);
			
		gboard.admin.login.model.data.button_enable('yes');
		gboard.admin.login.model.data.input_enable('yes');
		return;
	}
	
	// request_url 로 redirect 한다.
	window.location = data.request_url;
},

// access_token 결과 받는 callback function
// XXX 왠만하면 이벤트로 빼고 싶은데, 그냥 하자 ㅜㅜ
cb_access_token: function(result, jqXHR, data, textStatus, errorThrown) {
	if(!gboard.admin.login.action.valid_error(result, data)) { return; }
	
	if(!data.access_token) {
		clearInterval(gboard.admin.login.action.interval);
		gboard.admin.login.action.interval = null;
		
		gboard.admin.login.model.data.try_login(
			gboard.admin.login.lang[gboard.admin.login.model.language].error_message);
			
		gboard.admin.login.model.data.button_enable('yes');
		gboard.admin.login.model.data.input_enable('yes');
		return;
	}
	
	// login 시도
	gboard.ajax.login(gboard.admin.login.action.cb_login, data.access_token,
			gboard.admin.login.action.user_id,
			gboard.admin.login.action.user_password);
},

// authorize 결과 받는 callback function
// XXX 왠만하면 이벤트로 빼고 싶은데, 그냥 하자 ㅜㅜ
cb_authorize: function(result, jqXHR, data, textStatus, errorThrown) {
	if(!gboard.admin.login.action.valid_error(result, data)) { return; }

	if(!data.code) {
		clearInterval(gboard.admin.login.action.interval);
		gboard.admin.login.action.interval = null;
		
		gboard.admin.login.model.data.try_login(
			gboard.admin.login.lang[gboard.admin.login.model.language].error_message);
			
		gboard.admin.login.model.data.button_enable('yes');
		gboard.admin.login.model.data.input_enable('yes');
		return;
	}
	
	// access_token 요청
	gboard.ajax.access_token(gboard.admin.login.action.cb_access_token, data.code);
},

// gboard 에 로그인 시도
// XXX 왠만하면 이벤트로 빼고 싶은데, 그냥 하자 ㅜㅜ
login: function(user_id, user_password) {
	// user_id 체크
	if(!user_id) {
		gboard.admin.login.model.data.try_login(
			gboard.admin.login.lang[gboard.admin.login.model.language].no_user_id);
		gboard.admin.login.model.data.vis_login_process(true);
		gboard.admin.login.model.data.user_id_focus(true);
		return;
	}
	
	if(!user_password) {
		gboard.admin.login.model.data.try_login(
			gboard.admin.login.lang[gboard.admin.login.model.language].no_user_password);
		gboard.admin.login.model.data.vis_login_process(true);
		gboard.admin.login.model.data.user_password_focus(true);
		return;
	}

	// 버튼을 disabled 시킨다.
	gboard.admin.login.model.data.button_enable('no');
	gboard.admin.login.model.data.input_enable('no');


	gboard.admin.login.model.data.try_login(
		gboard.admin.login.lang[gboard.admin.login.model.language].try_login);
	gboard.admin.login.model.data.vis_login_process(true);
	gboard.admin.login.action.interval = setInterval(this.progress, 200);
	
	gboard.admin.login.action.user_id = user_id;
	gboard.admin.login.action.user_password = user_password;
	
	gboard.ajax.authorize(gboard.admin.login.action.cb_authorize);
}

};}


//--------------------------------------------------
// admin login 의 model
//--------------------------------------------------
if(!gboard.admin.login.model){gboard.admin.login.model={

language: 'ko',

data : {
	// text 변경
	type_user_id: ko.observable(gboard.admin.login.lang.type_user_id),				// 아이디 입력 안내 문구
	type_user_password: ko.observable(gboard.admin.login.lang.type_user_password),	// 패스워드 입력 안내 무구
	button_login: ko.observable(gboard.admin.login.lang.button_login),				// 버튼 텍스트
	try_login: ko.observable(gboard.admin.login.lang.try_login),					// 로그인 진행 안내 문구
	progress: ko.observable(gboard.admin.login.lang.progress),						// 로그인 진행 animation 문구(dot 증가 하는 것)
	
	// 형태 변경
	user_id_focus: ko.observable(true),			// user id 입력창 focus
	user_password_focus: ko.observable(false),	// user password 입력창 focus
	input_enable: ko.observable('yes'),			// input box enable, disable
	button_enable: ko.observable('yes'),		// button enable, disable
	vis_login_process: ko.observable(false)		// login 안내창 visible, envisible
	
},

init: function(clang) {
	ko.applyBindings(this.data);
	
	var _lang = gboard.admin.login.lang;
	gboard.admin.login.model.language = clang;
	
	this.data.type_user_id(_lang[clang].type_user_id);
	this.data.type_user_password(_lang[clang].type_user_password);
	this.data.button_login(_lang[clang].button_login);
	this.data.try_login(_lang[clang].try_login);
}
	
};}


//--------------------------------------------------
// Custom binding
//--------------------------------------------------
// 로그인 버튼 텍스트 변경 binding
ko.bindingHandlers.m_button = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
		
		$(element).button({label:valueUnwrapped});
	}
};

// 로그인 버튼 enable, disable binding
ko.bindingHandlers.m_button_enabled = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
		
		$(element).button({disabled:(valueUnwrapped=='yes'?false:true)});
	}
};

// 로그인 시도 안내창 보여주기, 숨기기 binding
ko.bindingHandlers.m_visible = {
	init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
		
		$(element).hide();
	},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
		
		if(valueUnwrapped) { $(element).stop(true).fadeIn('slow'); }
		else { $(element).stop(true).fadeOut('slow'); }
	}
};

// 아이디, 패스워드 입력 input box enable, disable binding
ko.bindingHandlers.m_input_enabled = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value); 
		
		if(valueUnwrapped == 'yes') { $(element).removeAttr('disabled'); }
		else						{ $(element).attr('disabled', 'disabled'); }
	}
};
