<?php



/**
* player.getState()           # get the state as a jsonable array
* player.getQueue()           # get the queue as an array

* player.enqueue(videoId)     # add videoId to the queue
* player.dequeue()            # dequeue the first videoId
* player.toggleAutoplay()     # toggle autoplay
*
* $player = new Player($status_filename, $queue_filename);
*/


class Player {
  
  private $status_filename;
  private $queue_filename;

  private $arr_status;
  private $arr_queue;
  

  /**
  * constructor
  */
  function __construct($status_filename, $queue_filename) {

    # filenames
    $this->status_filename = $status_filename;
    $this->queue_filename = $queue_filename;
    
    # raw file contents as strings
    $str_status = file_get_contents($status_filename);          # { "autoplay":"on|off" }
    $str_queue = file_get_contents($queue_filename);            # <videoId>.PHP_EOL.<videoId> ...

    # status and queue files as arrays
    $this->arr_status = json_decode($str_status, true);         # Eg. { "autoplay":"on" }
    $this->arr_queue = ('' == $str_queue)                       # Eg. [ "QC8iQqtG0hg", "DAjMZ6fCPOo" ]
      ? []
      : explode(PHP_EOL, $str_queue);
  }
    
    
  /**
  * getState()
  *
  * retval: a jsonable associative array eg.;
  *   [
  *     "autoplay"  => "on",
  *     "size"      => 2,
  *     "peek"      => "QC8iQqtG0hg",
  *     "mem"       => "350 MB"
  *   ]
  */
  function getState() {
    
    $mem = memory_get_peak_usage() / 1024 / 1024;       # peak amount of memory being used by php
    
    $videoId = (0 == count($this->arr_queue))           # the videoId of the video at the top of the queue
      ? ''
      : $this->arr_queue[0];
    
    return [
      "autoplay" => $this->arr_status['autoplay'],      # string     "on"|"off"
      "size"     => count($this->arr_queue),            # int        size of the queue
      "peek"     => $videoId,                           # string     videoId or '' if the queue is empty
      "mem"      => round($mem, 2).' MB'                # string     memory used by php
    ];
  }
  
  
  /**
  * getQueue()
  *
  * retval: the queue as an array of videoId strings eg. [ "QC8iQqtG0hg", "DAjMZ6fCPOo", ... ]
  */
  function getQueue() {
    
    return $this->arr_queue;
    
  }



  /**
  * enqueue(videoId)     # add videoId to the queue and save the queue
  */
  function enqueue($videoId) {
    
    array_push($this->arr_queue, $videoId);
    $str = implode(PHP_EOL, $this->arr_queue);
    
    file_put_contents($this->queue_filename, $str);
    
    return "enqueued {$videoId}";
  }



  /**
  * dequeue()            # dequeue the first videoId and save the queue
  *
  * retval:
  *   {
  *     "videoId": "<videoId>",
  *     "autoplay": "on|off"
  *   }
  */
  function dequeue() {
    
    $autoplay = $this->arr_status['autoplay'];          # "on"|"off"
    
    // if the queue is empty, return an empty string for the videoId
    if (0 == count($this->arr_queue))
      return [ "videoId"  => '',                        # no video to play
               "autoplay" => $autoplay ];               # "on"|"off"
    
    // if there is at least one video in the queue, remove the first one and return its videoId
    $videoId = array_shift($this->arr_queue);
    $str = implode(PHP_EOL, $this->arr_queue);          # imploding an empty array returns an empty string ''
    file_put_contents($this->queue_filename, $str);
    
    return [ "videoId"  => $videoId,                    # videoId string
             "autoplay" => $autoplay ];                 # "on"|"off"
  }
  
  
  
  /**
  * toggleAutoplay()     # toggle autoplay
  *
  * retval: autoplay as a string "on"|"off"
  */
  function toggleAutoplay() {
    
    $this->arr_status['autoplay'] = ('off' == $this->arr_status['autoplay'])
      ? 'on'
      : 'off';
      
    $str_status = json_encode($this->arr_status);                 # JSON_PRETTY_PRINT
    file_put_contents($this->status_filename, $str_status);
    
    return $arr_status['autoplay'];
  }

}

?>