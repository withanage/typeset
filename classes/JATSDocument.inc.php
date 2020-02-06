<?php

//todo
class JATSDocument extends DOMDocument {
	/**
	 * @var DOMXPath
	 */
	protected $xpath;
	/**
	 * @var DOMDocument
	 */
	private $document;

	/**
	 * JATSDocument constructor.
	 * @param $fileContent
	 */
	public function __construct($fileContent) {

		parent::__construct();

		$this->loadXML($fileContent, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
		$this->xpath = new DOMXPath($this);
	}

	public function  setImages($submission) {
		$media = $this->xpath->query("//graphic/@xlink:href");
		foreach ($media as $mediaElement) {
			$mediaElement->nodeValue = str_replace('media/','',$mediaElement->nodeValue);

		}

	}

	/**
	 * sets OJS metadata
	 * @param $submission
	 *
	 */
	public function setMeta($submission) {
		$front = $this->xpath->query("//article/front")->item(0);

		if ($front) {
			while ($front->hasChildNodes()) {
				$front->removeChild($front->firstChild);
			}

			$articleMeta = $this->createElement("article-meta");
			$front->appendChild($articleMeta);

			$titleGroup = $this->createElement("title-group");
			$articleMeta->appendChild($titleGroup);

			$articleTitle = $this->createElement("article-title", htmlspecialchars($submission->getLocalizedTitle()));
			$titleGroup->appendChild($articleTitle);

			if ($submission->getLocalizedSubtitle()) {
				$subtitle = $this->createElement("subtitle", htmlspecialchars($submission->getLocalizedSubtitle()));
				$titleGroup->appendChild($subtitle);
			}

			if (!empty($submission->getAuthors())) {

				$contribGroup = $this->createElement("contrib-group");
				$contribGroup->setAttribute("content-type", "author");
				$articleMeta->appendChild($contribGroup);

				foreach ($submission->getAuthors() as $key => $author) {
					/* @var $author Author */
					$contrib = $this->createElement("contrib");
					$contrib->setAttribute("contrib-type", "person");
					$contribGroup->appendChild($contrib);

					$name = $this->createElement("name");
					$contrib->appendChild($name);

					if ($author->getLastName()) {
						$lastName = $this->createElement("lastName", htmlspecialchars($author->getLastName()));
						$name->appendChild($lastName);
					}

					$firstName = $this->createElement("firstName", htmlspecialchars($author->getFirstName()));
					$name->appendChild($firstName);

					if ($author->getEmail()) {
						$email = $this->createElement("email", htmlspecialchars($author->getEmail()));
						$contrib->appendChild($email);
					}

					$xref = $this->createElement("xref");
					$xref->setAttribute("ref-type", "aff");
					$xref->setAttribute("rid", "aff-" . ($key + 1));
					$contrib->appendChild($xref);

					$aff = $this->createElement("aff");
					$aff->setAttribute("id", "aff-" . ($key + 1));
					$articleMeta->appendChild($aff);

					$institution = $this->createElement("institution", htmlspecialchars($author->getLocalizedAffiliation()));
					$aff->appendChild($institution);

					$country = $this->createElement("country", htmlspecialchars($author->getCountryLocalized()));
					$aff->appendChild($country);
				}
			}

			$history = $this->createElement("history");
			$articleMeta->appendChild($history);

			$dateReceived = $this->createElement("date");
			$dateReceived->setAttribute("date-type", "received");
			$drf = new DateTime($submission->getDateSubmitted());

			$dateReceived->setAttribute("iso-8601-date", $drf->format("Y-m-d"));
			$history->appendChild($dateReceived);

			$dayReceived = $this->createElement("day", $drf->format("d"));
			$dateReceived->appendChild($dayReceived);

			$monthReceived = $this->createElement("month", $drf->format("m"));
			$dateReceived->appendChild($monthReceived);
			$yearReceived = $this->createElement("year", $drf->format("Y"));
			$dateReceived->appendChild($yearReceived);

			if ($submission->getDatePublished()) {
				$datePublished = $this->createElement("date");
				$datePublished->setAttribute("data-type", "published");
				$dpf = new DateTime($submission->getDatePublished());
				$datePublished->setAttribute("iso-8601-date", $dpf->format("Y-m-d"));
				$history->appendChild($datePublished);

				$dayPublished = $this->createElement("day", $dpf->format("d"));
				$datePublished->appendChild($dayPublished);
				$monthPublished = $this->createElement("month", $dpf->format("m"));
				$datePublished->appendChild($monthPublished);
				$yearPublished = $this->createElement("year", $dpf->format("Y"));
				$datePublished->appendChild($yearPublished);
			}
		}
	}

}
