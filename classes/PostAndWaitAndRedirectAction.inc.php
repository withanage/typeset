<?php

import('lib.pkp.classes.linkAction.request.PostAndRedirectAction');

class PostAndWaitAndRedirectAction extends PostAndRedirectAction {
	function __construct($postUrl, $redirectUrl, $request, $resourceName) {
		parent::__construct($postUrl, $redirectUrl);
		$this->resourceName = $resourceName;
		$this->request = $request;
		$this->createNotificatoin();
	}

	function createNotificatoin() {
		$notificationMgr = new NotificationManager();
		$user = $this->getRequest()->getUser();
		$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $this->getResourceName()));
		return DAO::getDataChangedEvent();
	}

	function getRequest() {
		return $this->request;
	}

	function getResourceName() {
		return $this->resourceName;
	}

}
