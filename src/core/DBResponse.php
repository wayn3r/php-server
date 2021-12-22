<?php

namespace Core;

class DBResponse {
    public bool $error;
    public string $message;
    public $result;
    function __construct($result = null, bool $error = false, string $message = '') {
        $this->error = $error;
        $this->result = $result;
        $this->message = utf8_encode($message);
    }
}
