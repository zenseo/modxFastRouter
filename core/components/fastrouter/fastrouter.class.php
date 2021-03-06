<?php

require_once __DIR__ . '/vendor/nikic/fast-route/src/bootstrap.php';

class FastRouter {
    /**
     * Path to routes cache file
     *
     * @var string
     */
    protected $cacheFile;

    /**
     * @var modX
     */
    protected $modx;

    /**
     * @var FastRoute\Dispatcher\GroupCountBased
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $paramsKey;

    /**
     * @param modX $modx
     */
    function __construct(modX $modx) {
        $this->modx = $modx;
        $this->cacheFile = $modx->getOption(xPDO::OPT_CACHE_PATH) . 'fastrouter.cache.php';
        $this->paramsKey = $modx->getOption('fastrouter.paramsKey', null, 'fastrouter');
    }

    /**
     * @return mixed
     */
    protected function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return string
     */
    protected function getUri() {
        $alias = $this->modx->getOption('request_alias', null, 'q');

        $uri = isset($_REQUEST[$alias]) ? (string) $_REQUEST[$alias] : '';

        return '/' . ltrim($uri, '/');
    }

    /**
     * @return FastRoute\Dispatcher|FastRoute\Dispatcher\GroupCountBased
     */
    protected function getDispatcher() {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = FastRoute\cachedDispatcher(function (FastRoute\RouteCollector $router) {
                $this->getRoutes($router);
            }, array('cacheFile' => $this->cacheFile));
        }

        return $this->dispatcher;
    }

    /**
     * @param FastRoute\RouteCollector $router
     */
    protected function getRoutes(FastRoute\RouteCollector $router) {
        $routes = json_decode($this->modx->getChunk('fastrouter'), true);

        if (!$routes) {
            throw new InvalidArgumentException('Invalid routes');
        }

        foreach ($routes as $r) {
            if (isset($r[0], $r[1], $r[2]) && (int) $r[2]) {
                $router->addRoute($r[0], $r[1], $r[2]);
            }
        }
    }

    /**
     * @return null
     */
    public function dispatch() {
        $dispatcher = $this->getDispatcher();

        $params = $dispatcher->dispatch($this->getMethod(), $this->getUri());

        switch ($params[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                return $this->error();
                break;

            case FastRoute\Dispatcher::FOUND:
                return $this->handle($params[1], $params[2]);
                break;
        }
    }

    /**
     * @param int $id
     * @param array $data
     * @return null
     */
    protected function handle($id, array $data) {
        $_REQUEST += array($this->paramsKey => $data);

        $this->modx->sendForward($id);

        return null;
    }

    /**
     * @return null
     */
    protected function error() {
        $options = array(
            'response_code' => $this->modx->getOption('error_page_header', null, 'HTTP/1.1 404 Not Found'),
            'error_type' => '404',
            'error_header' => $this->modx->getOption('error_page_header', null, 'HTTP/1.1 404 Not Found'),
            'error_pagetitle' => $this->modx->getOption('error_page_pagetitle', null, 'Error 404: Page not found'),
            'error_message' => $this->modx->getOption('error_page_message', null, '<h1>Page not found</h1><p>The page you requested was not found.</p>')
        );

        $this->modx->sendForward($this->modx->getOption('error_page', $options, '404'), $options);

        return null;
    }

    /**
     * Remove routes cache file
     */
    public function clearCache() {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }
}
