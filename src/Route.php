<?php

namespace Webservis;

use Webservis\Redirect;

class Route
{
    public static array $patterns = [':id[0-9]?' => '([0-9]+)', ':url[0-9]?' => '([0-9a-zA-Z-_+]+)', ':slug[0-9]?' => '([0-9a-zA-Z-_]+)'];
    public static bool $hasRoute = false;
    public static array $routes = [];
    public static string $prefix = '';
    public static string $subdomain = '';

    public static function get(string $path, $callback): Route
    {
        self::$routes['get'][self::$prefix . $path] = ['callback' => $callback, 'subdomain' => self::$subdomain];
        return new self();
    }

    public static function post(string $path, $callback): void
    {
        self::$routes['post'][$path] = ['callback' => $callback, 'subdomain' => self::$subdomain];
    }

    public static function dispatch()
    {
        $url = self::getURL();
        $method = self::getMethod();
        $currentSubdomain = self::getSubdomain();

        foreach (self::$routes[$method] as $path => $props) {
            if ($props['subdomain'] != $currentSubdomain) {
                continue;
            }

            foreach (self::$patterns as $key => $pattern) {
                $path = preg_replace('#' . $key . '#', $pattern, $path);
            }

            $pattern = '#^' . $path . '$#';
            if (preg_match($pattern, $url, $params)) {
                self::$hasRoute = true;
                array_shift($params);

                if (isset($props['redirect'])) {
                    Redirect::to($props['redirect'], $props['status']);
                } else {
                    $callback = $props['callback'];
                    if (is_callable($callback)) {
                        echo call_user_func_array($callback, $params);
                    } elseif (is_string($callback)) {
                        [$controllerName, $methodName] = explode('@', $callback);
                        $controllerName = '\App\Controllers\\' . $controllerName;
                        $controller = new $controllerName();
                        echo call_user_func_array([$controller, $methodName], $params);
                    }
                }
            }
        }

        self::hasRoute();
    }

    public static function hasRoute()
    {
        if (self::$hasRoute === false) {
            Redirect::to('/');
        }
    }

    public static function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public static function getURL(): string
    {
        return str_replace(BASE_PATH, false, $_SERVER['REQUEST_URI']);
    }

    public function name(string $name): void
    {
        $key = array_key_last(self::$routes['get']);
        self::$routes['get'][$key]['name'] = $name;
    }

    public static function url(string $name, array $params = []): string
    {
        $route = array_filter(self::$routes['get'], function ($route) use ($name) {
            return isset($route[$name]) && $route['name'] === $name;
        });

        $route = array_key_first($route);
        return str_replace(array_map(fn($key) => ':' . $key, array_keys($params)), array_values($params), $route);
    }

    public static function prefix($prefix): Route
    {
        self::$prefix = $prefix;
        return new self();
    }

    public static function group(\Closure $closure): void
    {
        $closure();
        self::$prefix = '';
    }

    public static function where($key, $pattern)
    {
        self::$patterns[':' . $key] = '(' . $pattern . ')';
    }

    public static function redirect($from, $to, $status = 301)
    {
        $froms = array_filter(explode(',', $from));
        $froms = array_map('trim', $froms);
        foreach ($froms as $from_) {
            self::$routes['get'][$from_] = ['redirect' => $to, 'status' => $status, 'subdomain' => self::$subdomain];
        }
    }

    public static function subdomain($subdomain, \Closure $closure)
    {
        self::$subdomain = $subdomain;
        $closure();
        self::$subdomain = '';
    }

    public static function getSubdomain()
    {
        $hostParts = explode('.', $_SERVER['HTTP_HOST']);
        if (count($hostParts) >= 3) {
            return implode('.', array_slice($hostParts, 0, -2));
        } else {return '';}
    }
}
