{**
 * plugins/generic/heiMPT/templates/HeiMPTSettingsForm.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Usage statistics plugin management form.
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#HeiMPTForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="HeiMPTForm" method="post" action="{url op="manage" category="generic" plugin=$pluginName verb="save"}">
	{csrf}

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="HeiMPTFormNotification"}

	{fbvFormArea id="HeiMPTDisplayOptions" title="plugins.generic.heiMPT.settings.title"}

		{fbvFormSection for="settingsDescription" description="plugins.generic.heiMPT.settings.description"}
		{fbvElement type="text" id="heiMPTSettings" value=$heiMPTSettings size=$fbvStyles.size.SMALL}

		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons id="HeiMPTFormSubmit" submitText="common.save" hideCancel=true}
</form>
