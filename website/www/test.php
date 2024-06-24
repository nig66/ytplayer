<?php

require_once('Router.php');          # mini route handler

$router = new Router();

$router->get('', function() {
  return "Hello root"; 
});

$router->get('fred', function($fred) {
  return "foobar fred={$fred} x={$x}";
});

$router->get('x&fred', function($fred, $x) {
  return "Hi fred={$fred} and x={$x}"; 
});

$router->get('user&videoId', function($user, $videoId) {
  #return "The user is {$name} and v is {$videoId}"; 
  return "The user is {$user} and videoId is {$videoId}"; 
});

$strOutput = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['QUERY_STRING']);

?>

<title>test simple router v2</title>
<xmp><?=$strOutput?></xmp>