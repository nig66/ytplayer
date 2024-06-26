<?php

/**
* /status
*
* stack: push, pop, peek (or top), isEmpty, size.
* queue: enqueue, dequeue, front (or peek), isEmpty, size.
*/

# show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

# class files
require_once('../../Router.php');         # mini route handler
require_once('../../Player.php');         # player status controller


# consts
$status_filename = '../../status.json';
$queue_filename = '../../queue.txt';


# helper classes
$player = new Player($status_filename, $queue_filename);
$router = new Router();



/***************************************
* handle HTTP GET requests
*
*   GET '?state'      show the status as a json str { autoplay, peek, size }
*   GET '?queue'      show the raw queue file
*
* deprecated;
*   GET '?size'       get the size of the queue
*   GET '?autoplay'   get the autoplay status: on|off
*   GET '?peek'       peek at the first videoId
*   GET '?mem'        get the memory used by php
****************************************/


/**
* HTTP request handler: GET '?state'
* get the current state as a json string
*   {
*     "autoplay": "on"|"off",
*     "peek":     "<videoId>",
*     "size":     <int>,
*     "mem":     "<n> MB"
*   }  
*/
$router->get('state', function($state) use($player) {
  header('Content-type: application/json');
  return json_encode($player->getState(), JSON_PRETTY_PRINT);
});


/**
* HTTP request handler: GET '?queue'
* show the raw queue file
*/
$router->get('queue', function($queue) use($queue_filename) {
  $str_queue = file_get_contents($queue_filename);
  return "<xmp>{$str_queue}</xmp>";
});




/***************************************
* handle HTTP POST requests
*
*   POST ''                       enqueue videoId
*   POST '?dequeue'               dequeue the first videoId
*   POST '?dequeueId=<videoId>'   dequeue the specified videoId eg. "?dequeueId=QC8iQqtG0hg"
*   POST '?autoplay'              toggle autoplay
****************************************/

/**
* HTTP request handler: POST ''
* enqueue videoId
*/
$router->post('', function() use($player) {
  $videoId = trim($_POST["videoId"]);
  $player->enqueue($videoId);
  return "enqueued {$videoId}";
});


/**
* HTTP request handler: POST '?dequeueId=<videoId>'
* dequeue the specified videoId
*/
$router->post('dequeueId', function($dequeueId) use($player) {
  $json = $player->dequeueId($dequeueId);
  $str = json_encode($json, JSON_PRETTY_PRINT);
  header('Content-type: application/json');
  return $str;
});


/**
* HTTP request handler: POST '?dequeue'
* dequeue the first videoId
*/
$router->post('dequeue', function($dequeue) use($player) {
  $json = $player->dequeue();
  $str = json_encode($json, JSON_PRETTY_PRINT);
  header('Content-type: application/json');
  return $str;
});


/**
* HTTP request handler: POST '?autoplay'
* toggle autoplay
*/
$router->post('autoplay', function($autoplay) use($player) {
  $player->toggleAutoplay();
  return $player->getState()['autoplay'];
});


/**
* HTTP request handler: POST 'clear'
* clear the queue
*/
$router->post('clear', function($clear) use($queue_filename) {
  file_put_contents($queue_filename, '');
  return '';
});


// invoke handler
$output = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['QUERY_STRING']);
if (!is_null($output))
  die($output);


/*
/**
* HTTP request handler: GET '?autoplay'
* get the autoplay state: on|off
$router->get('xautoplay', function($autoplay) use($player) {
  return $player->getState()['autoplay'];
});
*/


/**
* HTTP request handler: GET '?size'
* get the size of the queue
$router->get('xsize', function($size) use($player) {
  return $player->getState()['size']; 
});
*/


/**
* HTTP request handler: GET 'peek'
* peek at the first videoId without removing it from the queue
$router->get('xpeek', function($peek) use($player) {
  die($player->getState()['peek']);
});
*/


/**
* HTTP request handler: GET '?mem'
* get the memory used by php
$router->get('xmem', function($mem) {
  $mem = memory_get_peak_usage() / 1024 / 1024;
  die(round($mem, 3).' MB');
});
*/

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.2">
    <style>
      label,input[text] {display:flex; flex-direction:column}
      label {color:cyan}
      xmp,a,span {color:white}
      form.inline {display:inline}
      input#videoUrl {width:18em}
    </style>
    <meta http-equiv="Pragma" content="no-cache">
  </head>
  <body bgcolor="DarkSlateGrey">

    <!-- Autoplay -->
    <form class="pd" action="/status/?autoplay" method="post">
      <input type="submit" value="Autoplay">
      <span><?=$player->getState()['autoplay']?></span>
    </form>
    <p>
      <span>Queued:</span>
      <span><?=$player->getState()['size']?></span>
    </p>

    <font color="yellow">
      <h1>State</h1>
    </font>

    <!-- Enqueue -->
    <form class="pd" action="/status/" method="post">
      <input id="videoId" type="hidden" name="videoId">
      <input id="videoUrl" type="text" name="videoUrl">
      <div>
        <input id="submitVideoId" type="submit" value="Enqueue">&nbsp;<span id="showVideoId"></span>
      </div>
    </form>
    <br/>
    
    <!-- Short videos -->
    <form class="pd" action="/status/" method="post">
      <label for="v1">milky way</label>
      <input type="text" name="videoId" value="QC8iQqtG0hg" id="v1">
      <input type="submit" value="Post">
    </form>
    <form class="pd" action="/status/" method="post">
      <label for="v2">5 second timer</label>
      <input type="text" name="videoId" value="DAjMZ6fCPOo" id="v2">
      <input type="submit" value="Post">
    </form>
    <form class="pd" action="/status/" method="post">
      <label for="v3">Nescafe - 6 Second Ad</label>
      <input type="text" name="videoId" value="46W7uzj-51w" id="v3">
      <input type="submit" value="Post">
    </form>
    <br/>
    
    <div>
      <!-- Dequeue -->
      <form class="inline pd" action="/status/?dequeue" method="post">
        <input type="submit" value="Dequeue">
      </form>
      
      <!-- Clear the queue -->
      <form class="inline pd" action="/status/?clear" method="post">
        <input type="submit" value="Clear">
      </form>
    </div>
    
    <br/>

    <!-- state -->
    <xmp><?=json_encode($player->getState(), JSON_PRETTY_PRINT)?></xmp>
    
    <!-- queue -->
    <xmp><?=implode(PHP_EOL, $player->getQueue())?></xmp>
    
    <script src="state.js"></script>
  </body>
</html>