// globals
const statusUrl   = 'http://pi1b.lan/status/';


// when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {

  // fetch the YouTube videoId (if there is one) from the browser address bar
  chrome.tabs.query({active: true, lastFocusedWindow: true}, tabs => {

    let url_string = tabs[0].url;
    var url = new URL(url_string);
    var v = url.searchParams.get("v");

    if (v)
      { document.getElementById("videoId").value = v }
  });

  // event listener to prevent forms redirect & refresh UI
  document.querySelectorAll("form.refresh").forEach(function(element) {
    element.addEventListener("submit", function(event){
      event.preventDefault();
      fetch(element.action, {
        method: "post",
        body: new URLSearchParams(new FormData(element))
      })
        .then(response => {
          refreshUi()
        })
    })
  });

  // fetch & show the queue size & autoplay status
  refreshUi();
    
});


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
      var badgeMsg = ('0' == size) ? '' : size;                               // queue size for the badge message
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