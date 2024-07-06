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


/**
* cors: Allow from any origin
*/
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // allow all origins
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

/**
* cors: Access-Control headers are received during OPTIONS requests
*/
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

//echo "You have CORS!";



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
*   POST    "?state_save_video"               save info about the video
*             body: videoId=<str>&author=<str>&title=<str>
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


/**
* HTTP POST '?state_save_video': toggle autoplay
*/
$router->post('state_save_video', function($videoId, $author, $title) use($player) {
  $arr = [
    'videoId' => $videoId,
    'author'  => $author,
    'title'   => $title
  ];
  $json = json_encode($arr, JSON_PRETTY_PRINT);
  $ret = file_put_contents("../../ids/{$videoId}.json", $json);
  return (false === $ret)
    ? 'failed'
    : 'ok';
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

/**
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
*/

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.2">
    <style>
      label,input[text] {display:flex; flex-direction:column}
      label,.lbl {color:cyan}
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

  
    <!--
    * Status
    * -->
    <h1><a href=".">Status</a></h1>


    <!--
    * Enqueue
    * -->
    <input id="videoUrl" type="text" name="videoUrl" />
    <form class="inline pd" action="/status/?queue" method="post">
      <input id="videoId" type="hidden" name="videoId">
      <div>
        <input id="submitVideoId" type="submit" value="Enqueue">
        <span id="showVideoId"></span>
      </div>
    </form>

    <br/>
    
    <!--
    * Dequeue Id: delete the videoId if at the top of the queue
    * -->
    <form class="pd" action="/status/?queue_delete_ifTop" method="post">
      <input type="text" name="videoId" value="QC8iQqtG0hg" size="11">
      <input type="submit" value="Dequeue Id">
    </form>

    <p>
      <!--
      * Dequeue: delete the videoId at the top of the queue
      * -->
      <form class="inline pd" action="/status/?queue_delete_top" method="post">
        <input type="submit" value="Dequeue">
      </form>
      
      <!--
      * Clear queue: empty the queue
      * -->
      <form class="inline confirmSubmit" action="/status/?queue_delete_all" method="post">
        <input type="submit" value="Clear queue">
      </form>
    </p>
    
    <!-- Short videos -->
    <form class="pd" action="/status/?queue" method="post">
      <input type="text" name="videoId" value="QC8iQqtG0hg" size="11">
      <input type="submit" value="Post">
      <span class="lbl">milky way</span>
    </form>
    <form class="pd" action="/status/?queue" method="post">
      <input type="text" name="videoId" value="DAjMZ6fCPOo" size="11">
      <input type="submit" value="Post">
      <span class="lbl">5 second timer</span>
    </form>
    <form class="pd" action="/status/?queue" method="post">
      <input type="text" name="videoId" value="46W7uzj-51w" size="11">
      <input type="submit" value="Post">
      <span class="lbl">Nescafe - 6s Ad</span>
    </form>
    <form class="pd" action="/status/?queue" method="post">
      <input type="text" name="videoId" value="vZLd81IHGQw" size="11">
      <input type="submit" value="Post">
      <span class="lbl">blocked by auth</span>
    </form>
    <br/>
  
    <div>
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
    
    <!--
    * save video
    * -->
    <p>
      <form class="pd" action="/status/?state_save_video" method="post">
        <input type="hidden" name="videoId" value="abcdefghijl"/>
        <input type="hidden" name="author" value="nig"/>
        <input type="hidden" name="title" value="my groovy video"/>
        <input type="submit" value="Save video"/>
      </form>
    </p>
    

    <!-- state -->
    <xmp id="stateJson"><?=json_encode($player->getState(), JSON_PRETTY_PRINT)?></xmp>
    
    <!-- queue -->
    <xmp id="rawQueue"><?=implode(PHP_EOL, $player->getQueue())?></xmp>
    
    <script src="state.js"></script>
  </body>
</html>