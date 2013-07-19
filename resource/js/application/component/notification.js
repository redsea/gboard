/**
 * notification list 를 지원한다.
 * 짧은 시간에 add, remove animation 이 일어 난다면 ul 특성상(아마도)
 * li 요소들의 위치가 자동적으로 바뀌는 문제점이 있다.
 * 이건 나중에 수정 필요하면 수정 하도록 하자.
 */

var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.component) { gboard.component = {}; }
if(!gboard.component.notification) {gboard.component.notification={

_nid_num: 0,

// progress 의 data model
data: {
	// notification 의 item list
	// noti_items 는 다음의 object 포함하는 array 이다.
	// {
	//     nid  {string} : notification 의 id
	//     name {string} : notification 의 name(윗줄에 나오는 글자)
	//     desc {observable} : notification 의 설명(아래줄에 나오는 글자)
	// }
	noti_items: ko.observableArray(),
	
	afterRenderItem: function(elements, data) {
		var $wrapper = $(elements[1]);
		
		var $wrapper_children = $wrapper.children();
		var $element = $wrapper_children.last();
		var $space = $wrapper_children.first();
		var $desc = $element.children().last();
		
		var $desc_children = $desc.children();
		var $close = $desc_children.first().next();
		
		$close.button({icons:{primary:'ui-icon-closethick'},text:false})
			.addClass('window-notification-close').width(15).click(function(evt){
				evt.stopPropagation();
				$(this).button({disabled:true});
				gboard.component.notification.removeNotification(data.nid);
			});
		
		$space.animate({width:0}, 'easeOutQuart');
	}
},

/**
 * notification 을 보여 준다.(실제로 추가 하는 작업 임)
 *
 * @param name {string} notification 에 보여줄 main text
 * @param desc {string} notification 에 보여줄 sub text
 */
addNotification: function(name, desc) {
	var nid = 'sys-noti-'+(gboard.component.notification._nid_num++);
	$('#window-notification').show();
	gboard.component.notification.data.noti_items.push({nid:nid, name:name, 
		desc:ko.observable(desc)});
	return nid;
},

/**
 * notification 을 삭제 한다.
 * 
 * @param nid {string} 삭제할 notification id. addNotification 에서 발급 된 값이다.
 */
removeNotification: function(nid) {
	// XXX knockout 의 beforeRemove 를 사용하면 문제가 있음.
	//     여러개 동시에 삭제 하면 knockout 에서 animation 처리시 tag 를 pop, insert 시키는 듯 함.
	//     직접 anmation 을 주고, 데이터를 삭제 하자.
	
	var $item = $('#'+nid);
	var $space = $item.children().first();
	
	$space.stop().animate({width:200}, 'easeOutQuart', function(){
			$item.animate({height:0}, 'easeOutQuart', function(){
					gboard.component.notification.data.noti_items.remove(function(item){
							return (item.nid == nid);
						});
				});
		});
},

changeDescription: function(nid, desc) {
	var notis = gboard.component.notification.data.noti_items();
	var count = notis.length;
	for(var i=0 ; i<count ; i++) {
		if(notis[i].nid == nid) {
			notis[i].desc(desc);
			break;
		}
	}
},


init: function() {
	ko.applyBindings(gboard.component.notification.data, $('#window-notification').get(0));
}

};}