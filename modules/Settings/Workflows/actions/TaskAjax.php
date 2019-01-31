<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Settings_Workflows_TaskAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('delete');
		$this->exposeMethod('changeStatus');
		$this->exposeMethod('changeStatusAllTasks');
		$this->exposeMethod('save');
	}

	public function delete(\App\Request $request)
	{
		if (!$request->isEmpty('task_id')) {
			$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($request->getInteger('task_id'));
			$taskRecordModel->delete();
			$response = new Vtiger_Response();
			$response->setResult(['ok']);
			$response->emit();
		}
	}

	public function changeStatus(\App\Request $request)
	{
		if (!$request->isEmpty('task_id')) {
			$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($request->getInteger('task_id'));
			$taskObject = $taskRecordModel->getTaskObject();
			if ($request->getBoolean('status')) {
				$taskObject->active = true;
			} else {
				$taskObject->active = false;
			}
			$taskRecordModel->save();
			$response = new Vtiger_Response();
			$response->setResult(['ok']);
			$response->emit();
		}
	}

	public function changeStatusAllTasks(\App\Request $request)
	{
		if (!$request->isEmpty('record')) {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($request->getInteger('record'));
			$taskList = $workflowModel->getTasks();
			foreach ($taskList as $task) {
				$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($task->getId());
				$taskObject = $taskRecordModel->getTaskObject();
				if ($request->getBoolean('status')) {
					$taskObject->active = true;
				} else {
					$taskObject->active = false;
				}
				$taskRecordModel->save();
			}
			$response = new Vtiger_Response();
			$response->setResult(['success' => true, 'count' => count($taskList)]);
			$response->emit();
		}
	}

	public function save(\App\Request $request)
	{
		$workflowId = $request->getInteger('for_workflow', '');
		if (!empty($workflowId)) {
			if (!$request->isEmpty('task_id')) {
				$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($request->getInteger('task_id'));
			} else {
				$workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
				$taskRecordModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($workflowModel, $request->getByType('taskType', 'Alnum'));
			}
			$taskObject = $taskRecordModel->getTaskObject();
			$taskObject->summary = $request->getByType('summary', 'Text');
			if ($request->getBoolean('active')) {
				$taskObject->active = true;
			} else {
				$taskObject->active = false;
			}
			if (!$request->isEmpty('check_select_date')) {
				$trigger = [
					'days' => ($request->getByType('select_date_direction', 'Standard') === 'after' ? 1 : -1) * $request->getInteger('select_date_days'),
					'field' => $request->getByType('select_date_field', 'Alnum'),
				];
				$taskObject->trigger = $trigger;
			} else {
				$taskObject->trigger = null;
			}
			$fieldNames = $taskObject->getFieldNames();
			foreach ($fieldNames as $fieldName) {
				if ($fieldName == 'field_value_mapping' || $fieldName == 'content') {
					$values = \App\Json::decode($request->getRaw($fieldName));
					if (is_array($values)) {
						foreach ($values as $index => $value) {
							$values[$index]['value'] = htmlspecialchars($value['value']);
						}

						$taskObject->$fieldName = \App\Json::encode($values);
					} else {
						$taskObject->$fieldName = $request->getRaw($fieldName);
					}
				} else {
					$taskObject->$fieldName = $request->get($fieldName);
				}
			}

			$taskType = get_class($taskObject);
			if ($taskType === 'VTCreateEntityTask' && $taskObject->field_value_mapping) {
				$relationModuleModel = Vtiger_Module_Model::getInstance($taskObject->entity_type);
				$ownerFieldModels = $relationModuleModel->getFieldsByType('owner');

				$fieldMapping = \App\Json::decode($taskObject->field_value_mapping);
				foreach ($fieldMapping as $key => $mappingInfo) {
					if (array_key_exists($mappingInfo['fieldname'], $ownerFieldModels)) {
						if ($mappingInfo['value'] == 'assigned_user_id') {
							$fieldMapping[$key]['valuetype'] = 'fieldname';
						} else {
							$userRecordModel = Users_Record_Model::getInstanceById($mappingInfo['value'], 'Users');
							$ownerName = $userRecordModel->get('user_name');

							if (!$ownerName) {
								$groupRecordModel = Settings_Groups_Record_Model::getInstance($mappingInfo['value']);
								$ownerName = $groupRecordModel->getName();
							}
							$fieldMapping[$key]['value'] = $ownerName;
						}
					}
				}
				$taskObject->field_value_mapping = \App\Json::encode($fieldMapping);
			}

			$taskRecordModel->save();
			$response = new Vtiger_Response();
			$response->setResult(['for_workflow' => $workflowId]);
			$response->emit();
		}
	}
}
