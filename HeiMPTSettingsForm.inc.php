<?php

/**
 * @file plugins/generic/heiMPT/HeiMPTSettingsForm.inc.php
 *
 */

import('lib.pkp.classes.form.Form');

//TODO

class HeiMPTForm extends Form
{

	/**
	 * HeiMPTForm constructor.
	 * @param $plugin
	 */
	/***	 * @var context	 */
	private $_context;

	/** @var Settings Plugin */
	private $_plugin;

	function __construct($plugin, $contextId) {
		$this->_context = $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('HeiMPTSettingsForm.tpl'));
		$this->setData('pluginName', $plugin->getName());
	}

	function initData()
	{
		$plugin = $this->_plugin;
		$context = Request::getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$heiMPT = $plugin->getSetting($contextId, 'heiMPT');
		if (isset($heiMPT) & !empty($heiMPT)) {
			$this->setData('heiMPT', $heiMPT);
		}
		else {
			$this->setData('heiMPT', '');
		}
	}

	function readInputData()
	{
		$this->readUserVars(array('heiMPT'));
	}


	function execute()
	{
		$plugin = $this->_plugin;
		$context = Request::getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$plugin->updateSetting($contextId, 'heiMPT', $this->getData('heiMPT'));
	}
	function manage($args, $request) {
		$plugin = $this->getAuthorizedContextObject(ASSOC_TYPE_PLUGIN);
		return $plugin->manage($args, $request);
	}

	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		return parent::fetch($request);
	}




}
