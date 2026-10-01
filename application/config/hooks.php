<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$hook['post_controller_constructor'] = array(
    'class' => 'Session_version_guard',
    'function' => 'enforce',
    'filename' => 'Session_version_guard.php',
    'filepath' => 'hooks'
);
