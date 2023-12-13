<?php
namespace Dogger\DoggerSdk;

use Exception;

class Dogger {

    private $config;
    public function __construct($config) {
        if ($config->key && $config->url && $config->env) {
            echo "Please give correct config to dogger instance";
            return;
        }

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
        $this->send($error);

    }

    private function send($error) {
        try {
            $dogger_key = $this->config->key;
            $dogger_env = $this->config->env;

            $payload = <<<DATA
            {
                http_code: 400,
                message: $error->message,
                stacktrace: $error->trace,
                type: error,
                env: $dogger_env
            }
            DATA;

            $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer $dogger_key"
            );

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $this->config->url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);

            curl_exec($curl);
            curl_close($curl);
        } catch (Exception $e) {
            echo "Can't send error to dogger please check yourn configuration";
        }
    }
}