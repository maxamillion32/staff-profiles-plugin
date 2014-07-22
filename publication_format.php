<?php
/**
 * Publications formatting of symplectic feeds
 * Contains classes to format different types of publications in MHRA, APA and Harvard styles
 * using data from a symplectic web feed
 * @author Peter Edwards <p.l.edwards@leeds.ac.uk>
 * @version 0.0.1
 */

/* abstract class which all publications must extend */
abstract class sp_publication
{
	/**
	 * interface type of thing
	 */
	abstract protected function format_Book();
	abstract protected function format_Chapter();
	abstract protected function format_Journalarticle();
	abstract protected function format_Conference();
	abstract protected function format_Other();
	abstract protected function format_Internetpublication();
	abstract protected function format_Composition();
	abstract protected function format_Performance();
	abstract protected function format_Report();
	abstract protected function format_Artefact();
	abstract protected function format_Design();
	abstract protected function format_Scholarlyedition();
	abstract protected function format_ThesisDissertation();

	/**
	 * properties
	 */
	protected $pub;
	protected $display;

	/**
	 * methods to set properties
	 */
	public function set_publication($pub)
	{
		$this->pub = $pub;
	}
	public function set_display($display)
	{
		$this->display = $display;
	}

	/**
	 * method to get formatted publication
	 */
	public final function get_formatted_publication()
	{
		if ( ! $this->pub ) {
			return '';
		}
		$methodname = "format_" . preg_replace("/[^a-zA-Z]/", "", $this->pub["publicationtype"]);
		if ( method_exists( $this, $methodname ) ) {
			return sprintf( '<li>%s</li>', $this->$methodname() );
		} else {
			return sprintf( '<li>%s</li>', $this->format_Other() );
		}
	}

	/**********************************************
	 * utility methods inherited by child classes *
	 **********************************************/

	/**
	 * Formats the place of publication and publisher as:
	 * [Place of publication][:] [link to publisher URL][publisher name][/link]
	 */
	public function format_publisher()
	{
		$out = "";
		if (isset($this->pub["placeofpublication"]) && trim($this->pub["placeofpublication"]) != "") {
			$place = (trim(trim($this->pub["placeofpublication"]), ':;');
			$out .= '<span class="placeofpublication">' . $place . '</span>: ';
		}
		if (isset($this->pub["publisher"]) && trim($this->pub["publisher"]) != "") {
			$sep = (substr(trim($this->pub["publisher"]), -1) == ".")? "": ".";
			if (isset($this->pub["publisherurl"]) && trim($this->pub["publisherurl"]) != "") {
				$out .= ' <span class="publisher"><a href="' . trim($this->pub["publisherurl"]) . '">' . trim($this->pub["publisher"]) . '</a>' . $sep . '</span>';
			} else {
				$out .= ' <span class="publisher">' . trim($this->pub["publisher"]) . $sep . '</span>';
			}
		}
		return $out;
	}

	/**
	 * artefacts do not contain a publisher field, so the Location field is used instead
	 * also, the medium field is inserted before the publisher
	 */
	public function format_artefact_publisher()
	{
		$out = "";
		if ( isset($this->pub["medium"]) && trim($this->pub["medium"]) != "" ) {
			$sep = (substr(trim($this->pub["medium"]), -1) == ":")? "": ":";
			$out .= ' <span class="medium">' . trim($this->pub["medium"]) . $sep . '</span>';
		}
		if ( isset($this->pub["location"]) && trim($this->pub["location"]) != "" ) {
			$sep = (substr(trim($this->pub["location"]), -1) == ".")? "": ".";
			if ( isset($this->pub["publisherurl"]) && trim($this->pub["publisherurl"]) != "" ) {
				$out .= ' <span class="location"><a href="' . trim($this->pub["publisherurl"]) . '" class="publisherurl">' . trim($this->pub["location"]) . '</a>' . $sep . '</span>';
			} else {
				$out .= ' <span class="location">' . trim($this->pub["location"]) . $sep . '</span>';
			}
		}
		return $out;
	}

	/**
	 * formats the page count of a publication as:
	 * [beginpage][+|-endpage]
	 */
	public function format_pages()
	{
		$out = "";
		if ( isset($this->pub["beginpage"]) && trim($this->pub["beginpage"]) != "" ) {
			$out .= ' <span class="pages">' . trim($this->pub["beginpage"]);
			if ( isset($this->pub["endpage"]) && trim($this->pub["endpage"]) != "" ) {
				$out .= '-' . trim($this->pub["endpage"]);
			} else {
				$out .= "+";
			}
			$out .= '</span>';
		}
		return $out;
	}

	/**
	 * formats the volume, issue and pages as:
	 * [volume][.][issue][: ][pages.]
	 */
	public function format_issue()
	{
		$out = "";
		if ( isset($this->pub["volume"]) && trim($this->pub["volume"]) != "" ) {
			$out .= ' <span class="volume">' . trim($this->pub["volume"]) . '</span>';
		}
		if ( isset($this->pub["issue"]) && trim($this->pub["issue"]) != "" ) {
			$out .= ($out != "")? ".": "";
			$out .= '<span class="issue">' . trim($this->pub["issue"]) . '</span>';
		}
		$pages = $this->format_pages();
		if ($pages) {
			$out .= ': ' . $pages . '.';
		}
		return $out;
	}

	/**
	 * formats the publication status, placed in square brackets
	 * does not display "Published" or items without a publication status
	 */
	public function format_status()
	{
		$out = "";
		if (isset($this->pub["status"]) && $this->pub["status"] != "Published" && $this->pub["status"] != "" && strtolower($this->pub["status"]) != "null") {
			$out .= ' (<span class="status">' . $this->pub["status"] . '</span>)';
		}
		return $out;
	}

	/**
	 * formats all the extra bits like notes, URLs, abstract...
	 */
	public function format_extras()
	{
		$out = "";
		if ($this->display["notes"] && isset($this->pub["notes"]) && trim($this->pub["notes"]) != "") {
			$out .= '<p class="notes indent">' . trim($this->pub["notes"]) . '</p>';
		}
		if ($this->display["authorurl"] && isset($this->pub["authorurl"]) && trim($this->pub["authorurl"]) != "") {
			$parsed = parse_url(trim($this->pub["authorurl"]));
			if ($parsed !== false && isset($parsed["host"]) && trim($parsed["host"]) != "") {
				$out .= '<p class="authorurl indent"><a href="' . trim($this->pub["authorurl"]) . '">Author URL [' . trim($parsed["host"]) . ']</a></p>';				
			}
		}
		if ($this->display["repositoryurl"] && isset($this->pub["repositoryurl"]) && trim($this->pub["repositoryurl"]) != "") {
			$parsed = parse_url(trim($this->pub["repositoryurl"]));
			if ($parsed !== false && isset($parsed["host"]) && trim($parsed["host"]) != "") {
				$out .= '<p class="repositoryurl indent"><a href="' . trim($this->pub["repositoryurl"]) . '">Repository URL [' . trim($parsed["host"]) . ']</a></p>';
			}
		}
		if ($this->display["abstract"] && isset($this->pub["abstract"]) && trim($this->pub["abstract"]) != "") {
			if ( isset($this->pub["medium"]) && $this->pub["medium"] === "CD") {
				$out .= '<div class="tracklist indent"><ol>';
				$out .= substr(preg_replace('/[0-9]+\. /', '</li><li style="list-style:decimal outside;margin:.5em 0 0 2em;">', $this->pub["abstract"]), 5) . '</li></ol></div>';
			} else {
				$out .= '<p class="abstract indent">' . trim($this->pub["abstract"]) . '</p>';
			}
		}
		return $out;
	}

	/**
	 * formats dates
	 */
	public function format_date($date, $fromtime = false)
	{
		$year = intval( substr( $date, 6, 4 ) );
		$month = intval( substr( $date, 3, 2 ) );
		$day = intval( substr( $date, 0, 2 ) );
		if ($fromtime && (mktime(1, 1, 1, $month, $day, $year) !== false)) {
			return (date("j M. Y", mktime(1, 1, 1, $month, $day, $year)));
		}
		return $day . "/" . $month . "/" . $year;
	}
}

/**
 * Class used to implement MHRA format
 */
class sp_mhra_publication extends sp_publication
{
	public function format_Book()
	{
		$out = '<p class="hanging indent">';
		if ( isset( $this->pub["authors"] ) && trim( $this->pub["authors"] ) != "" ) {
			$out .= sprintf( '<span class="authors">%s</span>, ', $this->format_names( $this->pub["authors"] ) );
		}
		if ( isset( $this->pub["title"] ) && trim( $this->pub["title"] ) != "" ) {
			$out .= sprintf( '<span class="title">%s</span>', trim( trim( $this->pub["title"] ), ',.' ) );
		}
		if ( isset( $this->pub["editors"] ) && trim( $this->pub["editors"] ) != "" ) {
			$out .= sprintf( ', ed. by <span class="editors">%s</span>', $this->format_names( $this->pub["editors"] ) );
		}
		if ( isset( $this->pub["series"] ) && trim( $this->pub["series"] ) != "" ) {
			$out .= sprintf( ', <span class="series">%s</span>', trim( trim( $this->pub["series"] ), ',.' ) );
		}
		if ( isset( $this->pub["edition"] ) && trim( $this->pub["edition"] ) != "" ) {
			$out .= sprintf( ', <span class="edition">%s</span>', trim( trim( $this->pub["edition"] ), ',' ) );
		}
		$out .= $this->format_publisher();
		$out .= $this->format_volume_pages();
		$out .= $this->format_status();
		$out .= '</p>';
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Chapter()
	{
		$out = '<p class="hanging indent">';
		if ( isset( $this->pub["authors"] ) && trim( $this->pub["authors"] ) != "" ) {
			$out .= sprintf( '<span class="authors">%s</span>, ', $this->format_names( $this->pub["authors"] ) );
		}
		if ( isset( $this->pub["title"] ) && trim( $this->pub["title"] ) != "" ) {
			$out .= sprintf( '<span class="chapter-title">&lsquo;%s&rsquo;</span>', trim( trim( $this->pub["title"] ), ',."\'“”‘’' ) );
		}
		if ( isset( $this->pub["parenttitle"] ) && trim( $this->pub["parenttitle"] ) !== "" ) {
			$out .= sprintf( ', in <span class="title">%s</span>', trim( trim( $this->pub["parenttitle"] ), ',."\'“”‘’' ) );
		}
		if ( isset( $this->pub["editors"] ) && trim( $this->pub["editors"] ) != "" ) {
			$out .= sprintf( ', ed. by <span class="editors">%s</span>', $this->format_names( $this->pub["editors"] ) );
		}
		if ( isset( $this->pub["series"] ) && trim( $this->pub["series"] ) != "" ) {
			$out .= sprintf( ', <span class="series">%s</span>', trim( trim( $this->pub["series"] ), ',.' ) );
		}
		if ( isset( $this->pub["edition"] ) && trim( $this->pub["edition"] ) != "" ) {
			$out .= sprintf( ', <span class="edition">%s</span>', trim( trim( $this->pub["edition"] ), ',' ) );
		}
		$out .= $this->format_publisher();
		$out .= $this->format_volume_pages();
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Journalarticle()
	{
		$out = '<p class="hanging indent">';
		if ( isset( $this->pub["authors"] ) && trim( $this->pub["authors"] ) != "" ) {
			$out .= sprintf( '<span class="authors">%s</span>, ', $this->format_names( $this->pub["authors"] ) );
		}
		if ( isset( $this->pub["title"] ) && trim( $this->pub["title"] ) != "" ) {
			$out .= sprintf( '<span class="article-title">&lsquo;%s&rsquo;</span>', trim( trim( $this->pub["title"] ), ',."\'“”‘’' ) );
		}
		if ( isset( $this->pub["journal"] ) && trim( $this->pub["journal"] ) !== "" ) {
			if ( trim( $this->pub["journal"] ) == strtoupper( trim( $this->pub["journal"] ) ) ) {
				$this->pub["journal"] = ucwords( trim( $this->pub["journal"] ) );
			}
			$out .= sprintf(', <span class="journal">%s</span>', trim( $this->pub["journal"] ) );
			if ( isset( $this->pub["editors"] ) && trim( $this->pub["editors"] ) != "" ) {
				$out .= sprintf( ', ed. by <span class="editors">%s</span>', $this->format_names( $this->pub["editors"] ) );
			}
		}
		if ( isset( $this->pub["series"] ) && trim( $this->pub["series"] ) != "" ) {
			$out .= sprintf(', <span class="series">%s</span>', trim( trim( $this->pub["series"] ), ',.' ) );
		}
		$out .= $this->format_volume_pages( true );
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_ThesisDissertation()
	{
		$out = '<p class="hanging indent">';
		if ( isset($this->pub["authors"]) && trim($this->pub["authors"]) != "" ) {
			$out .= '<span class="authors">' . $this->format_names($this->pub["authors"]) . '</span>, ';
		}
		if ( isset($this->pub["title"]) && trim($this->pub["title"]) != "" ) {
			$out .= sprintf( '<span class="thesis-title">&lsquo;%s&rsquo;</span>', trim( trim( $this->pub["title"] ), ',."\'“”‘’' ) );
		}
		if (isset($this->pub["publicationdate"]) && trim($this->pub["publicationdate"]) != "") {
			$out .= sprintf( ', (<span class="publicationdate">%s</span>)', $this->format_date($this->pub["publicationdate"], true) );
		}
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Conference()
	{
		$out = '<p class="hanging indent">';
		if ( isset($this->pub["authors"]) && trim($this->pub["authors"]) != "" ) {
			$out .= '<span class="authors">' . $this->format_names($this->pub["authors"]) . '</span>, ';
		}
		if ( isset($this->pub["title"]) && trim($this->pub["title"]) != "" ) {
			$class = ( isset($this->pub["publishedproceedings"]) && trim($this->pub["publishedproceedings"]) != "" )? 'title-with-parent': 'title';
			$out .= sprintf( '<span class="%s">&lsquo;%s&rsquo;</span>', $class, trim( trim( $this->pub["title"] ), ',."\'“”‘’' ) );
		}
		if ( isset($this->pub["publishedproceedings"]) && trim($this->pub["publishedproceedings"]) != "" ) {
			$out .= ', in <span class="publishedproceedings">';
			if (strpos(trim($this->pub["publishedproceedings"]), 'http') === 0) {
				$out .= sprintf('<a href="%s">%s</a>', trim($this->pub["publishedproceedings"]), trim($this->pub["publishedproceedings"]));
			} else {
				$out .= trim($this->pub["publishedproceedings"]);
			}
			$out .= '</span>';
			if ( isset($this->pub["editors"]) && trim($this->pub["editors"]) != "" ) {
				$out .= sprintf(', ed. by <span class="editors">%s</span>', $this->format_names($this->pub["editors"]));
			}
			$out .= $this->format_publisher();
			$out .= $this->format_volume_pages( false );
		}
		if ( isset($this->pub["conferencename"]) && trim($this->pub["conferencename"]) != "" ) {
			$out .= sprintf( ' <span class="conferencename">%s</span>', trim($this->pub["conferencename"]) );
		}
		if ( isset($this->pub["conferenceplace"]) && trim($this->pub["conferenceplace"]) != "" ) {
			$out .= sprintf( ', <span class="conferenceplace">%s</span>', trim($this->pub["conferenceplace"]) );
		}
		if ( isset($this->pub["conferenecestartdate"]) && trim($this->pub["conferenecestartdate"]) != "" ) {
			$out .= sprintf( ', <span class="startdate finishdate">%s', $this->format_date(trim($this->pub["conferenecestartdate"]), false) );
			if ( isset($this->pub["conferencefinishdate"]) && trim($this->pub["conferencefinishdate"]) != "" ) {
				$out .= sprintf( ' - %s', $this->format_date(trim($this->pub["conferencefinishdate"]), false) );
			}
			$out .= '</span> ';
		} elseif ( isset($this->pub["conferencestartday"]) && trim($this->pub["conferencestartday"]) != "" &&  isset($this->pub["conferencestartmonth"]) && trim($this->pub["conferencestartmonth"]) != "" &&  isset($this->pub["conferencestartyear"]) && trim($this->pub["conferencestartyear"]) != "") {
			$out .= sprintf( ', <span class="startdate finishdate">%s/%s/%s', trim($this->pub["conferencestartday"]), trim($this->pub["conferencestartmonth"]), trim($this->pub["conferencestartyear"]) );
			if ( isset($this->pub["conferencefinishday"]) && trim($this->pub["conferencefinishday"]) != "" &&  isset($this->pub["conferencefinishmonth"]) && trim($this->pub["conferencefinishmonth"]) != "" &&  isset($this->pub["conferencefinishyear"]) && trim($this->pub["conferencefinishyear"]) != "") {
				$out .= sprintf( ' - %s/%s/%s', trim($this->pub["conferencefinishday"]), trim($this->pub["conferencefinishmonth"]), trim($this->pub["conferencefinishyear"]) );
			}
			$out .= '</span> ';
		} elseif ( isset($this->pub["conferencefinishdate"]) && trim($this->pub["conferencefinishdate"]) != "" ) {
			$out .= sprintf( ', <span class="startdate finishdate">%s</span> ', $this->format_date(trim($this->pub["conferencefinishdate"]), false) );
		}
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}
    

	public function format_Other()
	{
		$out = '<p class="hanging indent">';
		$out .= $this->format_basics();
		$out .= $this->format_publisher(true);
		$out .= $this->format_issue();
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}


	public function format_Internetpublication()
	{
		$out = $this->format_minimal();
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Composition()
	{
		$out = $this->format_musical();
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Performance()
	{
		$out = $this->format_musical();
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Report()
	{
		$out = $this->format_minimal();
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Artefact()
	{
		$out = '<p class="hanging indent">';
		$out .= $this->format_basics();
		$out .= $this->format_artefact_publisher();
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Design()
	{
		return $this->format_minimal();
	}

	public function format_Scholarlyedition()
	{
		return $this->format_minimal();
	}

	private function format_minimal()
	{
		$out = '<p class="hanging indent">';
		$out .= $this->format_basics();
		$out .= $this->format_publisher();
		$out .= $this->format_volume_pages();
		$out .= $this->format_status();
		$out .= "</p>";
		return $out;
	}

	private function format_musical()
	{
		$out = '<p class="hanging indent">';
		$out .= $this->format_basics();
		/* publisher minimal */
		$publisher = ( isset($this->pub["publisher"]) ) ? trim(trim($this->pub["publisher"]), ','): "";
		$year = ( isset($this->pub["publicationyear"]) ) ? trim(trim($this->pub["publicationyear"]), '()'): "";
		$url = ( isset($this->pub["publisherurl"]) && strtolower( substr( trim($this->pub["publisherurl"]), 0, 4) ) === 'http') ? trim($this->pub["publisherurl"]) : "";
		
		/* if publisher is present, link to URL */
		if ( ! empty($url) && ! empty($publisher) ) {
			$publisher = sprintf('<a href="%s" class="publisherurl">%s</a>', $url, $publisher);
		}

		/* publisher and year */
		if ( ! empty($publisher) && ! empty($year) ) {
			$out .= sprintf(', (<span class="publisher">%s</span>, <span class="publicationyear">%s</span>)', $publisher, $year);
		/* publisher only */	
		} elseif ( ! empty($publisher) ) {
			$out .= sprintf(', (<span class="publisher">%s</span>)', $publisher);
		/* year only */
		} elseif ( ! empty($year) ) {
			$out .= sprintf(', (<span class="publicationyear">%s</span>)', $year);
		}

		if (isset($this->pub["medium"]) && trim($this->pub["medium"]) !== "") {
			$out .= ', <span class="music-medium">' . trim($this->pub["medium"]) . '</span> ';
		}
		$out .= $this->format_status();
		$out .= "</p>";
		return $out;
	}

	private function format_basics()
	{
		$out = "";
		if ( isset($this->pub["authors"]) && trim($this->pub["authors"]) != "" ) {
			$out .= '<span class="authors">' . $this->format_names($this->pub["authors"]) . '</span>, ';
		}
		$parent = false;
		if ( isset( $this->pub["journal"] ) && trim( $this->pub["journal"] ) !== "" ) {
			if ( trim( $this->pub["journal"] ) == strtoupper( trim( $this->pub["journal"] ) ) ) {
				$this->pub["journal"] = ucwords( trim( $this->pub["journal"] ) );
			}
			$parent = $this->pub["journal"];
		} elseif ( isset($this->pub["parenttitle"]) && trim($this->pub["parenttitle"]) !== "" ) {
			$parent = trim($this->pub["parenttitle"]);
		}
		if ( isset($this->pub["title"]) && trim($this->pub["title"]) != "" ) {
			$class = ( $parent )? 'title-with-parent': 'title';
			$out .= sprintf( '<span class="%s">%s</span>', $class, trim( trim( $this->pub["title"] ), ',."\'“”‘’' ) );
		}
		if ($parent) {
			$out .= sprintf(', <em>in</em> <span class="parent-title">%s</span>', trim($this->pub["parenttitle"]) );
			if ( isset( $this->pub["editors"] ) && trim( $this->pub["editors"] ) != "" ) {
				$out .= sprintf( ', ed. by <span class="editors">%s</span>', $this->format_names( $this->pub["editors"] ) );
			}
		}
		if ( isset($this->pub["series"]) && trim($this->pub["series"]) != "" ) {
			$sep = (substr(trim($this->pub["series"]), -1) == ".")? "": ".";
			$out .= ' <span class="series">' . trim($this->pub["series"]) . $sep . '</span> ';
		}
		if ( isset($this->pub["edition"]) && trim($this->pub["edition"]) != "" ) {
			$out .= sprintf( ', <span class="edition">%s</span>', trim($this->pub["edition"]) );
		}
		if ( isset($this->pub["series"]) && trim($this->pub["series"]) != "" ) {
			$out .= sprintf( ', <span class="series">%s</span>', trim($this->pub["series"]) );
		}
		return $out . ", ";
	}

	/**
	 * formats names of authors and editors
	 * names are separated by semicolons in symplectic
	 * up to three names are returned as a list, if more names are present, 
	 * only the first is returned with "and others" appended to it (unless
	 * the $truncate parameter is false, which will force listing all names).
	 * @param string names as a string, separated by semicolons
	 * @param boolean whether to truncate lists of more than 3 names
	 */
	public function format_names($namestr, $truncate = true)
	{
		if ( trim($namestr) == "" ) {
			return "";
		}
		$names = array_map( 'trim', explode( ";", $namestr ) );
		if ( ! count($names) ) {
			return "";
		} elseif ( count($names) === 1 ) {
			return $names[0];
		} elseif ( count($names) === 2 ) {
			return sprintf("%s and %s", $names[0], $names[1]);
		} elseif ( count($names) === 3 ) {
			return sprintf("%s, %s and %s", $names[0], $names[1], $names[2]);
		} else {
			if ( true === $truncate ) {
				return sprintf("%s and others", $names[0]);
			} else {
				$lastname = array_pop($names);
				return sprintf("%s and %s", implode(", ", $names), $lastname);
			}
		}
	}

	/**
	 * MHRA overrides format_publisher in parent class to add year
	 * ([Place of publication][:] [link to publisher URL][publisher name][/link]
	 * - always returns content if the place of publication, publisher or year is filled in
	 */
	public function format_publisher()
	{
		$place = ( isset($this->pub["placeofpublication"]) ) ? trim(trim($this->pub["placeofpublication"]), ':'): "";
		$publisher = ( isset($this->pub["publisher"]) ) ? trim(trim($this->pub["publisher"]), ','): "";
		$year = ( isset($this->pub["publicationyear"]) ) ? trim(trim($this->pub["publicationyear"]), '()'): "";
		$url = ( isset($this->pub["publisherurl"]) && strtolower( substr( trim($this->pub["publisherurl"]), 0, 4) ) === 'http') ? trim($this->pub["publisherurl"]) : "";
		
		/* if publisher is present, link to URL */
		if ( ! empty($url) && ! empty($publisher) ) {
			$publisher = sprintf('<a href="%s" class="publisherurl">%s</a>', $url, $publisher);
		/* or if publisher isn't present, but place is, link place to URL */
		} elseif ( ! empty($url) && ! empty($place) ) {
			$place = sprintf('<a href="%s" class="publisherurl">%s</a>', $url, $place);
		}

		/* all fields have been filled in */
		if ( ! empty($place) && ! empty($publisher) && ! empty($year) ) {
			return sprintf(' (<span class="placeofpublication">%s</span>: <span class="publisher">%s</span>, <span class="publicationyear">%s</span>)', $place, $publisher, $year);
		/* publisher and year */
		} elseif ( ! empty($publisher) && ! empty($year) ) {
			return sprintf(' (<span class="publisher">%s</span>, <span class="publicationyear">%s</span>)', $publisher, $year);
		/* place and year */
		} elseif ( ! empty($place) && ! empty($year) ) {
			return sprintf(' (<span class="placeofpublication">%s</span>: <span class="publisher">[n.pub.]</span> <span class="publicationyear">%s</span>)', $place, $year);
		/* place and publisher */	
		} elseif ( ! empty($publisher) && ! empty($place) ) {
			return sprintf(' (<span class="placeofpublication">%s</span>: <span class="publisher">%s</span>, <span class="publicationyear">[n.d.]</span>)', $place, $publisher);
		/* publisher only */	
		} elseif ( ! empty($publisher) ) {
			return sprintf(' (<span class="publisher">%s</span>, <span class="publicationyear">[n.d.]</span>)', $publisher);
		/* place only */
		} elseif ( ! empty($place) ) {
			return sprintf(' (<span class="placeofpublication">%s</span>: <span class="publisher">[n.pub.]</span>, <span class="publicationyear">[n.d.]</span>)', $place);
		/* year only */
		} elseif ( ! empty($year) ) {
			return sprintf(' (<span class="publisher">[n.pub.]</span>, <span class="publicationyear">%s</span>)', $year);
		} else {
			return ' (<span class="publisher">[n.pub.]</span>, <span class="publicationyear">[n.d.]</span>)';
		}
	}

	/**
	 * formats the volume and page numbers for all publications in MHRA style
	 */
	public function format_volume_pages($journal_article = false)
	{
		$out = "";
		if ( isset($this->pub["volume"]) && trim($this->pub["volume"]) != "" ) {
			$out .= ', <span class="volume">' . trim(trim($this->pub["volume"]), ',');
			if ( $journal_article && isset($this->pub["issue"]) && trim($this->pub["issue"]) != "" ) {
				$out .= "." . trim($this->pub["issue"]);
			}
			$out .= '</span>';
		}
		if ( $journal_article && isset($this->pub["publicationyear"]) && trim($this->pub["publicationyear"]) !== "" ) {
			if ( isset($this->pub["volume"]) && trim($this->pub["volume"]) != "" ) {
				$out .= sprintf(' (%s)', trim(trim($this->pub["publicationyear"]), '()'));
			} else {
				$out .= sprintf(' %s', trim(trim($this->pub["publicationyear"]), '()'));
			}
		}
		if ( isset($this->pub["beginpage"]) && trim($this->pub["beginpage"]) != "" ) {
			$out .= ', <span class="pages">' . trim($this->pub["beginpage"]);
			if ( isset($this->pub["endpage"]) && trim($this->pub["endpage"]) != "" ) {
				$out .= '-' . trim($this->pub["endpage"]);
			}
			$out .= '</span>';
		}
		return $out;
	}
}

/**
 * default publication format
 */
class sp_apa_publication extends sp_publication
{
	public function format_Journalarticle()
	{
		$out = "<p>";
		$out .= self::format_basics();
		$out .= self::format_issue();
		$out .= self::format_status();
		$out .= "</p>";
		$out .= self::format_extras();
		return $out;
	}

	public function format_Conference()
	{
		$out = "<p>";
		$out .= self::format_basics();
		if (trim($this->pub["conferencename"]) != "") {
			$out .= '<span class="conferencename">' . trim($this->pub["conferencename"]) . '</span> ';
		}
		if (trim($this->pub["location"]) != "") {
			$out .= '<span class="location">(' . trim($this->pub["location"]) . ')</span> ';
		}
		if (isset($this->pub["startdate"]) && trim($this->pub["startdate"]) != "") {
			$out .= '<span class="conferencedate">' . self::format_date(trim($this->pub["startdate"]), true);
			if (isset($this->pub["finishdate"]) && trim($this->pub["finishdate"]) != "") {
				$out .= " - " . self::format_date(trim($this->pub["finishdate"]), true);
			}
			$out .= '</span> ';
		}
		if (isset($this->pub["publishedproceedings"]) && trim($this->pub["publishedproceedings"]) != "") {
			$out .= '<span class="publishedproceedings">Proceedings: ';
			if (strpos(trim($this->pub["publishedproceedings"]), 'http') === 0) {
				$out .= sprintf('<a href="%s">%s</a>', trim($this->pub["publishedproceedings"]), trim($this->pub["publishedproceedings"]));
			} else {
				$out .= trim($this->pub["publishedproceedings"]);
			}
			$out .= '</span> ';
		}
		$out .= self::format_publisher();
		$out .= self::format_issue();
		$out .= self::format_status();
		$out .= "</p>";
		$out .= self::format_extras();
		return $out;
	}

	public function format_Chapter()
	{
		$out = "<p>";
		$out .= self::format_basics();
		$out .= self::format_publisher();
		$out .= self::format_pages();
		$out .= self::format_status();
		$out .= "</p>";
		$out .= self::format_extras();
		return $out;
	}

	public function format_Other()
	{
		$out = "<p>";
		$out .= self::format_basics();
		$out .= self::format_publisher();
		$out .= self::format_issue();
		$out .= self::format_status();
		$out .= "</p>";
		$out .= self::format_extras();
		return $out;
	}

	public function format_Book()
	{
		$out = self::format_minimal();
		$out .= self::format_extras();
		return $out;
	}

	public function format_Internetpublication()
	{
		$out = self::format_minimal();
		$out .= self::format_extras();
		return $out;
	}

	public function format_Composition()
	{
		$out = self::format_musical();
		$out .= self::format_extras();
		return $out;
	}

	public function format_Performance()
	{
		$out = self::format_musical();
		$out .= self::format_extras();
		return $out;
	}

	public function format_Report()
	{
		$out = self::format_minimal();
		$out .= self::format_extras();
		return $out;
	}

	public function format_Artefact()
	{
		$out = "<p>";
		$out .= self::format_basics();
		$out .= self::format_artefact_publisher();
		$out .= self::format_status();
		$out .= "</p>";
		$out .= self::format_extras();
		return $out;
	}

	public function format_Design()
	{
		return self::format_minimal();
	}

	public function format_Scholarlyedition()
	{
		return self::format_minimal();
	}

	public function format_ThesisDissertation()
	{
		$out = "<p>";
		$out .= self::format_basics();
		if (isset($this->pub["fileddate"]) && trim($this->pub["fileddate"]) != "") {
			$out .= ' <span class="fileddate">' . self::format_date($this->pub["fileddate"], true) . '</span> ';
		}
		$out .= self::format_status();
		$out .= "</p>";
	}

	public function format_minimal()
	{
		$out = "<p>";
		$out .= self::format_basics();
		$out .= self::format_publisher();
		$out .= self::format_status();
		$out .= "</p>";
		return $out;
	}

	public function format_musical()
	{
		$out = "<p>";
		$out .= self::format_basics();
		$out .= self::format_publisher();
		if (isset($this->pub["medium"]) && trim($this->pub["medium"]) !== "") {
			$out .= '<span class="music-medium">' . trim($this->pub["medium"]) . '</span> ';
		}
		if (isset($this->pub["startdate"]) && trim($this->pub["startdate"]) != "") {
			$out .= '<span class="startdate">' . self::format_date(trim($this->pub["startdate"]), true) . '</span> ';
		}
		$out .= self::format_status();
		$out .= "</p>";
		return $out;
	}

	public function format_basics()
	{
		$out = "";
		if (trim($this->pub["authors"]) != "") {
			$out .= '<span class="authors">' . trim($this->pub["authors"]) . '</span> ';
		} else {
			if (isset($this->pub["editors"]) && trim($this->pub["editors"]) != "") {
				$out .= '<span class="authors">' . trim($this->pub["editors"]) . ' (eds.) </span>';
			}
		}
		if (isset($this->pub["publicationyear"]) && trim($this->pub["publicationyear"]) != "") {
			$out .= '<span class="publicationyear">(' . trim($this->pub["publicationyear"]) . ')</span> ';
		}
		if (trim($this->pub["title"]) != "") {
			if ((isset($this->pub["parenttitle"]) && trim($this->pub["parenttitle"]) !== "") || (isset($this->pub["journal"]) && trim($this->pub["journal"]) !== "")) {
				$class = "title-with-parent";
				$title = "&ldquo;" . trim($this->pub["title"]) . "&rdquo;";
			} else {
				$class = "title";
				$title = trim($this->pub["title"]);
			}
			$out .= '<span class="' . $class . '">' . $title;
		}
		if (isset($this->pub["parenttitle"]) && trim($this->pub["parenttitle"]) !== "") {
			$out .= ',</span> ';
			$out .= '<em>In:</em> ';
			if (isset($this->pub["editors"]) && trim($this->pub["editors"]) != "") {
				$out .= ' <span class="editors">' . trim($this->pub["editors"]) . ' (eds.)</span>';
			}
			$out .= ' <span class="parent-title">' . trim($this->pub["parenttitle"]);
		}
		if (isset($this->pub["journal"]) && trim($this->pub["journal"]) !== "") {
			$out .= ',</span> ';
			$out .= '<span class="journal">' . trim($this->pub["journal"]);
			if (isset($this->pub["editors"]) && trim($this->pub["editors"]) != "") {
				$out .= ' <span class="editors">' . trim($this->pub["editors"]) . ' (eds.)</span>';
			}
		}
		$out .= '.</span> ';
		if (isset($this->pub["edition"]) && trim($this->pub["edition"]) != "") {
			$sep = (substr(trim($this->pub["edition"]), -1) == ".")? "": ".";
			$out .= ' <span class="edition">' . trim($this->pub["edition"]) . $sep . '</span> ';
		}
		if (isset($this->pub["series"]) && trim($this->pub["series"]) != "") {
			$sep = (substr(trim($this->pub["series"]), -1) == ".")? "": ".";
			$out .= ' <span class="series">' . trim($this->pub["series"]) . $sep . '</span> ';
		}
		return $out;
	}

	public function format_publisher()
	{
		$out = "";
		if (isset($this->pub["placeofpublication"]) && trim($this->pub["placeofpublication"]) != "") {
			$sep = (substr(trim($this->pub["placeofpublication"]), -1) == ":")? "": ":";
			$out .= ' <span class="publish-place">' . trim($this->pub["placeofpublication"]) . $sep . '</span>';
		}
		if (isset($this->pub["publisher"]) && trim($this->pub["publisher"]) != "") {
			$sep = (substr(trim($this->pub["publisher"]), -1) == ".")? "": ".";
			if (isset($this->pub["publisherurl"]) && trim($this->pub["publisherurl"]) != "") {
				$out .= ' <span class="publisher"><a href="' . trim($this->pub["publisherurl"]) . '">' . trim($this->pub["publisher"]) . '</a>' . $sep . '</span>';
			} else {
				$out .= ' <span class="publisher">' . trim($this->pub["publisher"]) . $sep . '</span>';
			}
		}
		return $out;
	}

	/**
	 * artefacts do not contain a publisher field, so the Location field is used instead
	 * also, the medium field is inserted before the publisher
	 */
	public function format_artefact_publisher()
	{
		$out = "";
		if (isset($this->pub["medium"]) && trim($this->pub["medium"]) != "") {
			$sep = (substr(trim($this->pub["medium"]), -1) == ":")? "": ":";
			$out .= ' <span class="publish-medium">' . trim($this->pub["medium"]) . $sep . '</span>';
		}
		if (isset($this->pub["location"]) && trim($this->pub["location"]) != "") {
			$sep = (substr(trim($this->pub["location"]), -1) == ".")? "": ".";
			if (isset($this->pub["publisherurl"]) && trim($this->pub["publisherurl"]) != "") {
				$out .= ' <span class="publisher"><a href="' . trim($this->pub["publisherurl"]) . '">' . trim($this->pub["location"]) . '</a>' . $sep . '</span>';
			} else {
				$out .= ' <span class="publisher">' . trim($this->pub["location"]) . $sep . '</span>';
			}
		}
		return $out;
	}

	public function format_issue()
	{
		$out = "";
		if (trim($this->pub["volume"]) != "") {
			$out .= ' <span class="volume">' . trim($this->pub["volume"]) . '</span>';
		}
		if (trim($this->pub["issue"]) != "") {
			$issue .= (trim($this->pub["issue"], ' ().,');
			$out .= '(<span class="issue">' . $issue . '</span>)';
		}
		$pages = self::format_pages();
		if ($pages) {
			$out .= ': ' . $pages . '.';
		}
		return $out;
	}

	/**
	 * formats names of authors and editors
	 * names are separated by semicolons in symplectic
	 * up to six names are returned as a list, if more names are present, 
	 * only the first six are returned with " et al." appended to it (unless
	 * the $truncate parameter is false, which will force listing all names).
	 * @param string names as a string, separated by semicolons
	 * @param boolean whether to truncate lists of more than 6 names
	 */
	public function format_names($namestr, $truncate = true)
	{
		if ( trim($namestr) == "" ) {
			return "";
		}
		$names = array_map( 'trim', explode( ";", $namestr ) );
		if ( ! count($names) ) {
			return "";
		} elseif ( count($names) === 1 ) {
			return $names[0];
		} elseif ( count($names) === 2 ) {
			return sprintf("%s &amp; %s", $names[0], $names[1]);
		} elseif ( count($names) <= 6 ) {
			$lastname = array_pop($names);
			return sprintf("%s &amp; %s", implode(", ", $names), $lastname);
		} else {
			if ( true === $truncate ) {
				while ( count($names > 6) ) {
					array_pop($names);
				}
				return sprintf("%s et al.", implode(", ", $names));
			} else {
				$lastname = array_pop($names);
				return sprintf("%s &amp; %s", implode(", ", $names), $lastname);
			}
		}
	}
}

/**
 * default publication format
 */
class sp_default_publication extends sp_publication
{
	public function format_Journalarticle()
	{
		$out = "<p>";
		$out .= $this->format_basics();
		$out .= $this->format_issue();
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Conference()
	{
		$out = "<p>";
		$out .= $this->format_basics();
		if ( isset($this->pub["conferencename"]) && trim($this->pub["conferencename"]) != "" ) {
			$out .= '<span class="conferencename">' . trim($this->pub["conferencename"]) . '</span> ';
		}
		if ( isset($this->pub["location"]) && trim($this->pub["location"]) != "" ) {
			$out .= '<span class="location">(' . trim($this->pub["location"]) . ')</span> ';
		}
		if (isset($this->pub["startdate"]) && trim($this->pub["startdate"]) != "") {
			$out .= '<span class="conferencedate">' . $this->format_date(trim($this->pub["startdate"]), true);
			if (isset($this->pub["finishdate"]) && trim($this->pub["finishdate"]) != "") {
				$out .= " - " . $this->format_date(trim($this->pub["finishdate"]), true);
			}
			$out .= '</span> ';
		}
		if (isset($this->pub["publishedproceedings"]) && trim($this->pub["publishedproceedings"]) != "") {
			$out .= '<span class="publishedproceedings">Proceedings: ';
			if (strpos(trim($this->pub["publishedproceedings"]), 'http') === 0) {
				$out .= sprintf('<a href="%s">%s</a>', trim($this->pub["publishedproceedings"]), trim($this->pub["publishedproceedings"]));
			} else {
				$out .= trim($this->pub["publishedproceedings"]);
			}
			$out .= '</span> ';
		}
		$out .= $this->format_publisher();
		$out .= $this->format_issue();
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Chapter()
	{
		$out = "<p>";
		$out .= $this->format_basics();
		$out .= $this->format_publisher();
		$out .= $this->format_pages();
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Other()
	{
		$out = "<p>";
		$out .= $this->format_basics();
		$out .= $this->format_publisher();
		$out .= $this->format_issue();
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Book()
	{
		$out = $this->format_minimal();
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Internetpublication()
	{
		$out = $this->format_minimal();
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Composition()
	{
		$out = $this->format_musical();
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Performance()
	{
		$out = $this->format_musical();
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Report()
	{
		$out = $this->format_minimal();
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Artefact()
	{
		$out = "<p>";
		$out .= $this->format_basics();
		$out .= $this->format_artefact_publisher();
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_Design()
	{
		return $this->format_minimal();
	}

	public function format_Scholarlyedition()
	{
		return $this->format_minimal();
	}

	public function format_ThesisDissertation()
	{
		$out = "<p>";
		$out .= $this->format_basics();
		if (isset($this->pub["fileddate"]) && trim($this->pub["fileddate"]) != "") {
			$out .= ' <span class="fileddate">' . $this->format_date($this->pub["fileddate"], true) . '</span> ';
		}
		$out .= $this->format_status();
		$out .= "</p>";
		$out .= $this->format_extras();
		return $out;
	}

	public function format_minimal()
	{
		$out = "<p>";
		$out .= $this->format_basics();
		$out .= $this->format_publisher();
		$out .= $this->format_status();
		$out .= "</p>";
		return $out;
	}

	public function format_musical()
	{
		$out = "<p>";
		$out .= $this->format_basics();
		$out .= $this->format_publisher();
		if (isset($this->pub["medium"]) && trim($this->pub["medium"]) !== "") {
			$out .= '<span class="music-medium">' . trim($this->pub["medium"]) . '</span> ';
		}
		if (isset($this->pub["startdate"]) && trim($this->pub["startdate"]) != "") {
			$out .= '<span class="startdate">' . $this->format_date(trim($this->pub["startdate"]), true) . '</span> ';
		}
		$out .= $this->format_status();
		$out .= "</p>";
		return $out;
	}

	public function format_basics()
	{
		$out = "";
		if ( isset($this->pub["authors"]) && trim($this->pub["authors"]) != "") {
			$out .= '<span class="authors">' . trim($this->pub["authors"]) . '</span> ';
		} else {
			if (isset($this->pub["editors"]) && trim($this->pub["editors"]) != "") {
				$out .= '<span class="authors">' . trim($this->pub["editors"]) . ' (eds.) </span>';
			}
		}
		if (isset($this->pub["publicationyear"]) && trim($this->pub["publicationyear"]) != "") {
			$out .= '<span class="publicationyear">(' . trim($this->pub["publicationyear"]) . ')</span> ';
		}
		if (trim($this->pub["title"]) != "") {
			if ((isset($this->pub["parenttitle"]) && trim($this->pub["parenttitle"]) !== "") || (isset($this->pub["journal"]) && trim($this->pub["journal"]) !== "")) {
				$class = "title-with-parent";
				$title = "&ldquo;" . trim($this->pub["title"]) . "&rdquo;";
			} else {
				$class = "title";
				$title = trim($this->pub["title"]);
			}
			$out .= '<span class="' . $class . '">' . $title;
		}
		if (isset($this->pub["parenttitle"]) && trim($this->pub["parenttitle"]) !== "") {
			$out .= ',</span> ';
			$out .= '<em>In:</em> ';
			if (isset($this->pub["editors"]) && trim($this->pub["editors"]) != "") {
				$out .= ' <span class="editors">' . trim($this->pub["editors"]) . ' (eds.)</span>';
			}
			$out .= ' <span class="parent-title">' . trim($this->pub["parenttitle"]);
		}
		if (isset($this->pub["journal"]) && trim($this->pub["journal"]) !== "") {
			$out .= ',</span> ';
			$out .= '<span class="journal">' . trim($this->pub["journal"]);
			if (isset($this->pub["editors"]) && trim($this->pub["editors"]) != "") {
				$out .= ' <span class="editors">' . trim($this->pub["editors"]) . ' (eds.)</span>';
			}
		}
		$out .= '.</span> ';
		if (isset($this->pub["edition"]) && trim($this->pub["edition"]) != "") {
			$sep = (substr(trim($this->pub["edition"]), -1) == ".")? "": ".";
			$out .= ' <span class="edition">' . trim($this->pub["edition"]) . $sep . '</span> ';
		}
		if (isset($this->pub["series"]) && trim($this->pub["series"]) != "") {
			$sep = (substr(trim($this->pub["series"]), -1) == ".")? "": ".";
			$out .= ' <span class="series">' . trim($this->pub["series"]) . $sep . '</span> ';
		}
		return $out;
	}

	public function format_publisher()
	{
		$out = "";
		if (isset($this->pub["placeofpublication"]) && trim($this->pub["placeofpublication"]) != "") {
			$sep = (substr(trim($this->pub["placeofpublication"]), -1) == ":")? "": ":";
			$out .= ' <span class="publish-place">' . trim($this->pub["placeofpublication"]) . $sep . '</span>';
		}
		if (isset($this->pub["publisher"]) && trim($this->pub["publisher"]) != "") {
			$sep = (substr(trim($this->pub["publisher"]), -1) == ".")? "": ".";
			if (isset($this->pub["publisherurl"]) && trim($this->pub["publisherurl"]) != "") {
				$out .= ' <span class="publisher"><a href="' . trim($this->pub["publisherurl"]) . '">' . trim($this->pub["publisher"]) . '</a>' . $sep . '</span>';
			} else {
				$out .= ' <span class="publisher">' . trim($this->pub["publisher"]) . $sep . '</span>';
			}
		}
		return $out;
	}

	/**
	 * artefacts do not contain a publisher field, so the Location field is used instead
	 * also, the medium field is inserted before the publisher
	 */
	public function format_artefact_publisher()
	{
		$out = "";
		if (isset($this->pub["medium"]) && trim($this->pub["medium"]) != "") {
			$sep = (substr(trim($this->pub["medium"]), -1) == ":")? "": ":";
			$out .= ' <span class="publish-medium">' . trim($this->pub["medium"]) . $sep . '</span>';
		}
		if (isset($this->pub["location"]) && trim($this->pub["location"]) != "") {
			$sep = (substr(trim($this->pub["location"]), -1) == ".")? "": ".";
			if (isset($this->pub["publisherurl"]) && trim($this->pub["publisherurl"]) != "") {
				$out .= ' <span class="publisher"><a href="' . trim($this->pub["publisherurl"]) . '">' . trim($this->pub["location"]) . '</a>' . $sep . '</span>';
			} else {
				$out .= ' <span class="publisher">' . trim($this->pub["location"]) . $sep . '</span>';
			}
		}
		return $out;
	}

	public function format_pages()
	{
		$out = "";
		if ( isset($this->pub["beginpage"]) && trim($this->pub["beginpage"]) != "" ) {
			$out .= ' <span class="pages">' . trim($this->pub["beginpage"]);
			if ( isset($this->pub["endpage"]) && trim($this->pub["endpage"]) != "" ) {
				$out .= '-' . trim($this->pub["endpage"]);
			} else {
				$out .= "+";
			}
			$out .= '</span>';
		}
		return $out;
	}

	public function format_issue()
	{
		$out = "";
		if ( isset($this->pub["volume"]) && trim($this->pub["volume"]) != "" ) {
			$out .= ' <span class="volume">' . trim($this->pub["volume"]) . '</span>';
		}
		if ( isset($this->pub["issue"]) && trim($this->pub["issue"]) != "" ) {
			$out .= ( isset($this->pub["volume"]) && trim($this->pub["volume"]) != "" )? ".": "";
			$out .= '<span class="issue">' . trim($this->pub["issue"]) . '</span>';
		}
		$pages = $this->format_pages();
		if ($pages) {
			$out .= ': ' . $pages . '.';
		}
		return $out;
	}

	public function format_status()
	{
		$out = "";
		if (isset($this->pub["status"]) && $this->pub["status"] != "Published" && $this->pub["status"] != "" && strtolower($this->pub["status"]) != "null") {
			$out .= ' <span class="publish-status">[' . $this->pub["status"] . ']</span>';
		}
		return $out;
	}

	public function format_extras()
	{
		$out = "";
		if ($this->display["notes"] && isset($this->pub["notes"]) && trim($this->pub["notes"]) != "") {
			$out .= '<p class="notes">' . trim($this->pub["notes"]) . '</p>';
		}
		if ($this->display["authorurl"] && isset($this->pub["authorurl"]) && trim($this->pub["authorurl"]) != "") {
			$parsed = parse_url(trim($this->pub["authorurl"]));
			if ($parsed !== false && isset($parsed["host"]) && trim($parsed["host"]) != "") {
				$out .= '<p class="authorurl"><a href="' . trim($this->pub["authorurl"]) . '">Author URL [' . trim($parsed["host"]) . ']</a></p>';				
			}
		}
		if ($this->display["repositoryurl"] && isset($this->pub["repositoryurl"]) && trim($this->pub["repositoryurl"]) != "") {
			$parsed = parse_url(trim($this->pub["repositoryurl"]));
			if ($parsed !== false && isset($parsed["host"]) && trim($parsed["host"]) != "") {
				$out .= '<p class="repositoryurl"><a href="' . trim($this->pub["repositoryurl"]) . '">Repository URL [' . trim($parsed["host"]) . ']</a></p>';
			}
		}
		if ($this->display["abstract"] && isset($this->pub["abstract"]) && trim($this->pub["abstract"]) != "") {
			if ( isset ($this->pub['medium']) && $this->pub["medium"] == "CD") {
				$out .= '<p><strong>Track List</strong></p><ol>';
				$out .= substr(preg_replace('/[0-9]+\. /', '</li><li style="list-style:decimal outside;margin:.5em 0 0 2em;">', $this->pub["abstract"]), 5) . '</li></ol>';
			} else {
				$out .= '<p class="abstract">' . trim($this->pub["abstract"]) . '</p>';
			}
		}
		return $out;
	}

	public function format_date($date, $fromtime = false)
	{
		$year = substr($date, 0, 4);
		$month = substr($date, 5, 2);
		$day = substr($date, 8, 2);
		if ($fromtime && (mktime(1, 1, 1, $month, $day, $year) !== false)) {
			return (date("j M. Y", mktime(1, 1, 1, $month, $day, $year)));
		}
		return $day . "/" . $month . "/" . $year;
	}

}