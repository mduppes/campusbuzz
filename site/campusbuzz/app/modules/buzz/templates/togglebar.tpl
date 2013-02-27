<div id = "togglebar">
	<div id="buzzbutton">
		<input type="button" onclick="studentBuzzMode()"  ontouchstart="this.className='pressedaction'" ontouchend="this.className=''" value={"TOGGLE_BUTTON_BUZZ"|getLocalizedString} />
		<div id="buzztip"/>
	</div>
	<div id="newsbutton">
		<input type="button" onclick="campusNewsMode()"  ontouchstart="this.className='pressedaction'" ontouchend="this.className=''" value={"TOGGLE_BUTTON_NEWS"|getLocalizedString} />
		<div id="newstip" />
	</div>
</div>
