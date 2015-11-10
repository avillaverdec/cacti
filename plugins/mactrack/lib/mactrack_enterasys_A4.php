<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

/* register this functions scanning functions */
if (!isset($mactrack_scanning_functions)) { $mactrack_scanning_functions = array(); }
array_push($mactrack_scanning_functions, "get_enterasysA4_switch_ports");
//array_push($mactrack_scanning_functions, "get_repeater_rev4_ports");

function get_cabletron_switch_ports($site, &$device, $lowPort, $highPort) {
	global $debug, $scan_date;

	/* initialize port counters */
	$device["ports_total"] = 0;
	$device["ports_active"] = 0;
	$device["ports_trunk"] = 0;
	$device["vlans_total"] = 0;

	/* get the ifIndexes for the device */
	$ifIndexes = xform_standard_indexed_data(".1.3.6.1.2.1.2.2.1.1", $device);
	mactrack_debug("ifIndexes data collection complete");

	/* get and store the interfaces table */
	$ifInterfaces = build_InterfacesTable($device, $ifIndexes, FALSE, TRUE);
	
	/* get VLAN information */
	$vlan_ids = xform_dot1q_vlan_associations($device, $device["snmp_readstring"]);
	mactrack_debug("VLAN data collected. There are " . (sizeof($vlan_ids)) . " VLANS.");
	
	/* map vlans to bridge ports */
	if (sizeof($vlan_ids) > 0) {
		/* get the port status information */
		$port_results = get_enterasys_A4_dot1dTpFdbEntry_ports($site, $device, $ifInterfaces, $device["snmp_readstring"], FALSE, $lowPort, $highPort);

		/* get the ifIndexes for the device */
		$vlan_names = xform_standard_indexed_data(".1.3.6.1.2.1.17.7.1.4.3.1.1", $device);

		$i = 0;
		$j = 0;
		$port_array = array();
		foreach($port_results as $port_result) {
				$ifIndex = $port_result["port_number"];
				$ifType = $ifInterfaces[$ifIndex]["ifType"];

				/* only output legitamate end user ports */
				if (($ifType >= 6) && ($ifType <= 9)) {
					$port_array[$i]["vlan_id"] = @$vlan_ids[$port_result["key"]];
					$port_array[$i]["vlan_name"] = @$vlan_names[$port_array[$i]["vlan_id"]];
					$port_array[$i]["port_number"] = @$port_result["port_number"];
					$port_array[$i]["port_name"] = @$ifInterfaces[$ifIndex]["ifName"];
					$port_array[$i]["mac_address"] = xform_mac_address($port_result["mac_address"]);
					$device["ports_active"]++;

					mactrack_debug("VLAN: " . $port_array[$i]["vlan_id"] . ", " .
							"NAME: " . $port_array[$i]["vlan_name"] . ", " .
							"PORT: " . $ifInterfaces[$ifIndex]["ifName"] . ", " .
							"NAME: " . $port_array[$i]["port_name"] . ", " .
							"MAC: " . $port_array[$i]["mac_address"]);

					$i++;
				}
				$j++;
		}

		/* display completion message */
		print("INFO: HOST: " . $device["hostname"] . ", TYPE: " . trim(substr($device["snmp_sysDescr"],0,40)) . ", TOTAL PORTS: " . $device["ports_total"] . ", ACTIVE PORTS: " . $device["ports_active"] . "\n");
		$device["last_runmessage"] = "Data collection completed ok";
		$device["macs_active"] = sizeof($port_array);
		mactrack_debug("macs active on this switch:" . $device["macs_active"]);
		db_store_device_port_results($device, $port_array, $scan_date);
	}else{
		print("INFO: HOST: " . $device["hostname"] . ", TYPE: " . substr($device["snmp_sysDescr"],0,40) . ", No active devcies on this network device.\n");
		$device["snmp_status"] = HOST_UP;
		$device["last_runmessage"] = "Data collection completed ok. No active devices on this network device.";
	}

	return $device;
}


/*	get_base_dot1dTpFdbEntry_ports - This function will grab information from the
  port bridge snmp table and return it to the calling progrem for further processing.
  This is a foundational function for all vendor data collection functions.
*/
function get_enterasys_A4_dot1dTpFdbEntry_ports($site, &$device, &$ifInterfaces, $snmp_readstring = "", $store_to_db = TRUE, $lowPort = 1, $highPort = 9999) {
	global $debug, $scan_date;
	mactrack_debug("FUNCTION: get_enterasys_A4_dot1dTpFdbEntry_ports started");

	/* initialize variables */
	$port_keys = array();
	$return_array = array();
	$new_port_key_array = array();
	$port_key_array = array();
	$port_number = 0;
	$ports_active = 0;
	$active_ports = 0;
	$ports_total = 0;

	/* cisco uses a hybrid read string, if one is not defined, use the default */
	if ($snmp_readstring == "") {
		$snmp_readstring = $device["snmp_readstring"];
	}

	/* get the operational status of the ports */
	$active_ports_array = xform_standard_indexed_data(".1.3.6.1.2.1.2.2.1.8", $device);
	mactrack_debug("get active ports: " . sizeof($active_ports_array));
	$indexes = array_keys($active_ports_array);

	$i = 0;
	foreach($active_ports_array as $port_info) {
		if (($ifInterfaces[$indexes[$i]]["ifType"] >= 6) &&
			($ifInterfaces[$indexes[$i]]["ifType"] <= 9)) {
			if ($port_info == 1) {
				$ports_active++;
			}
			$ports_total++;
		}
		$i++;
	}

	if ($store_to_db) {
		print("INFO: HOST: " . $device["hostname"] . ", TYPE: " . substr($device["snmp_sysDescr"],0,40) . ", TOTAL PORTS: " . $ports_total . ", OPER PORTS: " . $ports_active);
		if ($debug) {
			print("\n");
		}

		$device["ports_active"] = $ports_active;
		$device["ports_total"] = $ports_total;
		$device["macs_active"] = 0;
	}

	if ($ports_active > 0) {
		$bridgePortIfIndexes = xform_standard_indexed_data(".1.3.6.1.2.1.17.1.4.1.2", $device, $snmp_readstring);
		mactrack_debug("get bridgePortIfIndexes: " . sizeof($bridgePortIfIndexes));

		$port_status = xform_stripped_oid(".1.3.6.1.2.1.17.4.3.1.3", $device, $snmp_readstring);
		mactrack_debug("get port_status: " . sizeof($port_status));

		$port_numbers = xform_stripped_oid(".1.3.6.1.2.1.17.4.3.1.2", $device, $snmp_readstring);
		mactrack_debug("get port_numbers: " . sizeof($port_numbers));

		/* get VLAN information */
		$vlan_ids = xform_dot1q_vlan_associations($device, $snmp_readstring);
		mactrack_debug("get vlan_ids: " . sizeof($vlan_ids));

		/* get the ignore ports list from device */
		$ignore_ports = port_list_to_array($device["ignorePorts"]);

		/* determine user ports for this device and transfer user ports to
		   a new array.
		*/
		$i = 0;
		foreach ($port_numbers as $key => $port_number) {
			/* key = MAC Address from dot1dTpFdbTable */
			/* value = bridge port			  */
			if (($highPort == 0) ||
				(($port_number >= $lowPort) &&
				($port_number <= $highPort))) {

				if (!in_array($port_number, $ignore_ports)) {
					if (@$port_status[$key] == "3") {
						$port_key_array[$i]["key"] = $key;
						$port_key_array[$i]["port_number"] = $port_number;
						$i++;
					}
				}
			}
		}

		/* compare the user ports to the brige port data, store additional
		   relevant data about the port.
		*/
		$i = 0;
		foreach ($port_key_array as $port_key) {
			/* map bridge port to interface port and check type */
			if ($port_key["port_number"] > 0) {
				if (sizeof($bridgePortIfIndexes) != 0) {
					/* some hubs do not always return a port number in the bridge table.
					   test for it by isset and substiture the port number from the ifTable
					   if it isnt in the bridge table
					*/

					if (isset($bridgePortIfIndexes[$port_key["port_number"]])) {
						$brPortIfIndex = @$bridgePortIfIndexes[$port_key["port_number"]];
					}else{
						$brPortIfIndex = @$port_key["port_number"];
					}
					$brPortIfType = @$ifInterfaces[$brPortIfIndex]["ifType"];
				}else{
					$brPortIfIndex = $port_key["port_number"];
					$brPortIfType = @$ifInterfaces[$port_key["port_number"]]["ifType"];
				}

				if (($brPortIfType >= 6) &&
					($brPortIfType <= 9) &&
					(!isset($ifInterfaces[$brPortIfIndex]["portLink"]))) {
					/* set some defaults  */
					$new_port_key_array[$i]["vlan_id"] = "N/A";
					$new_port_key_array[$i]["vlan_name"] = "N/A";
					$new_port_key_array[$i]["mac_address"] = "NOT USER";
					$new_port_key_array[$i]["port_number"] = "NOT USER";
					$new_port_key_array[$i]["port_name"] = "N/A";

					/* now set the real data */
					$new_port_key_array[$i]["key"] = @$port_key["key"];
					$new_port_key_array[$i]["port_number"] = @$brPortIfIndex;
					$new_port_key_array[$i]["vlan_id"] = @$vlan_ids[$port_key["key"]];
#print_r($new_port_key_array[$i]);
					$i++;
				}
			}
		}
		mactrack_debug("Port number information collected: " . sizeof($new_port_key_array));

		/* map mac address */
		/* only continue if there were user ports defined */
		if (sizeof($new_port_key_array) > 0) {
			/* get the bridges active MAC addresses */
			$port_macs = xform_stripped_oid(".1.3.6.1.2.1.17.4.3.1.1", $device, $snmp_readstring);

			foreach ($port_macs as $key => $port_mac) {
				$port_macs[$key] = xform_mac_address($port_mac);
			}

			foreach ($new_port_key_array as $key => $port_key) {
				$new_port_key_array[$key]["mac_address"] = @$port_macs[$port_key["key"]];
				mactrack_debug("INDEX: '". $key . "' MAC ADDRESS: " . $new_port_key_array[$key]["mac_address"]);
			}

			mactrack_debug("Port mac address information collected: " . sizeof($port_macs));
		}else{
			mactrack_debug("No user ports on this network.");
		}
	}else{
		mactrack_debug("No user ports on this network.");
	}

	if ($store_to_db) {
		if ($ports_active <= 0) {
			$device["last_runmessage"] = "Data collection completed ok";
		}elseif (sizeof($new_port_key_array) > 0) {
			$device["last_runmessage"] = "Data collection completed ok";
			$device["macs_active"] = sizeof($new_port_key_array);
			db_store_device_port_results($device, $new_port_key_array, $scan_date);
		}else{
			$device["last_runmessage"] = "WARNING: Poller did not find active ports on this device.";
		}

		if(!$debug) {
			print(" - Complete\n");
		}
	}else{
		return $new_port_key_array;
	}

}
