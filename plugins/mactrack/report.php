<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2012 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2012 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */

/** Error reporting */

function mactrack_report($device_id){
	$sql_where = ' WHERE mac_track_interfaces.device_id='.$device_id." AND mac_track_interfaces.ifOperStatus='0'";
	$sqlorder = " ORDER BY mac_track_interfaces.last_up_time ASC";
	
	$sql_query = "SELECT mac_track_interfaces.*,
			mac_track_device_types.description AS device_type,
			mac_track_devices.device_name,
			mac_track_devices.host_id,
			mac_track_devices.disabled,
			mac_track_devices.last_rundate
			FROM mac_track_interfaces
			INNER JOIN mac_track_devices
			ON mac_track_interfaces.device_id=mac_track_devices.device_id
			INNER JOIN mac_track_device_types
			ON mac_track_device_types.device_type_id=mac_track_devices.device_type_id
			$sql_where
			$sqlorder";
	$stats = db_fetch_assoc($sql_query);
		
	/** Include path **/
	ini_set('include_path', ini_get('include_path').';./excelreport/Classes/');
	
	/** PHPExcel */
	include 'excelreport/Classes/PHPExcel.php';
	
	/** PHPExcel_Writer_Excel2007 */
	include 'excelreport/Classes/PHPExcel/Writer/Excel5.php';

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
	
	$hostname = $stats[0]['device_name'];
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	
	// Set document properties
	$objPHPExcel->getProperties()->setCreator("URJC - Dpto. Comunicaciones Unificadas")
								 ->setLastModifiedBy("Dpto. Comunicaciones Unificadas")
								 ->setTitle("Uso de puertos: $hostname")
								 ->setSubject("Office 2007 XLSX Test Document")
								 ->setDescription("Resumen del uso de puertos del dispositivo $hostname");
	
	// Set header
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()
				->setCellValue('A1', 'INFORME DE USO DE PUERTOS')
				->setCellValue('A2', 'Host:')
				->setCellValue('B2', $hostname)
				->setCellValue('A3', 'Activo desde:')
				->setCellValue('B3', $upTime);
	
	// Add table header
	$objPHPExcel->getActiveSheet()
				->setCellValue('A4', 'Puerto')
				->setCellValue('B4', 'Estado')
				->setCellValue('C4', 'Último cambio')
				->setCellValue('D4', 'Escaneado');
	
	$i=5;
	foreach($stats as $stat){
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
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A'.$i, $stat["ifName"])
					->setCellValue('B'.$i, $stat["ifOperStatus"])
					->setCellValue('C'.$i, $upTime)
					->setCellValue('D'.$i, mactrack_date($stat["last_rundate"]));
		$i++;
	} 
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle($hostname);
	
	foreach(range('A','G') as $columnID) {
		$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
	}
	
	// Redirect output to a client’s web browser (Excel2007)
	ob_end_clean();
	ob_start();
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$hostname.'.xls"');
	header('Cache-Control: max-age=0');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
}

mactrack_report('102');

?>