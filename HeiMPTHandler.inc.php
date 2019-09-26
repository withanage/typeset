<?php

import('classes.handler.Handler');
//TODO Doc
class HeiMPTHandler extends Handler {

	/**
	 * HeiMPTHandler constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->_plugin = PluginRegistry::getPlugin('generic', CONVERTER_PLUGIN_NAME);
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_AUTHOR),
			array('convert')
		);
	}

	/**
	 * @param Request $request
	 * @param array $args
	 * @param array $roleAssignments
	 * @return bool True on successful authorization false otherwise
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.SubmissionFileAccessPolicy');
		$this->addPolicy(new SubmissionFileAccessPolicy($request, $args, $roleAssignments, SUBMISSION_FILE_ACCESS_READ));
		return parent::authorize($request, $args, $roleAssignments);
	}

	public function convert($args, $request) {
		return new JSONMessage(true);
	}




}
