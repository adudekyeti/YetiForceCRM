{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-SharedOwner -->
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{if $FIELD_MODEL->getUIType() eq '120'}
		{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance($MODULE)->getAccessibleUsers('',$FIELD_MODEL->getFieldDataType())}
		{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance($MODULE)->getAccessibleGroups('',$FIELD_MODEL->getFieldDataType())}
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->getName()}
		{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
		{assign var=SHOW_FAVORITE_OWNERS value=AppConfig::module('Users','FAVORITE_OWNERS')}
		{if $FIELD_VALUE neq '' }
			{assign var=FIELD_VALUE value=vtlib\Functions::getArrayFromValue($FIELD_VALUE)}
			{assign var=NOT_DISPLAY_LIST value=array_diff_key(array_flip($FIELD_VALUE), $ALL_ACTIVEUSER_LIST, $ALL_ACTIVEGROUP_LIST)}
		{else}
			{assign var=NOT_DISPLAY_LIST value=[]}
			{assign var=FIELD_VALUE value=[]}
		{/if}
		{function OPTGRUOP BLOCK_NAME='' OWNERS=[] ACTIVE='inactive'}
			{if $OWNERS}
				<optgroup label="{\App\Language::translate($BLOCK_NAME)}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$OWNERS}
						<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}"
								{foreach item=ELEMENT from=$FIELD_VALUE}
									{if $ELEMENT eq $OWNER_ID } selected {/if}
								{/foreach}
								data-userId="{$CURRENT_USER_ID}"
								{if $SHOW_FAVORITE_OWNERS}
									data-url="" data-state="{$ACTIVE}" data-icon-active="fas fa-star" data-icon-inactive="far fa-star"
								{/if}>
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
			{/if}
		{/function}
		<div>
			<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value=""/>
			<select class="select2 form-control {if !empty($NOT_DISPLAY_LIST)}hideSelected{/if} {$ASSIGNED_USER_ID}"
					title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
					data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
					data-name="{$ASSIGNED_USER_ID}" name="{$ASSIGNED_USER_ID}[]" data-fieldinfo='{$FIELD_INFO}'
					multiple {if !empty($SPECIAL_VALIDATOR)} data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if}
					{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
					data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName={$ASSIGNED_USER_ID}" data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
					{elseif AppConfig::module('Users','FAVORITE_OWNERS')}
						data-show-additional-icons="true"
					{/if}>
				{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
					{foreach item=USER from=$FIELD_VALUE}
						{assign var=OWNER_NAME value=\App\Fields\Owner::getLabel($USER)}
						<option value="{$USER}" data-picklistvalue="{$OWNER_NAME}" selected="selected">
							{\App\Purifier::encodeHtml($OWNER_NAME)}
						</option>
					{/foreach}
				{else}
					{if $SHOW_FAVORITE_OWNERS}
						{assign var=FAVORITE_OWNERS value=\App\Fields\Owner::getFavorites(\App\Module::getModuleId($MODULE_NAME), $CURRENT_USER_ID)}
						{if $FAVORITE_OWNERS}
							{assign var=FAVORITE_OWNERS value=array_intersect_key($ALL_ACTIVEUSER_LIST, $FAVORITE_OWNERS) + array_intersect_key($ALL_ACTIVEGROUP_LIST, $FAVORITE_OWNERS)}
							{assign var=ALL_ACTIVEUSER_LIST value=array_diff_key($ALL_ACTIVEUSER_LIST, $FAVORITE_OWNERS)}
							{assign var=ALL_ACTIVEGROUP_LIST value=array_diff_key($ALL_ACTIVEGROUP_LIST, $FAVORITE_OWNERS)}
							{OPTGRUOP BLOCK_NAME='LBL_FAVORITE_OWNERS' OWNERS=$FAVORITE_OWNERS ACTIVE='active'}
						{/if}
					{/if}
					{OPTGRUOP BLOCK_NAME='LBL_USERS' OWNERS=$ALL_ACTIVEUSER_LIST}
					{OPTGRUOP BLOCK_NAME='LBL_GROUPS' OWNERS=$ALL_ACTIVEGROUP_LIST}
					{if !empty($NOT_DISPLAY_LIST)}
						{foreach from=$NOT_DISPLAY_LIST key=OWNER_ID item=OWNER_NAME}
							<option value="{$OWNER_ID}"
									{if in_array(\App\Purifier::encodeHtml($OWNER_NAME), $FIELD_VALUE)}selected{/if}
									disabled class="d-none">{\App\Purifier::encodeHtml($OWNER_NAME)}</option>
						{/foreach}
					{/if}
				{/if}
			</select>
		</div>
	{/if}
	<!-- /tpl-Base-Edit-Field-SharedOwner -->
{/strip}
