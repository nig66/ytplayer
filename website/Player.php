<?php


/**
* Usage;
*
*   $player = new Player($status_filename, $queue_filename);
*
*   player.getState()           # get the player state as a jsonable array
*   player.getQueue()           # get the queue as an array of YouTube videoId's
* 
*   player.enqueue(videoId)     # add a videoId to the queue
*   player.dequeue()            # dequeue the first videoId
*   player.dequeueId(videoId)   # dequeue the first videoId only if it matches the supplied videoId
*   player.toggleAutoplay()     # toggle autoplay on|off
*   player.getMessage()         # get the status message
*   player.setMessage()         # set the status message
*   player.unsetMessage()       # unSet the status message
*/


class Player {
  
  private $status_filename;         # relative path/filename of the status file
  private $queue_filename;          # relative path/filename of the queue file

  private $arr_status;              # the autoplay status: on|off and optionally a message
  private $arr_queue;               # the video queue as an array of YouTube videoId's
  
  

  /**
  * Constructor
  *
  * @param string $status_filename    Relative path/filename of the status file eg. "../../status.json";
  *
  *   {"autoplay":"on", "message":"hello world"}
  *
  * @param string $queue_filename     Relative path/filename of the queue file eg. "../../queue.txt";
  *
  *   QC8iQqtG0hg
  *   DAjMZ6fCPOo
  *   46W7uzj-51w
  */
  function __construct(string $status_filename, string $queue_filename) {

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
  * Get the current state of the player.
  *
  * @return array       A jsonable associative array eg.;
  *
  *   [
  *     "autoplay"  => "on",
  *     "size"      => 2,
  *     "peek"      => "QC8iQqtG0hg",
  *     "mem"       => "350 MB"
  *     "message"   => "Hello world"
  *   ]
  */
  function getState() {
    
    $mem = memory_get_peak_usage() / 1024 / 1024;       # peak amount of memory being used by php
    
    $videoId = (0 == count($this->arr_queue))           # the videoId of the video at the top of the queue
      ? ''
      : $this->arr_queue[0];
      
    $arr = [
      "autoplay" => $this->arr_status['autoplay'],      # string     "on"|"off"
      "size"     => count($this->arr_queue),            # int        size of the queue
      "peek"     => $videoId,                           # string     videoId or '' if the queue is empty
      "mem"      => round($mem, 2).' MB',               # string     memory used by php
    ];

    if (isset($this->arr_status['message']))
      $arr['message'] = $this->arr_status['message'];
    
    return $arr;
  }
  
  
  
  /**
  * Get the queue of YouTube video Id's
  *
  * @return array     The queue as an array of videoId strings eg;
  *
  *   [ "QC8iQqtG0hg", "DAjMZ6fCPOo", ... ]
  */
  function getQueue() {
    
    return $this->arr_queue;
    
  }



  /**
  * Add a videoId to the queue and save the queue.
  *
  * @param string $videoId  The 11 char videoId which uniquely identitifes every video on YouTube, including shorts.
  * @return string          A msg confirming that the specified videoId has been enqueued.
  */
  function enqueue($videoId) {
    
    array_push($this->arr_queue, $videoId);
    $str = implode(PHP_EOL, $this->arr_queue);
    
    file_put_contents($this->queue_filename, $str);
    
    return "enqueued {$videoId}";
  }



  /**
  * Dequeue the first videoId in the queue and save the queue.
  *
  * @return array     Return a jsonnable array containing the videoId of the dequeued video and the autoplay state;
  *
  *   [
  *     "videoId": "<videoId>",
  *     "autoplay": "on|off"
  *   ]
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
  * Dequeue the first videoId only if it matches the supplied $videoId, and save the queue.
  *
  * @param string $videoId  The videoId of the video to be dequeued.
  * @return array           Return a jsonnable array containing the videoId of the dequeued video and the autoplay state;
  *
  *   [
  *     "videoId": "<videoId>",
  *     "autoplay": "on|off"
  *   ]
  */
  function dequeueId(string $videoId): array
  {
    $autoplay = $this->arr_status['autoplay'];          # "on"|"off"
    
    // if the queue is empty, return an empty string for the videoId
    if (0 == count($this->arr_queue))
      return [ "videoId"  => '',                        # no video to play
               "autoplay" => $autoplay ];               # "on"|"off"
    
    // if the first video in the queue matches $videoId, remove it from the queue
    if ($videoId == $this->arr_queue[0])
      array_shift($this->arr_queue);

    // save the queue file
    $str = implode(PHP_EOL, $this->arr_queue);          # imploding an empty array returns an empty string ''
    file_put_contents($this->queue_filename, $str);
    
    return [ "videoId"  => $videoId,                    # videoId string
             "autoplay" => $autoplay ];                 # "on"|"off"
  }
  
  
  
  /**
  * Toggle the autoplay state on|off.
  *
  * @return string     The autoplay state as a string after it has been toggled; "on"|"off".
  */
  function toggleAutoplay() {
    
    $this->arr_status['autoplay'] = ('off' == $this->arr_status['autoplay'])
      ? 'on'
      : 'off';
    $this->saveStatus();

    return $arr_status['autoplay'];
  }
  
  
  
  /**
  * Get the status message if there is one.
  *
  * @return string    The message string eg. "Hello world".
  */
  function getMessage(): string
  {
    return (isset($this->arr_status['message']))
      ? $this->arr_status['message']
      : '';
  }



  /**
  * Set the status message.
  *
  * @param string $message    The message to store in the state file eg. "Hello world".
  * @return string            The message.
  */
  function setMessage(string $message): string
  {
    $this->arr_status['message'] = $message;
    $this->saveStatus();
    
    return $message;
  }



  /**
  * Unset the status message.
  */
  function unsetMessage(): void
  {
    if (isset($this->arr_status['message']))
      unset($this->arr_status['message']);
    $this->saveStatus();
  }



  /******************************
  *
  * private
  *
  */
  
  
  /**
  * Save the status json file.
  */
  private function saveStatus(): void
  {
    $str_status = json_encode($this->arr_status);                 # JSON_PRETTY_PRINT
    file_put_contents($this->status_filename, $str_status);
  }
  
}

?>