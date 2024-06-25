<?php

/**
* Simple router to match HTTP requests by REQUEST_METHOD and QUERY_STRING param keys only.
*
* usage;
*   $router = new Router; 
* 
*   $router->get('', function() {
*     return 'Hi root';
*   });
* 
*   $router->get('foo', function($foo) {
*     return "The value of foo is {$foo}"; 
*   });
* 
*   $router->get('foo&bar', function($foo, $bar) {
*     return "foo is {$foo} and bar is {$bar}"; 
*   });
* 
*   $str = $router->match('GET', '')                 # returns 'Hi root';
*   $str = $router->match('GET', 'about')            # returns 'Hello about';
*   $str = $router->match('GET', 'foo=xyz&bar=hi')   # returns 'foo is xyz and bar is hi';
*
* https://www.php-fig.org/psr/psr-12/
*/


class Router
{

  /*************************
  * Public.
  */
  
  public function get(string $queryString, $handler): void
  {
    $this->add_route('GET', $queryString, $handler);
  }

  public function post(string $queryString, $handler): void
  {
    $this->add_route('POST', $queryString, $handler);
  }

  public function match(string $request_method, string $queryString): ?string
  {
    return $this->invoke_handler($queryString, $this->routes[$request_method]);
  }


  /*************************
  * Private.
  */
  
  private $routes = [];
  
  /**
  * add route
  */
  private function add_route(string $method, string $queryString, $handler): void
  {
    $sorted_keys = $this->get_keys_sorted($queryString);
    if (isset($this->routes[$method][$sorted_keys]))
      throw new Exception("Route error: duplicate route '{$method} {$queryString}'");
    $this->routes[$method][$sorted_keys] = $handler;
  }
  
  
  /**
  * for the given queryString, return just the keys, sorted
  *
  *   eg. given  'world=bar&hello=foo'
  *       return 'hello&world'
  */
  private function get_keys_sorted(string $queryString): string
  {
    parse_str($queryString, $parsed);       # let $parse = $queryString as an array
    $keys = array_keys($parsed);
    sort($keys);
    return implode('&', $keys);
  }
  
  
  /**
  * for the given queryString, find a matching route and invoke its handler function
  */
  private function invoke_handler(string $queryString, array $routes): ?string
  {
    $queryStringKeys = $this->get_keys_sorted($queryString);      # eg. 'hello&world'
    
    # return if there are no matching routes
    if (!isset($routes[$queryStringKeys]))
      return null;
  
    parse_str($queryString, $parsed);       # let $parse = $queryString as an array
    
    # invoke the route handler
    try {
      $ret = call_user_func_array($routes[$queryStringKeys], $parsed);
    } catch (Error $e) {
      echo "Route handler error: Missing parameter in handler for route '{$route}'. ", $e->getMessage(), "\n";
    }
    return $ret;
  }
    
}

?>