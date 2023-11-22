<?php
namespace Dogger\DoggerSdk;

class Dogger {

    private $config;

    public function __construct($config) {
        $this->config = $config;
        $this->listenToErrors();
    }

    public function logErrorToDogger($error) {
        $this->handleErrorStack($error);
    }

    private function doggerErrorHandler($errno, $errstr, $errfile, $errline) {
        $error = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'trace' => debug_backtrace(),
        ];

        $this->handleErrorStack($error);
    }

    private function listenToErrors() {
        set_error_handler([$this, 'doggerErrorHandler']);
    }

    private function handleErrorStack($error) {
        error_log(json_encode($error));
    }
}