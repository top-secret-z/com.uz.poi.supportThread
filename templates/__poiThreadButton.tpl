{if $poi->supportThreadID && $supportThread->canRead()}
	<div class="box boxInfo">
		<div class="boxContent">
			<div class="formSubmit">
				<a href="{link application='wbb' controller='Thread' object=$supportThread}{/link}" class="button buttonPrimary">{lang}poi.poi.button.jumpToSupportThread{/lang}<br /><small>{lang}poi.poi.supportThread.info{/lang}</small></a>
			</div>
		</div>
	</div>
{/if}
