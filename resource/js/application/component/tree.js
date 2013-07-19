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
	initialized: false, 	// tree init 함수 호출 한번만 해 주기 위해서
	tree_json: null			// tree json
},

event: {
	folder: 'gbd-tree-folder'
},

data: {
	tree_items: ko.observableArray()
},

actionTreeArrow: function(evt, udata) {
	evt.stopPropagation();

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
			var $children = $arrow.siblings('ul');
			$children.show('blind', {}, 'fast');
		}
		// children 을 만들지 않았다면, children 을 만들고 보여준다.
		else {
			if(!js_element.children) { return false; }
			for(var i=0 ; i<js_element.children.length ; i++) {
				ko_element.children.push({
						parent_index_path: index_path+'-'+i,
						children: ko.observableArray(),
						type: js_element.children[i].menu_type,
						name: js_element.children[i].menu_name,
						folding_type: ko.observable(js_element.children[i].children.length?1:0),
						left_edge: next_edge,
						is_c_children: ko.observable(false),
						clickArrow: function(obj, evt) {
							evt.stopPropagation();
							var $this = $(evt.target);
							$('body').trigger(gboard.component.tree.event.folder, [$this]);
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
		var $children = $arrow.siblings('ul');
		$children.hide('blind', {}, 'fast');
		
		// 폴더 화살표를 연다.
		ko_element.folding_type(1);
	}
	// folder 화살표가 없다면 아무 반응 하지 않는다.
	else {
		return false;
	}
},


/**
 * tree 를 초기화 한다.
 * 
 * @param tag_id {string} tree 가 들어가는 tag 의 id
 * @param data {object} tree data
 * @param show {boolean} init 와 동시에 보여줄지 말지 여부
 */
init: function(tag_id, data, show) {
	if(gboard.component.tree.conf.initialized) { return; }
	
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
		
		// parent_index_path : knockout tree array 를 타고 들어갈 수 있는 원본 index path(children 표시 를 위해)
		// children          : 가지고 있는 children knockout array
		// type              : element 의 종류(종류에 맞게 icon 표시)
		// name              : element 의 name(화면에 표시되는 text)
		// folding_type      : 화살표 종류 및 존재 여부. 1:폴딩(오른쪽방향 화살표), 2:언폴딩(아래방향 화살표), 0:폴더가 아님(화살표가 없음)
		// left_edge         : children 표시 할때 왼쪽 여백을 주기 위해 자기 자신의 왼쪽 여백
		// is_c_children     : 최초 생성시에는 children 을 생성하지 않기 때문에, 추후 children 을 생성 했는지 여부(생성 이후 hide/show 하기 위해서 필요)
		// clickArrow        : children 을 포함 하고 있는 것의 arrow 이미지를 클릭 했을때의 반응 처리
		
		gboard.component.tree.data.tree_items.push({
				parent_index_path: i+'',
				children: ko.observableArray(),
				type: data[i].menu_type,
				name: data[i].menu_name,
				folding_type: ko.observable(data[i].children.length?1:0),
				left_edge: '0px',
				is_c_children: ko.observable(false),
				clickArrow: function(obj, evt) {
					evt.stopPropagation();
					var $this = $(evt.target);
					$('body').trigger(gboard.component.tree.event.folder, [$this]);
				}
			});
	}
	
	if(show) { $tree_area.show(); }
		
	gboard.component.tree.conf.initialized = true;
}


};}