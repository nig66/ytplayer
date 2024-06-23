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


// refresh badge
function refreshUi() {
  
  setBadge('x');
  
  // fetch queue size & display it in the badge
  fetch(statusUrl + '?size')
    .then(response => response.text())
    .then((size) => {
      document.getElementById("size").innerHTML = size;   // display the queue size
      var msg = (0 == size) ? '' : size;
      setBadge(msg);                                      // display the queue size
    })
    
  // fetch the autoplay status and display it
  fetch(statusUrl + '?autoplay')
    .then(response => response.text())
    .then((autoplay) => {
      document.getElementById("autoplay").innerHTML = autoplay;
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



/**
document.querySelectorAll("form.pd").forEach(function(element) {
  element.addEventListener("submit", function(event){
    event.preventDefault();
    fetch(element.action, {
      method: "post",
      body: new URLSearchParams(new FormData(element))
    });
  })
});
*/