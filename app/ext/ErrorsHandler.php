<?php

namespace app\ext;

class ErrorsHandler
{
    public static function fatal_handler()
    {
        $errors = error_get_last();
        if (!is_null($errors) && in_array($errors['type'] ?? 0, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            self::return_error($errors);
        }
    }

    protected static function return_error(array $errors)
    {
        self::write_log($errors);
        $code = 500;
        $file = ($errors['file'] ?? 'n/a') . ' (line ' . ($errors['line'] ?? 'n/a') . ')';
        $error = $errors['message'] ?? 'Unknown error';
        exit(require $_SERVER['DOCUMENT_ROOT'] . '/app/page/custom/error/error.php');
    }

    public static function error_handler(int $error_level, string $error_message, ?string $error_file = null, ?int $error_line = null): bool
    {
        if (!(error_reporting() & $error_level)) {
            return false;
        }

        $error = [
            'type'    => $error_level,
            'message' => $error_message,
            'file'    => $error_file,
            'line'    => $error_line,
        ];

        switch ($error_level) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_PARSE:
                self::return_error($error);
                return true;
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                self::return_error($error);
                return true;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            default:
                return true;
        }

    }

    protected static function write_log(array $errors): void
    {
        $logDir = __DIR__ . '/../logs/';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $type = $errors['type'] ?? $errors['lvl'] ?? 0;
        $file = $errors['file'] ?? 'n/a';
        $msg  = $errors['message'] ?? 'n/a';
        $line = $errors['line'] ?? 'n/a';

        $record = sprintf(
            "%s - NEW ERROR [%s] \n FILE - %s \n MESSAGE - %s \n LINE - %s \n ",
            date('d-m-Y H:i:s'),
            (string)$type,
            $file,
            $msg,
            (string)$line
        );

        file_put_contents($logDir . date('d-m-Y') . '.txt', $record, FILE_APPEND | LOCK_EX);
    }

    public function setErrors()
    {
        set_error_handler([self::class, 'error_handler']);
        register_shutdown_function([self::class, 'fatal_handler']);
    }
}
