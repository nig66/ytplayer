/**
*
* ytplayer chrome extension popup
*
*/

// globals
const statusUrl   = 'http://pi1b.lan/status/';


// when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {

  // parse the url for a videoId and if found, write it to the videoId textbox
  let queryOptions = { active: true, lastFocusedWindow: true };
  
  chrome.tabs.query(queryOptions, tabs => {
    const currentUrl = tabs[0].url;                   // url of the current active tab
    const videoId = parseYouTubeUrl(currentUrl);
    if (null !== videoId)
      document.getElementById("videoId").value = videoId;
  });
  
  // event listener to prevent forms redirect and instead refresh the UI to redraw the popup
  document.querySelectorAll("form.refresh").forEach((element) => {
    element.addEventListener("submit", function(event) {
      handleSubmit(element, event);
    })
  });

  // refresh the popup
  refreshUi();
});


/**
* Parse a YouTube Url and return the 11 char videoId (or null if none found) from a string of three possible patterns;
*
*   version: 1.2
* 
* hUHJu9fSEWg                                         return "hUHJu9fSEWg"
* https://www.youtube.com/watch?v=xkLcOo3B32I         return "xkLcOo3B32I"
* https://youtu.be/TAJkWQYmaoE?si=mbzUq5mCJ3gwtdBk    return "TAJkWQYmaoE"
* https://www.youtube.com/shorts/yCV_NyS-FKo          return "yCV_NyS-FKo" 
*
*   ^[\w-]{11}$                match exactly 11 valid chars of the videoId ("_", "-", "a-z", "A-Z" or "0-9").
*   (?<=\?v=)[\w-]{11}(?=&|$)  match "?v=" then 11 valid chars of the videoId then either the "&" char or the end.
*   (?<=\/)[\w-]{11}(?=\?)     match a forward slash then 11 valid chars of the videoId then a question mark.
*   (?<=\/shorts\/)[\w-]{11}$  match a string ending '/shorts/' then 11 valid chars of the videoId.
*/
function parseYouTubeUrl(str) {
  
  var myReg = /^[\w-]{11}$|(?<=\?v=)[\w-]{11}(?=&|$)|(?<=\/)[\w-]{11}(?=\?)|(?<=\/shorts\/)[\w-]{11}$/g;    // v1.2
  // var myReg = /^[\w-]{11}$|(?<=\?v=)[\w-]{11}$|(?<=\/)[\w-]{11}(?=\?)|(?<=\/shorts\/)[\w-]{11}$/g;     // v1.1
  // var myReg = /^[\w-]{11}$|(?<=\?v=)[\w-]{11}$|(?<=\/)[\w-]{11}(?=\?)/g;                               // v1.0
  
  const myArray = str.match(myReg);
  
  if (null == myArray) {
    return null;
  } else {
    return myArray[0];
  }
}


// parse the the browser address bar for a YouTube videoId
function xparseUrl(callback) {
  
  let queryOptions = { active: true, lastFocusedWindow: true };
  
  chrome.tabs.query(queryOptions, tabs => {
    
    const url_string = tabs[0].url;
    const url = new URL(url_string);
    const videoId = url.searchParams.get("v");
    
    callback(videoId);
  });
}


// when a form submit button is clicked, prevent page redirect and instead refresh the UI to redraw the popup
function handleSubmit(element, event) {
    
  event.preventDefault();
  
  fetch(element.action, {
    method: "post",
    body: new URLSearchParams(new FormData(element))
  })
    .then(response => {
      refreshUi()
    })
}



// refresh page
function refreshUi() {
  
  setBadge('x');
  
  // fetch status
  fetch(statusUrl + '?state')
    .then(response => response.json())
    .then((json) => {
      var size = json['size'].toString();
      document.getElementById("autoplay").innerHTML = json['autoplay'];   // display the autoplay status: on|off
      document.getElementById("size").innerHTML = size;                   // display the queue size
      var badgeMsg = ('0' == size) ? '' : size;                           // queue size for the badge message
      setBadge(badgeMsg);
    })
}


// set badge
function setBadge(msg) {
  
  chrome.action.setBadgeBackgroundColor({color:[0,0,255,255]});
  
  if ('' == msg) {
    chrome.action.setBadgeText({text:''})
  } else {
    chrome.action.setBadgeText({text:msg})
  }
}