/************* Map Related methods **************/
var map;
var buzzPinsArray = [];
var newsPinsArray = [];
var mode=0; // StudentBuzz: 0, CampusNews: 1
// var overlay;
var pieData = []; // total: 17

pieOverlay.prototype = new google.maps.OverlayView(); 

function initializeMap(){
    
    

    // var myLatLng = new google.maps.LatLng(62.323907, -150.109291);
    var mapOptions = {
          center: new google.maps.LatLng(49.26646,-123.250551),
          // center: myLatLng,
          zoom: 13,
          panControl: true,
          zoomControl: true,
          mapTypeControl: true,
          mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.RIGHT_TOP
            },
          scaleControl: false,
          streetViewControl: false,
          overviewMapControl: false,
          mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map_canvas"),mapOptions);


      // overlay = new pieOverlay(bounds, map);

    //add marker to center
    var marker = new google.maps.Marker({
        position: map.getCenter(),
        map: map,
        title: 'Click to zoom'
    });
  
    // event listeners
    google.maps.event.addListener(map, 'dblclick', function(event) {
      // addMarker(event.latLng);

      //fill in pieData randomly
      for (var i=0;i<4; i++){

        pieData.push(Math.round(Math.random() * 20));
      }

      //test custom overlay
      var swBound = event.latLng;
      var neBound = new google.maps.LatLng(49.400471, -123.005608);
      var bounds = new google.maps.LatLngBounds(swBound, neBound);
      var overlay = new pieOverlay(pieData, bounds, map);

      //check which layer to push
      if (mode==0)
        buzzPinsArray.push(overlay);
      else
        newsPinsArray.push(overlay);
      
      //add click event for overlay.... 
      // google.maps.event.addListener(overlay, 'dblclick', function() {
      //   // map.setZoom(8);
      //   // map.setCenter(marker.getPosition());
      //   console.log ('click');
      // });

      pieData=[];

    });
    google.maps.event.addListener(marker, 'click', function() {
        // marker.setMap(null);
        // marker=null;
        clearOverlays(buzzPinsArray);
    });


    //draw piechart pin
    //drawPin(pieData);

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

  if (mode==0)
    buzzPinsArray.push(marker);
  else
    newsPinsArray.push(marker);
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
function deleteOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(null);
    }
    markersArray.length = 0;
  }
}


////// Show/hide slide menu

function expandSlideMenu(){

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

    var tip = document.getElementById("newstip");
    addClass(tip, "show");
    removeClass(document.getElementById("buzztip"), "show");
    //set mode
    mode=1;

    //show news overlays
    showOverlays(newsPinsArray);
    //hide buzz overlays
    clearOverlays(buzzPinsArray);
}

function studentBuzzMode(){
    var tip = document.getElementById("buzztip");
    addClass(tip, "show");
    removeClass(document.getElementById("newstip"), "show");

    //set mode
    mode=0;

    //show buzz overlays
    showOverlays(buzzPinsArray);
    //hide news overlays
    clearOverlays(newsPinsArray);
    
}

/////////// go to detail view

function goToDetailView(){

  redirectTo("detail");
}
