<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Permission dependencies
|--------------------------------------------------------------------------
| Action permissions automatically include the view/history permission needed
| to navigate to the module and complete redirects after a successful action.
*/
$config['permission_dependencies'] = array(
    'products.create' => array('products.view'),
    'products.edit' => array('products.view'),
    'products.delete' => array('products.view'),

    'categories.create' => array('categories.view'),
    'categories.edit' => array('categories.view'),
    'categories.delete' => array('categories.view'),

    'suppliers.create' => array('suppliers.view'),
    'suppliers.edit' => array('suppliers.view'),
    'suppliers.delete' => array('suppliers.view'),

    'stock.stock_in' => array('stock.history'),
    'stock.stock_out' => array('stock.history'),
    'stock.adjust' => array('stock.history'),

    'reports.export' => array('reports.view'),

    'users.create' => array('users.view'),
    'users.edit' => array('users.view'),
    'users.delete' => array('users.view'),

    'roles.create' => array('roles.view'),
    'roles.edit' => array('roles.view'),
    'roles.delete' => array('roles.view'),
    'roles.permissions' => array('roles.view')
);
