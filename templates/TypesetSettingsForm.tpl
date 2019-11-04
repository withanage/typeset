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

	{fbvFormArea id="TypesetDisplayOptions" title="plugins.generic.typeset.settings.aggression"}

		{fbvFormSection for="settingsAggression" description="plugins.generic.typeset.settings.aggression"}
		{fbvElement type="text" id="toolAgression" value=$toolAggression size=$fbvStyles.size.SMALL inline=true required=false}
		{/fbvFormSection}



	{/fbvFormArea}
	{fbvFormButtons id="TypesetFormSubmit" submitText="common.save" hideCancel=true}
</form>
