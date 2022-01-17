<?php

namespace Http;

class UrlException extends \Exception {

    public function __toString() {
        return get_class($this) . " '{$this->message}'";
    }
}
