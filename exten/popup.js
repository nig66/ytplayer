// globals
const statusUrl   = 'http://pi1b.lan/status/';


// when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {

  // parse the url for a videoId and if found, write it to the videoId textbox
  parseUrl((videoId) => {
    if (undefined !== videoId)
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



// parse the the browser address bar for a YouTube videoId
function parseUrl(callback) {
  
  let queryOptions = { active: true, lastFocusedWindow: true };
  
  chrome.tabs.query(queryOptions, tabs => {
    
    const url_string = tabs[0].url;
    const url = new URL(url_string);
    const videoId = url.searchParams.get("v");
    
    callback(videoId);
  });
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