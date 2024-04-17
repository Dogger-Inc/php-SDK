<?php
namespace Dogger\DoggerSdk;

use Exception;

class Dogger {

    private $config;
    private $performances = [];
    public function __construct($config) {
        if (!array_key_exists('key', $config) || !array_key_exists('url', $config) || !array_key_exists('env', $config)) {
            echo "DOGGER - Please give correct config to dogger instance. \n";
            return;
        }

        error_reporting(E_ALL);

        $this->config = $config;
        $this->listenToErrors();
    }

    public function logErrorToDogger($error) {
        $this->handleErrorStack($error);
    }

    function doggerErrorHandler($errno, $errstr, $errfile, $errline) {
        $trace = json_encode(debug_backtrace());
        $error = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'trace' => "$trace",
        ];

        $this->handleErrorStack($error);
        return true;
    }

    // function doggerFatalErrorHandler() {
    //     $lastError = error_get_last();

    //     if(!$lastError) return;

    //     $line = $lastError['line'];
    //     $file = $lastError['file'];

    //     $error = [
    //         'type' => $lastError['type'],
    //         'message' => $lastError['message'],
    //         'file' => $file,
    //         'line' => $line,
    //         'trace' => "$file $line",
    //     ];

    //     $this->handleErrorStack($error);
    //     return true;
    // }

    public function startRecord($id) {
        $this->startTimer($id);
    }

    public function stopRecord($id, $threshold = 0) {
        $this->stopTimer($id, $threshold);
    }

    private function listenToErrors() {
        // register_shutdown_function([$this, 'doggerFatalErrorHandler']);
        set_error_handler([$this, 'doggerErrorHandler']);
    }

    private function handleErrorStack($error) {
        $this->sendError($error);
    }

    private function sendError($error) {
        try {
            $dogger_key = $this->config['key'];
            $dogger_env = $this->config['env'];
            $dogger_url = $this->config['url'];

            $payload = [
                'http_code' => 400,
                'message' => $error['message'],
                'stacktrace' => $error['trace'],
                'type' => 'error',
                'env' => $dogger_env
            ];

            $jsonPayload = json_encode($payload);

            $ch = curl_init("$dogger_url/api/issues/new");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer $dogger_key"
            ));

            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            echo "DOGGER - Can't send error to dogger please check yourn configuration";
        }
    }

    private function startTimer($id) {
        $entry = [
            'id' => $id,
            'startTimestamp' => floor(microtime(true) * 1000)
        ];
        $this->performances[] = $entry;
    }

    private function stopTimer($id, $threshold) {
        $index = array_search($id, array_column($this->performances, 'id'));

        if ($index !== false) {
            $entry = $this->performances[$index];
            array_splice($this->performances, $index, 1);

            if($entry){
                $endTimestamp = floor(microtime(true) * 1000);
                $elapsedTime = $endTimestamp - $entry['startTimestamp'];
                $pushToDatabase = $elapsedTime >= $threshold;
                $perf = [
                    'id' => $entry['id'],
                    'startTimestamp' => $entry['startTimestamp'],
                    'endTimestamp' => $endTimestamp,
                    'elapsedTime' => $elapsedTime
                ];
                
                if ($pushToDatabase) {
                    $this->sendPerformances($perf);
                }
            }
        }
    }

    private function sendPerformances($performance) {
        try {
            $dogger_key = $this->config['key'];
            $dogger_env = $this->config['env'];
            $dogger_url = $this->config['url'];

            $payload = [
                'duration' => $performance['elapsedTime'],
                'comment' => $performance['id'],
                'env' => $dogger_env
            ];

            $jsonPayload = json_encode($payload);

            $ch = curl_init("$dogger_url/api/performances/new");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer $dogger_key"
            ));

            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            echo "DOGGER - Can't send error to dogger please check yourn configuration";
        }
    }
}