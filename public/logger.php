<?php
function log_message($message) {
    $log_file = __DIR__ . '/../logs/log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
?>
