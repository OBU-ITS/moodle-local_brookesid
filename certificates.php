<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Brookes ID - Certificates
 *
 * @package    local_brookesid
 * @copyright  2017, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once('../../config.php');
require_once($CFG->libdir . '/pdflib.php');
require_once('./db_update.php');
require_once('./certificates_form.php');


require_login();
$context = context_system::instance();
require_capability('local/brookesid:admin', $context);

$home = new moodle_url('/');
$url = $home . 'local/brookesid/index.php';

$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('certificates', 'local_brookesid'));

$message = '';

$mform = new certificates_form(null, array());

if ($mform->is_cancelled()) {
    redirect($home);
} else if ($mform_data = $mform->get_data()) {
	$achievements = get_achievements($mform_data->date_from, $mform_data->date_to); // Get all selected achievements
	if (empty($achievements)) {
		$message = get_string('no_achievements', 'local_brookesid');
	} else {
		$pdf = new pdf();
	
		$pdf->SetTitle('Achievements certificates');
		$pdf->SetAuthor('Moodle ' . $CFG->release);
		$pdf->SetCreator('Moodle');
		$pdf->SetKeywords('BrookesID, PDF, badges, achievements, co-curricular activities');
		$pdf->SetSubject('Certificates generated by Moodle');
		$pdf->SetMargins(15, 15);
		
		$pdf->setPrintHeader(false);
		$pdf->setHeaderMargin(0);
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, 'b', 10));
		$pdf->setHeaderData('', 0);
		$pdf->setPrintFooter(false);
		$pdf->setFooterMargin(10);
		$pdf->setFooterFont(array(PDF_FONT_NAME_MAIN, '', 8));

		$studentnumber = 0;
		$contact = array();
		$categories = array();
		$badges = array();

		foreach ($achievements as $achievement) {
			if ($achievement->studentnumber != $studentnumber) {
				output($pdf, $studentnumber, $contact, $categories, $badges);
				$studentnumber = $achievement->studentnumber;
				$contact = array('firstname' => $achievement->firstname, 'lastname' => $achievement->lastname, 'email' => $achievement->email);
				$categories = array('confCount' => 0, 'entcCount' => 0, 'connCount' => 0, 'gensCount' => 0);
				$badges = array();
			}

			switch ($achievement->category) {
				case 'CONF':
					$categories['confCount'] += 1;
					break;
				case 'ENTC':
					$categories['entcCount'] += 1;
					break;
				case 'CONN':
					$categories['connCount'] += 1;
					break;
				case 'GENS':
					$categories['gensCount'] += 1;
					break;
				default:
					break;
			}

			$badges[] = array(
				'badge_category' => $achievement->category,
				'dateissued' => $achievement->dateissued,
				'badgename' => $achievement->badge_name,
				'badgedescription' => $achievement->badge_description
			);
		}
		output($pdf, $studentnumber, $contact, $categories, $badges); // Clear the chamber
		$pdf->Output('certificates_' . date('d-m-Y', $mform_data->date_from) . '_' . date('d-m-Y', $mform_data->date_to) . '.pdf');
		
		exit();
	}	 
}

echo $OUTPUT->header();
echo '<img src="/pix/brookesid-logo.png" width="120" alt="BrookesID" style="float: right"/>';

if ($message) {
    notice($message, $url);    
}
else {
    $mform->display();
}

echo $OUTPUT->footer();

exit();


function output($pdf, $studentnumber, $contact, $categories, $badges) {

	if ($studentnumber == 0) { // Wait for it...
		return;
	}
	/* award certificate */
	
		
	$pdf->SetTextColor(255,255,255);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetFont('helvetica', 'R', 24);
	$pdf->Cell(0, 0, 'BrookesID Certificate', 0, 1, 'C', 1);
		
	$pdf->SetFont('helvetica', 'R', 12);
	$pdf->Ln(6);
	$pdf->SetTextColor(0,0,0);
	
	$c = '';
	$c .= '<p style="text-align: center"><img src="/pix/logo-brookes.png" align="center" width="100"><br>';
	$c .= '<img src="/pix/brookesid-logo.png" align="center" width="100"></p>';
	$c .= '<h2 align="center">Certificate</h2>';
	
	$c .= '<p align="center">Awarded to</p>';
	$c .= '<h1 align="center">'. $contact['firstname'] . ' ' . $contact['lastname'] .'</h1>';
	$c .= '<p>&nbsp;</p>';
	$c .= '<table border="0" cellspacing="5" cellpadding="5">';
	// row preceded by spacer cell
	$c .= '<tr><td>&nbsp;</td><td align="center" style="border: 1px solid #f49103"><h5>Confidence</h5>';
		if ($categories['confCount'] == 0) {
			$c .= '<img src="/pix/spacer.gif" width="75">';
		}
		if ($categories['confCount'] == 1) {
			$c .= '<h1><br> 1 </h1>achievement';
		}
		if ($categories['confCount'] == 2) {
			$c .= '<img src="/pix/bronze.png" width="75"><br>';
			$c .= 'Bronze award';
		}
		if ($categories['confCount'] >= 3 || $categories['confCount'] == 4) {
			$c .= '<img src="/pix/silver.png" width="75"><br>';
			$c .= 'Silver award';
		}
		if ($categories['confCount'] == 5) {
			$c .= '<img src="/pix/gold.png" width="75"><br>';
			$c .= 'Gold award';
		}
	$c .= '</td><td align="center" style="border: 1px solid #d10373"><h5>Enterprising creativity</h5>';
		if ($categories['entcCount'] == 0) {
			$c .= '<img src="/pix/spacer.gif" width="75">';
		}
		if ($categories['entcCount'] == 1) {
			$c .= '<h1><br> 1 </h1>achievement';
		}
		if ($categories['entcCount'] == 2) {
			$c .= '<img src="/pix/bronze.png" height="75"><br>';
			$c .= 'Bronze award';
		}
		if ($categories['entcCount'] >= 3 || $categories['entcCount'] == 4) {
			$c .= '<img src="/pix/silver.png" height="75"><br>';
			$c .= 'Silver award';
		}
		if ($categories['entcCount'] == 5) {
			$c .= '<img src="/pix/gold.png" height="75"><br>';
			$c .= 'Gold award';
		}
	$c .= '    </td>';
	$c .= '<td>&nbsp;</td></tr>';
	//spacer cell on end of row
	$c .= '    <tr><td>&nbsp;</td><td align="center" style="border: 1px solid #9eab05"><h5>Connectedness</h5>';
		if ($categories['connCount'] == 0) {
			$c .= '<img src="/pix/spacer.gif" width="75">';
		}
		if ($categories['connCount'] == 1) {
			$c .= '<h1><br> 1 </h1>achievement';
		}
		if ($categories['connCount'] == 2) {
			$c .= '<img src="/pix/bronze.png" height="75"><br>';
			$c .= 'Bronze award';
		}
		if ($categories['connCount'] >= 3 || $categories['connCount'] == 4) {
			$c .= '<img src="/pix/silver.png" height="75"><br>';
			$c .= 'Silver award';
		}
		if ($categories['connCount'] == 5) {
			$c .= '<img src="/pix/gold.png" height="75"><br>';
			$c .= 'Gold award';
		}
	$c .= '</td><td align="center" style="border: 1px solid #003896"><h5>Generosity of spirit</h5>';
		if ($categories['gensCount'] == 0) {
			$c .= '<img src="/pix/spacer.gif" width="75">';
		}
		if ($categories['gensCount'] == 1) {
			$c .= '<h1><br> 1 </h1>achievement';
		}
		if ($categories['gensCount'] == 2) {
			$c .= '<img src="/pix/bronze.png" height="75"><br>';
			$c .= 'Bronze award';
		}
		if ($categories['gensCount'] >= 3 || $categories['gensCount'] == 4) {
			$c .= '<img src="/pix/silver.png" height="75"><br>';
			$c .= 'Silver award';
		}
		if ($categories['gensCount'] == 5) {
			$c .= '<img src="/pix/gold.png" height="75"><br>';
			$c .= 'Gold award';
		}
		
	$c .= '    </td><td>&nbsp;</td>';
	$c .= '</tr>';
	$c .= '</table>';
	$c .= '<h3>Signed</h3>';
	$c .= '<img src="/pix/signature.png" width="150"><br>Dr Bob Champion<br>BrookesID Co-curricular Activities Programme';
	$c .= '<br pagebreak="true"/>';
	
	/* list of badges */
	$c .= '<p style="text-align: center"><img src="/pix/logo-brookes.png" align="center" width="100"></p>';
	$c .= '<p style="text-align: center"><img src="/pix/brookesid-logo.png" align="center" width="100"></p>';
    $c .= '<h3>Achievements</h3>';

	$c .= '<table border="0" cellspacing="2" cellpadding="3">';
	$c .= '<tbody>';
			
	foreach ($badges as $badge) {
		$c .= '<tr><td valign="top" width="10%">';
		switch ($badge['badge_category']) {
				case 'CONF':
					$c .= '<img src="/pix/confidence-logo.png" align="center" width="30">';
					break;
				case 'ENTC':
					$c .= '<img src="/pix/entc-logo.png" align="center" width="30">';
					break;
				case 'CONN':
					$c .= '<img src="/pix/connected-logo.png" align="center" width="30">';
					break;
				case 'GENS':
					$c .= '<img src="/pix/gens-logo.png" align="center" width="30">';
					break;
				default:
					break;
			}
		$c .= '</td><td valign="bottom" width="90%"><strong>'. $badge['badgename'] . '</strong> (';
		switch ($badge['badge_category']) {
				case 'CONF':
					$c .= 'Confidence';
					break;
				case 'ENTC':
					$c .= 'Enterprising creativity';
					break;
				case 'CONN':
					$c .= 'Connectedness';
					break;
				case 'GENS':
					$c .= 'Generosity of spirit';
					break;
				default:
					break;
			}
		$c .= '), awarded on '. date('j F Y', $badge['dateissued']) .'</td></tr>';
		$c .= '</tr>';
	}
		    
	$c .= '</tbody>';
	$c .= '</table>';
	$c .= '<h3>Signed</h3>';
	$c .= '<img src="/pix/signature.png" width="150"><br>Dr Bob Champion<br>BrookesID Co-curricular Activities Programme';
	
	$c .= '<br pagebreak="true"/>';
	/* transcript of badges */
	$pdf->AddPage();
		
	$pdf->SetTextColor(255,255,255);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetFont('helvetica', 'R', 24);
	$pdf->Cell(0, 0, 'BrookesID Certificate', 0, 1, 'C', 1);
		
	$pdf->SetFont('helvetica', 'R', 12);
	$pdf->Ln(6);
	$pdf->SetTextColor(0,0,0);
	

	
	
	$c .= '<table border="0"  cellspacing="0" cellpadding="0">';
	$c .= '<thead><tr>';
	$c .= '<td style="text-align: left"><img src="/pix/logo-brookes.png" align="left" width="100"></td>';
	$c .= '<td style="text-align: right"><img src="/pix/brookesid-logo.png" align="right" width="100"></td>';
	$c .= '</tr><tr>';
	$c .= '<th colspan="2" style="font-size: 125%; font-weight: bold">Transcript of achievements</th>';
	$c .= '</tr></thead><tbody>';
	$c .= '<tr><th>Student number</th><td>'. $studentnumber .'</td></tr>';
	$c .= '<tr><th>Name</th><td>'. $contact['firstname'] . ' ' . $contact['lastname'] .'</td></tr>';
	$c .= '<tr><th>Categories</th><td>';
	//confidence
		if ($categories['confCount'] == 1) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievement<br/>'; 
		}
		if ($categories['confCount'] == 2) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Bronze award)<br/>'; 
		}
		if ($categories['confCount'] == 3 || $categories['confCount'] == 4) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Silver award)<br/>'; 
		}
		if ($categories['confCount'] == 5) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Gold award)<br/>'; 
		}
	//enterprising creativity
		if ($categories['entcCount'] == 1) {
			$c .= 'Enterprising creativity: '. $categories['entcCount'] .' achievement<br/>';
		}
		if ($categories['entcCount'] == 2) {
			$c .= 'Confidence: '. $categories['entcCount'] .' achievements (Bronze award)<br/>'; 
		}
		if ($categories['entcCount'] == 3 || $categories['entcCount'] == 4) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Silver award)<br/>'; 
		}
		if ($categories['entcCount'] == 5) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Gold award)<br/>'; 
		}
	//connectedness
		if ($categories['connCount'] == 1) {
			$c .= 'Connectedness: '. $categories['connCount'] .' achievement<br/>';
		}
		if ($categories['connCount'] == 2) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Bronze award)<br/>'; 
		}
		if ($categories['connCount'] == 3 || $categories['connCount'] == 4) {
			$c .= 'Confidence: '. $categories['connCount'] .' achievements (Silver award)<br/>'; 
		}
		if ($categories['connCount'] == 5) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Gold award)<br/>'; 
		}
	//generosity of spirit
		if ($categories['gensCount'] == 1) {
			$c .= 'Generosity of spirit: '. $categories['gensCount'] .' achievement<br/>';
		}
		if ($categories['gensCount'] == 2) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Bronze award)<br/>'; 
		}
		if ($categories['gensCount'] == 3 || $categories['confCount'] == 4) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Silver award)<br/>'; 
		}
		if ($categories['gensCount'] == 5) {
			$c .= 'Confidence: '. $categories['confCount'] .' achievements (Gold award)<br/>'; 
		}
		
	$c .= '    </td>';
	$c .= '</tr>';
	$c .= '</tbody>';
	$c .= '</table><br>';
    
	
	
	$c .= '<table border="1"  cellspacing="0" cellpadding="3">';
	$c .= '<tbody>';
			
	foreach ($badges as $badge) {
		$c .= '<tr>';
		$c .= '    <td><strong>'. $badge['badgename'] . '</strong> (';
		switch ($badge['badge_category']) {
				case 'CONF':
					$c .= 'Confidence';
					break;
				case 'ENTC':
					$c .= 'Enterprising creativity';
					break;
				case 'CONN':
					$c .= 'Connectedness';
					break;
				case 'GENS':
					$c .= 'Generosity of spirit';
					break;
				default:
					break;
			}
		$c .= '), awarded on '. date('j F Y', $badge['dateissued']) .'</td></tr>';
		$c .= '    <tr><td style="font-size: 75%; font-weight: normal">'. $badge['badgedescription'] .'</td></tr>';
				
		$c .= '</tr>';
	}
		    
	$c .= '</tbody>';
	$c .= '</table>';
	$c .= '<h3>Signed</h3>';
	$c .= '<img src="/pix/signature.png" width="150"><br>Dr Bob Champion<br>BrookesID Co-curricular Activities Programme';

	$pdf->writeHTML($c);
}