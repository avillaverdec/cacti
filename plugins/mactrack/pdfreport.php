<?php
//============================================================+
// File name   : pdfreport.php
// Begin       : 2015-03-04
// Last Update : 2015-03-09
//
// Description : Creates an PDF with the inactive ports of a 
//               switch, ordered by inactive time, using TCPDF
//				 library.				
//
// Author: Alberto
//============================================================+

if (isset($_GET["did"])){

	// Include the main TCPDF library (search for installation path).
	require_once('lib/tcpdf/tcpdf_include.php');
	
	$ip = 0;
	$name = 0;
	$description = 0;
	$data = 0;
	
	mactrack_report($_GET["did"]);
	
	// extend TCPF with custom functions
	class PDFREPORT extends TCPDF {
	
		//Page header
		public function Header() {
			// Logo
			$image_file = K_PATH_IMAGES.'logoUrjc.jpg';
			// 	Image ($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array())
			$this->Image($image_file, 20, 15, '', '15', 'JPG', '', 'L', false, 300, '', false, false, 0, false, false, false);
			// Set font
			$this->SetFont('helvetica', 'B', 10);
			//Set color
			$this->SetColor('text',255,0,0);
			//Cell ($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
			$this->Cell(0, 0, 'Grupo Comunicaciones Unificadas', 0, 0, 'R', 0, '', 1, false, 'T', 'C');
			$this->SetColor('text',0);
			$this->Ln();
			$this->SetFont('helvetica', 'B', 8);
			$this->Cell(0, 0, 'Servicio de Infraestructura Tecnológica', 0, 1, 'R', 0, '', 1, false, 'T', 'C');
			$this->Cell(0, 0, 'Despacho 027/33, Ampliación de Rectorado', 0, 1, 'R', 0, '', 1, false, 'T', 'C');
			$this->Cell(0, 0, 'Campus de Móstoles', 0, 1, 'R', 0, '', 1, false, 'T', 'C');
		}
		
		public function infoTable($leftColumn, $rightColumn){
			// Set margin to adjust the table to the centre
			$this->SetLeftMargin(35);
			// Create table
			foreach (range(0, sizeof($leftColumn)-1) as $number) {
				// Set left column color and content
				$this->headercolor();
				$this->Cell(40, 6, $leftColumn[$number], 1, 0, 'L', 1);
				// Set right column color and content
				$this->rowcolor();
				// If the text is too long, insert a multicell, if not insert a single cell
				strlen($rightColumn[$number])>50 ? $this->MultiCell(100, 6, $rightColumn[$number], 'TLRB', 0, 'R', 1) : $this->Cell(100, 6, $rightColumn[$number], 'TLRB', 0, 'R', 1);
				// New line
				$this->Ln();
			}	
		}
		
		// Colored table
		public function ColoredTable($header,$data) {
			// Colors, line width and bold font
			$this->SetLeftMargin(25);
			$this->SetFillColor(255, 0, 0);
			$this->SetTextColor(255);
			$this->SetDrawColor(128, 0, 0);
			$this->SetLineWidth(0.3);
			$this->SetFont('', 'B');
			
			// Header
			$w = array(10, 30, 35, 40, 45);
			$num_headers = count($header)+1;
			for($i = 0; $i < $num_headers; ++$i) {
				$this->Cell($w[$i], 7, ($i==0) ? "Nº" : $header[$i-1], 1, 0, 'C', 1);
				//$this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
			}
			$this->Ln();
			// Color and font restoration
			$this->SetFillColor(224, 235, 255);
			$this->SetTextColor(0);
			$this->SetFont('');
			// Data
			$fill = 0;
			$i = 1;
			foreach($data as $row) {
				$this->Cell($w[0], 6, $i++, 'LR', 0, 'R', $fill);
				$this->Cell($w[1], 6, $row[0], 'LR', 0, 'C', $fill);
				$this->Cell($w[2], 6, ($row[1] == 0 ? 'Down' : 'Up'), 'LR', 0, 'C', $fill);
				$this->Cell($w[3], 6, $row[2], 'LR', 0, 'C', $fill);
				$this->Cell($w[4], 6, $row[3], 'LR', 0, 'R', $fill);
				$this->Ln();
				$fill=!$fill;
			}
			$this->Cell(array_sum($w), 0, '', 'T');
		}
		
		// Set the color of the header column
		function headercolor(){
			$this->SetFillColor(255, 0, 0);
			$this->SetTextColor(255);
			$this->SetDrawColor(128, 0, 0);
			$this->SetLineWidth(0.3);
			$this->SetFont('', 'B');
		}
		
		function rowcolor(){
			$this->SetFillColor(224, 235, 255);
			$this->SetTextColor(0);
		}
	}
	
	// create new PDF document
	$pdf = new PDFREPORT(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Grupo Comunicaciones Unificadas');
	$pdf->SetTitle('Resumen de uso de puertos');
	//$pdf->SetSubject('TCPDF Tutorial');
	$pdf->SetKeywords('Uso puertos, electrónica, monitorización');
	
	// set default header data
	$pdf->SetHeaderData('logoUrjc.jpg', 40, PDF_HEADER_TITLE.' 011', PDF_HEADER_STRING);
	
	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	
	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	
	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	
	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	
	// ---------------------------------------------------------
	
	// set font
	$pdf->SetFont('helvetica', '', 12);
	
	// add a page
	$pdf->AddPage();
	
	$leftColumn = array('Dirección IP', 'Hostname', 'Descripción');
	$rightColumn = array($ip, $name, $description);
	
	// add the table with the info of the device
	$pdf->infoTable($leftColumn,$rightColumn);
	
	$pdf->SetLeftMargin(25);
	$pdf->Ln();	
	
	// column titles
	$header = array('Interfaz', 'Estado actual', 'Desde', 'Escaneado');
	
	// print colored table
	$pdf->ColoredTable($header, $data);
	
	// ---------------------------------------------------------
	
	$nombreArchivo = explode(".",$ip); 
	
	// close and output PDF document
	$pdf->Output('puertosInactivos-'.$nombreArchivo[2]."_".$nombreArchivo[3].'.pdf', 'I');
	
}

function mactrack_report($device_id){
	chdir('../../');
	$user_auth_realm_filenames["pdfreport.php"] = 2120;
	include("./include/auth.php");
	//include '../../include/global.php';
	
	$sql_where = ' WHERE mac_track_interfaces.device_id='.$device_id." AND mac_track_interfaces.ifOperStatus='0'";
	$sqlorder = " ORDER BY mac_track_interfaces.last_up_time ASC, mac_track_interfaces.ifIndex ASC";
	
	$sql_query = "SELECT mac_track_interfaces.*,
			mac_track_device_types.description AS device_type,
			mac_track_devices.device_name,
			mac_track_devices.hostname,
			mac_track_devices.host_id,
			mac_track_devices.disabled,
			mac_track_devices.last_rundate,
			mac_track_devices.snmp_sysName,
			mac_track_devices.snmp_sysDescr
			FROM mac_track_interfaces
			INNER JOIN mac_track_devices
			ON mac_track_interfaces.device_id=mac_track_devices.device_id
			INNER JOIN mac_track_device_types
			ON mac_track_device_types.device_type_id=mac_track_devices.device_type_id
			$sql_where
			$sqlorder";
	$stats = db_fetch_assoc($sql_query);

	if (sizeof($stats)>0)
		$last_run_time = max(strtotime($stats[0]["last_up_time"]),strtotime($stats[0]["last_down_time"]));
	else 
		$last_run_time = read_config_option("mt_last_run_time", TRUE);
	$diff = strtotime("now") - $last_run_time;
	$upTime = ($stats[0]["sysUptime"]/100) + $diff;
	$days      = intval($upTime / (60*60*24));
	$remainder = $upTime % (60*60*24);
	$hours     = intval($remainder / (60*60));
	$remainder = $remainder % (60*60);
	$minutes   = intval($remainder / (60));
	$upTime    = $days . "d:" . $hours . "h:" . $minutes . "m";
	
	$array = array();
	$i=5;
	foreach($stats as $stat){
		if (strpos($stat["ifName"],"fe") !== false || strpos($stat["ifName"],"ge") !== false){
			if ($stat["ifOperStatus"] == 0) {
				if ($stat["last_up_time"] == '0000-00-00 00:00:00')
					$upTime = "Since Restart";
				else {
					$lastUp = $stat["last_up_time"];
					$now = date("Y-m-d H:i:s");
					$time = strtotime($now) - strtotime($lastUp);
					$days      = intval($time / (60*60*24));
					$remainder = $time % (60*60*24);
					$hours     = intval($remainder / (60*60));
					$remainder = $remainder % (60*60);
					$minutes   = intval($remainder / (60));
					$upTime    = $days . "d:" . $hours . "h:" . $minutes . "m";
				}
			}else{
				if ($stat["last_down_time"] == '0000-00-00 00:00:00')
					$upTime = "Since Restart";
				else {
					$lastUp = $stat["last_down_time"];
					$now = date("Y-m-d H:i:s");
					$time = strtotime($now) - strtotime($lastUp);
					$days      = intval($time / (60*60*24));
					$remainder = $time % (60*60*24);
					$hours     = intval($remainder / (60*60));
					$remainder = $remainder % (60*60);
					$minutes   = intval($remainder / (60));
					$upTime    = $days . "d:" . $hours . "h:" . $minutes . "m";
				}
			}
			$row = array($stat["ifName"], $stat["ifOperStatus"], $upTime, $stat["last_rundate"]);
			array_push($array,$row);
			$i++;
		}
	} 
	
	// Retrieve data
	$GLOBALS["ip"] = $stats[0]["hostname"];
	$GLOBALS["name"] = $stats[0]["snmp_sysName"];
	$GLOBALS["description"] = $stats[0]["snmp_sysDescr"];
	$GLOBALS["data"] = $array;
	
}