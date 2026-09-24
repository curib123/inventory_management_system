<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ui_modal_action_group')) {
    function ui_modal_action_group($actions) {
        if (empty($actions) || !is_array($actions)) {
            return '';
        }

        $html = '<div class="d-flex flex-wrap gap-1 app-table-actions">';

        foreach ($actions as $action) {
            $label = isset($action['label']) ? $action['label'] : 'Action';
            $url = isset($action['url']) ? $action['url'] : '#';
            $variant = isset($action['variant']) ? $action['variant'] : 'secondary';
            $icon = isset($action['icon']) ? $action['icon'] : '';

            $variant = preg_replace('/[^a-z0-9-]/i', '', $variant);
            $icon = preg_replace('/[^a-z0-9-]/i', '', $icon);

            $html .= '<button type="button" class="btn btn-sm btn-outline-' . $variant . '" data-modal-url="' . html_escape($url) . '">';

            if ($icon !== '') {
                $html .= '<i class="bi ' . $icon . ' me-1"></i>';
            }

            $html .= html_escape($label) . '</button>';
        }

        $html .= '</div>';

        return $html;
    }
}
