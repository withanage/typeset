<?php

/**
 * @file plugins/generic/typeset/TypesetSettingsForm.inc.php
 *
 */

import('lib.pkp.classes.form.Form');

//TODO

class TypesetSettingsForm extends Form {

	/**
	 * TypesetForm constructor.
	 * @param $plugin
	 */
	/***     * @var context */
	private $_context;

	/** @var Settings Plugin */
	private $_plugin;

	private $_pluginSettings;

	function __construct($plugin, $contextId) {
		$this->_context = $contextId;
		$this->_plugin = $plugin;
		$this->_pluginSettings = [
			'typesetToolAggression',
			'typesetToolClean',
			'typesetToolImage',
			'typesetToolReference',
			'typesetPythonVirtualPath',
			'typesetToolOutputTEI'
		];

		parent::__construct($plugin->getTemplateResource('TypesetSettingsForm.tpl'));
		$this->setData('pluginName', $plugin->getName());

	}

	private function _getPluginSettings() {
		return $this->_pluginSettings;
	}

	function initData() {

		foreach ($this->_getPluginSettings() as $pluginSetting){
		$this->_setValue($pluginSetting);
		}
	}

	/**
	 * @return
	 */
	function execute() {
		$plugin = $this->_plugin;
		$context = Request::getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

		foreach ($this->_getPluginSettings() as $settingName) {
			$plugin->updateSetting($contextId, $settingName, $this->getData($settingName));
		}

		return true;

	}

	/**
	 * @param $args
	 * @param PKPRequest $request
	 * @return mixed
	 */

	function manage($args, $request) {
		$plugin = $this->getAuthorizedContextObject(ASSOC_TYPE_PLUGIN);
		return $plugin->manage($args, $request);
	}

	/**
	 * Fetchs template
	 * @param PKPRequest $request
	 * @return string
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		return parent::fetch($request);
	}

	/**
	 * @param string $pluginSetting
	 */
	private function _setValue(string $pluginSetting): void {
		$plugin = $this->_plugin;
		$context = Request::getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$setting = $plugin->getSetting($contextId, $pluginSetting);
		if (isset($setting) & !empty($setting)) {
			$this->setData($pluginSetting, $setting);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		parent::readInputData();
		$this->readUserVars($this->_getPluginSettings());
	}

}
