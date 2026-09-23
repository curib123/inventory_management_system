<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//authentication routes
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';

//dashboard routes
$route['dashboard'] = 'dashboard';


//products routes
$route['products'] = 'products';
$route['products/add'] = 'products/add';
$route['products/edit/(:num)'] = 'products/edit/$1';
$route['products/delete/(:num)'] = 'products/delete/$1';


//categories routes
$route['categories'] = 'categories';
$route['categories/add'] = 'categories/add';
$route['categories/edit/(:num)'] = 'categories/edit/$1';
$route['categories/delete/(:num)'] = 'categories/delete/$1';

//suppliers routes
$route['suppliers'] = 'suppliers';
$route['suppliers/add'] = 'suppliers/add';
$route['suppliers/edit/(:num)'] = 'suppliers/edit/$1';
$route['suppliers/delete/(:num)'] = 'suppliers/delete/$1';

//authorization routes
$route['roles'] = 'roles';
$route['roles/add'] = 'roles/add';
$route['roles/edit/(:num)'] = 'roles/edit/$1';
$route['roles/delete/(:num)'] = 'roles/delete/$1';


// stock management routes
$route['stock'] = 'stock/history';
$route['stock/history'] = 'stock/history';
$route['stock/details/(:num)'] = 'stock/details/$1';
$route['stock/in'] = 'stock/stock_in';
$route['stock/out'] = 'stock/stock_out';
$route['stock/adjustment'] = 'stock/adjustment';
$route['stock/adjustments'] = 'stock/adjustments';
$route['stock/low-stock'] = 'stock/low_stock';


//reports management routes
$route['reports'] = 'reports';
$route['reports/inventory'] = 'reports/inventory';
$route['reports/stock-in'] = 'reports/stock_in';
$route['reports/stock-out'] = 'reports/stock_out';
$route['reports/movement'] = 'reports/movement';
$route['reports/low-stock'] = 'reports/low_stock';
$route['reports/valuation'] = 'reports/valuation';
$route['reports/export/(:any)/(:any)'] = 'reports/export/$1/$2';
