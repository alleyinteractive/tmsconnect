<?php
/**
 * This file contains all helper functions for getting specific post level data.
 *
 * @package Freer_Sackler
 */

/**
 * Get an image caption from an attachment.
 *
 * @param  WP_Post|int $attachment The attachment ID or post object.
 * @return string                  The attachment image caption.
 */
function tmsc_get_image_public_caption( $attachment ) : string {
	$attachment = get_post( $attachment );

	// The public image caption is saved as post content.
	if ( $attachment instanceof WP_Post ) {
		return $attachment->post_content;
	}

	return '';
}

/* Element appears on a per-image basis */
function tms_helper_image_mediaview() {
	return '';
}

/* Element appears on a per-image basis */
function tms_helper_image_makers() {
	return '';
}

/* Element appears on a per-image basis */
function tms_helper_image_keywords() {
	return '';
}

function tms_helper_type() {
	return '<a href="#">Manuscript</a>';
}

function tms_helper_maker() {
	return '<strong>Calligrapher:</strong> <a href="#">Ahmad Sayri</a>';
}

function tms_helper_time_period() {
	return '<a href="#">Safavid period</a>, <a href="#">1598 (1006 A.H.)</a>';
}

/**
 * Get a post's movement.
 *
 * @param  int    $post The post ID.
 * @return string       The post movement.
 */
function tmsc_get_movement( $post_id ) {
	return get_post_meta( $post_id, 'movement', true );
}

/**
 * Get a post's school.
 *
 * @param  int    $post The post ID
 * @return string       The post school.
 */
function tmsc_get_school( $post_id ) {
	return get_post_meta( $post_id, 'school', true );
}

/**
 * Get a post's medium.
 *
 * @param  int    $post The post ID.
 * @return string       The post medium.
 */
function tmsc_get_medium( $post_id ) {
	return get_post_meta( $post_id, 'medium', true );
}

function tms_helper_style() {
	return '';
}

/**
 * Get a post's dimensions.
 *
 * @param  int    $post The post ID.
 * @return string       The post dimensions.
 */
function tmsc_get_dimensions( $post_id ) {
	return get_post_meta( $post_id, 'dimensions', true );
}

function tms_helper_georgraphy() {
	return '<a href="#">Iran</a>';
}

function tms_helper_notes() {
	return '';
}

function tms_helper_creditline() {
	return 'Purchase &mdash; Charles Lang Freer Endowment';
}

function tms_helper_collection() {
	return '<a href="#">Freer Gallery of Art</a>';
}

/**
 * Get a post's accession number.
 *
 * @param  int    $post The post ID.
 * @return string       The post accession number.
 */
function tmsc_get_accession_number( $post_id ) {
	return get_post_meta( $post_id, 'accession_number', true );
}

function tms_helper_jades_captions() {
	return '';
}

/**
 * Get a post's location.
 *
 * @param  int    $post The post ID.
 * @return string       The post location.
 */
function tmsc_get_location( $post_id ) {
	return get_post_meta( $post_id, 'location', true );
}

function tms_helper_classifications() {
	return '<a href="#">Manuscript</a>';
}

function tms_helper_keywords() {
	return '<a href="#">illumination</a>, <a href="#">Iran</a>, <a href="#">Islam</a>, <a href="#">muhaqqaq script</a>, <a href="#">naskh script</a>, <a href="#">nasta\'liq script</a>, <a href="#">Qur\'an</a>, <a href="#">Safavid period (1501 - 1722</a>, <a href="#">thuluth script</a>';
}

/**
 * Get a post's provenance.
 *
 * @param  int    $post The post ID.
 * @return string       The post provenance.
 */
function tmsc_get_provenance( $post_id ) {
	return get_post_meta( $post_id, 'provenance', true );
}

function tms_helper_constituent() {
	'<a href="#">Hagop Kevorkian (1872 - 1962)</a>';
}

function tms_helper_description() {
	// Note: when the description exists, part of it is also copied above the slideshow, below the item name
	return '<p>Manuscript; the Qur\'an with selection of prayers and a falname; Arabic in black naskh script with white headings in illuminated cartouches in thuluth, muhaqqaq, and nasta\'liq script; vocalized in black; 288 folios with 2 shamsa (1 verso, 2 recto) a frontispiece (2 verso, 3 recto), a sarlawh (3 verso), and 3 finispiece (287 verso, 288 recto/verso); inscriptions (fols. 12 recto, 204 recto); rosette verse markers; gold roundels; marginal medallions, inscribed marginal medallions containing the word "ashr" (ten) indicating the end of a tenth verse and the word "juz" (part); standard page: one column; 15 lines of text. Bound folios F1932.66-70 of the same manuscript are accessioned separately.<br>
		Border: The manuscript is bound in modern red leather over paper-pasteboards.</p>';
}

function tms_helper_inscriptions() {
	return '<p>Fol. 12 recto, waqf Ghara Mustafa Pasha.<br>
			"Endowments of Ghara Mustafa Pasha."<br>
			Fol. 204 recto, waqf<br>
			"Inalienable donation."</p>';
}

function tms_helper_signatures() {
	return '';
}

function tms_helper_markings() {
	return '';
}

function tms_helper_label() {
	return '';
}

function tms_helper_past_label() {
	return '<p>1. (Massumeh Farhad, "Arts of the Islamic World", 31 August 2000-5 March 2001)</p>

			<p>Double-page in a Koran<br>
			Iran<br>
			Safavid period, dated 1598<br>
			Ink, opaque watercolor and gold on paper<br>
			Purchase F1932.65.563-.564</p>

			<p>The central role of the Koran in Islam meant that no effort was spared to enhance the visual beauty of the text. This Koran, written in elegant naskh script, is illuminated throughout with elaborate motifs in gold and brilliant blue, derived from powdered lapis lazuli. The folios on view are inscribed with the concluding chapters of the Koran and a colophon signed by the scribe Ahmad Sayri, stating he has completed the manuscript on the tenth day of the month of Jumada II, a.h. 1006, which corresponds to January 18, 1598.</p>

			<p>2. (Massumeh Farhad, Islamic Rotation, 3/18/2001 - 9/17/2001)</p>

			<p>Two pages from a Koran<br>
			Suras 106-13<br>
			Iran, dated 1598<br>
			Ink, opaque watercolor, and gold on paper<br>
			Purchase F1932.65.561-562</p>

			<p>The Koran comprises 114 chapters (sura) revealed to the Prophet Muhammad by the archangel Gabriel at Mecca and Medina in present-day Saudi Arabia. Copied only after the Prophet\'s death in 632, the chapters appear in descending order of length and are divided into thirty relatively equal sections (juz). These sections correspond to one day\'s reading during the month of Ramadan, the month of fasting, when the entire text is recited at the mosque. The Koran is further organized into sevens to correspond to the days of the week. These divisions are usually marked by elaborate, illuminated medallions in the margins. Within the text, verse endings are also indicated by illuminated motifs, while new chapter headings are frequently set within elaborate designs in gold and lapis lazuli.</p>

			<p>The short chapters here appear toward the end of the Koran and are believed to have been revealed at Mecca. As is typical of chapter headings throughout the Koran, the titles here, such as "al-Kawthar" (The Abundance) or "al-Kafirun" (The Disbeliever) are taken from words in the verses.</p>

			<p>3. (Massumeh Farhad, "Arts of the Islamic World", 30 September 2001-10 March 2002)</p>

			<p>Two folios in a Koran<br>
			Copied by Ahmad Sayri<br>
			Iran, dated 1598<br>
			Ink, opaque watercolor, and gold on paper<br>
			Purchase F1932.65.559-.560</p>

			<p>The Koran consists of 114 chapters (sura) revealed to the Prophet Muhammad by the archangel Gabriel at Mecca and Medina in present-day Saudi Arabia. Initially, the divine message was transmitted orally and was only written down after Muhammad\'s death in 632. Koran chapters appear in descending order of length and are divided into thirty relatively equal sections (juz). These sections correspond to one day\'s reading in the month of Ramadan, the month of fasting, when the entire text is recited at the mosque. The Koran is further organized into sevens to correspond to the days of the week.</p>

			<p>In this fine manuscript from sixteenth-century Iran, lavishly painted medallions in the margins mark the text divisions. Within the body of the text, verse endings are indicated with illuminated motifs, while chapter headings are set within elaborate designs painted in gold and powdered lapis lazuli, a semiprecious stone.</p>


			<p>4. (Massumeh Farhad, "Arts of the Islamic World", 19 March 2002 - 29 September 2002)</p>

			<p>Double-folio in a Koran<br>
			Copied by Ahmad al-Sayri<br>
			Iran, dated 1598<br>
			Ink, opaque watercolor, and gold on paper<br>
			Purchase F1932.65.57-.58</p>

			<p>The Koran consists of 114 chapters (sura) revealed to the Prophet Muhammad by the archangel Gabriel at Mecca and Medina in present-day Saudi Arabia. Initially, the divine message was transmitted orally and was only transcribed in its entirety after Muhammad\'s death in 632. Koran chapters appear in descending order of length and are divided into thirty relatively equal sections (juz). These sections correspond to one day\'s reading in Ramadan, the month of fasting, when the entire text is recited at the mosque. The Koran is further organized into sevenths to correspond to the days of the week.</p>

			<p>In this manuscript from sixteenth-century Iran, lavishly painted medallions in the margins mark the text divisions. Within the body of the text, verse endings are indicated with illuminated motifs, while chapter headings are set within elaborate designs painted in gold and powdered lapis lazuli, a semiprecious stone.</p>

			<p>5. (Massumeh Farhad, Exhibition label for "Arts of the Islamic World", Freer Gallery of Art, Smithsonian Institution, Gallery 3, 23 September 2002 - 11 May 2003</p>

			<p>Double page in a Koran<br>
			Suras 96 and 97<br>
			Iran, dated 1598<br>
			Opaque watercolor, ink, and gold on paper<br>
			Purchase F1932.65.555-.556a</p>

			<p>The Koran consists of 114 chapters (sura) revealed to the Prophet Muhammad by the archangel Gabriel at Mecca and Medina in present-day Saudi Arabia. Initially, the divine message was transmitted orally and was only transcribed in its entirety after Muhammad\'s death in 632. Koran chapters appear in descending order of length and are divided into thirty relatively equal sections (juz). These sections correspond to one day\'s reading in Ramadan, the month of fasting, when the entire text is recited at the mosque. The Koran is further organized into sevenths to correspond to the days of the week.</p>

			<p>In this fine manuscript from sixteenth-century Iran, lavishly painted medallions in the margins mark the text divisions. Within the body of the text, verse endings are indicated with illuminated motifs, while chapter headings are set within elaborate designs painted in gold and powdered lapis lazuli, a semiprecious stone.</p>

			<p>6. (Massumeh Farhad, exhibition label, "Arts of the Islamic World", 25 May 2003 - 21 December 2003)</p>

			<p>Double folio in a Koran<br>
			Sura 90, verse 17-sura 94, verse 8<br>
			Copied by Ahmad Sayri<br>
			Iran, dated 1598<br>
			Opaque watercolor, ink, and gold on paper<br>
			Purchase F1932.65, folios 553b-554a</p>

			<p>The Koran (also spelled Qur\'an) consists of 114 suras (chapters) revealed to the Prophet Muhammad by the archangel Gabriel at Mecca and Medina in present-day Saudi Arabia. Initially, the divine message was transmitted orally and was completely written down only after Muhammad\'s death in 632. Derived from the Arabic word "to recite," the Koran is a text that is not only copied, but also read aloud. Koran chapters appear in descending order of length and are divided into thirty relatively equal sections (juz), with titles derived from words and terms from the verses. Each section corresponds to one day\'s reading in the month of Ramadan, the month of fasting, when the entire text is recited at the mosque. The Koran is further organized into sevens to correspond to the days of the week.<p>

			<p>In this fine manuscript from sixteenth-century Iran, lavishly painted medallions in the margins mark the text divisions. Within the body of the text, verse endings are indicated with illuminated motifs, while chapter headings are set within elaborate designs painted in gold and powdered lapis lazuli.</p>

			<p>7. (Massumeh Farhad, "Arts of the Islamic World", January 2004 - 5 July 2004)</p>

			<p>Double folio in a Koran<br>
			Sura 88:20-26; sura 89; sura 90:1-16<br>
			Iran, dated 1598<br>
			Ink, opaque watercolor, and gold on paper<br>
			Purchase F1932.65</p>

			<p>The Koran (also spelled Qur\'an) consists of 114 chapters (suras) revealed to the Prophet Muhammad by the archangel Gabriel at Mecca and Medina in present-day Saudi Arabia. Initially, the divine message was transmitted orally and was only completely written down after Muhammad\'s death in 632. Koran chapters appear in descending order of length and are divided into thirty relatively equal sections (juz), and its titles are derived from words and terms in the verses. These sections correspond to one day\'s reading during Ramadan, the month of fasting, when the entire text is recited at the mosque. The Koran is further organized into sevenths to correspond to the days of the week.</p>

			<p>In this fine manuscript from sixteenth-century Iran, lavishly painted medallions in the margins mark the text divisions. Within the body of the text, verse endings are indicated with illuminated motifs, while chapter headings are set within elaborate designs painted in gold and powdered lapis lazuli, a semiprecious stone.</p>

			<p>8. (Massumeh Farhad, "Arts of the Islamic World", Freer Gallery of Art, Smithsonian Insititution, 15 January 2005 - 10 July 2005.)</p>

			<p>Double page in a Koran<br>
			Suras 86-88: 1-20<br>
			Iran, dated 1598<br>
			Ink, opaque watercolor, and gold on paper<br>
			Purchase	F1932.65, folios 549b-550a</p>

			<p>The Koran consists of 114 chapters (suras) revealed to the Prophet Muhammad by the archangel Gabriel at Mecca and Medina in present-day Saudi Arabia. The chapter titles-such as "The Cattle," "The Repentance," "The Prophet Abraham," and "Mary"-were inspired by terms and concepts mentioned in the chapters. Initially the divine message was transmitted orally; it was recorded in its entirety only after Muhammad\'s death, in 632. Koranic chapters appear in descending order of length and are divided into thirty relatively equal sections (juz). These sections correspond to one day\'s reading in the month of Ramadan, the month of fasting, when the entire text is recited at the mosque. The Koran is further organized into sevenths to correspond to the days of the week.</p>

			<p>In this fine manuscript from sixteenth-century Iran, lavishly painted medallions in the margins mark the text divisions, while within the body of the text, illuminated circular motifs indicate the verse endings. The chapter headings, written in white, are set within elaborate designs painted in gold and powdered lapis lazuli, a semiprecious stone.</p>

			<p>9. ("Arts of the Islamic World", Freer Gallery of Art, Smithsonian Institution, Gallery 3/4, July 2005)</p>

			<p>Double page in a Koran<br>
			copied by Ahmad Sayri<br>
			Iran, Safavid dynasty, dated 1598<br>
			Ink, opaque watercolor, and gold on paper<br>
			Purchase F1932.65</p>

			<p>The Koran comprises 114 chapters, called sura, revealed to the Prophet Muhammad by the Archangel Gabriel at Mecca and Medina in present-day Saudi Arabia. Copied after the Prophet\'s death in 632, the chapters appear in descending order of length and are divided into thirty relatively equal sections called juz-corresponding to one day\'s reading during the month of Ramadan, the month of fasting. The Koran is additionally organized into sevens to correspond to the days of the week. Major divisions are usually marked by elaborate, illuminated medallions in the margins. Verse endings are also indicated by illuminated motifs, and new chapter headings are frequently set within elaborate designs in gold and lapis lazuli.</p>

			<p>10. (Massumeh Farhad, "Arts of the Islamic World", 11 March 2006 - 10 September 2006.)</p>

			<p>Double folio in a Koran<br>
			Copied by Ahmad al-Sayri<br>
			Iran, dated 1598<br>
			Ink, opaque watercolor, and gold on paper<br>
			Purchase F1932.65</p>

			<p>The Koran consists of 114 chapters (sura) revealed to the Prophet Muhammad by the archangel Gabriel at Mecca and Medina in present-day Saudi Arabia. Initially, the divine message was transmitted orally and only transcribed in its entirety after Muhammad\'s death in 632. Koran chapters appear in descending order of length and are divided into thirty relatively equal sections (juz). These sections correspond to one day\'s reading in Ramadan, the month of fasting, when the entire text of the Koran is recited at the mosque.</p>

			<p>In this manuscript from sixteenth-century Iran, lavishly painted medallions in the margins mark the text divisions. Within the body of the text, verse endings are indicated with illuminated motifs, while chapter headings are set within elaborate designs painted in gold and powdered lapis lazuli, a semiprecious stone.</p>

			<p>11. (Massumeh Farhad and Simon Rettig, "The Art of the Qur’an: Treasures from the Museum of Turkish and Islamic Arts" October 22, 2016 to February 20, 2017)</p>

			<p>Illuminated Prayers<br>
			Little is known about Ahmad Sayri, another accomplished sixteenth-century calligrapher from Shiraz. He was responsible for several finely illuminated manuscripts, including the large Qur’an at the beginning of this exhibition.</p>

			<p>This single volume follows the traditional format of other Qur’ans from Shiraz and closes with an intricately designed folio of illuminated prayers. Readers often recited these prayers, known as du’a al-khatim, at the conclusion of the Qur’an. Since they are not integral to the Holy Text, the prayers are different in both style and format. In contrast to the rest of the Qur’an, they are transcribed in white thuluth script and separated in richly decorated bands.</p>

			<p>Single-volume Qur’an<br>
			Copied by Ahmad Sayri<br>
			Iran, Safavid period, dated 1598 (AH 1006)<br>
			Ink, color, and gold on paper<br>
			Purchase—Charles Lang Freer Endowment<br>
			Freer Gallery of Art, F1932.65, folios 285b–286a</p>';
}

function tms_helper_jades_curatorial_remarks() {
	return '';
}

function tms_helper_unpublished_research() {
	return '';
}

function tms_helper_bibliography() {
	return '';
}

function tms_helper_rightsstatement() {
	return 'Copyright with museum';
}


/**
 * Helper function for slideshow/gallery
 */

/**
 * Slideshow directory - bool
 *
 * true = RTL
 * false = LTR
 */
function tms_helper_slideshow_rtl() {
	return true;
}


/**
 * Slide to start the slideshow on
 */
function tms_helper_initial_slide() {
	return 0;
}


/**
 * Rights type - whether or not the image should have a download link
 *
 * Note: Appears to be stored as a number; I'm not sure what they mean, though, so I stuck with a simple boolean.
 */
function tms_helper_rights_type() {
	return true;
}


/**
 * Media Type
 *
 * Imitating this, which is used to pick what's output for the slideshow: https://github.com/FreerSackler/pattern-library/blob/master/new-templates-from-freer-sackler/object.php#L270-L276
 */

function tms_helper_media_type() {
	return 'Image';
}

function tms_helper_media_view() {
	return 'group view';
}


/**
 * Slideshow images - contains an array of the TMS object image IDs and titles.
 * In single-tms_object.php, it's looped through to recreate the original markup from the Object-Template example
 *
 * To emulate the Object-Template example, some images have a 'data-media-master-id' value
 */
function tms_helper_images() {
	$tms_images = array(
		0 => array(
			'id'                   => 'F1932.65_folio562',
			'filename'             => 'filename1',
			'title'                => '',
			'data-primary-display' => '0',
			'media-master-id'      => '',
		),
		1 => array(
			'id'                   => 'F1932.65_folio561',
			'filename'             => 'filename2',
			'title'                => '',
			'data-primary-display' => '0',
			'media-master-id'      => '',
		),
		2 => array(
			'id'                   => 'F1932.65_folio567',
			'filename'             => 'filename3',
			'title'                => '',
			'data-primary-display' => '0',
			'media-master-id'      => '',
		),
		3 => array(
			'id'                   => 'F1932.65_folio566',
			'filename'             => 'filename4',
			'title'                => '',
			'data-primary-display' => '0',
			'media-master-id'      => '',
		),
		4 => array(
			'id'                   => 'F1932.65_203a202b',
			'filename'             => 'filename5',
			'title'                => '',
			'data-primary-display' => '0',
			'media-master-id'      => '',
		),
		5 => array(
			'id'                   => 'F1932.65_285b286a',
			'filename'             => 'filename6',
			'title'                => '',
			'data-primary-display' => '0',
			'media-master-id'      => '',
		),
		6 => array(
			'id'                   => 'F1932.65_001b002a',
			'filename'             => 'filename7',
			'title'                => '',
			'data-primary-display' => '0',
			'media-master-id'      => '',
		),
	);
	return $tms_images;
}
