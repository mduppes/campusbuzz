

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
              cat_list[name]= selected_cat_list[name];
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

function categoryCloudClickHandler (self){
	// query item according to filter selected

  //get category type! 
	var className= self.className;
  console.log ("my click class: "+className);

  var category="";

  if (mode==0){
    //check category for Buzz mode
    if(className.indexOf("rightBottom") != -1){
              category="Life";
    }else if (className.indexOf("leftBottom") != -1){
              category="Club";
    }else if (className.indexOf("rightTop") != -1){
              category="Health";
    }else if (className.indexOf("leftTop") != -1){
              category="Leisure";
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


  var args= Array();
  args["category"]= category;
  args["isOfficial"]= mode;
  args ["neLng"]= bounds.getNorthEast().lng().toFixed(8); //round up to 8th decimal to fix bounding box inaccuracy issue
  args ["neLat"]= bounds.getNorthEast().lat().toFixed(8);
  args ["swLng"]= bounds.getSouthWest().lng().toFixed(8);
  args ["swLat"]= bounds.getSouthWest().lat().toFixed(8);
  args ["keyword"]= $("#searchbar").find("input").val();
  args ["sortBy"]= "time";
  redirectTo("detail", args);
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

  var args= Array();
  args["category"]= category;
  args["isOfficial"]= isOfficial;
  args ["neLng"]= neLng;
  args ["neLat"]= neLat;
  args ["swLng"]= swLng;
  args ["swLat"]= swLat;
  args ["keyword"]= keyword;
  args ["sortBy"]= selected;
  redirectTo("detail", args);

}


function toggleGPS(event){

  var gps = $("#gpsButton");

  if($("#gpsButton").hasClass("enable")){
      $("#gpsButton").removeClass("enable");
      //turn off gps tracking
      navigator.geolocation.clearWatch(watchID);
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
      }, geo_error, {timeout:10000});
      
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
  // do_something(position.coords.latitude, position.coords.longitude);
  console.log ("position: "+position.coords.latitude, position.coords.longitude);
  var userLatLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

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
  alert("Sorry, no position available. Try again later.");
  $("#gpsButton").removeClass("enable");
  $("#loading").hide();
}

function clearLocationPin(){
  if (locationPin)
    locationPin.setMap(null);
}



















