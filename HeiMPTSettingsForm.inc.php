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
		$toolPath = $plugin->getSetting($contextId, 'toolPath');
		if (isset($toolPath) & !empty($toolPath)) {
			$this->setData('toolPath', $toolPath);
		}
		else {
			$this->setData('toolPath', '');
		}
	}

	function readInputData()
	{
		$this->readUserVars(array('toolPath'));
	}

	/**
	 * @return
	 */
	function execute()
	{
		$plugin = $this->_plugin;
		$context = Request::getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

		$toolPath = $this->getData('toolPath');

		if (!file_exists($toolPath)) {
			import('classes.notification.NotificationManager');
			$notificationMgr = new NotificationManager();
			$request = Application::getRequest();
			$notificationMgr->createTrivialNotification($request->getUser()->getId(),
				NOTIFICATION_TYPE_ERROR, array('contents' => __('plugins.generic.heiMPT.tool.PathNotFound')));
			return  false;
		}
		else {
			$plugin->updateSetting($contextId, 'toolPath', $toolPath);
			return true;
		}

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




}
