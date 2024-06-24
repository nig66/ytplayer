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
  */
  private function invoke_matching_handler(string $queryString, array $routes): ?string
  {
    parse_str($queryString, $arrQueryString);         # set $arrQueryString
    $queryStringKeys = array_keys($arrQueryString);
    sort($queryStringKeys);
    
    $ret = [];
    
    foreach($routes as $route => $routeFunction) {
      
      parse_str($route, $arrRoute);         # set $arrRoute
      $routeKeys = array_keys($arrRoute);
      sort($routeKeys);
      
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