<html>
<head>
	<link rel="stylesheet" href="/resource/css/application/admin/login.css" type="text/css" />
	<script type="text/javascript" src="/resource/js/lib/jquery/plugin/jquery.cookie.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/data/url.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/login.js"></script>
	<script type="text/javascript" src="/resource/js/application/view/admin/login.js"></script>
</head>
<body>
<div class="window-container">
	<div class="window-content">
		<div class="view-port-content-display" style="top:0px;">
			<div class="window-login">
				<div class="view-port-login-header"><span class="login-header-text">Login</span></div>
				<div class="view-port-login-body">
					<div class="login-input-box">
						<label for="user_id" data-bind="text:type_user_id">사용자 아이디</label>
						<input class="login-body-input" data-bind="m_input_enabled:input_enable, hasfocus:user_id_focus" type="text" id="user_id" maxlength="64" tabindex="1" />
					</div>
					<div class="login-input-box">
						<label for="password" data-bind="text:type_user_password">암호</label>
						<input class="login-body-input" data-bind="m_input_enabled:input_enable, hasfocus:user_password_focus" type="password" id="password" maxlength="64" tabindex="2" />
					</div>
					
					<button id="login-btn" data-bind="m_button:button_login, m_button_enabled:button_enable">로그인</button>
					<div class="login-result" data-bind="m_visible:vis_login_process">
						<div class="login-result-txt" data-bind="text:try_login"></div>
						<div class="login-result-txt" data-bind="text:progress"></div>
					</div>
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