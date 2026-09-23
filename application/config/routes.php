<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['dashboard'] = 'dashboard';
$route['products'] = 'products';
$route['products/add'] = 'products/add';
$route['products/edit/(:num)'] = 'products/edit/$1';
$route['products/delete/(:num)'] = 'products/delete/$1';
$route['suppliers'] = 'suppliers';
$route['suppliers/add'] = 'suppliers/add';
$route['suppliers/edit/(:num)'] = 'suppliers/edit/$1';
$route['suppliers/delete/(:num)'] = 'suppliers/delete/$1';
