<?php

namespace Core;

class HttpResponse {
    public int $status;
    public $response;
    public string $error;
    public string $warning;

    private bool $utf8_encode = true;
    private string $content_type;
    private string $content_disposition;
    private string $cache_control;

    public function __construct(int $status, $response, string $error = '') {
        $this->status = utf8_encode($status);
        $this->response = $response;
        $this->error = utf8_encode($error);
        $this->content_type = 'application/json';
    }

    public function __toString() {
        if (empty($this->error)) unset($this->error);
        if (empty($this->warning)) unset($this->warning);
        switch ($this->content_type) {
            case 'application/json':
                $this->response = json_decode(json_encode($this->response));
                $response = json_encode($this);
                break;
            default:
            case 'image/png':
            case 'image/jpeg':
                $response = $this->response;
                break;
        }
        if (!$response) {
            $this->response = 'Error al codificar la respuesta';
            $this->status = INTERNAL_SERVER_ERROR;
            $this->content_type = 'application/json';
            $response = json_encode($this);
        }
        // codificando la respuesta
        if ($this->utf8_encode) $response = utf8_encode($response);
        $this->header();
        return $response;
    }

    public function utf8_encode(bool $utf8_encode): bool {
        return $this->utf8_encode = $utf8_encode;
    }
    public function content_type(string $content_type): string {
        return $this->content_type = utf8_decode($content_type);
    }
    public function content_disposition(string $content_disposition): string {
        return $this->content_disposition = utf8_decode($content_disposition);
    }
    public function cache_control(string $cache_control): string {
        return $this->cache_control = utf8_decode($cache_control);
    }
    public static function cast(\Core\HttpResponse $response): \Core\HttpResponse {
        return $response;
    }
    /**
     * Establece el header de la repuesta de la petición en base al \Core\HttpResponse
     * @param \Core\HttpResponse $response
     * La respuesta de la petición
     * @return void
     */
    private function header(): void {
        http_response_code($this->status);
        if (!empty($this->content_type))
            header("Content-Type: {$this->content_type}");
        if (!empty($this->content_disposition)) {
            header("Content-Disposition: {$this->content_disposition}");
            header("Access-Control-Expose-Headers: Content-Disposition");
        }
        if (!empty($this->cache_control))
            header("Cache-Control: {$this->cache_control}");
    }
}
