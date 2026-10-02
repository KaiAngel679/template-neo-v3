<?php

namespace app\modules\module_page_skinchanger\ext;

class Router
{
  private static ?Router $instance = null;
  private array $routes = [];
  private ?object $controller = null;

  public static function getInstance(): self
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public static function controller(object $controller): void
  {
    self::getInstance()->controller = $controller;
  }

  public static function get(string $uri, $handler): Route
  {
    return self::getInstance()->addRoute(['GET'], $uri, $handler);
  }

  public static function post(string $uri, $handler): Route
  {
    return self::getInstance()->addRoute(['POST'], $uri, $handler);
  }

  public static function put(string $uri, $handler): Route
  {
    return self::getInstance()->addRoute(['PUT', 'PATCH'], $uri, $handler);
  }

  public static function delete(string $uri, $handler): Route
  {
    return self::getInstance()->addRoute(['DELETE'], $uri, $handler);
  }

  public static function any(string $uri, $handler): Route
  {
    return self::getInstance()->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $handler);
  }

  private function addRoute(array $methods, string $uri, $handler): Route
  {
    $route = new Route($methods, $uri, $handler);
    $this->routes[] = $route;
    return $route;
  }

  public static function dispatch(string $uri, string $method = null): void
  {
    self::getInstance()->dispatchRequest($uri, $method);
  }

  private function dispatchRequest(string $uri, ?string $method): void
  {
    $uri    = '/' . trim($uri, '/');
    $method = $method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'POST' && isset($_POST['_method'])) {
      $method = strtoupper($_POST['_method']);
    }

    foreach ($this->routes as $route) {
      if (!in_array($method, $route->methods, true)) {
        continue;
      }

      if (!preg_match($route->pattern, $uri, $matches)) {
        continue;
      }

      array_shift($matches);
      if (!empty($route->paramNames)) {
        $_GET = array_merge($_GET, array_combine($route->paramNames, $matches));
      }

      $rateLimits = new Controllers\RateLimits();
      if ($route->rateLimitGroup !== null && !$rateLimits->check($route->rateLimitGroup)) {
        $this->error(429, 'rate_limit_exceeded');
      }

      foreach ($route->middleware as $mw) {
        $this->runMiddleware($mw);
      }

      if (is_string($route->handler)) {
        $this->controller->handle($route->handler);
      } else {
        ($route->handler)();
      }
      return;
    }

    $this->error(404, 'route_not_found');
  }

  private function runMiddleware(string $middleware): void
  {
    if ($middleware === 'auth') {
      if (empty($_SESSION['steamid'])) {
        $this->error(401, 'unauthorized');
      }
    } elseif ($middleware === 'admin') {
      if (empty($_SESSION['steamid'])) {
        $this->error(401, 'unauthorized');
      }
      if (!isset($_SESSION['user_admin']) || !$_SESSION['user_admin']) {
        $this->error(403, 'forbidden');
      }
    }
  }

  private function error(int $code, string $error): void
  {
    http_response_code($code);
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'error' => $error]));
  }
}

class Route
{
  public array $methods;
  public string $uri;
  public string $pattern;
  public array $paramNames;
  public $handler;
  public array $middleware = [];
  public ?string $rateLimitGroup = null;

  public function __construct(array $methods, string $uri, $handler)
  {
    $this->methods = $methods;
    $this->uri     = $uri;
    $this->handler = $handler;

    [$this->pattern, $this->paramNames] = self::compile($uri);
  }

  public function middleware($middleware): self
  {
    if (is_array($middleware)) {
      $this->middleware = array_merge($this->middleware, $middleware);
    } else {
      $this->middleware[] = $middleware;
    }
    return $this;
  }

  public function rateLimit(string $group): self
  {
    $this->rateLimitGroup = $group;
    return $this;
  }

  private static function compile(string $uri): array
  {
    $uri        = '/' . trim($uri, '/');
    $paramNames = [];

    $pattern = preg_replace_callback('/\{(\w+)\}/', function ($m) use (&$paramNames) {
      $paramNames[] = $m[1];
      return '(\d+)';
    }, $uri);

    return ['#^' . $pattern . '$#', $paramNames];
  }
}
