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
  console.log (bounds.getNorthEast().lng());
  console.log (bounds.getNorthEast().lat());
  console.log (bounds.getSouthWest().lng());
  console.log (bounds.getSouthWest().lat());

  //make API call for DetailView
  //loadDetailView(category, bounds);

	// goToDetailView();
	// loadDetailView();


  var args= Array();
  args["category"]= category;
  args["isOfficial"]= mode;
  args ["neLng"]= bounds.getNorthEast().lng();
  args ["neLat"]= bounds.getNorthEast().lat();
  args ["swLng"]= bounds.getSouthWest().lng();
  args ["swLat"]= bounds.getSouthWest().lat();
  redirectTo("detail", args);
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

function loadDetailView (category, bounds){
  //clear all pins on map

  makeAPICall(
    'POST', 'buzz', 'getPosts', 
    {"isOfficial":mode, "neLng": bounds.getNorthEast().lng(),"neLat": bounds.getNorthEast().lat(),"swLng": bounds.getSouthWest().lng(), "swLat":bounds.getSouthWest().lat(), "category": category},
    function(response){
      
      //iterate and get data
      // var json = $.parseJSON(response);
      // $(json.docs).each(function(i,data){
        
      //     console.log ("data's category; ");
           
      // });
    var args= new Array;
    args["response"]= response;
    console.log(args["response"]);
    redirectTo("detail", args);

      
  });
}


