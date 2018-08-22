{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-CronTasks-ListViewHeader -->
<div class="">
	<div class='widget_header row '>
		<div class="col-md-6">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
		<div class="col-md-6 ml-2 ml-md-0">
			<div class="d-flex flex-wrap justify-content-md-end">
				<span class="mr-1">{\App\Language::translate('LBL_LAST_START',$QUALIFIED_MODULE)}:</span>
				<strong>{$LAST_CRON['laststart']}</strong>
			</div>
			<div class="d-flex flex-wrap justify-content-md-end">
				<span class="mr-1">{\App\Language::translate('LBL_TOTAL_LAST_DURATION',$QUALIFIED_MODULE)}:</span>
				<strong>
					{if $LAST_CRON['duration']==='running'}<i class="fa fa-spinner fa-spin"
															  title="{\App\Language::translate('LBL_IS_RUNNING',$QUALIFIED_MODULE)}"></i>
					{elseif $LAST_CRON['duration']==='timeout'}<i class="fa fa-exclamation-triangle text-danger"
																  title="{\App\Language::translate('LBL_HAD_TIMEOUT',$QUALIFIED_MODULE)}"></i>
					{else}{$LAST_CRON['duration']}
					{/if}
				</strong>
			</div>
		</div>
	</div>
	<div class="listViewActionsDiv row">
		<div class="{if !empty($SUPPORTED_MODULE_MODELS)}col-md-5{else}col-md-8{/if} btn-toolbar">
			{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
				{if $LINK->getLabel()}
					{assign var="LABEL" value=\App\Language::translate($LINK->getLabel(), $QUALIFIED_MODULE)}
				{/if}
				<button type="button" title="{if $LINK->getLabel()}{$LABEL}{/if}"
						class="btn{if $LINK->getClassName()} {$LINK->getClassName()}{else} btn-light{/if} {if $LINK->get('modalView')}showModal{/if}"
						{if $LINK->getUrl()}
							{if stripos($LINK->getUrl(), 'javascript:')===0} onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'
							{else} onclick='window.location.href = "{$LINK->getUrl()}"' {/if}
						{/if}
						{if $LINK->get('linkdata') neq '' && is_array($LINK->get('linkdata'))}
					{foreach from=$LINK->get('linkdata') key=NAME item=DATA}
						data-{$NAME}="{$DATA}"
					{/foreach}
						{/if}>
					{if $LINK->get('linkicon')}
						<span class="{$LINK->get('linkicon')}"></span>
					{/if}
					{if $LINK->getLabel() && $LINK->get('showLabel') eq 1}
						&nbsp;
						<strong>{$LABEL}</strong>
					{/if}
				</button>
			{/foreach}
		</div>
		{if !empty($SUPPORTED_MODULE_MODELS)}
			<div class="col-md-3 btn-toolbar marginLeftZero">
				<select class="select2 form-control" id="moduleFilter"
						data-placeholder="{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}"
						data-select="allowClear, true">
					<optgroup class="p-0">
						<option value="">{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}</option>
					</optgroup>
					{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
						<option {if $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if} value="{$TAB_ID}">
							{App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
						</option>
					{/foreach}
				</select>
			</div>
		{/if}
		<div class="col-md-4">
			{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="listViewContentDiv listViewPageDiv" id="listViewContents">
		<!-- /tpl-Settings-CronTasks-ListViewHeader -->
		{/strip}
