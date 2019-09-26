<?php

/**
 * @file plugins/generic/heiMPT/HeiMPTPlugin.inc.php
 *
 * @brief main class of the HeiMPT Converter Plugin
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class HeiMPTPlugin extends GenericPlugin {

	/**
	 * Register the plugin
	 *
	 * @param $category string Plugin category
	 * @param $path string Plugin path
	 * @param $mainContextId
	 * @return bool True on successful registration false otherwise
	 */
	public function register($category, $path, $mainContextId = NULL) {
		// Register the plugin
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {
			HookRegistry::register('TemplateManager::fetch', array($this, 'templateFetchCallback'));
			HookRegistry::register('LoadHandler', array($this, 'callbackLoadHandler'));
			$this->_registerTemplateResource();

		}
		return $success;
	}

	//Callbacks


	public function callbackLoadHandler($hookName, $args) {
		$page = $args[0];
		$op = $args[1];

		if ($page == "heiMPT" && $op == "convert") {
			define('HANDLER_CLASS', 'HeiMPTHandler');
			define('CONVERTER_PLUGIN_NAME', $this->getName());
			$args[2] = $this->getPluginPath() . '/' . 'HeiMPTHandler.inc.php';
		}

		return false;
	}

	/**
	 * Adds links to submission files grid row
	 * @param $hookName string The name of the invoked hook
	 * @param $args array Hook parameters
	 */
	public function templateFetchCallback($hookName, $params) {
		$request = $this->getRequest();
		$dispatcher = $request->getDispatcher();

		$templateMgr = $params[0];
		$resourceName = $params[1];
		if ($resourceName == 'controllers/grid/gridRow.tpl') {
			/* @var $row GridRow */
			$row = $templateMgr->get_template_vars('row');
			$data = $row->getData();

			if (is_array($data) && (isset($data['submissionFile']))) {
				$submissionFile = $data['submissionFile'];
				$fileExtension = strtolower($submissionFile->getExtension());
				//TODO odt
				if (strtolower($fileExtension) == 'docx') {

					$stageId = (int)$request->getUserVar('stageId');
					$path = $dispatcher->url($request, ROUTE_PAGE, null, 'heiMPT', 'convert', null,
						array(
							'submissionId' => $submissionFile->getSubmissionId(),
							'fileId' => $submissionFile->getFileId(),
							'stageId' => $stageId
						));
					$pathRedirect = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'access',
						array(
							'submissionId' => $submissionFile->getSubmissionId(),
							'fileId' => $submissionFile->getFileId(),
							'stageId' => $stageId
						));

					import('lib.pkp.classes.linkAction.request.AjaxAction');
					$linkAction = new LinkAction(
						'parse',
						new PostAndRedirectAction($path, $pathRedirect),
						__('plugins.generic.heiMPT.button.parseDocx')
					);
					$row->addAction($linkAction);
				}
			}
		}
	}


	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $actionArgs) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled() ? array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array_merge($actionArgs, array('verb' => 'settings'))),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			) : array(),
			parent::getActions($request, $actionArgs)
		);
	}
	/**
	 * Determine whether the plugin can be disabled.
	 * @return boolean
	 */
	function getCanDisable() {
		return true;
	}

	/**
	 * Determine whether the plugin can be enabled.
	 * @return boolean
	 */
	function getCanEnable() {
		return true;
	}
	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.heiMPT.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.heiMPT.description');
	}

	/**
	 * Determine whether the plugin is enabled.
	 * @return boolean
	 */
	function getEnabled() {
		$request = PKPApplication::getRequest();
		if (!$request) return false;
		$context = $request->getContext();
		if (!$context) return false;
		return $this->getSetting($context->getId(), 'enabled');
	}


	/**
	 * Get plugin URL
	 * @param $request PKPRequest
	 * @return string
	 */
	function getPluginUrl($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath();
	}

	function manage($args, $request) {
		$this->import('HeiMPTSettingsForm');
		$context = Request::getContext();
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$settingsForm = new HeiMPTForm($this, $context->getId());
				$settingsForm->initData();
				return new JSONMessage(true, $settingsForm->fetch($request));
			case 'save':
				$settingsForm = new HeiMPTForm($this, $context->getId());
				$settingsForm->readInputData();
				if ($settingsForm->validate()) {
					$settingsForm->execute();
					$notificationManager = new NotificationManager();
					$notificationManager->createTrivialNotification(
						$request->getUser()->getId(),
						NOTIFICATION_TYPE_SUCCESS,
						array('contents' => __('plugins.generic.wgl.settings.saved'))
					);
					return new JSONMessage(true);
				}
				return new JSONMessage(true, $settingsForm->fetch($request));
		}
		return parent::manage($args, $request);
	}

}
