<?php

namespace Http;

final class Response {
    private function header(string $key, string $value): void {
        $key = utf8_decode($key);
        $value = utf8_decode($value);
        header($key . ': ' . $value);
    }

    public function status(int $status) {
        http_response_code($status);
        return $this;
    }
    public function contentType(string $contentType) {
        $this->header('Content-Type', $contentType);
        return $this;
    }
    public function contentDisposition(string $contentDisposition) {
        $this->header('Content-Disposition', $contentDisposition);
        $this->header('Access-Control-Expose-Headers', 'Content-Disposition');
        return $this;
    }
    public function cacheControl(string $cacheControl) {
        $this->header('Cache-Control', $cacheControl);
        return $this;
    }
    public function send($data) {
        echo $data;
        return $this;
    }
    public function json($data) {
        $this->contentType('application/json');
        return $this->send(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
