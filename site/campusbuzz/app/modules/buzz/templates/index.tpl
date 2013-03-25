{include file="findInclude:common/templates/header.tpl"}

{include file="findInclude:modules/buzz/templates/togglebar.tpl"}

	
<div id="map_canvas" >
<img onload= "studentBuzzMode()" src="/modules/buzz/images/loader_img.png"> </img>
</div>

<div id="searchbar">
	<form class="form-wrapper cf">
        <input type="text" placeholder="Search by keywords..." required>
        <button type="button" onclick="searchKeyword(this)">Search</button>
        <div id="searchButton" onclick="expandSearchBar(event)">
			<!-- <input type="button" onclick="expandSearchBar()" value={"SEARCH_TITLE"|getLocalizedString} /> -->
			<img src="/modules/buzz/images/search.png"> </img>
		</div>
    </form>  
</div>

<div id="gpsButton" >
	<img src="/modules/buzz/images/gps.png"> </img>
</div>

{include file="findInclude:modules/buzz/templates/slidemenu.tpl"}

{include file="findInclude:modules/map/templates/fullscreenfooter.tpl" hideFooterLinks=true}
