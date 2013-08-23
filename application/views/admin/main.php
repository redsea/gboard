<html>
<head>
	<link rel="stylesheet" href="/resource/css/application/admin/main.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/application/component/quick-bar.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/application/component/tree.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/application/component/notification.css" type="text/css" />
	
	<script type="text/javascript" src="/resource/js/lib/jquery/plugin/jquery.cookie.js"></script>
	
	<script type="text/javascript" src="/resource/js/application/component/quick-bar.js"></script>
	<script type="text/javascript" src="/resource/js/application/component/tree.js"></script>
	<script type="text/javascript" src="/resource/js/application/component/notification.js"></script>
	
	<script type="text/javascript" src="/resource/js/application/view/admin/main.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/main.js"></script>
	
	<!-- quick menu item template -->
	<script type="text/html" id="tpl-quick-bar-item">
		<div class="quick-element" 
				data-bind="click:clickThis, attr:{id:id}, style:{left:pos}">
			<div data-bind="attr:{title:title, class:className}, css:{'quick-icon-custom':is_image_custom}, style:{'backgroundImage':image}, m_quick_item_hover:use_hover"></div>
			<div class="quick-icon-name" data-bind="text: title"></div>
			<div class="quick-icon-close" data-bind="m_quick_item_hover:use_hover, click:clickClose"></div>
		</div>
	</script>
	
	<!-- notification item template -->
	<script type="text/html" id="tpl-notification-item">
		<li class="window-notification-element-wrapper" data-bind="attr:{id:nid, title:desc}">
			<div class="window-notification-element-space"></div>
			<div class="window-notification-element">
				<div class="window-notification-element-edge"></div>
				<div class="window-notification-name" data-bind="text:name"></div>
				<div class="window-notification-desc">
					<div class="window-notification-main-text" data-bind="text:desc"></div>
					<button id="window-notification-close">Close</button>
				</div>
			</div>
		</li>
	</script>
	
	<!-- tree item template -->
	<script type="text/html" id="tpl-tree-item">
		<li class="navigator-tree-row" data-bind="attr:{title:description}">
			<div data-bind="attr:{id:id}, m_tree_row_single_click:row_click_handler">
				<span class="navigator-left-space" data-bind="style:{width:left_edge}"></span>
				<span data-bind="attr:{'x-pos':parent_index_path}, css:{'navigator-tree-row-folder-arrow':folding_type()==1, 'navigator-tree-row-unfolder-arrow':folding_type()==2, 'navigator-tree-row-empy-arrow':folding_type()==0}, click:clickArrow"></span>
				<span data-bind="css:{'navigator-row-folder-icon':type=='folder', 'navigator-tree-row-static-icon':type=='static'||type=='dynamic'}"></span>
				<span class="navigator-tree-row-label" data-bind="text:name"></span>
			</div>
			<ul data-bind="template:{name:'tpl-tree-item', foreach:children}"></ul>
		</li>
	</script>
</head>
<body>
<div id="window-buffer" class="window-buffer">
	<?php if($profile_image): ?>
	<div id="window-hidden-profile-image" class="window-hidden">
		<?=json_encode($profile_image)?>
	</div>
	<?php endif; ?>
	<?php if($session_expire_time): ?>
	<div id="window-hidden-session-expire-time" class="window-hidden">
		<?=$session_expire_time?>
	</div>
	<?php endif; ?>
	<?php if($home_url): ?>
	<div id="window-hidden-home-url" class="window-hidden">
		<?=$home_url?>
	</div>
	<?php endif; ?>
	<?php if($service_list): ?>
	<div id="window-hidden-service-list" class="window-hidden">
	    <?=json_encode($service_list)?>
	</div>
    <?php endif; ?>
</div>

<div class="window-container">
	<div id="window-block-all" class="window-block-all"></div>
	<ul id="window-notification" class="window-notification" 
		data-bind="template:{name:'tpl-notification-item', foreach:noti_items, afterRender:afterRenderItem}"></ul>

	<div id="window-content" class="window-content">
		<div id="window-menu" class="window-menu">
			<div class="window-menu-line"></div>
			<div id="view-port-menu-tab" class="view-port-menu-tab"></div>
			<!-- ko stopBinding: true -->
			<div id="view-port-menu-content" class="view-port-menu-content">
			</div>
			<!-- /ko -->
			<div class="view-port-bottom-status-bar-left">
				<div class="window-bottom-status-bar-line"></div>
			</div>
		</div>
		
		<div id="window-content-display" class="window-content-display">
			<div id="view-port-depth-indicator" class="view-port-depth-indicator" data-bind="m_depth_ind:indicator"></div>
			<!-- ko stopBinding: true -->
			<div id="view-port-content-display" class="view-port-content-display"></div>
			<!-- /ko -->
			<div class="view-port-bottom-status-bar-right">
				<div class="window-bottom-status-bar-line"></div>
				<div class="label-connect-keep-time">
					<span data-bind="text:keep_connect"></span>
					<span data-bind="text:keep_time"></span>
				</div>
				<div class="button-close-navigator"></div>
			</div>
		</div>
	</div>
	
	<div id="window-top-quick-bar" class="window-top-quick-bar">
		<div class="view-port-top-quick-bar">
			<div id="quick-bar-part-center" class="quick-bar-part-center">
				<div id="quick-bark-part-center-content" class="quick-bark-part-center-content" 
					data-bind="style:{width:center_width, left:center_x}, template:{name:'tpl-quick-bar-item', foreach:center_items}"></div>
			</div>
			<div id="quick-bar-part-left" class="quick-bar-part-left" 
				data-bind="css:{'quick-bar-part-left-shadow':center_left_shadow()>0}, template:{name:'tpl-quick-bar-item', foreach:left_items}">
			</div>
			
			<div id="quick-bar-part-right" class="quick-bar-part-right" 
					data-bind="css:{'quick-bar-part-right-shadow':center_right_shadow()>0}">
				<div class="quick-element" style="right:10px;" data-bind="style:{display:right_item()[0]}">
					<div data-bind="attr:{title:right_item()[1], class:right_item()[2]}"></div>
					<div class="quick-icon-name" data-bind="text:right_item()[1]"></div>
				</div>
			</div>
			<div id="quick-element-cursor" class="quick-element-cursor" data-bind="m_quick_cursor:cursor_pos">
				<div class="quick-icon-close"></div>
			</div>
		</div>
		<div id="view-port-quick-bar-scroll" class="view-port-quick-bar-scroll">
			<div id="quick-bar-scroll-bar" class="quick-bar-scroll-bar" 
					data-bind="style:{width:scroll_indicator_width}"></div>
		</div>
	</div>
</div>
</body>
</html>