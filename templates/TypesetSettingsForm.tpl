{**
 * plugins/generic/typeset/templates/TypesetSettingsForm.tpl
 *

 * Usage jeiMPT plugin management form.
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#TypesetForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="TypesetForm" method="post" action="{url op="manage" category="generic" plugin=$pluginName verb="save"}">
	{csrf}

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="TypesetFormNotification"}

	{fbvFormArea id="TypesetDisplayOptions" title="plugins.generic.typeset.settings.title"}

		{fbvFormSection for="settingsDescription" description="plugins.generic.typeset.settings.description"}
		{fbvElement type="text" id="toolPath" value=$toolPath size=$fbvStyles.size.LARGE inline=true required=true}
		{/fbvFormSection}



	{/fbvFormArea}
	{fbvFormButtons id="TypesetFormSubmit" submitText="common.save" hideCancel=true}
</form>
