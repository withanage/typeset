{**
 * templates/TypesetArticleGalley.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * typeset editor page
 *}
<script type="text/javascript">

	$(function() {ldelim}
		$('#typesetGalleyForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim})

</script>


<form class="pkp_form" id="typesetGalleyForm" method="post" action="{url op="createGalley" submissionId=$submissionId stageId=$stageId fileStage=$fileStage fileId=$fileId }" >

    {csrf}

	{fbvFormArea id="typesetGalleyFormArea"}
	{fbvFormSection title="submission.layout.galleyLabel" required=true}
	{fbvElement type="text" label="submission.layout.galleyLabelInstructions" value=$label id="label" size=$fbvStyles.size.MEDIUM inline=true required=true}
	{/fbvFormSection}
	{fbvFormSection}
	{fbvElement type="select" id="galleyLocale" label="common.language" from=$supportedLocales selected=$galleyLocale|default:$formLocale size=$fbvStyles.size.MEDIUM translate=false inline=true required=true}
	{/fbvFormSection}

    {/fbvFormArea}

    {fbvFormButtons submitText="common.save"}
</form>

