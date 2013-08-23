/**
 * 하나만 사용 할 것이기 때문에 tree 두 개 생성하는 것은 지원 하지 않음.
 * 하나 이상을 하려면 new 형식으로 바꾸거나 plugin 형식으로 바꾸도록 하자.
 *
 * tree 는 stopBinding 을 주로 사용할 것으로 보인다.
 * knockout 의 virtualElement 로 stopBinding 을 하자.
 */

var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.component) { gboard.component = {}; }
if(!gboard.component.tree) {gboard.component.tree={

conf: {
	initialized: false, 			// tree init 함수 호출 한번만 해 주기 위해서
	tree_cursor: 'tree-cursor',		// tree 커서의 id
	tree_json: null,				// tree json
	prev_select_row_id: null		// 커서가 존재하는 이전 위치의 element(li)
},

event: {
	folder: 'gbd-tree-folder'
},

data: {
	tree_items: ko.observableArray()
},

actionTreeArrow: function(evt, udata) {
	evt.stopPropagation();
	evt.preventDefault();

	var $arrow = $(udata[0]);
	var index_path = $arrow.attr('x-pos');
	var arr_index_path = index_path.split('-');
	var next_edge = (arr_index_path.length*14)+'px';
	
	// 화살표 영역을 눌렀을 때 반응 할 row element 를 찾는다
	var idx = arr_index_path.shift();
	var js_element = gboard.component.tree.conf.tree_json[idx];
	var ko_element = gboard.component.tree.data.tree_items()[idx];
	
	while(arr_index_path.length > 0) {
		idx = arr_index_path.shift();
		js_element = js_element.children[idx];
		ko_element = ko_element.children()[idx];
	}
	
	// folder 화살표가 있고, folding 되어 있다면
	if(ko_element.folding_type() == 1) {
		// children 을 만들었다면, 그냥 보여준다.
		if(ko_element.is_c_children()) {
			var $children = $arrow.parent().siblings('ul');
			$children.show('blind', {}, 'fast');
		}
		// children 을 만들지 않았다면, children 을 만들고 보여준다.
		else {
			if(!js_element.children) { return false; }
			for(var i=0 ; i<js_element.children.length ; i++) {
				ko_element.children.push({
						id: 'tree-row-'+js_element.children[i].element_srl,
						parent_index_path: index_path+'-'+i,
						children: ko.observableArray(),
						type: js_element.children[i].menu_type,
						name: js_element.children[i].menu_name,
						folding_type: ko.observable(js_element.children[i].children.length?1:0),
						left_edge: next_edge,
						is_c_children: ko.observable(false),
						description: js_element.children[i].description,
						controller: js_element.children[i].menu_controller,
						action: js_element.children[i].menu_action,
						clickArrow: function(obj, evt) {
							evt.stopPropagation();
							evt.preventDefault();
							var $this = $(evt.target);
							$('body').trigger(gboard.component.tree.event.folder, [$this]);
						},
						row_click_handler: {
							single_action: false, 
							double_action: (js_element.children[i].menu_type=='folder'?false:true)
						}
					});
			}
		}
		
		
		// 폴더 화살표를 연다.
		ko_element.folding_type(2);
		ko_element.is_c_children(true);
	}
	// folder 화살표가 있고, un-folding 되어 있다면, 폴더를 닫는다.
	else if(ko_element.folding_type() == 2) {
		var $children = $arrow.parent().siblings('ul');
		$children.hide('blind', {}, 'fast');
		
		// 폴더 화살표를 연다.
		ko_element.folding_type(1);
	}
	// folder 화살표가 없다면 row select 반응 하도록 한다.
	else {
		return false;
	}
},

/**
 * tree 의 element 를 single click 했을 때의 반응.
 * 현재 single click 했을 때의 반응이 없어서 구현 하지 않았음.
 */
actionRowSingleClick: function($element) {
	// TODO single click 반응이 필요하면 구현 해야 한다.
},

actionRowDoubleClick: function($element) {
	var index_path = $element.children().first().next().attr('x-pos');
	var arr_index_path = index_path.split('-');
	
	var indicator_label = [];
	var idx = arr_index_path.shift();
	var js_element = gboard.component.tree.conf.tree_json[idx];
	
	indicator_label.push(js_element.menu_name);
	
	while(arr_index_path.length > 0) {
		idx = arr_index_path.shift();
		js_element = js_element.children[idx];
		
		indicator_label.push(js_element.menu_name);
	}
	
	if(gboard.component.quickbar) {
		gboard.component.quickbar.pushCenterItem({
			title: js_element.menu_name,
			depth: indicator_label,
			url: '/'+js_element.menu_controller+'/'+js_element.menu_action,
			type: js_element.menu_type
		}, true);
	}
	
	
/*
	console.log('> index_path['+index_path+']');
	console.log(js_element);
*/
	
	
	
	//console.log(id);
	
	//var 
},


/**
 * tree 를 초기화 한다.
 * 
 * @param tag_id {string} tree 가 들어가는 tag 의 id
 * @param data {object} tree data
 * @param show {boolean} init 와 동시에 보여줄지 말지 여부
 */
init: function(tag_id, data, show) {
	//if(gboard.component.tree.conf.initialized) { return; }
	
	console.log('--------->here');
	
	
	$('body').bind(gboard.component.tree.event.folder, gboard.component.tree.actionTreeArrow);
	
	// tag 를 추가하고 applyBinding 시킨다.
	var $tree_area = $('#'+tag_id);
	var $tree = $('<ul>').addClass('navigator-tree')
		.attr('data-bind', "template:{name:'tpl-tree-item', foreach:tree_items}");
	$tree_area.append($tree);
	ko.applyBindings(gboard.component.tree.data, $tree_area.get(0));
	
	gboard.component.tree.conf.tree_json = data;
	
	var item_count = 0;
	for(var i=0 ; i<data.length ; i++) {
		item_count = gboard.component.tree.data.tree_items().length;
	
		// 원본과 knockout array 의 구조를 같이 할 것이기 때문에 원본에서의 패스만 알면 됨.
		// 추가 삭제 할때도 원본을 같이 건드려 줘야 함.
		
		// id                : tree row 의 id
		// parent_index_path : knockout tree array 를 타고 들어갈 수 있는 원본 index path(children 표시 를 위해)
		// children          : 가지고 있는 children knockout array
		// type              : element 의 종류(종류에 맞게 icon 표시)
		// name              : element 의 name(화면에 표시되는 text)
		// folding_type      : 화살표 종류 및 존재 여부. 1:폴딩(오른쪽방향 화살표), 2:언폴딩(아래방향 화살표), 0:폴더가 아님(화살표가 없음)
		// left_edge         : children 표시 할때 왼쪽 여백을 주기 위해 자기 자신의 왼쪽 여백
		// is_c_children     : 최초 생성시에는 children 을 생성하지 않기 때문에, 추후 children 을 생성 했는지 여부(생성 이후 hide/show 하기 위해서 필요)
		// description       : element 의 설명
		// clickArrow        : children 을 포함 하고 있는 것의 arrow 이미지를 클릭 했을때의 반응 처리
		// row_click_handler : row 를 싱글 클릭, 더블 클릭 했을때 반응 할지 여부
		//                     single_action 이 true 이면 싱글 클릭에도 반응 한다.
		//                     double_action 이 true 이면 더블 클릭에도 반응 한다.
		
		gboard.component.tree.data.tree_items.push({
				id: 'tree-row-'+data[i].element_srl,
				parent_index_path: i+'',
				children: ko.observableArray(),
				type: data[i].menu_type,
				name: data[i].menu_name,
				folding_type: ko.observable(data[i].children.length?1:0),
				left_edge: '0px',
				is_c_children: ko.observable(false),
				description: data[i].description,
				clickArrow: function(obj, evt) {
					evt.stopPropagation();
					evt.preventDefault();
					var $this = $(evt.target);
					$('body').trigger(gboard.component.tree.event.folder, [$this]);
				},
				row_click_handler: {
					single_action: false, 
					double_action: (data[i].menu_type=='folder'?false:true)
				}
			});
	}
	
	if(show) { $tree_area.show(); }
		
	//gboard.component.tree.conf.initialized = true;
}


};}


//--------------------------------------------------
// tree Custom binding
//--------------------------------------------------
// row 에 대해서 single click, double click 모두 반응 하게 하기 위한 bind
ko.bindingHandlers.m_tree_row_single_click = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var handler = valueAccessor(), delay=250, clickCount=0, clickTimeout=null;
		var $element = $(element);
		var id = $element.attr('id');
		
		$element.bind({
				click: function(evt) {
					evt.stopPropagation();
					evt.preventDefault();
					
					if(clickCount >= 1) {
						clearTimeout(clickTimeout);
						clickTimeout = null;
						clickCount = 0;
						if(handler.double_action) {
							gboard.component.tree.actionRowDoubleClick($element);
						}
						return;
					}
					
					if(!clickTimeout) {
						clickTimeout = setTimeout(function(){
								clickCount = 0;
								clickTimeout = null;
								if(handler.single_action) {
									gboard.component.tree.actionRowSingleClick($element);
								}
								return;
							}, delay);
					}
					
					// UI 반응을 위해서 이건 클릭 하자 마자 바로 반응 시킨다.
					// 이전 위치의 커서를 삭제 한다.
					if(gboard.component.tree.conf.prev_select_row_id!=null && id!=gboard.component.tree.conf.prev_select_row_id) {
						$('#'+gboard.component.tree.conf.prev_select_row_id).removeClass('navigator-tree-row-select');
					}
					
					// 현재 위치에 커서를 넣는다.
					if(!$element.hasClass('navigator-tree-row-select')) {
						gboard.component.tree.conf.prev_select_row_id = id;
						$element.addClass('navigator-tree-row-select');
					}
					
					clickCount++;
				},
				dblclick: function(evt) {
					evt.stopPropagation();
					evt.preventDefault();
				}
			});
		
		
	}
};