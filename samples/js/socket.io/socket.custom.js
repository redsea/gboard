var CSio;

if(CSio && typeof CSio != 'object') { throw '[CSio] CSio is not object. exist'; }

if(!CSio) {

CSio = function() {
	this._socket = null;
	
	this.__handler = {
		// connect 되었을 때
		connected: function(data) {
			console.log('connect handler');
			console.log(data);
		},
		message: function(data) {
			console.log('message['+data+']');
		}
		
	};
};

// Socket custom handler 를 지정한다.
// type 에러가 발생하면 실행 중지되고, fn 타입이 function 이 아니면 디폴트 handler 를 사용한다.
CSio.prototype.setHandler = function(type, fn) {
	if(!this.__handler[type]) {
		console.error('[CSio] not exist supported handler. type['+type+']');
		return false;
	}
	
	if(typeof fn != 'function') {
		console.warn('[CSio] user-handler is not function for type['+type+']. use default handler');
	} else {
		this.__handler[type] = fn;
	}
	
	return true;
};

CSio.prototype.addHandler = function(type, fn) {
	if(typeof fn != 'function') {
		console.error('[CSio] fn is not function for addHandler. type['+type+']');
		return;
	}
	this.__handler[type] = fn;
	//this._socket.on(type, this.__handler[type]);
};

CSio.prototype.connect = function(url) {
	this._socket = io.connect(url);
	//this._socket.on('connected', CustomSocketIo.handler.connected);
	
	// handler 를 등록한다.
	for(var prop in this.__handler) {
		this._socket.on(prop, this.__handler[prop]);
	}
};

CSio.prototype.request = function(type, data) {
	if(this._socket) {
		this._socket.emit(type, data);
	} else {
		console.error('[CSio] invalid socket');
	}
};

/*
socket: null,

// 지원하는 Socket handler 정의
handler: {
	// connect 되었을 때
	connected: function(data) {
		console.log('connect handler');
		console.log(data);
	}
},




request: function(type, data) {
	if(CustomSocketIo.socket) {
		CustomSocketIo.socket.emit(type, data);
	} else {
		console.error('[CustomSocketIo] invalid socket');
	}
}
	
};

*/

}

