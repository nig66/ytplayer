// globals
const statusUrl   = 'https://zeb.uk.to/status/';


// create an alarm (minimum timeout is 30 seconds)
chrome.alarms.create({ periodInMinutes: 0.5 })


// create an alarm handler
chrome.alarms.onAlarm.addListener(() => {

  // fetch status
  fetch(statusUrl + '?state')
    .then(response => response.json())
    .then((json) => {
      var size = json['size'].toString();         // get the queue size from the response
      var badgeMsg = ('0' == size) ? '' : size;   // if the queue size is zero, the badge msg is ''
      setBadge(badgeMsg);
    })
  
});


// set badge
function setBadge(msg) {
  
  chrome.action.setBadgeBackgroundColor({color:[0,0,255,255]});
  
  if ('' == msg) {
    chrome.action.setBadgeText({text:''})
  } else {
    chrome.action.setBadgeText({text:msg})
  }
}