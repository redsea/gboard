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
// Custom binding
//--------------------------------------------------
// 로그인 버튼 텍스트 변경(언어 때문에...)
ko.bindingHandlers.button = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value); 
		$(element).button({label:valueUnwrapped});
	}
}

ko.bindingHandlers.enabled = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value); 
		$(element).button({disabled:'yes'?false:true});
		
		
	}
}

// 로그인 시도 안내창 보여주기, 숨기기
ko.bindingHandlers.fvisible = {
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
}


//--------------------------------------------------
// Login 에서의 actions
//--------------------------------------------------
if(!gboard.admin.login.model){gboard.admin.login.action={

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
		return false;
	}
	
	if(data.error != 'S000001') {
		// animation 을 멈춘다.
		clearInterval(gboard.admin.login.action.interval);
		gboard.admin.login.action.interval = null;
	
		gboard.admin.login.model.data.try_login(data.message);
		gboard.admin.login.model.data.button_enable('yes');
		return false;
	}
	
	return true;
},

// login 결과 받는 callback function
// XXX 왠만하면 이벤트로 빼고 싶은데, 그냥 하자 ㅜㅜ
cb_login: function(result, jqXHR, data, textStatus, errorThrown) {
	if(!gboard.admin.login.action.valid_error(result, data)) { return; }
	
	// TODO 여기 부터는 login API 를 만들고 다시 진행 해야 한다.

	//console.log('> end login');

		// 안내창을 가리고
	//gboard.admin.login.model.data.try_login(
	//	gboard.admin.login.lang[gboard.admin.login.model.language].end_login);
	//gboard.admin.login.model.data.vis_login_process(false);

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
		return;
	}
	
	// login 시도
	gboard.ajax.login(gboard.admin.login.action.cb_login, data.access_token);
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
		return;
	}
	
	// access_token 요청
	gboard.ajax.access_token(gboard.admin.login.action.cb_access_token, data.code);
},

// gboard 에 로그인 시도
// XXX 왠만하면 이벤트로 빼고 싶은데, 그냥 하자 ㅜㅜ
login: function(user_id, user_password) {
	// 버튼을 disabled 시킨다.
	gboard.admin.login.model.data.button_enable('no');

	// user_id 체크
	if(!user_id) {
		gboard.admin.login.model.data.try_login(
			gboard.admin.login.lang[gboard.admin.login.model.language].no_user_id);
		gboard.admin.login.model.data.vis_login_process(true);
		gboard.admin.login.model.data.button_enable('yes');
		$('#user_id').focus();
		return;
	}
	
	if(!user_password) {
		gboard.admin.login.model.data.try_login(
			gboard.admin.login.lang[gboard.admin.login.model.language].no_user_password);
		gboard.admin.login.model.data.vis_login_process(true);
		gboard.admin.login.model.data.button_enable('yes');
		$('#password').focus();
		return;
	}


	gboard.admin.login.model.data.try_login(
		gboard.admin.login.lang[gboard.admin.login.model.language].try_login);
	gboard.admin.login.model.data.vis_login_process(true);
	gboard.admin.login.action.interval = setInterval(this.progress, 200);
	
	gboard.ajax.authorize(gboard.admin.login.action.cb_authorize);
}

};}


//--------------------------------------------------
// admin login 의 model
//--------------------------------------------------
if(!gboard.admin.login.model){gboard.admin.login.model={

language: 'ko',

data : {
	type_user_id: ko.observable(gboard.admin.login.lang.type_user_id),
	type_user_password: ko.observable(gboard.admin.login.lang.type_user_password),
	button_login: ko.observable(gboard.admin.login.lang.button_login),
	button_enable: ko.observable('yes'),
	vis_login_process: ko.observable(false),
	try_login: ko.observable(gboard.admin.login.lang.try_login),
	progress: ko.observable(gboard.admin.login.lang.progress)
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


