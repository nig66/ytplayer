<?php

declare(strict_types=1);
//require_once('../Router.php');          # mini route handler



/***********************************
* stack v queue;
*
*   stack: push, pop, peek (or top), isEmpty, size.
*   queue: enqueue, dequeue, front (or peek), isEmpty, size.
*/


/**
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
*
*
* HTTP API
* --------
* "crud" verbs : HTTP methods;
*
*   create : POST
*   read   : GET
*   update : PUT
*   delete : DELETE
*
* HTTP request templates;
*
*   GET ?<entity>&param1=val1&param2=val2...
*   POST|PUT|DELETE ?<entity>
*     body: &param1=val1&param2=val2...
*
* queue HTTP handlers;
*
*   GET     "?queue"                                get the queue eg. { "QC8iQqtG0hg", "DAjMZ6fCPOo" }
*   POST    "?queue" body:[videoId=?]               add the specified videoId to the end of the queue
*   POST    "?queue_delete_top"                     delete the videoId at the top of the queue                
*   POST    "?queue_delete_ifTop" body:[videoId=?]  delete the specified videoId only if it is at the top of the queue
*   POST    "?queue_delete_all"                     empty the queue
*
* state HTTP handlers;
*
*   GET     "?state"                          return the player state
*   POST    "?state_message"  body:[msg=?]    set the status message to the specified text eg. "hello world" 
*   POST    "?state_unset_message"            unset the status message
*   POST    "?state_autoplay"                 toggle autoplay "on" | "off"
*/



function test_router() {
  
  $router = new Router();

  $router->get('', function() {
    return "Hello root"; 
  });

  $router->get('fred_add', function($hi) {
    return "Hi there fred '{$hi}'";
  });

  $router->get('foo', function($msg) {
    return "Wassup {$msg}";
  });

  $router->post('qux', function($test) {
    return "The test is '{$test}'"; 
  });

  return $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['QUERY_STRING']);
}


function get_body_params(): array
{
  $paramString = file_get_contents('php://input');
  parse_str($paramString, $paramsArray);         # convert the queryString to an array
  return $paramsArray;
}

if ('POST' === $_SERVER['REQUEST_METHOD']) {
  $output = get_body_params();
  $str = print_r($output, true);
  die($str);
}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>test</title>
  </head>
  <body>
    <h1>heooo world</h1>
    
    <form class="confirmSubmit" action="?delete" method="post">
      <input type="hidden" name="videoId" value="abcdefghijk"/>
      <input type="submit" value="Delete">
    </form>

    <form class="confirmSubmit" action="?clear" method="post">
      <input type="hidden" name="hello" value="world"/>
      <input type="hidden" name="foo" value="bar"/>
      <input type="submit" value="Clear">
    </form>
    <!--
    <xmp><?=$output?></xmp>
    -->
    
    <script>
      /**
      * open confirmation dialog box 'ok / cancel' before submitting the form
      *
      *   <form class="confirmSubmit" action="?delete" method="post">
      *     <input type="hidden" name="videoId" value="abcdefghijk"/>
      *     <input type="submit" value="Delete"/>
      *   </form>
      */
      
      function formSubmitHandler(event) {
        
        event.preventDefault();
        
        var form = event.target;
        var submitButtonValue = form.querySelector("input[type='submit']").value;
        var ok = confirm(submitButtonValue);
        
        // if user clicks ok on the confirm dialog then submit the form
        if (ok) {
          fetch(form.action, {
            method: "post",
            body: new URLSearchParams(new FormData(form))
          })
            .then((response) => response.text())
            .then((text) => {
              console.log(text);
            });
        }
      }
      
      // webpage loaded and ready
      function pageReady() {
        
        // add event listener to all confirmSubmit buttons
        document.querySelectorAll('.confirmSubmit').forEach((element) => {
          element.addEventListener('submit', formSubmitHandler);
        });
      }

      // DOMContentLoaded
      window.addEventListener('DOMContentLoaded', () => {
        setTimeout(pageReady, 0);
      });
      
    </script>
  </body>
</html>
