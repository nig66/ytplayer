/***********************************
* Run script on every page matching;
*
*   https://www.youtube.com/*
*
*/


// globals
const statusUrl   = 'https://zeb.uk.to/status/';


// init
setTimeout(insertEnqueueButtons, 4000);



/**
* iterate over the video links. for each one add a button which when
* clicked then grabs the videoId and enqueue's it
*/
function insertEnqueueButtons() {
  
  //const selector = 'a[href^="/watch?v="][id="video-title"]';
  const selector = 'a[href^="/watch?v="][id="video-title-link"]';
  
  document.querySelectorAll(selector).forEach((link) => {
    
    // create an enqueue button
    var myButton = document.createElement('button');
    myButton.innerHTML = 'Enqueue';
    myButton.setAttribute('id', 'enqueue');
    
    // add a click event handler
    myButton.addEventListener('click', (event) => {
      var videoId = getHrefVideoId(link.getAttribute('href'));
      enqueueVideo(videoId);
    });
    
    // insert the button if one does not already exist
    if ('enqueue' !== link.parentNode.parentNode.lastChild.getAttribute('id'))
      link.parentNode.parentNode.appendChild(myButton);
  });
  
  setTimeout(insertEnqueueButtons, 3000);
}


/**
* enqueue the specified videoId
*/
function enqueueVideo(videoId) {
  fetch(statusUrl + '?queue', {
    method: "post",
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body: new URLSearchParams({ videoId: videoId })
  })
}


/**
* return a videoId from the href of the given element
*/
function getHrefVideoId(href) {
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
* insert an enqueue button
*
function insertButton(element) {
  
  var href = element.getAttribute('href');
  var videoId = getHrefVideoId(href);
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
*/


/*
// insert enqueue buttons
function xinsertEnqueueButtons() {
  const selector1 = "a[href^='/watch?v='][id='video-title']";
  const selector2 = "a[href^='/watch?v='][id='video-title-link']";
  document.querySelectorAll(selector1).forEach((element) => { insertButton(element) });
  document.querySelectorAll(selector2).forEach((element) => { insertButton(element) });
}
*/

/*
function zinsertEnqueueButtons() {
  const selector = 'a[href^="/watch?v="]';
  var linkElements = document.querySelectorAll(selector);
  linkElements.forEach((element) => {
    var videoId = getHrefVideoId(element.getAttribute('href'));
    element.style = 'border: 2px solid yellow';
    element.appendChild(document.createTextNode(' foo:' + videoId));
    element.addEventListener('mouseenter', (event) => {
      event.target.style = 'border: 1px solid red';
      var videoId = getHrefVideoId(event.target.getAttribute('href'));
      event.target.appendChild(document.createTextNode(' foo:' + videoId));
    });
  });
}

function yinsertEnqueueButtons() {
  //const selector = "a[href^='/watch?v=']";
  const selector = '[href^="/watch?v="]';
  var elements = document.querySelectorAll(selector);
  elements.forEach((element) => {
    element.style = 'border: 2px solid red';
    var myDiv = document.createElement('div');
    var href = element.getAttribute('href');
    var videoId = getHrefVideoId(href);
    myDiv.innerHTML = 'href:' + href + ' id:' + videoId;
    element.after(myDiv);       // insert myDiv after element
  });
}
*/

/**
  link.style = 'border: 2px solid yellow';
  button.style = 'border: 2px solid red';
  button.innerHTML = 'videoId: ' + videoId;
  link.style = 'border: 2px solid green';       // change link style
  var id = getHrefVideoId(link.getAttribute('href'));
  var button = event.target;
  document.querySelectorAll('a[href^="/watch?v="]').forEach((link) => {
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