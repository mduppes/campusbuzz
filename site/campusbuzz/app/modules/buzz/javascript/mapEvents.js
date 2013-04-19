

function filterOutCategory (categoryList){

  for(var i=0;i<categoryList.length;i++)
    console.log ("categories in filter: "+ categoryList[i]);

  makeAPICall(
    'POST', 'buzz', 'filterOut',
    {"isOfficial":mode,  "categoryList": categoryList, "lon": campusCenter.lng(), "lat":campusCenter.lat(), "distance": searchRadius},
    function(response){

      console.log (response);
      //iterate and plot pins
      var json = $.parseJSON(response);
      $(json.docs).each(function(i,data){

          var locArray = data.locationGeo.split(',');
          var loc= new google.maps.LatLng(locArray[0],locArray[1]);
          var category;
          console.log ("data's loc; "+locArray[0]+ ", "+locArray[1]);
          console.log ("google's loc; "+loc.lat()+ ", "+loc.lng());

          var selected_cat_list;
          if (mode==0){
            selected_cat_list= buzz_category;
          }else{
            selected_cat_list= news_category;
          }
          //create obj array of selected categories
          var cat_list = {};
          for (var i in categoryList){
              var name= categoryList[i];
              cat_list[name]= selected_cat_list[n
          }

          // assign item's category as first matching category in cat_list
          for(var name in cat_list){
              if (data.category.indexOf(name) != -1)
              {
                category=cat_list[name];
                break;
              }
          }

          //create marker for item
          if (category!=null){
            var marker = new google.maps.Marker({
              position: loc,
              map: map
            });
            marker.set("category", category);
            console.log ("category; "+marker.get("category"));

            if (mode==0){
              buzzMarkers.push(marker);
              buzzMarkerCluster.addMarker(marker);
              marker.set("isOfficial", false);


            }else{
              newsMarkers.push(marker);
              newsMarkerCluster.addMarker(marker);
              marker.set("isOfficial", true);
            }
            console.log ("official; "+marker.get("isOfficial"));
          }
      });
      $("#loading").hide();
      //end of handler
    });
}



function displayClouds(that){
  //change visibility of clouds
  addClass(that.cloud_, "show");
  removeClass(that.cloud_, "hidden");
}

//filter categories from slide out menu
function filterCategory(obj){
  $("#loading").show();
  var className=obj.className;
  var id= obj.id;
  console.log ("category: "+id);

  if (id.indexOf("buzz") != -1){
    //filter in buzz mode
    id= id.replace("buzz","");

    if (className.indexOf("filterOut")>-1){
      removeClass(obj, "filterOut");//show
      buzzCategoryList.push(id);
    }else{
      addClass(obj, "filterOut");//hide
      var found= buzzCategoryList.indexOf(id);
      buzzCategoryList.splice(found,1);
    }
    initializeMap();
    filterOutCategory(buzzCategoryList);
  }else{
    //filter in news mode
    id= id.replace("news","");

    if (className.indexOf("filterOut")>-1){
      removeClass(obj, "filterOut");//show
      newsCategoryList.push(id);
    }else{
      addClass(obj, "filterOut");//hide
      var found= newsCategoryList.indexOf(id);
      newsCategoryList.splice(found,1);
    }
    initializeMap();
    filterOutCategory(newsCategoryList);
  }
}


function toggleGPS(event){

  var gps = $("#gpsButton");

  if($("#gpsButton").hasClass("enable")){
      $("#gpsButton").removeClass("enable");
      //turn off gps tracking
      navigator.geolocation.clearWatch(watchID);
      //lastTrackedLocation="";
      clearLocationPin();
      console.log ("Turn off tracking");

  }else{
      $("#loading").show();
      $("#gpsButton").addClass("enable");
      //turn on gps tracking
      console.log ("Turn on tracking");
    if(navigator.geolocation) {
      browserSupportFlag = true;

      //set campus bounds
      var sw= new google.maps.LatLng(49.24080, -123.26523);
      var ne= new google.maps.LatLng(49.28091, -123.22540);
      var campusBounds= new google.maps.LatLngBounds(sw, ne);

      //check if location is within campus
      navigator.geolocation.getCurrentPosition(function(position){
        initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
        console.log ("initial pos: "+ initialLocation);
        if (campusBounds.contains(initialLocation)){
          watchID = navigator.geolocation.watchPosition(geo_success, geo_error, geo_options);
        }else{
          alert("You are not currently on UBC campus");
          $("#gpsButton").removeClass("enable");
        }
        $("#loading").hide();
      }, geo_error, {timeout:30000});

    }
    // Browser doesn't support Geolocation
    else {
      browserSupportFlag = false;
      handleNoGeolocation(browserSupportFlag);
      alert("Geolocation is not supported by your browser");
    }
  }
}

var geo_options = {
  enableHighAccuracy: true,
  maximumAge        : 100000,
  timeout           : 27000
};

function geo_success(position) {
  console.log ("position: "+position.coords.latitude, position.coords.longitude);

  var userLatLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
  lastTrackedLocation=userLatLng;

  clearLocationPin();
  locationPin= new google.maps.Marker({
          map: map,
          position: userLatLng,
          icon:{
            path: google.maps.SymbolPath.CIRCLE,
            fillOpacity: 0.8,
            strokeWeight: 2,
            strokeColor: '#0000FF',
            fillColor: '#0000FF',
            scale: 7
         }
  });

  map.panTo(userLatLng);
}

function geo_error() {
  alert("Sorry, no position available. Turn on your GPS and try again later.");
  lastTrackedLocation="";
  clearLocationPin();
  $("#gpsButton").removeClass("enable");
  navigator.geolocation.clearWatch(watchID);
  $("#loading").hide();
}

function clearLocationPin(){
  if (locationPin)
    locationPin.setMap(null);
}

function categoryCloudClickHandler (self){
  // query item according to filter selected

  //get category type!
  var className= self.className;

  var category="";

  if (mode==0){
    //check category for Buzz mode
    if(className.indexOf("rightBottom") != -1){
              category="Life";
    }else if (className.indexOf("leftBottom") != -1){
              category="Clubs";
    }else if (className.indexOf("rightTop") != -1){
              category="Health";
    }else if (className.indexOf("leftTop") != -1){
              category="Recreation";
    }
  }else{
    //check category for Official mode
    if(className.indexOf("rightBottom") != -1){
              category="News";
    }else if (className.indexOf("leftBottom") != -1){
              category="Career";
    }else if (className.indexOf("rightTop") != -1){
              category="Learning";
    }else if (className.indexOf("leftTop") != -1){
              category="Leisure";
    }
  }

  //get search bounds
  var bounds= $(self).closest($(".cluster")).data("bound");
  console.log (category);
  console.log (bounds.getNorthEast().lng().toFixed(8));
  console.log (bounds.getNorthEast().lat().toFixed(8));
  console.log (bounds.getSouthWest().lng().toFixed(8));
  console.log (bounds.getSouthWest().lat().toFixed(8));

  function toFixed( number, precision ) {
    var multiplier = Math.pow( 10, precision );
    return Math.round( number * multiplier ) / multiplier;
}

  var args= Array();
  args["category"]= category;
  args["isOfficial"]= mode;
  args ["neLng"]= (bounds.getNorthEast().lng()+0.0005).toFixed(8); //round up to 8th decimal to fix bounding box inaccuracy issue
  args ["neLat"]= (bounds.getNorthEast().lat()+0.0005).toFixed(8);
  args ["swLng"]= (bounds.getSouthWest().lng()-0.0005).toFixed(8);
  args ["swLat"]= (bounds.getSouthWest().lat()-0.0005).toFixed(8);
  args ["keyword"]= $("#searchbar").find("input").val();
  args ["sortBy"]= "time";
  args ["index"] = 0;

  //get user location
  var longitude="";
  var latitude="";
  if (lastTrackedLocation!=""){
    longitude=lastTrackedLocation.lng();
    latitude=lastTrackedLocation.lat();
  }else{
    if (initialLocation!=null){
      longitude=initialLocation.lng();
      latitude=initialLocation.lat();
    }else{
      console.log ("initialLocation is null");
    }
  }

  //save data to db
  makeAPICall(
    'POST', 'buzz', 'sendDetailQueryData',
    {"isOfficial":mode, "category": category, "keyword": args ["keyword"], "neLng": args ["neLng"], "neLat":args ["neLng"], "swLng": args ["swLng"], "swLat": args ["swLat"], "sort": args ["sortBy"],"userLng": longitude, "userLat": latitude},
    function(response){
      redirectTo("detail", args);
    });
}

//detail view only

$(window).scroll(function(){
  if ($(window).scrollTop() == $(document).height() - $(window).height()){
    console.log ("bottom!");
    loadMorePosts();
  }
});


function sortPosts(that){
  var selected= $(that).val();
  var param= $(that).data("param");
  $(that).val("popularity"); //set to point to popularity..

  var neLng= (param["neLng"]);
  var neLat= (param["neLat"]);
  var swLng= (param["swLng"]);
  var swLat= (param["swLat"]);
  var isOfficial= (param["isOfficial"]);
  var keyword= (param["keyword"]);
  var category= (param["category"]);
  var index= $(that).data("index");

  var args= Array();
  args["category"]= category;
  args["isOfficial"]= isOfficial;
  args ["neLng"]= neLng;
  args ["neLat"]= neLat;
  args ["swLng"]= swLng;
  args ["swLat"]= swLat;
  args ["keyword"]= keyword;
  args ["sortBy"]= selected;
  args ["index"] = index;
  redirectTo("detail", args);

}

function loadMorePosts(){
  var param= $(".sortinput").data("param");

  var neLng= (param["neLng"]);
  var neLat= (param["neLat"]);
  var swLng= (param["swLng"]);
  var swLat= (param["swLat"]);
  var isOfficial= (param["isOfficial"]);
  var keyword= (param["keyword"]);
  var category= (param["category"]);
  var index= $(".sortinput").attr("data-index");
  var sort= (param["sort"]);

  makeAPICall(
    'POST', 'buzz', 'loadMorePosts',
    {"isOfficial":isOfficial, "neLng": neLng, "neLat":neLat, "swLng": swLng, "swLat":swLat, "keyword":keyword, "category":category,"index":index,"sort":sort},
    function(response){
      console.log (response);
      // set new index value
      var newIndex= parseInt(index)+10;
      console.log($('#sort').data('index'));
      $('#sort').attr('data-index', newIndex);
      console.log($('#sort').attr('data-index'));
      //console.log ("new index: "+ $(".sortinput").attr("data-index"));


      var json = $.parseJSON(response);
      var textToInsert = [];


      $(json.docs).each(function(i,data){

          console.log("extra item!");
          var title = data.title;
          var url= data.url;
          var name= data.name;
          var content= data.content;
          var imageUrl= data.imageUrl;
          var pubDate= data.pubDate;
          var startDate= data.startDate;
          var endDate= data.endDate;
          var locationName= data.locationName;
          var sourceType= data.sourceType;

          if (content!=""){
            content=$.trim(content);
            content=content.substring(0,100)+"...";
          }else{
            if (title.length > 150)
              title=title.substring(0,150)+"...";
          }



          //date conversion
          var d=new Date (pubDate);
          var m=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
          var D=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
          var year=d.getFullYear();
          var month=m[d.getMonth()];
          var date=d.getDate();
          var day=D[d.getDay()];
          var localDate=day+' '+month+' '+date+' '+year+' '+d.getHours()+':'+d.getMinutes()+':'+d.getSeconds();

          if (startDate!=""){
            var sd=new Date (startDate);
            //var sd_convert= D[sd.getDay()]+" "+m[sd.getMonth]+" "+sd.getYear+ " "+sd.getHours()+ ":"+sd.getMinutes();
          }
          if (endDate!=""){
            var ed=new Date (endDate);
            //var ed_convert= D[ed.getDay()]+" "+m[ed.getMonth]+" "+ed.getYear+ " "+ed.getHours()+ ":"+ed.getMinutes();
          }

          //dynammically append list item
          textToInsert[i++]  = '<li>';
          textToInsert[i++] = '<div class= "ribbon"><div class="r-triangle-top"></div><div class="r-triangle-bottom"></div><div class="rectangle">';
          textToInsert[i++] = localDate;
          textToInsert[i++] = '</div></div>';

          textToInsert[i++]  = '<table class="content" border="0"><tr><td class="imageCell">';
          if(imageUrl!=""){
            textToInsert[i++] = '<img class= "thumbnail postImage" src='+imageUrl+'></img>';
          }else{
            textToInsert[i++] = '<img class= "thumbnail" src="/modules/buzz/images/placeholder.png"/>';
          }
          textToInsert[i++] = '</td>';
          if (title!=content && content!="")
            textToInsert[i++] = '<td><a class="title" href='+url+'>'+title+'</a><div class="smallprint">'+content+'</div>';
          else
            textToInsert[i++] = '<td><a class="title" href='+url+'>'+title+'</a>';

          if(sourceType=="RSSEvents"){
            textToInsert[i++] = '<div class="date_text">Start Time: '+sd+'</div>';
            textToInsert[i++] = '<div class="date_text">End Time: '+ed+'</div>';
          }
          textToInsert[i++] = '</td></tr></table>';


          if(sourceType=="TwitterGeoSearch"||sourceType=="Twitter"){
            textToInsert[i++] = '<img class= "icon" src="/modules/buzz/images/icons/twitter_icon.png"/>';
          }else if (sourceType=="Facebook"){
            textToInsert[i++] = '<img class= "icon" src="/modules/buzz/images/icons/facebook-icon.png"/>';
          }else if (sourceType=="RSS"){
            textToInsert[i++] = '<img class= "icon" src="/modules/buzz/images/icons/feed-icon.png"/>';
          }else{
            textToInsert[i++] = '<img class= "icon" src="/modules/buzz/images/icons/event-icon.png"/>';
          }

          textToInsert[i++] = '<span class="smallprint authorField">Posted By: ';
          textToInsert[i++] = name+'@'+locationName;
          textToInsert[i++] = '</span></li>';
          $(".results").append(textToInsert.join(''));
          textToInsert = []
      });

      if (newIndex>=json.numFound){
        console.log("no more posts");
        $("#scrollText").text("No More Posts.");
      }


  });
}
