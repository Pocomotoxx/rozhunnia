<?php
class Logger {
    private $file;

    public function __construct($file = __DIR__ . '/../server.log') {
        $this->file = $file;
    }

    public function log($message) {
        $entry = '[' . date('c') . '] ' . $message . "\n";
        file_put_contents($this->file, $entry, FILE_APPEND);
    }
}
?>
