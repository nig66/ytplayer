/***********************************
* Run script on every page matching;
*
*   http://pi1b.lan/status/*
*   https://www.youtube.com/*
*
*/


/******************
* pi1b stuff
*   http://pi1b.lan/status/*
*/

// content
const myTextarea = document.querySelector("textarea");

// log a msg
function log(msg) {
  myTextarea.value += msg + '\r\n';
}



/******************
* YouTube stuff
*   https://www.youtube.com/*
*/

setTimeout(insertEnqueueButtons, 3000);


// insert all buttons
function insertEnqueueButtons() {
  const selector1 = "a[href^='/watch?v='][id='video-title']";
  const selector2 = "a[href^='/watch?v='][id='video-title-link']";
  document.querySelectorAll(selector1).forEach((element) => { insertButton(element) });
  document.querySelectorAll(selector2).forEach((element) => { insertButton(element) });
}

// insert one enqueue button
function insertButton(element) {
  var href = element.getAttribute('href');
  var index = href.indexOf('?');
  var queryString = href.substring(index + 1);
  var params = new URLSearchParams(queryString);
  var videoId = params.get('v');
  var enqueueForm = buildEnqueueForm(videoId);
  element.parentNode.appendChild(enqueueForm);
}
function xinsertButton(element) {
  var href = element.getAttribute('href');
  const videoId = href.slice(-11);
  var enqueueForm = buildEnqueueForm(videoId);
  element.parentNode.appendChild(enqueueForm);
}

/**
*  <form class="pd" action="/status/" method="post">
*    <input type="hidden" name="videoId" value="QC8iQqtG0hg">
*    <input type="submit" value="Enqueue">
*  </form>
*/
function buildEnqueueForm(videoId) {
  var myForm = document.createElement("form");
  myForm.setAttribute("class", "pd");
  myForm.setAttribute("action", "http://pi1b.lan/status/");
  myForm.setAttribute("method", "post");
  myForm.setAttribute("style", "position: relative");
  myForm.setAttribute("z-index", "999");
  var inputHidden = document.createElement("input");
  inputHidden.setAttribute("type", "hidden");
  inputHidden.setAttribute("name", "videoId");
  inputHidden.setAttribute("value", videoId);
  var inputSubmit = document.createElement("input");
  inputSubmit.setAttribute("type", "submit");
  //inputSubmit.setAttribute("value", "Enqueue " + videoId);
  inputSubmit.setAttribute("value", videoId);
  myForm.appendChild(inputHidden);
  myForm.appendChild(inputSubmit);
  return myForm;
}


/**
* prevent forms redirect
*
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




/********************************
*
* junk
*
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