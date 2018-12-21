<?php

namespace App\SystemWarnings\Security;

/**
 * Security conf system warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class SecurityConf extends \App\SystemWarnings\Template
{
	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $title = 'LBL_SECURITY_CONF';
	/**
	 * {@inheritdoc}
	 *
	 * @var int
	 */
	protected $priority = 7;

	/**
	 * Checking whether there is a security configuration issue.
	 */
	public function process()
	{
		$this->status = 1;
		$errors = \App\Utils\ConfReport::getAllErrors();
		unset($errors['security']['HTTPS']);
		if (!empty($errors['security'])) {
			$this->status = 0;
		}
		if (!$this->status) {
			$reference = \App\Utils\ConfReport::getAll();
			$errorsText = '<br><pre>';
			if (!empty($errors['security'])) {
				$errorsText .= '<strong>' . \App\Language::translate('LBL_SECURITY', 'Settings:SystemWarnings') . ':</strong>';
				foreach ($errors['security'] as $key => $value) {
					$errorsText .= \PHP_EOL . "  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value) . ' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') . ': \'' . $reference['security'][$key]['recommended'] . '\')';
				}
				$errorsText .= \PHP_EOL . \PHP_EOL;
			}
			if (!empty($errors['writableFilesAndFolders'])) {
				$errorsText .= '<strong>' . \App\Language::translate('LBL_NO_FILE_WRITE_RIGHTS', 'Settings:SystemWarnings') . ':</strong>';
				foreach ($errors['writableFilesAndFolders'] as $key => $value) {
					$errorsText .= \PHP_EOL . "  {$key}";
				}
				$errorsText .= \PHP_EOL . \PHP_EOL;
			}
			$errorsText .= '</pre>';

			$this->link = 'https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirements';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs('LBL_SECURITY_CONF_DESC', 'Settings:SystemWarnings', '<a target="_blank" rel="noreferrer" href="' . \App\Language::translate('LBL_CONFIG_DOC_URL', 'Settings:SystemWarnings') . '"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>', $errorsText);
		}
	}
}
