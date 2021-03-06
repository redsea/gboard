<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "welcome";
$route['404_override'] = '';


// route for controllers/admin/admin.php
$route['admin'] = 'admin/admin/index';
$route['admin/(:any)'] = 'admin/admin/$1';

// route for controllers/member/member.php
$route['member'] = 'member/member/index';
$route['member/(:any)'] = 'member/member/$1';

// route for controllers/mfile/mfile.php
$route['file'] = 'mfile/mfile/index';
$route['file/(:any)'] = 'mfile/mfile/$1';

// route for controllers/member/member.php
$route['oauth'] = 'oauth20/oauth20/index';
$route['oauth/(:any)'] = 'oauth20/oauth20/$1';

// route for controllers/service/service.php
$route['service'] = 'service/service/index';
$route['service/(:any)'] = 'service/service/$1';

// route for controllers/mlanguage/mlanguage.php
$route['lang'] = 'mlanguage/mlanguage/index';
$route['lang/(:any)'] = 'mlanguage/mlanguage/$1';

// route for controllers/board/board.php
$route['board'] = 'board/board/index';
$route['board/(:any)'] = 'board/board/$1';


/* End of file routes.php */
/* Location: ./application/config/routes.php */