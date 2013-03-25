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
    filterOutCategory(newsCategoryList);
  }
}

function filterOutCategory (categoryList){

  makeAPICall(
    'POST', 'buzz', 'filterOut', 
    {"isOfficial":mode,  "categoryList": categoryList, "lon": campusCenter.lng(), "lat":campusCenter.lat(), "distance": searchRadius},
    function(response){
      
      console.log (response);
    });
}



