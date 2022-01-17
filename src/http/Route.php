<?php

namespace Http;

final class Route {
    const ALL = 'ALL';
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    private const URL_PARAM_ID = ':';
    private const URL_PARAM_REGEX = '([^\/]+)';

    /** @var callable[] $controllers */
    private array $controllers;

    private string $method;

    private array $params;

    private string $path;

    private bool $hasRouter;

    private string $urlMatchRegex;

    public function __construct(
        string $method,
        string $path,
        array $controllers
    ) {
        $this->method = $method;
        $this->controllers = $controllers;
        $this->setPath($path);
        $this->setParams();
        $this->setHasRouter();
        $this->urlMatchRegex = ($this->hasRouter) ? "/^{$this->path}(\/.*)?$/" : "/^{$this->path}$/";
    }

    private function setPath(string $path) {
        if ($path !== self::ALL
            && !\Helpers\Strings::startsWith('/', $path)
        ) {
            $path = ($path === '') ? 'Empty string' : $path;
            throw new \Http\UrlException("All paths must start with / [{$path}] recieved");
        }

        $this->path = str_replace('/', '\/', $path);
    }

    private function setHasRouter(): void {
        $this->hasRouter = \Helpers\Arrays::Some(
            fn ($controller) => $controller instanceof \Http\Router,
            $this->controllers
        );
    }

    private function setParams() {
        $id = self::URL_PARAM_ID;
        $regex = "/(?<=\/){$id}[^\/\\\]+/";
        preg_match_all($regex, $this->path, $params);
        $this->path = preg_replace($regex, self::URL_PARAM_REGEX, $this->path);
        $this->params = ($params[0] ?? []);
    }

    private function matchMethod(string $method): bool {
        return $this->method === self::ALL || $this->method === $method;
    }

    private function matchPath(string $url): bool {
        return $this->path === self::ALL
            || (bool) preg_match(
                $this->urlMatchRegex,
                ($url) ?: '/'
            );
    }

    public function match(string $method, string $url): bool {
        return $this->matchMethod($method) && $this->matchPath($url);
    }

    public function method(): string {
        return $this->method;
    }

    public function path(): string {
        return $this->path;
    }

    /** @return callable[] */
    public function controllers(): array {
        return $this->controllers;
    }

    public function getParamsFromUrl(string $url): array {
        preg_match_all($this->urlMatchRegex, $url, $matches);
        if (!$matches) {
            return [];
        }

        array_shift($matches);
        if ($this->hasRouter) {
            array_pop($matches);
        }

        $matches = array_merge(...$matches);
        foreach ($this->params as $key => $param) {
            $param = ltrim($param, self::URL_PARAM_ID);
            $matches[$param] = $matches[$key];
            unset($matches[$key]);
        }

        return $matches;
    }
}
