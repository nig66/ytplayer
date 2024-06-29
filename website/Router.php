<?php

/**
* Simple router to match HTTP requests by REQUEST_METHOD and QUERY_STRING only.
*
* usage;
*   $router = new Router; 
* 
*   $router->get('', function() {
*     return 'Hi root';
*   });
* 
*   $router->get('foo', function() {
*     return "Hello foo"; 
*   });
* 
*   $router->get('bar', function($hi) {
*     return "Wassup {$hi}"; 
*   });
* 
*   $router->post('qiz', function($foo, $bar) {
*     return "foo is {$foo} and bar is {$bar}"; 
*   });
* 
*   $str = $router->match('GET', '')                # returns 'Hi root';
*   $str = $router->match('GET', 'foo')             # returns 'Hello foo';
*   $str = $router->match('GET', 'bar&hi=there')    # returns 'Wassup there';
*   $str = $router->match('POST', 'qiz') 
*      body: foo=abc&bar=xyz                        # returns 'foo is abc and bar is xyz';
*
*
* HTTP requests;
*
*   GET ?route&param1=val1&param2=val2...
*
*   POST|PUT|DELETE ?route
*     body: &param1=val1&param2=val2...
*
*
* https://www.php-fig.org/psr/psr-12/
*/

class Router
{

  /*************************
  *
  * Public
  *
  */
  
  /**
  * create an HTTP GET route map
  *
  * @param string $route          route to map to the given handler
  * @param function $handler      handler for the given route
  */
  public function get(string $route, callable $handler): void
  {
    $this->add_route('GET', $route, $handler);
  }


  /**
  * create an HTTP POST route map
  *
  * @param string $route          route to map to the given handler
  * @param function $handler      handler for the given route
  */
  public function post(string $route, callable $handler): void
  {
    $this->add_route('POST', $route, $handler);
  }


  /**
  * invoke the matching route handler for the given HTTP request method and queryString
  *
  * @param string $request_method       an HTTP request method eg. "GET", "POST, "PUT", "DELETE" etc
  * @param string $queryString          an HTTP request queryString eg. "foo?hi=there&colour=red"
  */
  public function match(string $request_method, string $queryString): ?string
  {
    $queryArray = $this->parse_params_string($queryString);  # convert the queryString to an array
    
    $route = (0 === count($queryArray))         # if there is no queryString
      ? ''                                      # then $route is the empty string ""
      : array_key_first($queryArray);           # otherwise $route is the first key of the queryString
    
    array_shift($queryArray);          # drop the route from the array leaving only the params, if any
      
    # for GET requests the params are in the queryString, otherwise the params are in the request body
    $paramsArray = ('GET' == $request_method)
      ? $queryArray                                                       # get the params from the queryString
      : $this->parse_params_string(file_get_contents('php://input'));     # get params from the request body

    # invoke the matching handler
    return $this->invoke_handler($route, $paramsArray, $this->routes[$request_method]);
  }



  /*************************
  * Private.
  */
  
  private $routes = [];
  
  /**
  * add route
  */
  private function add_route(string $method, string $route, $handler): void
  {
    if (isset($this->routes[$method][$route]))
      throw new Exception("Route error: duplicate route '{$method} ?{$route}'");
    $this->routes[$method][$route] = $handler;
  }
  
  
  /**
  * for a given route, find a matching route and invoke its handler function
  */
  private function invoke_handler(string $route, array $paramsArray, array $routes): ?string
  {
    # return if there are no matching routes
    if (!isset($routes[$route]))
      return null;
  
    # invoke the matching route handler
    try {
      $ret = call_user_func_array($routes[$route], $paramsArray);
    } catch (Error $e) {
      echo "Route handler error: Missing parameter in handler for route '{$route}'. ", $e->getMessage(), "\n";
    }
    return $ret;
  }
  

  /**
  * convert a set of zero or more parameters from string to array
  *
  * @param string $paramString        param string to convert eg. "foo=bar&colour=red"
  * @return array                     array of params eg. ["foo" -> bar, "colour" -> "red"]
  */
  private function parse_params_string(string $paramString): array
  {
    parse_str($paramString, $paramsArray);         # convert the queryString to an array
    return $paramsArray;
  }  
    
}  

?>