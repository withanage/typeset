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
	{fbvElement type="text" id="typesetToolAggression" value=$typesetToolAggression size=$fbvStyles.size.SMALL inline=true required=false}
	{/fbvFormSection}

    {fbvFormSection title="plugins.generic.typeset.settings.cleanTitle" list=true}
    {fbvElement type="checkbox" name="typesetToolClean" id="typesetToolClean" checked=$typesetToolClean label="plugins.generic.typeset.settings.cleanDescription"}
    {/fbvFormSection}

    {fbvFormSection title="plugins.generic.typeset.settings.imageTitle" list=true}
    {fbvElement type="checkbox" name="typesetToolImage" id="typesetToolImage" checked=$typesetToolImage label="plugins.generic.typeset.settings.imageDescription"}
    {/fbvFormSection}

    {fbvFormSection title="plugins.generic.typeset.settings.referenceTitle" list=true}
    {fbvElement type="checkbox" name="typesetToolReference" id="typesetToolReference" checked=$typesetToolReference label="plugins.generic.typeset.settings.referenceDescription"}
    {/fbvFormSection}

    {/fbvFormArea}
	{fbvFormButtons id="TypesetFormSubmit" submitText="common.save" hideCancel=true}
</form>
