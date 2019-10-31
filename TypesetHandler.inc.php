<?php

import('classes.handler.Handler');

//TODO Doc
class TypesetHandler extends Handler {

	/**
	 * TypesetHandler constructor.
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

	/**
	 * Delete folder and its contents
	 * @note Adapted from https://www.php.net/manual/de/function.rmdir.php#117354
	 */
	function rrmdir($src) {
		$dir = opendir($src);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				$full = $src . '/' . $file;
				if (is_dir($full)) {
					$this->rrmdir($full);
				} else {
					unlink($full);
				}
			}
		}
		closedir($dir);
		rmdir($src);
	}

	/**
	 * @param Request $request
	 * @param array $args
	 * @return JSONMessage
	 */
	public function convert($args, $request) {

		$user = $request->getUser();
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$filePath = $submissionFile->getFilePath();
		$userVars = $request->getUserVars();
		$notificationMgr = new NotificationManager();

		list($typesetterOutputPath, $convertedFile, $typesetterCommand) = $this->meTypeset($filePath, $userVars['fileExtension']);

		$output = '';
		$returnCode = 0;

		//run typesetter
		exec(escapeshellcmd($typesetterCommand), $output, $returnCode);


		if ($returnCode > 0) {

			$errorMsg = __('plugins.generic.typeset.tool.ConversionError');
			$notificationMgr->createTrivialNotification($request->getUser()->getId(), NOTIFICATION_TYPE_ERROR, array('contents' => $errorMsg));

		} else {


			// create output file
			$submissionDao = Application::getSubmissionDAO();
			$submissionId = $submissionFile->getSubmissionId();
			$submission = $submissionDao->getById($submissionId);
			$tmpfname = tempnam(sys_get_temp_dir(), basename($this->getPluginPath()));
			$fileContent = file_get_contents($convertedFile);

			import('plugins.generic.typeset.classes.JATSDocument');
			$JATSDocument = new JATSDocument($fileContent);
			$JATSDocument->setMeta($submission);
			$JATSDocument->setImages($submission);

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
			$newSubmissionFile = $submissionFileDao->insertObject($newSubmissionFile, $tmpfname);

			unlink($tmpfname);

			// create dependent files
			$mediaFilesPath = glob($typesetterOutputPath . DIRECTORY_SEPARATOR . 'media/*');

			if (!empty($mediaFilesPath)) {
				foreach ($mediaFilesPath as $filePath) {
					$this->_attachSupplementaryFile($request, $submission, $submissionFileDao, $newSubmissionFile, $filePath);
				}
			}

			$this->rrmdir($typesetterOutputPath);

			$successMsg = __('plugins.generic.typeset.tool.ConversionSuccess');
			$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $successMsg));
		}
		return DAO::getDataChangedEvent();
	}

	/**
	 * Override the builtin to get the correct plugin path.
	 * @return string
	 */
	public function getPluginPath() {
		return $this->_plugin->getPluginPath();
	}

	/**
	 * typeset using meTypeset
	 * @param $filePath source file path
	 * @param $fileType source file type
	 * @return array
	 */
	private function meTypeset($filePath, $fileType) {
		$request = Application::getRequest();
		$toolPath = $this->_plugin->getToolPath($request);
		$typesetterOutputPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid(basename($this->getPluginPath()));
		$convertedFile = $typesetterOutputPath . DIRECTORY_SEPARATOR . 'nlm' . DIRECTORY_SEPARATOR . 'out.xml';
		$typesetterCommand = 'python3 ' . $toolPath . ' --aggression 0 --nogit ' . $fileType . ' ' . $filePath . ' ' . $typesetterOutputPath;
		return array($typesetterOutputPath, $convertedFile, $typesetterCommand);
	}

	/**
	 * Adds attached images etc.
	 * @note Adapted from docxConverterPlugin
	 *
	 * @param Request $request
	 * @param Submission $submission
	 * @param SubmissionFileDAO $submissionFileDao
	 * @param SubmissionFile $newSubmissionFile
	 * @param string $originalName
	 * @param string $fileData
	 */
	private function _attachSupplementaryFile(Request $request, Submission $submission, SubmissionFileDAO $submissionFileDao, SubmissionFile $newSubmissionFile, $filePath) {

		$tmpfnameSuppl = tempnam(sys_get_temp_dir(), $this->getPluginPath());
		file_put_contents($tmpfnameSuppl, file_get_contents($filePath));
		$mimeType = mime_content_type($filePath);

		$genreDao = DAORegistry::getDAO('GenreDAO');
		$genres = $genreDao->getByDependenceAndContextId(true, $request->getContext()->getId());
		$supplGenreId = null;
		while ($genre = $genres->next()) {
			if (($mimeType == "image/png" || $mimeType == "image/jpeg") && $genre->getKey() == "IMAGE") {
				$supplGenreId = $genre->getId();
			}
		}

		if (!$supplGenreId) {
			unlink($tmpfnameSuppl);
			return;
		}

		// Set file
		$supplementaryFile = $submissionFileDao->newDataObjectByGenreId($supplGenreId);
		$supplementaryFile->setSubmissionId($submission->getId());
		$supplementaryFile->setSubmissionLocale($submission->getLocale());
		$supplementaryFile->setGenreId($supplGenreId);
		$supplementaryFile->setFileStage(SUBMISSION_FILE_DEPENDENT);
		$supplementaryFile->setDateUploaded(Core::getCurrentDate());
		$supplementaryFile->setDateModified(Core::getCurrentDate());
		$supplementaryFile->setUploaderUserId($request->getUser()->getId());
		$supplementaryFile->setFileSize(filesize($tmpfnameSuppl));
		$supplementaryFile->setFileType($mimeType);
		$supplementaryFile->setAssocId($newSubmissionFile->getFileId());
		$supplementaryFile->setAssocType(ASSOC_TYPE_SUBMISSION_FILE);
		$supplementaryFile->setOriginalFileName(basename($filePath));

		$submissionFileDao->insertObject($supplementaryFile, $tmpfnameSuppl);
		unlink($tmpfnameSuppl);
	}


}
