/***********************************
* Run script on every page matching;
*
*   https://www.youtube.com/*
*
*/



// globals
const statusUrl   = 'https://zeb.uk.to/status/';


// init
setTimeout(insertEnqueueButtons, 3000);


// insert enqueue buttons
function insertEnqueueButtons() {
  const selector1 = "a[href^='/watch?v='][id='video-title']";
  const selector2 = "a[href^='/watch?v='][id='video-title-link']";
  document.querySelectorAll(selector1).forEach((element) => { insertButton(element) });
  document.querySelectorAll(selector2).forEach((element) => { insertButton(element) });
}


/**
* insert an enqueue button
*/
function insertButton(element) {
  
  var videoId = getHrefVideoId(element);
  var myButton = document.createElement('button');
  myButton.innerHTML = 'Add ' + videoId;
  
  myButton.addEventListener('click', (event) => {
    fetch(statusUrl + '?queue', {
      method: "post",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams({ videoId: videoId })
    })
  });
  
  element.parentNode.parentNode.appendChild(myButton);
}


/**
* return a videoId from the href of the given element
*/
function getHrefVideoId(element) {
  var href = element.getAttribute('href');
  var index = href.indexOf('?');
  var queryString = href.substring(index + 1);
  var params = new URLSearchParams(queryString);
  return params.get('v');
}





/********************************
*
* junk
*
*/

/**
* prevent forms redirect
function addSubmitButtonListener(formElement) {
  formElement.addEventListener("submit", function(event){
    event.preventDefault();
    fetch(formElement.action, {
      method: "post",
      body: new URLSearchParams(new FormData(formElement))
    });
  })
}
*/


/**
*  <form class="pd" action="/status/?queue" method="post">
*    <input type="hidden" name="videoId" value="QC8iQqtG0hg">
*    <input type="submit" value="Enqueue">
*  </form>
function buildEnqueueForm(videoId) {
  var myForm = document.createElement("form");
  myForm.setAttribute("class", "pd");
  myForm.setAttribute("action", "https://zeb.uk.to/status/?queue");
  myForm.setAttribute("method", "post");
  myForm.setAttribute("style", "position: relative");
  myForm.setAttribute("z-index", "999");
  var inputHidden = document.createElement("input");
  inputHidden.setAttribute("type", "hidden");
  inputHidden.setAttribute("name", "videoId");
  inputHidden.setAttribute("value", videoId);
  var inputSubmit = document.createElement("input");
  inputSubmit.setAttribute("type", "submit");
  inputSubmit.setAttribute("value", videoId);
  myForm.appendChild(inputHidden);
  myForm.appendChild(inputSubmit);
  return myForm;
}
*/




  //const urlParams = new URLSearchParams(href);
  //const videoId = urlParams.get('v');
  //var videoId = "QC8iQqtG0hg";
  //element.parentNode.appendChild(enqueueForm);
  //element.parentNode.append(enqueueForm);
  //element.parentNode.after(enqueueForm);
  //element.after(enqueueForm);
  //element.append(enqueueForm);
  //element.appendChild(enqueueForm);
  //element.after("<button>hi</button>")
  //element.after("foo");


/**
addEnqueueForm();
function addEnqueueForm() {
  enqueueForm = buildEnqueueForm();
  var e = document.getElementById("logarea");
  e.after(enqueueForm);
}
*/


/**
<a id="video-title-link" 
  class="yt-simple-endpoint focus-on-expand style-scope ytd-rich-grid-media" 
  aria-label="Watch Sky News by Sky News 1,901,958 views" 
  title="Watch Sky News" 
  href="/watch?v=oJUvTVdTMyY">
  
  <yt-formatted-string
    id="video-title" 
    class="style-scope ytd-rich-grid-media" 
    aria-label="Watch Sky News by Sky News 1,901,958 views"
    >Watch Sky News</yt-formatted-string>
</a>
*/


/**
const x = document.querySelector("input[id='v1']");
log('ele:' + x.value);
elem.value = 'hi';

document.querySelectorAll("a#video-title-link").forEach((element) => {
  alert('ele: ' + element.innerHTML);
});
*/



/**
addPressHereButton();

function addPressHereButton() {
  var button = document.createElement("button");
  button.setAttribute("style", "color:red");
  var txt = document.createTextNode("Press here");
  button.appendChild(txt);
  var e = document.getElementById("logarea");
  e.after(button);
}
*/


/**
//document.querySelectorAll("input[type='text']").forEach((element) => {
// hrefs
document.querySelectorAll("a").forEach((element) => {
  //log(element.innerHTML + ' ' + element.href);
  element.after("foo");
});
*/