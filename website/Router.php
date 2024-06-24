<?php

/**
* https://www.php-fig.org/psr/psr-12/
*
* usage;
*   $route = new clsRoute; 
* 
*   $route->get('', function(){
*     return 'Hi root';
*   });
* 
*   $route->get('foo', function($foo){
*     return "The value of foo is {$foo}"; 
*   });
* 
*   $route->get('foo&bar', function($foo, $bar) {
*     return "foo is {$foo} and bar is {$bar}"; 
*   });
* 
*   $str = $route->match('GET', '')                 # returns 'Hi root';
*   $str = $route->match('GET', 'about')            # returns 'Hello about';
*   $str = $route->match('GET', 'foo=xyz&bar=hi')   # returns 'foo is xyz and bar is hi';
*/


class Router
{

  /*************************
  * Public.
  */
  
  public function get(string $queryString, $handler): void
  {
    $this->routes['GET'][$queryString] = $handler;
  }

  public function post(string $queryString, $handler): void
  {
    $this->routes['POST'][$queryString] = $handler;
  }

  public function match(string $request_method, string $queryString): ?string
  {
    return $this->invoke_matching_handler($queryString, $this->routes[$request_method]);
  }


  /*************************
  * Private.
  */
  
  private $routes = [];
  
  /**
  * return array keys sorted
  */
  private function get_keys_sorted(array $arr): array
  {
    $keys = array_keys($arr);
    sort($keys);
    return $keys;
  }
  
  /**
  * for the given queryString, find a matching route and invoke its handler function
  */
  private function invoke_matching_handler(string $queryString, array $routes): ?string
  {
    parse_str($queryString, $arrQueryString);       # set $arrQueryString
    $queryStringKeys = $this->get_keys_sorted($arrQueryString);
    
    $ret = [];
    
    foreach($routes as $route => $routeFunction) {
      
      parse_str($route, $arrRoute);       # set $arrRoute
      $routeKeys = $this->get_keys_sorted($arrRoute);
      
      if ($queryStringKeys === $routeKeys) {
        try {
          $ret[] = call_user_func_array($routeFunction, $arrQueryString);
        } catch (Error $e) {
          echo "Route handler error: Missing parameter in handler for route '{$route}'. ", $e->getMessage(), "\n";
        }
      }
    }
    
    if (count($ret) > 1)
      throw new \Exception("More than one matching route handler for queryString '{$queryString}'");

    return (1 === count($ret))      # if there is exactly one matching route
      ? $ret[0]                     # then return the output from that route handler
      : null;                       # otherwise return null
  }
  
}

?>