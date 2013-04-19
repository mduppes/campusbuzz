var buzz_category={
'Life': 1,
'Clubs':2,
'Health':3,
'Recreation':4
};

var news_category={
'News': 1,
'Career':2,
'Learning':3,
'Leisure':4
};
/************* Map Related vars **************/
//geolocation vars
var browserSupportFlag =  new Boolean();
var initialLocation;
var watchID;
var lastTrackedLocation="";
var locationPin;
var map;
// var buzzPinsArray = [];
// var newsPinsArray = [];
var buzzMarkers = [];
var newsMarkers = [];
var buzzMarkerCluster;
var newsMarkerCluster;
var searchTerm="";
// var buzzSearchMarkerCluster;
// var newsSearchMarkerCluster;
var mode=0; // Unofficial: 0, Official: 1
// var overlay;
var pieData = []; // total: 17

var campusCenter= new google.maps.LatLng(49.26646,-123.250551);
var searchRadius= 2000; //in metres

//filter category arrays
var buzzCategoryList= ["Life", "Clubs", "Health", "Recreation"];
var newsCategoryList= ["News", "Career", "Learning", "Leisure"];


pieOverlay.prototype = new google.maps.OverlayView();



function initializeMap(){
  //turn off gps tracking
  $("#gpsButton").removeClass("enable");
  navigator.geolocation.clearWatch(watchID);

  //set up ui for default buzz mode
  // studentBuzzMode();


    var mapOptions = {
          center: campusCenter,
          zoom: 13,
          panControl: false,
          zoomControl: true,
          zoomControlOptions: {
                position: google.maps.ControlPosition.RIGHT_BOTTOM
            },
          scaleControl: false,
          streetViewControl: false,
          mapTypeControl: false,
          overviewMapControl: false,
          mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map_canvas"),mapOptions);

    //delete overlays
    deleteOverlays(buzzMarkers);
    deleteOverlays(newsMarkers);

    // Try W3C Geolocation (Preferred)
  if(navigator.geolocation) {
    browserSupportFlag = true;
    navigator.geolocation.getCurrentPosition(function(position) {
      initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
      //TODO: send location to server
      console.log("location:"+initialLocation);
      // check if location is within campus area, yes -> center current loc, no -> set ubc centre
      //map.setCenter(initialLocation);
    }, function() {
      alert("error location!");
      //handleNoGeolocation(browserSupportFlag);
    });
  }
  // Browser doesn't support Geolocation
  else {
    browserSupportFlag = false;
    handleNoGeolocation(browserSupportFlag);
    alert("error location!");
  }

  function handleNoGeolocation(errorFlag) {
    if (errorFlag == true) {
      alert("Geolocation service failed.");
      initialLocation = campusCenter;
    } else {
      alert("Your browser doesn't support geolocation. We've placed you in UBC.");
      initialLocation = campusCenter;
    }
    map.setCenter(initialLocation);
  }


    //create bounding area circle
    var boundingCircleOptions = {
      strokeColor: "white",
      strokeOpacity: 0.5,
      strokeWeight: 2,
      fillColor: "#ccc",
      fillOpacity: 0.2,
      map: map,
      center: campusCenter,
      radius: searchRadius
    };
    mapCircle = new google.maps.Circle(boundingCircleOptions);

    //create marker clusterer obj
    buzzMarkerCluster = new MarkerClusterer(map, buzzMarkers);
    newsMarkerCluster = new MarkerClusterer(map, newsMarkers);

    // event listeners
    // google.maps.event.addListener(map, 'dblclick', function(event) {
    //   // addMarker(event.latLng);

    //   //fill in pieData randomly
    //   for (var i=0;i<4; i++){

    //     pieData.push(Math.round(Math.random() * 20));
    //   }

    //   //test custom overlay
    //   var swBound = event.latLng;
    //   var neBound = new google.maps.LatLng(49.400471, -123.005608);
    //   var bounds = new google.maps.LatLngBounds(swBound, neBound);
    //   var overlay = new pieOverlay(pieData, bounds, map);

    //   //check which layer to push
    //   // if (mode==0)
    //   //   buzzPinsArray.push(overlay);
    //   // else
    //   //   newsPinsArray.push(overlay);

    //   pieData=[];

    // });



}

function loadMapPins (){
  //clear all pins on map

  makeAPICall(
    'GET', 'buzz', 'getMapPins',
    {"isOfficial":mode, "lon": campusCenter.lng(), "lat":campusCenter.lat(), "distance": searchRadius},
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

          // assign item's category as first matching category in cat_list
          for(var name in selected_cat_list){
              if (data.category.indexOf(name) != -1)
              {
                // somewhat inefficient hack to get around all categories getting the default Life category, since this was assigned first
                category=selected_cat_list[name];
              }
          }

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
  });

}


function searchKeyword (that){
  $("#loading").show();
  var searchString= $(that).prev().val();
  console.log ("search: "+searchString);

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

  console.log ("user search loc: "+ longitude + ", "+latitude)

  makeAPICall(
    'POST', 'buzz', 'searchKeyword',
    {"isOfficial":mode, "keyword": searchString, "lon": campusCenter.lng(), "lat":campusCenter.lat(), "distance": searchRadius, "userLng": longitude, "userLat": latitude},
    function(response){
      console.log (response);

      //iterate and plot pins
      var json = $.parseJSON(response);

      if (json["numFound"]==0){

        $(that).prev().val("").attr('placeholder', "No Results, search again.");
        $("#loading").hide();
      }else{

        // $(that).prev().val("").attr('placeholder', json["numFound"]+" results.");

        initializeMap();

        $(json.docs).each(function(i,data){

            var locArray = data.locationGeo.split(',');
            var loc= new google.maps.LatLng(locArray[0],locArray[1]);
            var category;
            console.log ("search data's category; "+data.category);


          var selected_cat_list;
          if (mode==0){
            selected_cat_list= buzz_category;
          }else{
            selected_cat_list= news_category;
          }

          // assign item's category as first matching category in cat_list
          for(var name in selected_cat_list){
              if (data.category.indexOf(name) != -1)
              {
                category=selected_cat_list[name];
                break;
              }
          }

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
      }
    });

}

function pieOverlay (pieData, bounds, map){


    //initialize properties
    this.bounds_= bounds;
    this.map_= map;
    this.pieData_= pieData;
    // We define a property to hold the image's
    // div. We'll actually create this div
    // upon receipt of the add() method so we'll
    // leave it null for now.
    this.div_ = null;

    // Explicitly call setMap() on this overlay
    this.setMap(map);

}


pieOverlay.prototype.onAdd = function() {

  // Note: an overlay's receipt of onAdd() indicates that
  // the map's panes are now available for attaching
  // the overlay to the map via the DOM.

  // Create the DIV and set some basic attributes.
  var div = document.createElement('div');
  div.style.border = "none";
  div.style.borderWidth = "0px";
  div.style.position = "absolute";

  // Create an IMG element and attach it to the DIV.
  var  contain= document.createElement("div");
  // contain.style.width = "100%";
  // contain.style.height = "100%";

  drawPin(this.pieData_,contain);



  div.appendChild(contain);

  // Set the overlay's div_ property to this DIV
  this.div_ = div;

  // We add an overlay to a map via one of the map's panes.
  // We'll add this overlay to the overlayImage pane.
  var panes = this.getPanes();
  panes.overlayMouseTarget.appendChild(div);

  //attach event listener

  // set this as locally scoped var so event does not get confused
  var me = this;

  // Add a listener - we'll accept clicks anywhere on this div, but you may want
  // to validate the click i.e. verify it occurred in some portion of your overlay.
  google.maps.event.addDomListener(div, 'click', function() {
    google.maps.event.trigger(me, 'click');
    console.log ("expand bubbles!");
    //expand the bubbles

  });
}

pieOverlay.prototype.draw = function() {
  // Size and position the overlay. We use a southwest and northeast
  // position of the overlay to peg it to the correct position and size.
  // We need to retrieve the projection from this overlay to do this.
  var overlayProjection = this.getProjection();

  // Retrieve the southwest and northeast coordinates of this overlay
  // in latlngs and convert them to pixels coordinates.
  // We'll use these coordinates to resize the DIV.
  var sw = overlayProjection.fromLatLngToDivPixel(this.bounds_.getSouthWest());
  var ne = overlayProjection.fromLatLngToDivPixel(this.bounds_.getNorthEast());

  // Resize the image's DIV to fit the indicated dimensions.
  var div = this.div_;
  div.style.left = sw.x + 'px';
  div.style.top = sw.y + 'px';
  // div.style.width = (ne.x - sw.x) + 'px';
  // div.style.height = (sw.y - ne.y) + 'px';
}

pieOverlay.prototype.onRemove = function() {
  this.div_.parentNode.removeChild(this.div_);
  this.div_ = null;
}




function drawPin (pieData, container){
    //total determine size of circle
    //find length of array elements non-zero -> determine slices
    //calculate the proportions for each slice
    //append all to a container -> attach to map

    // order: studentlife, club, health, leisure
    var total= pieData[0]+pieData[1]+pieData[2]+pieData[3];
    console.log ("total: "+total);
    var proportionArr = [];
    var diameter=20+total*0.4;
    var radius=diameter/2;
    var startDeg=0;
    var degree;

    for (var val in pieData){
        //calulate proportions
        var deg= (pieData[val]/total)*360;
        proportionArr.push(deg);
    }

    //draw container pie and fill background
    // var container = document.createElement('div');
    container.setAttribute('class', 'pieContainer');
    container.style.height = diameter+'px';
    container.style.width = diameter+'px';

    var background = document.createElement('div');
    background.setAttribute('class', 'pieBackground');
    background.style.height = diameter+'px';
    background.style.width = diameter+'px';
    background.style.borderRadius= radius+'px';

    container.appendChild(background);


    //loop thru proportionArr to draw slices
    for (var i=0;i<proportionArr.length; i++)
    {
        degree= proportionArr[i];
        console.log ("degree "+i+": "+degree);

        //if slice is over 50%, draw a 180 pie
        if (degree>180){
            var slice = document.createElement('div');
            // slice.setAttribute('class', 'hold');
            slice.style.position= 'absolute';
            slice.style.height = diameter+'px';
            slice.style.width = diameter+'px';
            slice.style.borderRadius= radius+'px';
            slice.style.clip= "rect(0px,"+diameter+"px, "+ diameter+"px, "+ radius+"px)";

            var pie = document.createElement('div');
            pie.style.position= 'absolute';
            pie.style.clip= "rect(0px, "+radius+"px, "+diameter+"px, 0px)";
            pie.style.height = diameter+'px';
            pie.style.width = diameter+'px';
            pie.style.borderRadius= radius+'px';

            slice.appendChild(pie);
            container.appendChild(slice);


            switch (i)
            {
                case 0:
                    addClass (pie, "slice_life");
                    break;
                case 1:
                    addClass (pie, "slice_health");
                    break;
                case 2:
                    addClass (pie, "slice_club");
                    break;
                case 3:
                    addClass (pie, "slice_leisure");
                    break;
            }

            //rotate slice (starting pos)
            slice.style.webkitTransform = "rotate("+startDeg+"deg)";
            startDeg+=180;
            //rotate pie (degree proportion)
            // pie.style.transform = "rotate("+proportionArr[i]+"deg)"
            pie.style.webkitTransform = "rotate("+180+"deg)";
            degree-=180;
            slice.appendChild(pie);
            container.appendChild(slice);
        }


        var slice = document.createElement('div');
        // slice.setAttribute('class', 'hold');
        slice.style.position= 'absolute';
        slice.style.height = diameter+'px';
        slice.style.width = diameter+'px';
        slice.style.borderRadius= radius+'px';
        slice.style.clip= "rect(0px,"+diameter+"px, "+ diameter+"px, "+ radius+"px)";

        var pie = document.createElement('div');
        pie.style.position= 'absolute';
        pie.style.clip= "rect(0px, "+radius+"px, "+diameter+"px, 0px)";
        pie.style.height = diameter+'px';
        pie.style.width = diameter+'px';
        pie.style.borderRadius= radius+'px';


        slice.appendChild(pie);
        container.appendChild(slice);


        switch (i)
        {
            case 0:
                addClass (pie, "slice_life");
                break;
            case 1:
                addClass (pie, "slice_health");
                break;
            case 2:
                addClass (pie, "slice_club");
                break;
            case 3:
                addClass (pie, "slice_leisure");
                break;
            }
        //rotate slice (starting pos)
        slice.style.webkitTransform = "rotate("+startDeg+"deg)";
        startDeg+=degree;

        //rotate pie (degree proportion)
        //pie.style.transform = "rotate("+proportionArr[i]+"deg)"
        pie.style.webkitTransform = "rotate("+degree+"deg)";
        slice.appendChild(pie);
        container.appendChild(slice);
    }

    //display total # tag, place at center of container
    var num = document.createElement('div');
    num.innerHTML = total;
    num.setAttribute("class", "numText");
    num.style.padding= radius/2+"px";
    num.style.fontSize= radius*0.7;
    container.appendChild(num);


    container.style.opacity= 0.8;
    // document.body.appendChild(container);
}

function addMarker(location) {
  marker = new google.maps.Marker({
    position: location,
    map: map,
    draggable:true,
    animation: google.maps.Animation.BOUNCE
  });

  // if (mode==0)
  //   buzzPinsArray.push(marker);
  // else
  //   newsPinsArray.push(marker);
}

// Removes the overlays from the map, but keeps them in the array
function clearOverlays(pinArray) {
  if (pinArray) {
    for (i in pinArray) {
      pinArray[i].setMap(null);
    }
  }
}

// Shows any overlays currently in the array
function showOverlays(pinArray) {
  if (pinArray) {
    for (i in pinArray) {
      pinArray[i].setMap(map);
    }
  }
}

// Deletes all markers in the array by removing references to them
function deleteOverlays(markersArray) {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(null);
    }
    markersArray.length = 0;
  }
}


////// Show/hide slide menu

function expandSlideMenu(event){


    var menu = document.getElementById("slideout_inner");
    var button = document.getElementById("pulltab");


    var className= button.className;

    if (className.indexOf("expand")>-1){
        removeClass(menu, "expand");
        removeClass(button, "expand");
    }else{
        addClass(menu, "expand");
        addClass(button, "expand");
    }

}

////// switching modes (student buzz OR campus news) -cng

function campusNewsMode(){
    $("#loading").show();
    hideSearchbar();
    $("#gpsButton").removeClass("enable");
    //button state
    var button = document.getElementById("newstoggle");
    addClass(button, "pressed");
    removeClass(document.getElementById("buzztoggle"), "pressed");

    //button tip
    var tip = document.getElementById("newstip");
    addClass(tip, "show");
    removeClass(document.getElementById("buzztip"), "show");

    //change slidemenu color
    var menu = document.getElementById("slideout_inner");
    var tab = document.getElementById("pulltab");
    removeClass(menu, "buzzMode");
    removeClass(tab, "buzzMode");
    addClass(tab, "newsMode");
    addClass(menu, "newsMode");

    //hide buzz filter buttons
    var list = document.getElementsByClassName("buzzCategory");
    for (var i = 0; i < list.length; i++) {
        // list[i] is a node with the desired class name
        addClass(list[i], "hidden");
    }
    //show news filter buttons
    var list2 = document.getElementsByClassName("newsCategory");
    for (var i = 0; i < list2.length; i++) {
        // list[i] is a node with the desired class name
        removeClass(list2[i], "hidden");
    }

    //set mode
    mode=1;

    //show news overlays
    // showOverlays(newsPinsArray);
    //hide buzz overlays
    // clearOverlays(buzzPinsArray);
    $(".newsCategory").removeClass("filterOut");
    initializeMap();
    //draw piechart pins on map
    loadMapPins();
}

function studentBuzzMode(){
    $("#loading").show();

    //clear all filters

    //close search bar
    hideSearchbar();
    $("#gpsButton").removeClass("enable");

    //button state
    var button = document.getElementById("buzztoggle");
    addClass(button, "pressed");
    removeClass(document.getElementById("newstoggle"), "pressed");

    //button tip
    var tip = document.getElementById("buzztip");
    addClass(tip, "show");
    removeClass(document.getElementById("newstip"), "show");

    //change slidemenu color
    var menu = document.getElementById("slideout_inner");
    removeClass(menu, "newsMode");
    addClass(menu, "buzzMode");
    var tab = document.getElementById("pulltab");
    removeClass(tab, "newsMode");
    addClass(tab, "buzzMode");

    //hide news filter buttons
    var list = document.getElementsByClassName("newsCategory");
    for (var i = 0; i < list.length; i++) {
        // list[i] is a node with the desired class name
        addClass(list[i], "hidden");
    }
    //show buzz filter buttons
    var list2 = document.getElementsByClassName("buzzCategory");
    for (var i = 0; i < list2.length; i++) {
        // list[i] is a node with the desired class name
        removeClass(list2[i], "hidden");
    }

    //set mode
    mode=0;

    //show buzz overlays
    // showOverlays(buzzPinsArray);
    //hide news overlays
    // clearOverlays(newsPinsArray);
    $(".buzzCategory").removeClass("filterOut");
    initializeMap();
    //draw piechart pins on map
    loadMapPins();
}

////// Show/hide search bar

function hideSearchbar(){
  var search = document.getElementById("searchbar");
  removeClass(search, "expand");

  $(search).find("input").attr("placeholder", "Search by keywords...");
}

function expandSearchBar(event){

    var search = document.getElementById("searchbar");

    var className= search.className;



    if (className.indexOf("expand")>-1){
        removeClass(search, "expand");
    }else{
        addClass(search, "expand");
    }

}

/////////// go to detail view

function goToDetailView(){

  redirectTo("detail");

  //make API call to query solr for feed details

}





