<?php

/**
* A simple router to match HTTP requests by REQUEST_METHOD and <token> only.
*
* HTTP request formats;
*   GET  /                  eg. GET /
*   GET  /?token            eg. GET /?foobar
*   GET  /?params           eg. GET /?param1=val1&param2=val2
*   GET  /?token&params     eg. GET /?foobar&param1=val1&param2=val2
*   POST /                  eg. POST /  
*   POST /?token            eg. POST /?foobar
*   POST /                  eg. POST /
*     body: params                body: param1=val1&param2=val2
*   POST /?token            eg. POST /?foobar
*     body: params                body: param1=val1&param2=val2
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
  * invoke the matching route handler for the given HTTP request method and query string and return its value
  *
  * @param string $request_method       an HTTP request method; GET|POST
  * @param string $query_string         an HTTP request query string eg. "?foo&hi=there&colour=red"
  *
  * @return ?string                     return the value returned by the handler
  */
  public function match(string $request_method, string $query_string): ?string
  {
    $query_array = $this->parse_params_string($query_string);     # convert the query string into an array
    $token = $this->get_token($query_array);
    $params = $this->get_params($request_method, $query_array);

    # invoke the matching handler
    return $this->invoke_handler($token, $params, $this->routes[$request_method]);
  }

  
  
  
  /*************************
  *
  * Private.
  *
  */
  
  
  /**
  * an array of route maps, each mapping a method/token key pair to a handler function [method][token] => handler() eg.;
  *
  *   [
  *     ['GET']  =>
  *        [ 'token1' => myfn1(),
  *          'token2' => myfn2() ],
  *     ['POST'] =>
  *        [ 'foo' => bar(),
  *          'hi'  => world() ]
  *   ]
  */
  private array $routes = [];
  

  /**
  * given a set of query params, return either '' or the first param key
  *
  * @param array $query_params      the queryString as an array
  * @return string                  token
  */
  private function get_token($query_params): string
  {
    # if there are no query params or the first param has a value then the token is the empty string
    if ((0 == count($query_params)) || ('' !== reset($query_params)))
      return '';
    
    # otherwise the token is the key of the first param
    return array_key_first($query_params);
  }


  /**
  * return a set of params for the given request method and query params
  *
  * @param string $request_method       the HTTP request method either "GET" or "POST"
  * @param array $query_params          the HTTP request query string as an array
  *
  * @return array                       a set of params either from the query string or the request body
  */
  private function get_params(string $request_method, array $query_params): array
  {
    # for HTTP POST requests, return params from the request body
    if ('POST' == $request_method)
      return $this->parse_params_string(file_get_contents('php://input'));
      
    # if there are query params but the first param value is the empty string then drop the first param
    if ((0 !== count($query_params)) && ('' === reset($query_params)))
      array_shift($query_params);

    return $query_params;
  }


  /**
  * add a route
  */
  private function add_route(string $method, string $route, $handler): void
  {
    if (isset($this->routes[$method][$route]))
      throw new Exception("Route error: duplicate route '{$method} ?{$route}'");
    $this->routes[$method][$route] = $handler;
  }
  
  
  /**
  * for a given route, invoke the matching route handler
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