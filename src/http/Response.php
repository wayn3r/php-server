<?php

namespace Http;

class Response {
    private int $status;
    private $response;
    private string $error;

    private bool $utf8 = true;
    private string $contentType;
    private string $contentDisposition;
    private string $cacheControl;

    public function __construct(int $status, $response) {
        $this->status = $status;
        $this->response = $response;
        $this->contentType = 'application/json';
    }

    public function __toString() {
        if (empty($this->error)) unset($this->error);
        if (empty($this->warning)) unset($this->warning);
        switch ($this->contentType) {
            case 'application/json':
                $response = json_encode([
                    'status' => $this->status,
                    'response' => $this->response,
                    'error' => $this->error,
                ]);
                break;
            default:
                $response = $this->response;
                break;
        }
        if (!$response) {
            $this->response = 'Error al codificar la respuesta';
            $this->status = INTERNAL_SERVER_ERROR;
            $this->contentType = 'application/json';
            $response = json_encode($this);
        }
        // codificando la respuesta
        $this->header();
        return $this->utf8 ? utf8_encode($response) : $response;
    }

    private function header(): void {
        http_response_code($this->status);
        if (!empty($this->contentType))
            header("Content-Type: {$this->contentType}");
        if (!empty($this->contentDisposition)) {
            header("Content-Disposition: {$this->contentDisposition}");
            header("Access-Control-Expose-Headers: Content-Disposition");
        }
        if (!empty($this->cacheControl))
            header("Cache-Control: {$this->cacheControl}");
    }
    public function status(int $status) {
        $this->status = $status;
        return $this;
    }
    public function error(string $error) {
        $this->error = $error;
        return $this;
    }
    public function utf8(bool $utf8) {
        $this->utf8 = $utf8;
        return $this;
    }
    public function contentType(string $contentType) {
        $this->contentType = utf8_decode($contentType);
        return $this;
    }
    public function contentDisposition(string $contentDisposition) {
        $this->contentDisposition = utf8_decode($contentDisposition);
        return $this;
    }
    public function cacheControl(string $cacheControl) {
        $this->cacheControl = utf8_decode($cacheControl);
        return $this;
    }
}
