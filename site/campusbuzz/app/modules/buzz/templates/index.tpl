<div id="mapContainer">	

{include file="findInclude:common/templates/header.tpl"}


<div id = "togglebar">
	<div id="buzzbutton">
		<input id = "buzztoggle" class= "togglebutton" type="button" onclick="studentBuzzMode()"  value="Student Buzz" />
		<div id="buzztip"/>
	</div>
	<div id="newsbutton">
		<input  id = "newstoggle" class= "togglebutton" type="button" onclick="campusNewsMode()"   value="Campus News"} />
		<div id="newstip" />
	</div>
</div>

<div id="map_canvas" >
		<img onload= "studentBuzzMode()" src="/modules/buzz/images/loader_img.png"> </img>
</div>

<div id="searchbar">
	<form class="form-wrapper cf">
        <input type="text" placeholder="Search by keywords..." required>
        <button type="button" onclick="searchKeyword(this)">Search</button>
        <div id="searchButton" onclick="expandSearchBar(event)">
			<img src="/modules/buzz/images/search.png"> </img>
		</div>
    </form>  
</div>

<div id="gpsButton" onclick="toggleGPS(event)">
	<img src="/modules/buzz/images/gps.png"> </img>
</div>

{include file="findInclude:modules/buzz/templates/slidemenu.tpl"}

{include file="findInclude:modules/map/templates/fullscreenfooter.tpl" hideFooterLinks=true}

<div id="loading">
  <p><img src="/modules/buzz/images/ajax-loader.gif" /> Please Wait...</p>
</div>

</div>