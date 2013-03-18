function categoryCloudClickHandler (cid){
	//query item according to filter selected
	// var id= obj.id;
   // console.log ("my click id: "+cid);
   // var response = ;
   // var args=[response];
   
	//goToDetailView();
	loadDetailView();
}

function displayClouds(that){

  //change visibility of clouds
  addClass(that.cloud_, "show");
  removeClass(that.cloud_, "hidden");

  
}

//filter categories from slide out menu
function filterCategory(obj){

  var className=obj.className;
  var id= obj.id;
  console.log ("id: "+id);
    if (className.indexOf("filterOut")>-1){
        removeClass(obj, "filterOut");
        //add items to map

    }else{
        addClass(obj, "filterOut");
        //remove  items from map

    }

}

function loadDetailView (){
  //clear all pins on map

  makeAPICall(
    'GET', 'buzz', 'getPosts', 
    {"isOfficial":mode, "lon": campusCenter.lng(), "lat":campusCenter.lat(), "distance": searchRadius},
    function(response){
      
      //iterate and plot pins
      var json = $.parseJSON(response);
      $(json.docs).each(function(i,data){
        
          console.log ("data's category; ");
           
      });

      redirectTo("detail", json.docs);
  });
}


