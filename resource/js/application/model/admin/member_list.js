var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.content) { gboard.content = {}; }
if(!gboard.content.main) { gboard.content.main = {}; }


if(!gboard.content.main.data){gboard.content.main.data={

conf: {
	language: 'ko',
	text: null
},

body: {
	//table_header: ko.observable(),	// 이건 init 에서 값을 준다
	txt_text_title: ko.observable(),
	txt_text_list_help1: ko.observable(),
	
	txt_dialog_title: ko.observable(),	// dialog title
	txt_close: ko.observable(),			// 상세 보기 닫기
	txt_title1_detail: ko.observable(),	// 테이블1 header
	txt_title2_detail: ko.observable(),	// 테이블2 header
	txt_title3_detail: ko.observable(),	// 테이블3 header
	
	txt_row1_column1: ko.observable(),	// 멤버 넘버
	txt_row1_column2: ko.observable(),	// 아이디
	txt_row2_column1: ko.observable(),	// 이메일 주소
	txt_row2_column2: ko.observable(),	// 이름
	txt_row3_column1: ko.observable(),	// 별명
	txt_row3_column2: ko.observable(),	// 메일 수신 여부
	txt_row4_column1: ko.observable(),	// 메시지 수신 여부
	txt_row4_column2: ko.observable(),	// 차단 여부
	txt_row5_column1: ko.observable(),	// 이메일 확인
	txt_row5_column2: ko.observable(),	// 유효 제한일
	txt_row6_column1: ko.observable(),	// 최종 로그인 날짜
	txt_row6_column2: ko.observable(),	// 패스워드 변경 최종일
	txt_row7_column1: ko.observable(),	// 그룹명
	txt_row7_column2: ko.observable(),	// 등록일
	
	val_row1_column1: ko.observable(),	// 멤버 넘버
	val_row1_column2: ko.observable(),	// 아이디
	val_row2_column1: ko.observable(),	// 이메일 주소
	val_row2_column2: ko.observable(),	// 이름
	val_row3_column1: ko.observable(),	// 별명
	val_row3_column2: ko.observable(),	// 메일 수신 여부
	val_row4_column1: ko.observable(),	// 메시지 수신 여부
	val_row4_column2: ko.observable(),	// 차단 여부
	val_row5_column1: ko.observable(),	// 이메일 확인
	val_row5_column2: ko.observable(),	// 유효 제한일
	val_row6_column1: ko.observable(),	// 최종 로그인 날짜
	val_row6_column2: ko.observable(),	// 패스워드 변경 최종일
	val_row7_column1: ko.observable(),	// 그룹명
	val_row7_column2: ko.observable(),	// 등록일

	txt2_row1_column1: ko.observable(),	// 홈페이지
	txt2_row1_column2: ko.observable(),	// 블로그
	txt2_row2_column1: ko.observable(),	// 생년월일
	txt2_row2_column2: ko.observable(),	// 성별
	txt2_row3_column1: ko.observable(),	// 국적
	txt2_row3_column2: ko.observable(),	// 국가 전화번호
	txt2_row4_column1: ko.observable(),	// 휴대폰 번호
	txt2_row4_column2: ko.observable(),	// 일반 전화 번호
	txt2_row5_column1: ko.observable(),	// 소셜 종류
	txt2_row5_column2: ko.observable(),	// 소셜 아이디
	txt2_row6_column1: ko.observable(),	// 로그인 횟수
	txt2_row6_column2: ko.observable(),	// 연속 로그인 횟수
	
	val2_row1_column1: ko.observable(),	// 홈페이지
	val2_row1_column2: ko.observable(),	// 블로그
	val2_row2_column1: ko.observable(),	// 생년월일
	val2_row2_column2: ko.observable(),	// 성별
	val2_row3_column1: ko.observable(),	// 국적
	val2_row3_column2: ko.observable(),	// 국가 전화번호
	val2_row4_column1: ko.observable(),	// 휴대폰 번호
	val2_row4_column2: ko.observable(),	// 일반 전화 번호
	val2_row5_column1: ko.observable(),	// 소셜 종류
	val2_row5_column2: ko.observable(),	// 소셜 아이디
	val2_row6_column1: ko.observable(),	// 로그인 횟수
	val2_row6_column2: ko.observable(),	// 연속 로그인 횟수
	
	txt_head_size: ko.observable(),		// 프로필 이미지 사이즈
	txt_head_preview: ko.observable(),	// 프로필 이미지 미리보기
	
	user_profiles: ko.observableArray()	// 유저 프로필 이미지
}

};}


if(!gboard.content.main.action) { gboard.content.main.action={

get_date_format: function(date) {
	if(!date || date.length < 14) { return ''; }
	
	return date.substring(0, 4)+'-'+	// year
		date.substring(4, 6)+'-'+		// month
		date.substring(6, 8)+' '+		// day
		date.substring(8, 10)+':'+		// hour
		date.substring(10, 12)+':'+		// min
		date.substring(12, 14);			// sec
},

/**
 * 파일 상세 보기 ajax 결과 처리
 */
show_member_detail: function(result, jqXHR, data, textStatus, excep) {
	$('.window-container').addClass('view-popup-stop-scrolling');
	$('#view-port-popup').fadeIn();
	
	// 실패 했다면 dialog 를 띄운다
	if(!result || !data || !data.error) {
		var $dialog = $('#view-popup-dialog')
			.text(gboard.content.main.data.conf.text.error_message)
			.dialog({
				appendTo: '#view-port-popup',
				close: function(evt, ui) {
					evt.stopPropagation();
					evt.preventDefault();
					
					$('.window-container').removeClass('view-popup-stop-scrolling');
					$('#view-port-popup').fadeOut(function(){$('#view-popup').hide();});
				}
			});
		return false;
	}
	
	if(data.error != 'S000001') {
		var $dialog = $('#view-popup-dialog')
			.text(data.message)
			.dialog({
				appendTo: '#view-port-popup',
				close: function(evt, ui) {
					evt.stopPropagation();
					evt.preventDefault();
					
					$('.window-container').removeClass('view-popup-stop-scrolling');
					$('#view-port-popup').fadeOut(function(){$('#view-popup').hide();});
				}
			});
		return false;
	}
	
	// 성공 했으면 detail 을 보여 준다.
	// 멤버 기본 정보
	gboard.content.main.data.body.val_row1_column1(data.member_srl);
	gboard.content.main.data.body.val_row1_column2(data.user_id);
	gboard.content.main.data.body.val_row2_column1(data.email_address);
	gboard.content.main.data.body.val_row2_column2(data.user_name);
	gboard.content.main.data.body.val_row3_column1(data.nick_name);
	gboard.content.main.data.body.val_row3_column2(data.allow_mailing);
	gboard.content.main.data.body.val_row4_column1(data.allow_message);
	gboard.content.main.data.body.val_row4_column2(data.block);
	gboard.content.main.data.body.val_row5_column1(data.email_confirm);
	gboard.content.main.data.body.val_row5_column2(
			gboard.content.main.action.get_date_format(data.limit_date));
	gboard.content.main.data.body.val_row6_column1(
			gboard.content.main.action.get_date_format(data.last_login_date));
	gboard.content.main.data.body.val_row6_column2(
			gboard.content.main.action.get_date_format(data.change_password_date));
	gboard.content.main.data.body.val_row7_column1(data.group_name.join());
	gboard.content.main.data.body.val_row7_column2(
			gboard.content.main.action.get_date_format(data.c_date));
	
	// 멤버 기타 정보
	gboard.content.main.data.body.val2_row1_column1(data.homepage);
	gboard.content.main.data.body.val2_row1_column2(data.blog);
	gboard.content.main.data.body.val2_row2_column1(data.birthday);
	gboard.content.main.data.body.val2_row2_column2(data.gender);
	gboard.content.main.data.body.val2_row3_column1(data.country);
	gboard.content.main.data.body.val2_row3_column2(data.country_call_code);
	gboard.content.main.data.body.val2_row4_column1(data.mobile_phone_number);
	gboard.content.main.data.body.val2_row4_column2(data.phone_number);
	gboard.content.main.data.body.val2_row5_column1(data.account_social_type);
	gboard.content.main.data.body.val2_row5_column2(data.account_social_id);
	gboard.content.main.data.body.val2_row6_column1(data.login_count);
	gboard.content.main.data.body.val2_row6_column2(data.serial_login_count);
	
	// 유저 프로필 이미지
	gboard.content.main.data.body.user_profiles.removeAll();
	for(var prop in data.image_mark) {
		gboard.content.main.data.body.user_profiles.push({
				size: prop,
				preview: data.image_mark[prop]
			});
	}
	
	if(gboard.content.main.data.body.user_profiles().length <= 0) {
		gboard.content.main.data.body.user_profiles.push({
				size: 'N/A',
				preview: ''
			});
	}
	
	$('#view-popup').show();
	$('#view-port-popup').fadeIn();
	$('.view-popup-content').scrollTop(0);
},

/**
 * 파일 상세 보기 ajax
 */
member_detail: function(member_id) {
	member_id = member_id.replace(/member_/, '');
	
	$.ajax({
			type: 'POST',
			url: gboard.admin.url.member_detail,
			data: {user_id:member_id},
			success: function(data, textStatus, jqXHR) {
				try {
					if(typeof data == 'string') { data = eval('('+data+')'); }
				} catch(exception) {
					(cb)(false, jqXHR, null, 'error', exception, userdata);
					return;
				}
				
				gboard.content.main.action.adapter_member_detail(data);
				gboard.content.main.action.show_member_detail(true, jqXHR, data, textStatus, null);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				gboard.content.main.action.show_member_detail(false, jqXHR, null, textStatus, errorThrown);
			}
		});
},

/**
 * file detail ajax 결과 adapter
 */
adapter_member_detail: function(input) {
	return input;
},

init: function(clang) {
	gboard.content.main.data.conf.language = clang;
	gboard.content.main.data.conf.text = gboard.admin.text[clang];

	gboard.content.main.data.body.txt_text_title(gboard.content.main.data.conf.text.member_list);
	gboard.content.main.data.body.txt_text_list_help1(gboard.content.main.data.conf.text.help_member_list);

	gboard.content.main.data.body.txt_title1_detail(gboard.content.main.data.conf.text.title_detail_member);
	gboard.content.main.data.body.txt_title2_detail(gboard.content.main.data.conf.text.title_detail_member2);
	gboard.content.main.data.body.txt_title3_detail(gboard.content.main.data.conf.text.title_detail_member3);
	
	gboard.content.main.data.body.txt_row1_column1(gboard.content.main.data.conf.text.member_srl);
	gboard.content.main.data.body.txt_row1_column2(gboard.content.main.data.conf.text.user_id);
	gboard.content.main.data.body.txt_row2_column1(gboard.content.main.data.conf.text.email_address);
	gboard.content.main.data.body.txt_row2_column2(gboard.content.main.data.conf.text.user_name);
	gboard.content.main.data.body.txt_row3_column1(gboard.content.main.data.conf.text.nick_name);
	gboard.content.main.data.body.txt_row3_column2(gboard.content.main.data.conf.text.allow_mailing);
	gboard.content.main.data.body.txt_row4_column1(gboard.content.main.data.conf.text.allow_message);
	gboard.content.main.data.body.txt_row4_column2(gboard.content.main.data.conf.text.block);
	gboard.content.main.data.body.txt_row5_column1(gboard.content.main.data.conf.text.email_confirm);
	gboard.content.main.data.body.txt_row5_column2(gboard.content.main.data.conf.text.limit_date);
	gboard.content.main.data.body.txt_row6_column1(gboard.content.main.data.conf.text.last_login_date);
	gboard.content.main.data.body.txt_row6_column2(gboard.content.main.data.conf.text.change_password_date);
	gboard.content.main.data.body.txt_row7_column1(gboard.content.main.data.conf.text.group);
	gboard.content.main.data.body.txt_row7_column2(gboard.content.main.data.conf.text.cdate);
	
	gboard.content.main.data.body.txt2_row1_column1(gboard.content.main.data.conf.text.homepage);
	gboard.content.main.data.body.txt2_row1_column2(gboard.content.main.data.conf.text.blog);
	gboard.content.main.data.body.txt2_row2_column1(gboard.content.main.data.conf.text.birth);
	gboard.content.main.data.body.txt2_row2_column2(gboard.content.main.data.conf.text.gender);
	gboard.content.main.data.body.txt2_row3_column1(gboard.content.main.data.conf.text.country);
	gboard.content.main.data.body.txt2_row3_column2(gboard.content.main.data.conf.text.country_call_number);
	gboard.content.main.data.body.txt2_row4_column1(gboard.content.main.data.conf.text.mobile_phone_number);
	gboard.content.main.data.body.txt2_row4_column2(gboard.content.main.data.conf.text.phone_number);
	gboard.content.main.data.body.txt2_row5_column1(gboard.content.main.data.conf.text.social_type);
	gboard.content.main.data.body.txt2_row5_column2(gboard.content.main.data.conf.text.social_id);
	gboard.content.main.data.body.txt2_row6_column1(gboard.content.main.data.conf.text.login_count);
	gboard.content.main.data.body.txt2_row6_column2(gboard.content.main.data.conf.text.serial_login_count);
	
	gboard.content.main.data.body.txt_head_size(gboard.content.main.data.conf.text.resolution);
	gboard.content.main.data.body.txt_head_preview(gboard.content.main.data.conf.text.preview);
	
	gboard.content.main.data.body.txt_close(gboard.content.main.data.conf.text.close);
	gboard.content.main.data.body.txt_dialog_title(gboard.content.main.data.conf.text.title_failed);

	gboard.content.main.data.body.table_header = ko.observable({
			user_id: gboard.content.main.data.conf.text.user_id,
			email_address: gboard.content.main.data.conf.text.email_address,
			user_name: gboard.content.main.data.conf.text.user_name,
			nick_name: gboard.content.main.data.conf.text.nick_name,
			group: gboard.content.main.data.conf.text.group,
			block: gboard.content.main.data.conf.text.block,
			cdate: gboard.content.main.data.conf.text.cdate});

	ko.applyBindings(gboard.content.main.data.body, $('#window-content').get(0));

	var $oTable = $('#data-tables1').dataTable({
		'bJQueryUI': true,
		'sPaginationType': 'full_numbers',
		'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': gboard.admin.url.member_list,
        'sServerMethod': 'POST',
        'aoColumns': [
        	{'mData':'user_id'},
        	{'mData':'email_address'},
        	{'mData':'user_name', 'sClass':'center'},
        	{'mData':'nick_name', 'sClass':'center'},
        	{'mData':'block', 'bSortable': false, 'sClass':'center'},
        	{
        		'mData':'c_date', 'sClass':'center', 
        		'mRender':function(data, type, full){
        			return gboard.content.main.action.get_date_format(data);
        		}
        	}
        ],
        'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
        	$(nRow).click(function(evt){
        			evt.stopPropagation();
					evt.preventDefault();
					
        			var $this = $(this);
        			gboard.content.main.action.member_detail($this.attr('id'));
        		});
        }
	});
}

};}

ko.bindingHandlers.m_button = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
		var $element = $(element);
		
		$element.text(valueUnwrapped).button().addClass('close-popup').click(function(evt){
			evt.stopPropagation();
			evt.preventDefault();
			
			$('.window-container').removeClass('view-popup-stop-scrolling');
			$('#view-port-popup').fadeOut(function(){
					$('#view-popup').hide();
				});
		});
	}
};