<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
$scheme = $https ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string) $_SERVER['HTTP_HOST']) : 'localhost';
$script_name = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '/index.php';
$base_path = rtrim(str_replace('\\', '/', dirname($script_name)), '/');
$base_path = ($base_path === '.' || $base_path === '/') ? '' : $base_path;

$config['base_url'] = $scheme . '://' . $host . $base_path . '/';
$config['index_page'] = '';
$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language'] = 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = FALSE;
$config['subclass_prefix'] = 'MY_';
$config['composer_autoload'] = FALSE;
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
$config['enable_query_strings'] = FALSE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd';
$config['allow_get_array'] = TRUE;
$config['log_threshold'] = (ENVIRONMENT === 'development') ? 1 : 2;
$config['log_path'] = APPPATH . 'logs/';
$config['log_file_extension'] = 'log';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';
$config['error_views_path'] = '';
$config['cache_path'] = '';
$config['cache_query_string'] = FALSE;
$environment_key = getenv('INVENTORY_ENCRYPTION_KEY');

if ($environment_key === FALSE || trim($environment_key) === '') {
    if (ENVIRONMENT === 'production') {
        exit('INVENTORY_ENCRYPTION_KEY must be configured in production.');
    }

    // Development-only fallback so local XAMPP clones still run without
    // shipping a reusable production secret in source control.
    $environment_key = hash(
        'sha256',
        FCPATH . '|' . php_uname('n') . '|inventory-development'
    );
}

$config['encryption_key'] = $environment_key;
$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_expiration'] = 7200;
$config['sess_save_path'] = sys_get_temp_dir();
$config['sess_match_ip'] = FALSE;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = TRUE;
$config['cookie_prefix'] = '';
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_secure'] = $https;
$config['cookie_httponly'] = TRUE;
$config['standardize_newlines'] = FALSE;
$config['global_xss_filtering'] = FALSE;
$config['csrf_protection'] = TRUE;
$config['csrf_token_name'] = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_cookie';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = TRUE;
$config['csrf_exclude_uris'] = array();
$config['compress_output'] = FALSE;
$config['time_reference'] = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';

/*
|--------------------------------------------------------------------------
| UI styling
|--------------------------------------------------------------------------
| TRUE  = Bootstrap, Bootstrap Icons, DataTables CSS, and app CSS are loaded.
| FALSE = No CSS stylesheets are loaded; pages render as plain HTML while
|         server-side features, forms, validation, and permissions still work.
*/
$config['ui_styling_enabled'] = TRUE;

/*
|--------------------------------------------------------------------------
| Per-page and per-modal styling
|--------------------------------------------------------------------------
| These are internal configuration switches only; there is no UI control.
| Route-specific keys such as "categories/index" override controller-wide
| keys such as "categories". "*" is the fallback.
*/
$config['ui_page_styles'] = array(
    '*' => TRUE
);

$config['ui_modal_styles'] = array(
    '*' => TRUE
);

/*
|--------------------------------------------------------------------------
| Private local UI overrides
|--------------------------------------------------------------------------
| Optional local-only file. It is git-ignored so personal demo/prank display
| settings do not need to be committed or shared with the rest of the team.
|
| Return format:
| array(
|     'pages' => array('categories/index' => FALSE),
|     'modals' => array('categories' => TRUE)
| );
*/
$private_ui_file = APPPATH . 'config/ui_style.local.php';

if (is_file($private_ui_file)) {
    $private_ui = include $private_ui_file;

    if (is_array($private_ui)) {
        if (isset($private_ui['pages']) && is_array($private_ui['pages'])) {
            $config['ui_page_styles'] = array_replace(
                $config['ui_page_styles'],
                $private_ui['pages']
            );
        }

        if (isset($private_ui['modals']) && is_array($private_ui['modals'])) {
            $config['ui_modal_styles'] = array_replace(
                $config['ui_modal_styles'],
                $private_ui['modals']
            );
        }
    }
}

