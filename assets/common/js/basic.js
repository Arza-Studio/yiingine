/* DEFAULT FUNCTIONS */

function getWindowHeight(){
  myHeight = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    myHeight = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    myHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    myHeight = document.body.clientHeight;
  }
  return myHeight;
}

function getWindowWidth(){
  var myWidth = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    myWidth = window.innerWidth;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
  }
  return myWidth;
}

// The following script will obtain the scrolling offsets
function getScrollXY() {
  var scrOfX = 0, scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }
  return [ scrOfX, scrOfY ];
}

function isInt(x) {
   var y=parseInt(x);
   if (isNaN(y)) return false;
   return x==y && x.toString()==y.toString();
} 

/*This function converts a number into money using two decimal places and adding zeros if the amount is a round number.
Instructions:    This function requires the isNumeric function also available on this site. The isNumeric function has been added to the script above to save you having to hunt around for it.*/

function twoDP(num){
    if (isNumeric(num,true,true)) {
        dnum = Math.round(num * 100)/100;
        twoDPString = dnum + "";
        if (twoDPString.indexOf(".") == -1) {twoDPString += ".00"}
        if (twoDPString.indexOf(".") == twoDPString.length-2) {twoDPString += "0"}
        return twoDPString;
    } else {
        return "0.00"
    }
}

function isNumeric(sText,decimals,negatives) {
    var isNumber=true;
    var numDecimals = 0;
    var validChars = "0123456789";
    if (decimals)  validChars += ".";
    if (negatives) validChars += "-";
    var thisChar;
    for (i = 0; i < sText.length && isNumber == true; i++) {  
        thisChar = sText.charAt(i); 
        if (negatives && thisChar == "-" && i > 0) isNumber = false;
        if (decimals && thisChar == "."){
            numDecimals = numDecimals + 1;
            if (i==0 || i == sText.length-1) isNumber = false;
            if (numDecimals > 1) isNumber = false;
        }
        if (validChars.indexOf(thisChar) == -1) isNumber = false;
    }
    return isNumber;
}

/**
 * @param {element} obj : the target element (selector)
 * @param {string} eventName : the type of event ('load','resize',...)
 * @param {function} callback : the function to execute on the event
 */
function addEvent(obj, eventName, callback)
{       
    // If window onload with Netscape a 500ms delay is added
    //if(obj==window && eventName=='load' && navigator.appName == "Netscape")
    //{
        //callback = function(){ setTimeout(callback,500); };
    //}
    
    if(obj.addEventListener) // DOM standard
    {
        obj.addEventListener(eventName, callback, false);
    }
    else if(obj.attachEvent) // IE
    {
        obj.attachEvent("on"+eventName, function(e){ return callback.call(obj, e); });
    } 
}

function isScrolledIntoView(elem)
{
    var $elem = $(elem);
    var $window = $(window);

    var docViewTop = $window.scrollTop();
    var docViewBottom = docViewTop + $window.height();

    var elemTop = $elem.offset().top;
    var elemBottom = elemTop + $elem.height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}

var waitForFinalEvent = (function () {
  var timers = {};
  return function (callback, ms, uniqueId) {
    if (!uniqueId) {
      uniqueId = "Don't call this twice without a uniqueId";
    }
    if (timers[uniqueId]) {
      clearTimeout (timers[uniqueId]);
    }
    timers[uniqueId] = setTimeout(callback, ms);
  };
})();

//var syncDocumentHeightObjects = new Array();
function syncDocumentHeight(object)
{
    // Launching check
    //console.log('syncDocumentHeight :'); console.log(object);
    
    windowHeight = getWindowHeight(); //console.log('windowHeight : '+windowHeight); 
    htmlHeight = $('html').height(); //console.log('htmlHeight : '+htmlHeight); 
    
    if(windowHeight>htmlHeight)
    {
        object.css({height:windowHeight});
    }
    else
    {
        object.css({height:htmlHeight});
    }
}

// http://www.cssnewbie.com/equal-height-columns-with-jquery/#.UVmOFqvNh6s
function equalHeight(group) {
    var tallest = 0;
    group.each(function() {
        var thisHeight = $(this).height();
        if(thisHeight > tallest) {
            tallest = thisHeight;
        }
    });
    group.height(tallest);
}