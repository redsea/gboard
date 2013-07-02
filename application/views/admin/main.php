<html>
<head>
	<link rel="stylesheet" href="/resource/css/application/admin/main.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/application/component/tree.css" type="text/css" />
	<script type="text/javascript" src="/resource/js/lib/jquery/plugin/jquery.cookie.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/main.js"></script>
	<script type="text/javascript" src="/resource/js/application/view/admin/main.js"></script>
</head>
<body x-data-expire="<?=$session_expire_time?>" x-data-home-url="<?=$home_url?>">
<div class="window-container">
	<div id="window-buffer" class="window-buffer"></div>
	<div class="window-content">
		<div class="window-menu-navigator">
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
		</div>
		
		<div class="window-content-display">
			<div id="view-port-depth-indicator" class="view-port-depth-indicator" data-bind="m_depth_ind:content_depth"></div>
			<div class="view-port-content-display">
			</div>
		</div>
		
	</div>
	
	<div class="window-top-quick-bar">
		<div class="view-port-top-quick-bar">
			<div id="quick-bar-part-left" class="quick-bar-part-left">
				<div class="quick-element" style="left:10px;" data-bind="click: quickItemClick" id="quick-menu-0">
					<div class="quick-icon-home" data-bind="attr:{title:go_home}"></div>
					<div class="quick-icon-name" data-bind="text:go_home"></div>
				</div>
				<div class="quick-element" style="left:60px;" data-bind="click: quickItemClick" id="quick-menu-1">
					<div class="quick-icon-profile" data-bind="attr:{title:my_profile}"></div>
					<div class="quick-icon-name" data-bind="text:my_profile"></div>
				</div>
				<div id="quick-element-cursor" class="quick-element-cursor" data-bind="m_quick_cursor:quick_cursor_pos"></div>
			</div>
			<div class="quick-bark-part-left-shadow">
			</div>
			
			<div class="quick-bar-part-right">
				<!-- 테스트로 일단 하나 넣어 본 것임. 여기에 들어갈 것 생각 해 봐야 한다 -->
				<!-- 여기는 커서가 오면 안된다 -->
				<div class="quick-element" style="right:10px;">
					<div class="quick-icon-profile" data-bind="attr:{title:my_profile}"></div>
					<div class="quick-icon-name" data-bind="text:my_profile"></div>
				</div>
			</div>
			
			<div id="quick-bar-part-center" class="quick-bar-part-center">
				<!-- 테스트로 일단 하나 넣어 본 것임. 여기에 들어갈 것 생각 해 봐야 한다 -->
				<div class="quick-element" style="left:5px;" data-bind="click: quickItemClick" id="quick-menu-2">
					<div class="quick-icon-profile" data-bind="attr:{title:my_profile}"></div>
					<div class="quick-icon-name" data-bind="text:my_profile"></div>
				</div>
				
				<div class="quick-element" style="left:55px;" data-bind="click: quickItemClick" id="quick-menu-2">
					<div class="quick-icon-profile" data-bind="attr:{title:my_profile}"></div>
					<div class="quick-icon-name" data-bind="text:my_profile"></div>
				</div>
				
				<div class="quick-element" style="left:105px;" data-bind="click: quickItemClick" id="quick-menu-2">
					<div class="quick-icon-profile" data-bind="attr:{title:my_profile}"></div>
					<div class="quick-icon-name" data-bind="text:my_profile"></div>
				</div>
				
				<div class="quick-element" style="left:155px;" data-bind="click: quickItemClick" id="quick-menu-2">
					<div class="quick-icon-profile" data-bind="attr:{title:my_profile}"></div>
					<div class="quick-icon-name" data-bind="text:my_profile"></div>
				</div>
				
				<div class="quick-element" style="left:205px;" data-bind="click: quickItemClick" id="quick-menu-2">
					<div class="quick-icon-profile" data-bind="attr:{title:my_profile}"></div>
					<div class="quick-icon-name" data-bind="text:my_profile"></div>
				</div>
				
				<div class="quick-element" style="left:255px;" data-bind="click: quickItemClick" id="quick-menu-2">
					<div class="quick-icon-profile" data-bind="attr:{title:my_profile}"></div>
					<div class="quick-icon-name" data-bind="text:my_profile"></div>
				</div>
			</div>
			
<!--
			<div class="quick-element" data-bind="click: quickItemClick" id="quick-menu-2">
				<div class="quick-icon-profile" data-bind="attr:{title:my_profile}"></div>
				<div class="quick-icon-name" data-bind="text:my_profile"></div>
			</div>
-->
			
			
		</div>
	</div>
	
	<div class="window-bottom-status-bar">
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