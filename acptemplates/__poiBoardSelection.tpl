{if !POI_SUPPORT_THREAD_SINGLE_BOARD && $supportThreadBoardNodeList|isset}
	<dl{if $errorField == 'supportThreadBoardID'} class="formError"{/if}>
		<dt><label for="supportThreadBoardID">{lang}poi.poi.supportThread.boardID{/lang}</label></dt>
		<dd>
			<select id="supportThreadBoardID" name="supportThreadBoardID">
				<option value="0">{lang}wcf.global.noSelection{/lang}</option>
				{foreach from=$supportThreadBoardNodeList item=boardNode}
					{if !$boardNode->getBoard()->isExternalLink() && (!$boardNode->getBoard()->isCategory() || $boardNode->hasChildren())}
						<option value="{@$boardNode->getBoard()->boardID}"{if $boardNode->getBoard()->isCategory()} disabled="disabled"{/if}{if $boardNode->getBoard()->boardID == $supportThreadBoardID} selected="selected"{/if}>{if $boardNode->getDepth() > 1}{@'&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:-1+$boardNode->getDepth()}{/if}{$boardNode->getBoard()->getTitle()}</option>
					{/if}
				{/foreach}
			</select>
			<small>{lang}poi.poi.supportThread.boardID.description{/lang}</small>
		</dd>
	</dl>
{/if}
