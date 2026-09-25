<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Exceptions extends CI_Exceptions {

    private function error_id() {
        try {
            return 'ERR-' . strtoupper(bin2hex(random_bytes(5)));
        } catch (Exception $exception) {
            return 'ERR-' . strtoupper(substr(sha1(uniqid('', TRUE)), 0, 10));
        }
    }

    private function log_reference() {
        $extension = config_item('log_file_extension');
        $extension = is_string($extension) && trim($extension) !== ''
            ? ltrim(trim($extension), '.')
            : 'php';

        return 'application/logs/log-' . date('Y-m-d') . '.' . $extension;
    }

    private function render_app_error(
        $heading,
        $message,
        $status_code = 500,
        $category = 'Application Error',
        $error_id = '',
        $source = ''
    ) {
        if (is_cli()) {
            echo "\n" . strip_tags((string) $heading) . "\n";
            echo strip_tags((string) $message) . "\n";
            if ($error_id !== '') {
                echo 'Error ID: ' . $error_id . "\n";
            }
            if ($source !== '') {
                echo 'Source: ' . $source . "\n";
            }
            echo 'Log: ' . $this->log_reference() . "\n\n";
            return;
        }

        set_status_header((int) $status_code);

        if (!headers_sent()) {
            if ($error_id !== '') {
                header('X-Error-Reference: ' . $error_id);
            }

            header('X-Error-Log: ' . $this->log_reference());
        }

        $app_error_heading = (string) $heading;
        $app_error_message = is_array($message)
            ? implode(' ', array_map('strip_tags', $message))
            : strip_tags((string) $message);
        $app_error_code = (int) $status_code;
        $app_error_category = (string) $category;
        $app_error_id = (string) $error_id;
        $app_error_log_reference = $this->log_reference();
        $app_error_source = (
            defined('ENVIRONMENT') &&
            ENVIRONMENT === 'development'
        ) ? (string) $source : '';

        $templates_path = config_item('error_views_path');

        if (empty($templates_path)) {
            $templates_path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR;
        }

        include $templates_path . 'html' . DIRECTORY_SEPARATOR . '_app_error.php';
    }

    public function show_404($page = '', $log_error = TRUE) {
        $error_id = $this->error_id();
        $message = 'The page, route, or record you requested could not be found.';

        if ($log_error) {
            log_message(
                'error',
                '[' . $error_id . '] 404 Not Found: ' . (string) $page
            );
        }

        $this->render_app_error(
            'Page or Record Not Found',
            $message,
            404,
            'Not Found',
            $error_id,
            trim((string) $page)
        );

        exit(4);
    }

    public function show_error(
        $heading,
        $message,
        $template = 'error_general',
        $status_code = 500
    ) {
        $error_id = $this->error_id();
        $category = 'Application Error';

        if ($template === 'error_db') {
            $category = 'Database Error';
        } elseif ((int) $status_code === 401) {
            $category = 'Authentication Error';
        } elseif ((int) $status_code === 403) {
            $category = 'Permission Error';
        } elseif ((int) $status_code === 404) {
            $category = 'Not Found';
        } elseif ((int) $status_code === 405) {
            $category = 'Request Error';
        }

        $plain_message = is_array($message)
            ? implode(' | ', array_map('strip_tags', $message))
            : strip_tags((string) $message);
        $public_message = $plain_message;

        if ($template === 'error_db') {
            $public_message = 'The application could not complete the requested database operation. Use the Error ID below to find the detailed database error in the application log.';
        }

        log_message(
            'error',
            '[' . $error_id . '] ' .
            'HTTP ' . (int) $status_code . ' ' .
            strip_tags((string) $heading) . ' --> ' .
            $plain_message
        );

        if (is_cli()) {
            return parent::show_error($heading, $message, $template, $status_code);
        }

        ob_start();
        $this->render_app_error(
            $heading,
            $public_message,
            $status_code,
            $category,
            $error_id
        );
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    public function show_exception($exception) {
        $error_id = $this->error_id();
        $message = $exception->getMessage();

        if ($message === '') {
            $message = 'An unexpected exception occurred.';
        }

        $source = basename($exception->getFile()) . ':' . $exception->getLine();

        log_message(
            'error',
            '[' . $error_id . '] Uncaught ' . get_class($exception) .
            ': ' . $message . ' --> ' .
            $exception->getFile() . ' ' . $exception->getLine() .
            "\nStack trace:\n" . $exception->getTraceAsString()
        );

        if (is_cli()) {
            $this->render_app_error(
                'Unexpected Server Error',
                $message,
                500,
                'Exception',
                $error_id,
                $source
            );
            return;
        }

        if (ob_get_level() > $this->ob_level + 1) {
            @ob_end_clean();
        }

        $this->render_app_error(
            'Unexpected Server Error',
            (
                defined('ENVIRONMENT') && ENVIRONMENT === 'development'
                    ? $message
                    : 'The server encountered an unexpected problem while processing the request.'
            ),
            500,
            'Exception',
            $error_id,
            $source
        );
    }

    public function show_php_error($severity, $message, $filepath, $line) {
        $error_id = $this->error_id();
        $severity_name = isset($this->levels[$severity])
            ? $this->levels[$severity]
            : (string) $severity;
        $source = basename((string) $filepath) . ':' . (int) $line;

        log_message(
            'error',
            '[' . $error_id . '] Severity: ' . $severity_name .
            ' --> ' . $message . ' ' . $filepath . ' ' . $line
        );

        if (is_cli()) {
            $this->render_app_error(
                'Application Runtime Error',
                $message,
                500,
                'PHP Runtime Error',
                $error_id,
                $source
            );
            return;
        }

        if (ob_get_level() > $this->ob_level + 1) {
            @ob_end_clean();
        }

        $this->render_app_error(
            'Application Runtime Error',
            (
                defined('ENVIRONMENT') && ENVIRONMENT === 'development'
                    ? $message
                    : 'The application encountered a runtime problem while processing the request.'
            ),
            500,
            'PHP Runtime Error',
            $error_id,
            $source
        );
    }
}
