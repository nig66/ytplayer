/**
* "To test your website as if user were strongly engaged with your site and playback autoplay would be always
* allowed, you can disable the autoplay policy entirely by using a command line flag:"
* 
*   chrome.exe --autoplay-policy=no-user-gesture-required
* 
* https://developer.chrome.com/blog/autoplay/
*
* QC8iQqtG0hg   milky way
* DAjMZ6fCPOo   5 second timer
* lh__r69Jqec   7 second timer
* 46W7uzj-51w   Nescafe - 6 Second Ad
*
* https://stackoverflow.com/questions/33899401/setting-keyboard-focus-to-youtube-embed
* https://stackoverflow.com/questions/497094/how-do-i-find-out-which-dom-element-has-the-focus


event handling

  onPlayerReady
  
  onTimeout
    checkState;
      if there is a video to play and autoplay is on then
        dequeue video and play it
      else
        timer(onTimeout, 2000)
*
*/


// globals
const statusUrl   = 'http://pi1b.lan/status/';

// After the API code downloads, replace the 'ytplayer' element with an <iframe> and YouTube player .
var player;

// timer to check for video added to empty queue when autoplay is on, or autoplay enabled when queue not empty
var timeoutId;



/**  
*  const author = player.getVideoData()['author'];
*  const title = player.getVideoData()['title'];
*  const errorCode = player.getVideoData()['errorCode'];     // null |
*  const isPlayable = player.getVideoData()['isPlayable'];   // true | false
*  const isLive = player.getVideoData()['isLive'];           // true | false
*
*  onPlayerStateChange;
*
*   -1:unstarted
*    0:ended
*    1:PLAYING
*    2:PAUSED
*    3:buffering
*    5:CUED
*    
*  keypress spacebar:  if CUED or PAUSED then play
*                      if PLAYING then pause
*/
function onPlayerStateChange(event) {
  
  const playerState = event.data;
  const videoId = player.getVideoData()['video_id'];
  
  console.log('Player state change ' + playerState + ' ' + videoId);
  
  if (1 == playerState) {                       // state: PLAYING
    const duration = player.getDuration();      // available just after video starts playing. after buffering?
    console.log('duration: ' + duration);
  }
  
  if (event.data !== 0)         // 0 = ENDED
      return;
      
  console.log('Video ended');
  
  fetch(statusUrl + '?queue_delete_ifTop', {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams({ videoId: player.getVideoData()['video_id'] })
    })
    .then(() => {
      setTimeout(qNext, 0, event.target)                // q up the next video if there is one    
    })
}



/**
* q up the next video ready to play
*/
function qNext(target) {
  
  fetch(statusUrl + '?state')
    .then(response => response.json())
    .then((json) => {
      var msg = ('' == json['peek'])
        ? 'Queue empty. Idle.'
        : 'peek: ' + json['peek'];
      console.log(msg);
      if ('' == json['peek'])
        return true;                          // queue is empty so start timer
      if ('on' == json['autoplay']) {
        target.loadVideoById(json['peek']);   // play video
        return false;                         // don't start timer
      } else {
        target.cueVideoById(json['peek']);    // load video but don't play it
        return true;                          // don't start timer  
      }
    })
    .then((flg) => {
      if (flg) {
        //console.log('Start timer');
        setTimeout(qNext, 3000, target);
      }
    })
}



// fired once when the YouTube player is ready
function onPlayerReady(event) {
  
  console.log('Player ready');
  
  const urlParams = new URLSearchParams(window.location.search);
  const videoId = urlParams.get('v');
  
  if (null == videoId) {
    setTimeout(qNext, 0, event.target);
  } else {
    event.target.loadVideoById(videoId);
  }
}



// Load the IFrame Player API code asynchronously.
(function() {
  
  var tag = document.createElement('script');
  tag.src = "https://www.youtube.com/player_api";
  var firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
  
})();



// document ready
document.addEventListener('DOMContentLoaded', function() {
  
  // set focus to playPause button
  var playPause = document.getElementById('playPauseButton');
  playPause.focus();
  
  // add keypress event handler
  playPause.addEventListener("keypress", function(event) {
    if (32 == event.keyCode)    // spacebar
      { togglePlayPause() }
  });
  
});



// toggle playPause
function togglePlayPause() {

  console.log('toggle playPause');
  
  const state = player.getPlayerState();
  if (YT.PlayerState.PLAYING == state)                                  // PLAYING=1
    { player.pauseVideo() }
  if ([YT.PlayerState.CUED, YT.PlayerState.PAUSED].includes(state))     // CUED=5, PAUSED=2
    { player.playVideo() }
  
}



/**
* YouTubePlayer event handlers
*
* -1  unstarted     2  paused
*  0  ended         3  buffering
*  1  playing       5  cued
*/
function onYouTubePlayerAPIReady() {
  player = new YT.Player('ytplayer', {
    events: {
      'onReady': onPlayerReady,
      'onStateChange': onPlayerStateChange,
      'onAutoplayBlocked': onAutoplayBlocked,
      'onError': onPlayerError
    }
  });
}

function onAutoplayBlocked(event) {
  console.log('Autoplay blocked: ' + event.data)
}



// YTplayer error handler
function onPlayerError(event) {

  const errCode = event.data;
  const errTxt = (150 == errCode)
    ? 'video owner blocked playback'
    : 'unknown code';
  const errMsg = 'Err ' + errCode + ': ' + errTxt + ' ' + player.getVideoData()['video_id'];
  console.log(errMsg);
  
  // save the error message then remove the troublesome video from the queue and continue
  fetch(statusUrl + '?message', {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams({ message: errMsg })
    })
    .then(() => {
      const videoId = player.getVideoData()['video_id'];
      fetch(statusUrl + '?dequeueId=' + videoId, { method:"POST" })      // dequeue the current video
        .then(() => {
          setTimeout(qNext, 0, event.target)                // q up the next video if there is one    
        })
    })
}