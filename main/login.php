<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" href="css/jquery/smoothness/jquery-ui-1.10.2.custom.min.css" type="text/css" />
	
	<link rel="stylesheet" href="css/view/common.css" type="text/css" />
	<link rel="stylesheet" href="css/view/admin_login.css" type="text/css" />
	
	<!--style type="text/css"></style-->
	
	<script type="text/javascript" src="js/lib/jquery/core/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="js/lib/jquery/ui/jquery-ui-1.10.2.custom.min.js"></script>
	<script type="text/javascript" src="js/lib/knockout/knockout-2.2.1.js"></script>
	<script type="text/javascript" src="js/login/admin.js"></script>
	
	<!--script type="text/javascript"></script-->
	
	<title>Gamma Board</title>
</head>
<body>

<div class="window-container">
	<div class="window-content">
		<div class="view-port-content-display">
			<div class="window-login">
				<div class="view-port-login-header"><span class="login-header-text">Login</span></div>
				<div class="view-port-login-body">
				
					<div class="login-input-box">
						<label for="user_id">사용자 아이디</label>
						<input class="login-body-input" type="text" id="user_id" autofocus />
					</div>
					<div class="login-input-box">
						<label for="password">암호</label>
						<input class="login-body-input" type="text" id="password" />
					</div>
					
					<button id="login-btn">로그인</button>
				</div>
			</div>
		</div>
	</div>
	
	<div class="window-top-quick-bar">
		<div class="view-port-top-quick-bar"></div>
	</div>
	<div class="window-bottom-status-bar">
	</div>
	

</div>



</body>
</html>