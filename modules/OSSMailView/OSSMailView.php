<?php

/**
 * OSSMailView CRMEntity class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView extends CRMEntity
{

	public $table_name = 'vtiger_ossmailview';
	public $table_index = 'ossmailviewid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_ossmailviewcf', 'ossmailviewid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_ossmailview', 'vtiger_ossmailviewcf'];
	public $related_tables = ['vtiger_ossmailviewcf' => ['ossmailviewid', 'vtiger_ossmailview', 'ossmailviewid']];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossmailview' => 'ossmailviewid',
		'vtiger_ossmailviewcf' => 'ossmailviewid'];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'number' => ['ossmailview' => 'ossmailview_no'],
		'From' => ['ossmailview' => 'from_email'],
		'Subject' => ['ossmailview' => 'subject'],
		'To' => ['ossmailview' => 'to_email'],
		'SendType' => ['ossmailview' => 'ossmailview_sendtype'],
		'Assigned To' => ['ossmailview' => 'assigned_user_id']
	];
	public $list_fields_name = [
		/* Format: Field Label => fieldname */
		'number' => 'ossmailview_no',
		'From' => 'from_email',
		'Subject' => 'subject',
		'To' => 'to_email',
		'SendType' => 'ossmailview_sendtype',
		'Assigned To' => 'assigned_user_id'
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['ossmailview_no', 'from_email', 'subject', 'to_email', 'ossmailview_sendtype', 'assigned_user_id'];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'number' => ['ossmailview' => 'ossmailview_no'],
		'From' => ['ossmailview' => 'from_email'],
		'Subject' => ['ossmailview' => 'subject'],
		'To' => ['ossmailview' => 'to_email'],
		'SendType' => ['ossmailview' => 'ossmailview_sendtype'],
		'Assigned To' => ['ossmailview' => 'assigned_user_id']
	];
	public $search_fields_name = [
		/* Format: Field Label => fieldname */
		'number' => 'ossmailview_no',
		'From' => 'from_email',
		'Subject' => 'subject',
		'To' => 'to_email',
		'SendType' => 'ossmailview_sendtype',
		'Assigned To' => 'assigned_user_id'
	];
	// For Popup window record selection
	public $popup_fields = ['from', 'subject', 'ossmailview_sendtype'];
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Required Information for enabling Import feature
	public $required_fields = ['subject' => 1];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['subject', 'from'];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	public $unit_price;

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	public function getListViewSecurityParameter($module)
	{
		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		$sec_query = '';
		$tabid = \App\Module::getModuleId($module);

		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabid] == 3) {

			$sec_query .= " && (vtiger_crmentity.smownerid in($current_user->id) || vtiger_crmentity.smownerid IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'
					)
					OR vtiger_crmentity.smownerid IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid=" . $current_user->id . " && tabid=" . $tabid . "
					)
					OR
						(";

			// Build the query based on the group association of current user.
			if (sizeof($current_user_groups) > 0) {
				$sec_query .= " vtiger_groups.groupid IN (" . implode(",", $current_user_groups) . ") || ";
			}
			$sec_query .= " vtiger_groups.groupid IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=" . $current_user->id . " and tabid=" . $tabid . "
						)";
			$sec_query .= ")
				)";
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	public function createExportQuery($where)
	{
		$current_user = vglobal('current_user');

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery('OSSMailView', "detail_view");

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, vtiger_users.user_name AS user_name
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if (!empty($this->customFieldTable)) {
			$query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";

		$where_auto = " vtiger_crmentity.deleted=0";

		if ($where != '')
			$query .= " WHERE ($where) && $where_auto";
		else
			$query .= " WHERE $where_auto";

		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		// Security Check for Field Access
		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[\App\Module::getModuleId('OSSMailView')] == 3) {
			//Added security check to get the permitted records only
			$query = $query . " " . getListViewSecurityParameter($thismodule);
		}
		return $query;
	}

	/**
	 * Transform the value while exporting
	 */
	public function transformExportValue($key, $value)
	{
		if ($key == 'owner')
			return \App\Fields\Owner::getLabel($value);
		return parent::transformExportValue($key, $value);
	}

	/**
	 * Function which will give the basic query to find duplicates
	 */
	public function getDuplicatesQuery($module, $table_cols, $field_values, $ui_type_arr, $select_cols = '')
	{
		$select_clause = sprintf('SELECT %s.%s AS recordid, vtiger_users_last_import.deleted, %s', $this->table_name, $this->table_index, $table_cols);

		// Select Custom Field Table Columns if present
		if (isset($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if (isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}
		$from_clause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$where_clause = "	WHERE vtiger_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);

		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
			// Consider custom table join as well.
			if (isset($this->customFieldTable)) {
				$sub_query .= " INNER JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}


		$query = $select_clause . $from_clause .
			" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
			" INNER JOIN (" . $sub_query . ") AS temp ON " . get_on_clause($field_values) .
			$where_clause .
			" ORDER BY $table_cols," . $this->table_name . "." . $this->table_index . " ASC";

		return $query;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param string $moduleName Module name
	 * @param string $eventType Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		require_once('include/utils/utils.php');
		$dbCommand = App\Db::getInstance()->createCommand();
		if ($eventType === 'module.postinstall') {
			\App\Fields\RecordNumber::setNumber($moduleName, 'M_', 1);
			$displayLabel = 'OSSMailView';
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => $displayLabel])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'email_list', 'parameter' => 'widget_limit', 'value' => '10'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'email_list', 'parameter' => 'target', 'value' => '_blank'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'email_list', 'parameter' => 'permissions', 'value' => 'vtiger'])->execute();
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));
			$registerLink = true;
			$module = vtlib\Module::getInstance($moduleName);
			$userName = \App\User::getCurrentUserModel()->getDetail('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_InstallModule', 'info' => $moduleName . ' ' . $module->version, 'user' => $userName])->execute();
		} else if ($eventType === 'module.disabled') {
			$registerLink = false;
		} else if ($eventType === 'module.enabled') {
			$registerLink = true;
		} else if ($eventType === 'module.postupdate') {
			$module = vtlib\Module::getInstance($moduleName);
			$userName = \App\User::getCurrentUserModel()->getDetail('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_UpdateModule', 'info' => $moduleName . ' ' . $module->version, 'user' => $userName, 'start_time' => date('Y-m-d H:i:s')])->execute();
		}
		$displayLabel = 'Mail View';
		if ($registerLink) {
			Settings_Vtiger_Module_Model::addSettingsField('LBL_MAIL', [
				'name' => $displayLabel,
				'iconpath' => 'adminIcon-oss_mailview',
				'description' => 'LBL_MAIL_VIEW_DESCRIPTION',
				'linkto' => 'index.php?module=OSSMailView&parent=Settings&view=index'
			]);
		} else {
			$dbCommand->delete('vtiger_settings_field', ['name' => $displayLabel])->execute();
		}
	}
}
