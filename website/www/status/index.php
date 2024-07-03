<?php

/**
* handle HTTP GET & POST requsts to /status
*
* Queue v stack;
*
*   stack: push, pop, peek (or top), isEmpty, size.
*   queue: enqueue, dequeue, front (or peek), isEmpty, size.
*/

# strict type handling
declare(strict_types=1);

# show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

# class files
require_once('../../Router.php');         # mini route handler
require_once('../../Player.php');         # player state controller

# consts
$status_filename = '../../status.json';
$queue_filename = '../../queue.txt';

# init helper classes
$player = new Player($status_filename, $queue_filename);
$router = new Router();



/***************************************
* API data structures
* -------------------
* videoId: an 11 char string that uniquely identifies every YouTube video (including shorts & live streams) eg;
*
*   "QC8iQqtG0hg"
*
* queue: a set of videoIds eg;
*   {
*     "QC8iQqtG0hg",
*     "DAjMZ6fCPOo",
*     "46W7uzj-51w"
*   }
*
* state: the current state of the video player eg;
*   {
*     "autoplay": "on",               # on|off; if on, when a video finishes playing the next one in the queue begins
*     "size": 3,                      # the number of videoIds in the queue
*     "peek": "QC8iQqtG0hg",          # the videoId at the top of the queue or "" if the queue is empty
*     "mem": "0.35 MB"                # the value returned by memory_get_peak_usage() in megabytes
*   }
*/



/**
* queue HTTP handlers
* -------------------
* GET  "?queue"                                  get the queue eg. { "QC8iQqtG0hg", "DAjMZ6fCPOo" }
* POST "?queue"              body:[videoId=?]    add the specified videoId to the end of the queue
* POST "?queue_delete_top"                       delete the videoId at the top of the queue                
* POST "?queue_delete_ifTop" body:[videoId=?]    delete the specified videoId only if it is at the top of the queue
* POST "?queue_delete_all"                       empty the queue
*/

/**
* HTTP GET '?queue': return the raw queue file
*/
$router->get('queue', function() use($queue_filename) {
  header("Content-Type: text/plain");
  return file_get_contents($queue_filename);
});

/**
* HTTP POST '?queue': enqueue a videoId
*/
$router->post('queue', function($videoId) use($player) {
  $player->enqueue($videoId);
  return "enqueued {$videoId}";
});

/**
* HTTP POST '?queue_delete_top': dequeue the first videoId
*/
$router->post('queue_delete_top', function() use($player) {
  $json = $player->dequeue();
  $str = json_encode($json, JSON_PRETTY_PRINT);
  header('Content-type: application/json');
  return $str;
});

/**
* HTTP POST '?queue_delete_ifTop': delete the specified videoId only if it is at the top of the queue
*/
$router->post('queue_delete_ifTop', function($videoId) use($player) {
  $json = $player->dequeueId($videoId);
  $str = json_encode($json, JSON_PRETTY_PRINT);
  header('Content-type: application/json');
  return $str;
});

/**
* HTTP POST '?queue_delete_all': clear the queue
*/
$router->post('queue_delete_all', function() use($queue_filename) {
  file_put_contents($queue_filename, '');
  return '';
});



/**
* state HTTP handlers;
*
*   GET     "?state"                          return the player state
*   POST    "?state_message"  body:[msg=?]    set the status message to the specified text eg. "hello world" 
*   POST    "?state_unset_message"            unset the status message
*   POST    "?state_autoplay"                 toggle autoplay "on" | "off"
*/

/**
* HTTP GET '?state': return the current state as a json string eg.;
*   {
*     "autoplay": "on"|"off",
*     "peek":     "<videoId>",
*     "size":     <int>,
*     "mem":     "<n> MB"
*   }  
*/
$router->get('state', function() use($player) {
  header('Content-type: application/json');
  return json_encode($player->getState(), JSON_PRETTY_PRINT);
});


/**
* HTTP POST '?message': set the status message
*/
$router->post('state_message', function($message) use($player) {
  $player->setMessage($message);
  return $player->getState()['message'];
});


/**
* HTTP POST '?state_unset_message': unset the status message
*/
$router->post('state_unset_message', function() use($player) {
  $player->unsetMessage();
  return '';
});


/**
* HTTP POST '?state_autoplay': toggle autoplay
*/
$router->post('state_autoplay', function() use($player) {
  $player->toggleAutoplay();
  return $player->getState()['autoplay'];
});




/************************
*
* if there is an appropriate handler invoke it then die(),
* otherwise fall through and return the status webpage html
*
*/
$output = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['QUERY_STRING']);

if (!is_null($output))
  die($output);

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.2">
    <style>
      label,input[text] {display:flex; flex-direction:column}
      label {color:cyan}
      a {color:yellow}
      xmp, span {color:white}
      form.inline {display:inline}
      input#videoUrl {width:18em}
    </style>
    <meta http-equiv="Pragma" content="no-cache">
  </head>
  <body bgcolor="DarkSlateGrey">

    <!-- Autoplay -->
    <form class="inline pd" action="/status/?state_autoplay" method="post">
      <span id="autoplayStatus"><?=$player->getState()['autoplay']?></span>
      <input type="submit" value="Autoplay">
    </form>
    
    <!-- mem -->
    &nbsp;&nbsp;<?=$player->getState()['mem']?>&nbsp;&nbsp;
    
    <!-- Size of the queue -->
    <span id="queueSize">Queued: <?=$player->getState()['size']?></span>
  
    <h1><a href=".">Status</a></h1>

    <!-- Enqueue -->
    <input id="videoUrl" type="text" name="videoUrl">
    <div>
      <form class="inline pd" action="/status/?queue" method="post">
        <input id="videoId" type="hidden" name="videoId">
          <input id="submitVideoId" type="submit" value="Enqueue">&nbsp;<span id="showVideoId"></span>
      </form>

      <!-- delete the videoId at the top of the queue -->
      <form class="inline pd" action="/status/?queue_delete_top" method="post">
        <input type="submit" value="Dequeue">
      </form>
      
      <!-- empty the queue -->
      <form class="inline confirmSubmit" action="/status/?queue_delete_all" method="post">
        <input type="submit" value="Clear">
      </form>
    </div>
    
    <br/>
    
    <!-- delete the videoId if at the top of the queue -->
    <form class="pd" action="/status/?queue_delete_ifTop" method="post">
      <input type="text" name="videoId" value="QC8iQqtG0hg">
      <input type="submit" value="delete_ifTop">
    </form>

    <br/>
    
    <!-- Short videos -->
    <form class="pd" action="/status/?queue" method="post">
      <label for="v1">milky way</label>
      <input type="text" name="videoId" value="QC8iQqtG0hg" id="v1">
      <input type="submit" value="Post">
    </form>
    <form class="pd" action="/status/?queue" method="post">
      <label for="v2">5 second timer</label>
      <input type="text" name="videoId" value="DAjMZ6fCPOo" id="v2">
      <input type="submit" value="Post">
    </form>
    <form class="pd" action="/status/?queue" method="post">
      <label for="v3">Nescafe - 6 Second Ad</label>
      <input type="text" name="videoId" value="46W7uzj-51w" id="v3">
      <input type="submit" value="Post">
    </form>
    <form class="pd" action="/status/?queue" method="post">
      <label for="v4">blocked by author</label>
      <input type="text" name="videoId" value="vZLd81IHGQw" id="v4">
      <input type="submit" value="Post">
    </form>
    <br/>
  
<!--
* queue HTTP handlers
* -------------------
* GET  "?queue"                                  get the queue eg. { "QC8iQqtG0hg", "DAjMZ6fCPOo" }
* POST "?queue"              body:[videoId=?]    add the specified videoId to the end of the queue
* POST "?queue_delete_top"                       delete the videoId at the top of the queue                
* POST "?queue_delete_ifTop" body:[videoId=?]    delete the specified videoId only if it is at the top of the queue
* POST "?queue_delete_all"                       empty the queue
*
*
* state HTTP handlers
* -------------------
* GET  "?state"                          return the player state
* POST "?state_message"  body:[msg=?]    set the status message to the specified text eg. "hello world" 
* POST "?state_unset_message"            unset the status message
* POST "?state_autoplay"                 toggle autoplay "on" | "off"

-->

    <div>
      <xbr/>

      <!-- set message -->
      <form class="inline pd" action="/status/?state_message" method="post">
        <label for="v5">message</label>
        <input type="text" name="message" value="hello foo world" id="v5">
        <input type="submit" value="Set">
      </form>
      <!-- unset message -->
      <form class="inline pd" action="/status/?state_unset_message" method="post">
        <input type="submit" value="Unset">
      </form>

    </div>
    
    <!-- state -->
    <xmp id="stateJson"><?=json_encode($player->getState(), JSON_PRETTY_PRINT)?></xmp>
    
    <!-- queue -->
    <xmp id="rawQueue"><?=implode(PHP_EOL, $player->getQueue())?></xmp>
    
    <script src="state.js"></script>
  </body>
</html>