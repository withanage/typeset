<?php

import('classes.handler.Handler');

//TODO Doc
class HeiMPTHandler extends Handler {

	/**
	 * HeiMPTHandler constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->_plugin = PluginRegistry::getPlugin('generic', TYPESET_PLUGIN_NAME);
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

		$user = $request->getUser();
		$stageId = (int)$request->getUserVar('stageId');
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$filePath = $submissionFile->getFilePath();

		list($typesetterOutputPath, $convertedFile, $typesetterCommand) = $this->runMeTypeset($filePath);

		$output = '';
		$returnCode = 0;
		$notificationMgr = new NotificationManager();

		//run typesetter
		exec($typesetterCommand, $output, $returnCode);

		if ($returnCode > 0) {
			$errorMsg = __('plugins.generic.heiMPT.tool.ConversionError');
			$notificationMgr->createTrivialNotification($request->getUser()->getId(), NOTIFICATION_TYPE_ERROR, array('contents' => $errorMsg));

		} else {
			$submissionDao = Application::getSubmissionDAO();
			$submissionId = $submissionFile->getSubmissionId();
			$submission = $submissionDao->getById($submissionId);
			$tmpfname = tempnam(sys_get_temp_dir(), 'heiMPT');
			$fileContent = file_get_contents($convertedFile);

			import('plugins.generic.heiMPT.classes.JATSDocument');
			$JATSDocument = new JATSDocument($fileContent);
			$JATSDocument->setMeta($submission);

			file_put_contents($tmpfname, $JATSDocument->saveXML());

			$genreId = $submissionFile->getGenreId();
			$fileSize = filesize($tmpfname);

			$originalFileInfo = pathinfo($submissionFile->getOriginalFileName());

			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
			$newSubmissionFile = $submissionFileDao->newDataObjectByGenreId($genreId);
			$newSubmissionFile->setSubmissionId($submission->getId());
			$newSubmissionFile->setSubmissionLocale($submission->getLocale());
			$newSubmissionFile->setGenreId($genreId);
			$newSubmissionFile->setFileStage($submissionFile->getFileStage());
			$newSubmissionFile->setDateUploaded(Core::getCurrentDate());
			$newSubmissionFile->setDateModified(Core::getCurrentDate());
			$newSubmissionFile->setOriginalFileName($originalFileInfo['filename'] . ".xml");
			$newSubmissionFile->setUploaderUserId($user->getId());
			$newSubmissionFile->setFileSize($fileSize);
			$newSubmissionFile->setFileType("text/xml");
			$newSubmissionFile->setSourceFileId($submissionFile->getFileId());
			$newSubmissionFile->setSourceRevision($submissionFile->getRevision());
			$newSubmissionFile->setRevision(1);
			$insertedSubmissionFile = $submissionFileDao->insertObject($newSubmissionFile, $tmpfname);

			unlink($tmpfname);
			rmdir($typesetterOutputPath);
			$successMsg = __('plugins.generic.heiMPT.tool.ConversionSuccess');
			$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $successMsg));
		}
		return DAO::getDataChangedEvent();
	}

	/**
	 * typesets using meTypeset
	 * @param $filePath
	 * @return array
	 */
	private function runMeTypeset($filePath) {
		$request = Application::getRequest();
		$toolPath = $this->_plugin->getToolPath($request);
		$typesetterOutputPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('heiMPT');
		$convertedFile = $typesetterOutputPath . DIRECTORY_SEPARATOR . 'nlm' . DIRECTORY_SEPARATOR . 'out.xml';
		$typesetterCommand = escapeshellcmd('python3 ' . $toolPath . ' --aggression 0 --nogit docx ' . $filePath . ' ' . $typesetterOutputPath);
		return array($typesetterOutputPath, $convertedFile, $typesetterCommand);
	}

}
