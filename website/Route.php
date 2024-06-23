<?php

/**
* Router class
*
* Simple router to match HTTP requests by REQUEST_METHOD and QUERY_STRING only.
*
*   $router = new Router($_SERVER);
*   
*   Example: match HTTP GET '/?hi';
*
*   $router->get('hi', function() {
*     echo 'hi there';
*   });
*   
*   Example: match HTTP GET '/';
*   
*   $foo = 'hello world';

*   $router->get('', function() use($foo) {
*     echo "<h1>{$foo}</h1>";
*   });
*/
class Router {
  
  private $server;
  
  function __construct($server) {
    $this->server = $server;
  }
  
  function get($query, $fn) {
    $this->handler('GET', $query, $fn);
  }
  
  function post($query, $fn) {
    $this->handler('POST', $query, $fn);
  }
  
  private function handler($method, $query, $fn) {
    
    $m = $this->server['REQUEST_METHOD'];
    $q = $this->server['QUERY_STRING'];
    
    if ( ($method == $m) && ($query == $q) )
      $fn();
  }
}

?>