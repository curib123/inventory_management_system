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

if (!function_exists('ui_modal_form_attributes')) {
    function ui_modal_form_attributes($confirmation = array(), $extra = array()) {
        $attributes = array_merge(
            array('data-modal-form' => '1'),
            is_array($extra) ? $extra : array()
        );

        if (empty($confirmation) || !is_array($confirmation)) {
            return $attributes;
        }

        $attributes['data-confirm-required'] = '1';
        $attributes['data-confirm-title'] = html_escape(
            isset($confirmation['title'])
                ? (string) $confirmation['title']
                : 'Confirm changes'
        );
        $attributes['data-confirm-message'] = html_escape(
            isset($confirmation['message'])
                ? (string) $confirmation['message']
                : 'Review the information before continuing.'
        );
        $attributes['data-confirm-label'] = html_escape(
            isset($confirmation['label'])
                ? (string) $confirmation['label']
                : 'Confirm'
        );
        $attributes['data-confirm-variant'] = isset($confirmation['variant'])
            ? preg_replace('/[^a-z0-9-]/i', '', (string) $confirmation['variant'])
            : 'primary';
        $attributes['data-confirm-icon'] = isset($confirmation['icon'])
            ? preg_replace('/[^a-z0-9-]/i', '', (string) $confirmation['icon'])
            : 'bi-check2-circle';

        if (!empty($confirmation['assist'])) {
            $attributes['data-confirm-assist'] = html_escape((string) $confirmation['assist']);
        }

        if (!empty($confirmation['impact'])) {
            $attributes['data-confirm-impact'] = html_escape((string) $confirmation['impact']);
        }

        return $attributes;
    }
}
