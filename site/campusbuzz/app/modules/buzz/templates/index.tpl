{include file="findInclude:common/templates/header.tpl"}

{include file="findInclude:modules/buzz/templates/togglebar.tpl"}

	
<div id="map_canvas" >
<img onload= "initializeMap()" src="/modules/buzz/images/icons/leisure-button.png"> </img>
</div>

<!-- <div id="pieContainer">
     <div class="pieBackground"></div>
     <div id="pieSlice1" class="hold"><div class="pie"></div></div>
</div> -->

{include file="findInclude:modules/buzz/templates/slidemenu.tpl"}

{include file="findInclude:modules/map/templates/fullscreenfooter.tpl" hideFooterLinks=true}
