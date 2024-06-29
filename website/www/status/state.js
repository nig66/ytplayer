/**
* /status
*
* stack: push, pop, peek (or top), isEmpty, size.
* queue: enqueue, dequeue, front (or peek), isEmpty, size.
*/


// globals
const statusUrl   = 'http://pi1b.lan/status/';


// do first refresh
tickerRefresh();


// refeshUi and start a timer to do the next refeshUi
function tickerRefresh() {
  refeshUI();
  setTimeout(tickerRefresh, 4000);
}


//method: element.attributes['method'].value,
// intercept form submits
document.querySelectorAll("form.pd").forEach(function(element) {
  
  element.addEventListener("submit", function(event){
    
    event.preventDefault();
    
    fetch(element.action, {
      method: "post",
      body: new URLSearchParams(new FormData(element))
    })
    .then((response) => {
      setTimeout(function() {
        refeshUI();
      }, 0)
    })
    
  })
})



/**
* refresh the changeable UI elements; autoplay status, queued count, status json, queue
*/
function refeshUI() {

  // refresh the autoplayStatus, queueSize & stateJson
  fetch(statusUrl + '?state')
    .then(response => response.json())
    .then((json) => {
      document.getElementById("autoplayStatus").innerHTML = json['autoplay'];
      document.getElementById("queueSize").innerHTML = 'Queued: ' + json['size'];
      document.getElementById("stateJson").innerHTML = JSON.stringify(json, null, 2);
    });
    
  // refresh the queue
  fetch(statusUrl + '?queue')
    .then((response) => response.text())
    .then((text) => {
      document.getElementById("rawQueue").innerHTML = text;
    });
}



/**
* DOMContentLoaded
*/
document.addEventListener('DOMContentLoaded', function() {

  // create an event listener to get a videoId from a YouTube Url
  document.getElementById("videoUrl").addEventListener("input", (event) => {
    const videoId = parseYouTubeUrl(event.target.value.trim());
    if (null == videoId) {
      //document.getElementById("submitVideoId").disabled = true;
      document.getElementById("showVideoId").innerHTML = "";
    } else {
      //document.getElementById("submitVideoId").disabled = false;
      document.getElementById("showVideoId").innerHTML = videoId;
      document.getElementById("videoId").value = videoId;
    }
  });
  
});


/**
* Parse a YouTube Url and return the 11 char videoId (or null if none found) from a string of three possible patterns;
* 
* hUHJu9fSEWg                                          return "hUHJu9fSEWg"
* https://www.youtube.com/watch?v=xkLcOo3B32I          return "xkLcOo3B32I"
* https://youtu.be/TAJkWQYmaoE?si=mbzUq5mCJ3gwtdBk     return "TAJkWQYmaoE"
*
*   ^[\w-]{11}$             match a string of exactly 11 valid chars of the videoId ("_", "-", "a-z", "A-Z" or "0-9").
*   (?<=\?v=)[\w-]{11}$     match a string ending "?v=" then exactly 11 valid chars of the videoId. 
*   (?<=\/)[\w-]{11}(?=\?)  match a forward slash then exactly 11 valid chars of the videoId then question mark.
*/
function parseYouTubeUrl(str) {
  
  var myReg = /^[\w-]{11}$|(?<=\?v=)[\w-]{11}$|(?<=\/)[\w-]{11}(?=\?)/g;
  
  const myArray = str.match(myReg);
  
  if (null == myArray) {
    return null;
  } else {
    return myArray[0];
  }
}