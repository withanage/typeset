{**
 * plugins/generic/heiMPT/templates/HeiMPTSettingsForm.tpl
 *

 * Usage jeiMPT plugin management form.
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
		{fbvElement type="text" id="toolPath" value=$toolPath size=$fbvStyles.size.LARGE inline=true required=true}
		{/fbvFormSection}



	{/fbvFormArea}
	{fbvFormButtons id="HeiMPTFormSubmit" submitText="common.save" hideCancel=true}
</form>
