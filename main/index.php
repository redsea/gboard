<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" href="css/jquery/smoothness/jquery-ui-1.10.2.custom.min.css" type="text/css" />
	
	<link rel="stylesheet" href="css/view/common.css" type="text/css" />
	<link rel="stylesheet" href="css/view/ui-component.css" type="text/css" />
	
	<!--style type="text/css"></style-->
	
	<script type="text/javascript" src="js/lib/jquery/core/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="js/lib/jquery/ui/jquery-ui-1.10.2.custom.min.js"></script>
	<script type="text/javascript" src="js/lib/knockout/knockout-2.2.1.js"></script>
	
	<!--script type="text/javascript"></script-->
	
	<title>Gamma Board</title>
</head>
<body>

<div class="window-container">
	
	
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
			<div class="view-port-depth-indicator"></div>
			<div class="view-port-content-display">
				
				<!-- 임시로 로그인 하기 위해서 만든 것임 -->
				
				UserID : <input type="text" name="user_id" /><br/>
				Password : <input type="text" name="user_password" /><br/>
				<button type="button">Ok</button>
				
			</div>
		</div>
	</div>
	
	<div class="window-top-quick-bar">
		<div class="view-port-top-quick-bar"></div>
	</div>
	<div class="window-bottom-status-bar">
		<div class="view-port-bottom-status-bar-left"></div>
		<div class="view-port-bottom-status-bar-right">
			<div class="button-close-navigator"></div>
		</div>
	</div>
	

</div>



</body>
</html>