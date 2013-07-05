<html>
<head>
	<link rel="stylesheet" href="/resource/css/application/admin/main.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/application/component/quick-bar.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/application/component/tree.css" type="text/css" />
	
	<script type="text/javascript" src="/resource/js/lib/jquery/plugin/jquery.cookie.js"></script>
	<script type="text/javascript" src="/resource/js/application/component/quick-bar.js"></script>
	<script type="text/javascript" src="/resource/js/application/view/admin/main.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/main.js"></script>
	
	<script type="text/html" id="tpl-quick-bar-items">
		<div class="quick-element" 
				data-bind="click:clickThis, attr:{id:id}, style:{left:pos}">
			<div data-bind="attr:{title:title, class:className}, css:{'quick-icon-custom':is_image_custom}, style:{'background-image':image}"></div>
			<div class="quick-icon-name" data-bind="text: title"></div>
		</div>
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
</div>

<div class="window-container">
	<div id="window-content" class="window-content">
		<div class="window-menu">
			<div class="window-menu-line"></div>
			<div class="view-port-menu-tab"></div>
		
			<!--
			<div class="view-port-menu-navigator">
				<ul class="navigator-tree">
					<li class="navigator-tree-row">
						<span class="navigator-tree-row-folder-arrow"></span>
						<span class="navigator-row-folder-icon"></span>
						<span class="navigator-tree-row-label">가나다라</span>
					</li>
					<li class="navigator-tree-row">
						<span class="navigator-tree-row-unfolder-arrow"></span>
						<span class="navigator-row-folder-icon"></span>
						<span class="navigator-tree-row-label">dir2</span>
					</li>
					<li class="navigator-tree-row">
						<span class="navigator-tree-row-empy-arrow"></span>
						<span class="navigator-tree-row-unnamed-icon"></span>
						<span class="navigator-tree-row-label">unnamed</span>
					</li>
					<li class="navigator-tree-row">
						<span class="navigator-tree-row-folder-arrow"></span>
						<span class="navigator-row-folder-icon"></span>
						<span class="navigator-tree-row-label">DIR3</span>
					</li>
				</ul>
			
			</div>
			-->
			
		</div>
		
		
		<!--
		<div class="window-content-display">
			<div id="view-port-depth-indicator" class="view-port-depth-indicator" data-bind="m_depth_ind:indicator"></div>
			<div class="view-port-content-display">
			</div>
		</div>
		-->
	</div>
	
	<div id="window-top-quick-bar" class="window-top-quick-bar">
		<div class="view-port-top-quick-bar">
			<div id="quick-bar-part-center" class="quick-bar-part-center">
				<div id="quick-bark-part-center-content" class="quick-bark-part-center-content" 
					data-bind="style:{width:center_width, left:center_x}, template:{name:'tpl-quick-bar-items', foreach:center_items}"></div>
			</div>
			<div id="quick-bar-part-left" class="quick-bar-part-left" 
				data-bind="css:{'quick-bar-part-left-shadow':center_left_shadow()>0}, template:{name:'tpl-quick-bar-items', foreach:left_items}">
			</div>
			
			<div id="quick-bar-part-right" class="quick-bar-part-right" 
					data-bind="css:{'quick-bar-part-right-shadow':center_right_shadow()>0}">
				<div class="quick-element" style="right:10px;" data-bind="style:{display:right_item()[0]}">
					<div data-bind="attr:{title:right_item()[1], class:right_item()[2]}"></div>
					<div class="quick-icon-name" data-bind="text:right_item()[1]"></div>
				</div>
			</div>
			<div id="quick-element-cursor" class="quick-element-cursor" data-bind="m_quick_cursor:cursor_pos"></div>
		</div>
		<div id="view-port-quick-bar-scroll" class="view-port-quick-bar-scroll">
			<div id="quick-bar-scroll-bar" class="quick-bar-scroll-bar" 
					data-bind="style:{width:scroll_indicator_width}"></div>
		</div>
	</div>
	
	<div id="window-bottom-status-bar" class="window-bottom-status-bar">
		<div class="view-port-bottom-status-bar-left"></div>
		<div class="view-port-bottom-status-bar-right">
			<div class="button-close-navigator"></div>
			<div class="label-connect-keep-time">
				<span data-bind="text:keep_connect"></span>
				<span data-bind="text:keep_time"></span>
			</div>
		</div>
	</div>
	
</div>
</body>
</html>