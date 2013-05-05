$(function() {
   var startIntervalId = 0;
   var stopIntervalId = 0;
   startIntervalId = startStreaming(); //start querying Instagram API
   
   if($('#tagHolder').length > 0){
       $('#titleholder').elipsesAnimation({text:"Streaming tag",numDots:3,delay:950});//animate the dots
   }
  
  if($('#streamController').length > 0){  
      
      $("#streamController").bind("click", function(event){//bind a functionality to the link button for stop/continue streaming
             
             if($(this).html() == 'Pause'){
                stopIntervalId = stopStreaming(startIntervalId); //perform tagId update but return no data
                $(this).html('Stream').removeClass('btn-danger').addClass('btn-inverse');
                $('#loader').css('display','none');
                $('.current_tag_holder').css('display','none');
              }
              else{
                 startIntervalId = continueStreaming(stopIntervalId); //continue streaming data 
                 $(this).html('Pause').removeClass('btn-inverse').addClass('btn-danger');
                 $('#loader').css('display','');
                 $('.current_tag_holder').css('display','');
              }  
              return false;
        });
   } 
    
    scroll($('#content-box'));//capability to scroll directly to the top/bottom most part of the page
    
    $("form").submit(function(e){
        $.blockUI({ css: { 
                          border: 'none', 
                          padding: '15px', 
                          backgroundColor: '#000', 
                          '-webkit-border-radius': '10px', 
                          '-moz-border-radius': '10px', 
                          opacity: .5, 
                          color: '#fff' 
                      }
                  });
    });
    
     //responsible for adding data when the page got scrolled down
     if( $("#content-box").length > 0){
        $(window).scroll(function(){
            var wintop = $(window).scrollTop() + 10, docheight = $(document).height(), winheight = $(window).height();
            var  scrolltrigger = 0.95;
            if  ((wintop/(docheight-winheight)) > scrolltrigger) {
              lastAddedFeed();
            }
        }); 
     }
     
    $('#fullSize').bind('click', function() {//bind a click function to append the latest feed age(unix time) for full size view
       var url = '/site/SuperSize/?tag=' + $('#tagName').val().replace(/[^a-zA-Z ]/g, "") + '&id=' + latestFeedDate();//strip the tagname for special chars
       window.location.href=url;
    });

   
});

//ajax function that will add the last feed on the bottom when scrolled
function lastAddedFeed(){
        if($('.instagram_stream_container').length > 30){//limit the instagram box to 30
                    return;
        }
         var url = jQuery.trim($('div.ajaxDownUrl').html());
         var tag_id = $('#tagId').val();
         var querystring1 = "?age="+oldestFeedDate();
         var queryString2 = "&tagId="+tag_id;
         var fullUrl = url + querystring1 + queryString2; 
         $.get(fullUrl, function(data){
            
             if (data != "none" && data != '') {
                 var responseSplitter = data.split('||');
                 if(!isExist(responseSplitter[1],2110)){ //set the max number to the highest
                     $("#content-box").append(responseSplitter[0]);
                     var el = $('#'+responseSplitter[1]);
                     if(el){
                        el.css('display','none').slideDown('slow').css('opacity','0.2').animate({opacity:1},2000);  
                     }
                 }
            }
            
        });
}


function startStreaming(){ //function that will start calling Instagram API thru ajax
    var intervalId = 0;
    if($('.instagram_stream_container').length > 0){
        intervalId = setInterval( ajaxify, 4500 );
    }
    return intervalId;
}

function bindClickFunction(identifier,url,displayurl){
          var imageId = 'bigImage_'+identifier;
          var image = $('#'+imageId);
          image.attr('src',url);//assign the media url   
          $('#myModal_'+identifier).modal('toggle').css({ width: 'auto',
                'margin-left': function () {
                return -($(this).width() / 2);
                },'margin-top': function () {
                 return -($(this).height() / 2);
                }
          });
          $('#myModal_'+identifier).appendTo($("body"));
          CreateNewLikeButton(identifier,displayurl);//parse the social widgets
}


function stopStreaming(intervalId){
     var updateTagIdInterval = 0;
     clearInterval(intervalId);//clear the ajaxify function interval 
     updateTagIdInterval = setInterval( updateTagIdTime, 3500 );
     return updateTagIdInterval;
}

function continueStreaming(intervalId){
    var queryIntervalId = 0;
    clearInterval(intervalId);//clear the updateTagIdTime function interval 
    queryIntervalId = setInterval( ajaxify, 4500 );
    return queryIntervalId;
}

//ajax call that will only update the time stamp for tagId so it cannot be deleted
function updateTagIdTime(){
    var url = jQuery.trim($('div.ajaxUrl').html());
    var tag_id = $('#tagId').val();
    setCookieValue('instastrmr'); //update the cookie value
    
    $.ajax({  
                    type: "POST",  
                    url: url,  
                    data: { tagId:tag_id  },  
                    success: function(response) {}  
           }); 
    
}

//checks if a picture is fully/completely loaded on the page
function displayImageHolder(param1){
       $('#'+param1).slideDown('slow').css('opacity','0.2').animate({opacity:1},2000);
}

function ajaxify(){
   
    var url = jQuery.trim($('div.ajaxUrl').html());
    var maxAge = latestFeedDate();
    var tag_id = $('#tagId').val();
    var tag_name = $('#tagName').val();
    setCookieValue('instastrmr');//update the cookie value
    
    $.ajax({  
                    type: "POST",  
                    url: url,  
                    data: { age:maxAge,tagId:tag_id,tagName:tag_name },  
                    success: function(response) {
                       if(response != 'none' && response != 'refresh'){
                         var responseSplitter = response.split('||');
                           if(!isExist(responseSplitter[1],31)){
                                $(responseSplitter[0]).insertAfter("#top_holder").css('display','none');
                                $('#img_'+responseSplitter[1]).load(function(){//check if image is loaded on the page
                                    displayImageHolder(responseSplitter[1]);
                                });
                            
                           }
                        }
                        else if(response == 'refresh'){
                           location.reload();//reload the users page 
                        }
                    }  
           }); 

}

function CreateNewLikeButton(identifier,url)
{
   
    $("#facebook_plugin_holder_"+identifier).empty().append('<fb:like href="'+url+'" send="false" layout="button_count" width="10" show_faces="false" font="arial"></fb:like>');
    FB.XFBML.parse($("#facebook_plugin_holder_"+identifier).get(0));//extra parse for facebook
    twttr.widgets.load();//extra parse for twitter
    gapi.plusone.go();
}


function isExist($unique_id,maxNumberToDisplay){
    
    
    var ids = new Array();
    
    $('.instagram_stream_container').each(function(){
         ids.push($(this).attr('id'));
     });
    
    return inArray($unique_id,ids,maxNumberToDisplay);
}

function randomFromTo(from, to){
       return Math.floor(Math.random() * (to - from + 1) + from);
    }

function inArray(needle, haystack, maxNumber) {
    var isExist = false;
    var length = haystack.length;
    
    if(length == maxNumber || length > maxNumber){ //we're sure length > 3 @ this point
       var pos = randomFromTo(1,length - 2);  
       $('#'+haystack[pos]).remove();
       length = length - 1;
    }
    
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle){
            isExist = true;
            return isExist;
        } 
    }
    
    return isExist;
}

function latestFeedDate(){//get all instagram unique identifiers on the page
    var counter = 0;
    var age_string = '';
    $('.ages').each(function(){
          if(counter == 0){ //check if this is the top most or first feed
             age_string = $(this).val();
             return false;
          }
          counter++;
    });
    return age_string;   
}

//grab the oldest feed on the page
function oldestFeedDate(){
       var counter = 0;
       var age_string = '';
       var count_size = $('.ages').length - 1;
      
       $('.ages').each(function(){
          if(counter == count_size){ //check if this is the down most or last feed
             age_string = $(this).val();
          }
          counter++;
       });
       
      return age_string;   
}

//start functions for manipulating cookies

function deleteCookie(cookieName) {
       var cookie = cookieName;
        var cookieValue = 0;//set the cookie value to zero
        var expirationDate = new Date();
        expirationDate.setDate(expirationDate.getDate() + 99999); //set the expiration to forever
        var tmp2 = cookie + "=" + cookieValue + "; expires=" + expirationDate.toGMTString() + "; path=/";
        document.cookie = tmp2;
}

function getCookieVaue(cookieName) { //gets the value of a cookie by giving the cookie name
    var i, x, y, ARRcookies = document.cookie.split(";");
    for (i=0; i<ARRcookies.length; i++) {
        x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
        y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
        x=x.replace(/^\s+|\s+$/g,"");
        if (x === cookieName) {
            return unescape(y);
        }    
    }
}

function setCookieValue(cookieName){//update or set the cookie value
        var cookie = cookieName;
        var cookieValue = Math.round((new Date()).getTime() / 1000);//set the cookie value to the present unix time
        var expirationDate = new Date();
        expirationDate.setDate(expirationDate.getDate() + 99999); //set the expiration to forever
        var tmp2 = cookie + "=" + cookieValue + "; expires=" + expirationDate.toGMTString() + "; path=/";
        document.cookie = tmp2;
}

function isCookiePresent(cookieName){
    if(document.cookie.indexOf(cookieName) >= 0)
    {
        return true;
    }
    else
     return false;
}


function isCookieOld(cookieName){
    var isOld = false;
    var timeDiff = Math.round((new Date()).getTime() / 1000) - getCookieVaue(cookieName);
    if(timeDiff > 5){  //meaning cookie has not been updated for past 5 seconds ago
       isOld = true; 
    }
    return isOld;
}

//end functions for manipulating cookies


function isThereAnotherOpenBrowser(cookieName){
    if(!isCookiePresent(cookieName)){ //first time visitor
      return false;
    }
    else{
      if(isCookieOld(cookieName)){
           return false; 
        }
        else
          return true;
    }
}

function scroll(element){
    var $elem = element;
    
    $('#nav_up').fadeIn('slow');
    $('#nav_down').fadeIn('slow');  
    
    $(window).bind('scrollstart', function(){
            $('#nav_up,#nav_down').stop().animate({'opacity':'0.2'});
    });
    $(window).bind('scrollstop', function(){
            $('#nav_up,#nav_down').stop().animate({'opacity':'1'});
    });
    
    $('#nav_down').click(
            function (e) {
                    $('html, body').animate({scrollTop: $elem.height()}, 1000);
            }
    );
    $('#nav_up').click(
            function (e) {
                    $('html, body').animate({scrollTop: '0px'}, 1000);
            }
    );
}
