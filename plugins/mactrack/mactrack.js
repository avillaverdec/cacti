var xmlHttp
var url

function applyReportFilterChange(objForm) {
	strURL = '?report=' + objForm.report.value;
	document.location = strURL;
}

function applySiteFilterChange(objForm) {
	strURL = '?report=sites';
	if (objForm.hidden_device_type_id) {
		strURL = strURL + '&device_type_id=-1';
		strURL = strURL + '&site_id=-1';
	}else{
		strURL = strURL + '&device_type_id=' + objForm.device_type_id.value;
		strURL = strURL + '&site_id=' + objForm.site_id.value;
	}
	strURL = strURL + '&detail=' + objForm.detail.checked;
	strURL = strURL + '&filter=' + objForm.filter.value;
	strURL = strURL + '&rows=' + objForm.rows.value;
	document.location = strURL;
}

function applyIPsFilterChange(objForm) {
	strURL = '?report=ips';
	strURL = strURL + '&site_id=' + objForm.site_id.value;
	strURL = strURL + '&rows=' + objForm.rows.value;
	document.location = strURL;
}

function applyDeviceFilterChange(objForm) {
	strURL = '?report=devices';
	strURL = strURL + '&site_id=' + objForm.site_id.value;
	strURL = strURL + '&status=' + objForm.status.value;
	strURL = strURL + '&type_id=' + objForm.type_id.value;
	strURL = strURL + '&device_type_id=' + objForm.device_type_id.value;
	strURL = strURL + '&filter=' + objForm.filter.value;
	strURL = strURL + '&rows=' + objForm.rows.value;
	document.location = strURL;
}

function applyMacFilterChange(objForm) {
	strURL = '?report=macs';
	strURL = strURL + '&site_id=' + objForm.site_id.value;
	strURL = strURL + '&device_id=' + objForm.device_id.value;
	strURL = strURL + '&scan_date=' + objForm.scan_date.value;
	strURL = strURL + '&rows=' + objForm.rows.value;
	strURL = strURL + '&mac_filter_type_id=' + objForm.mac_filter_type_id.value;
	strURL = strURL + '&mac_filter=' + objForm.mac_filter.value;
	strURL = strURL + '&authorized=' + objForm.authorized.value;
	strURL = strURL + '&filter=' + objForm.filter.value;
	strURL = strURL + '&vlan=' + objForm.vlan.value;
	strURL = strURL + '&ip_filter_type_id=' + objForm.ip_filter_type_id.value;
	strURL = strURL + '&ip_filter=' + objForm.ip_filter.value;
	document.location = strURL;
}

function applyArpFilterChange(objForm) {
	strURL = '?report=arp';
	strURL = strURL + '&site_id=' + objForm.site_id.value;
	strURL = strURL + '&device_id=' + objForm.device_id.value;
	strURL = strURL + '&rows=' + objForm.rows.value;
	strURL = strURL + '&mac_filter_type_id=' + objForm.mac_filter_type_id.value;
	strURL = strURL + '&mac_filter=' + objForm.mac_filter.value;
	strURL = strURL + '&filter=' + objForm.filter.value;
	strURL = strURL + '&ip_filter_type_id=' + objForm.ip_filter_type_id.value;
	strURL = strURL + '&ip_filter=' + objForm.ip_filter.value;
	document.location = strURL;
}

function applyInterfaceFilterChange(objForm) {
	strURL = '?site=' + objForm.site.value
	strURL = strURL + '&rows=' + objForm.rows.value
	strURL = strURL + '&device=' + objForm.device.value
	strURL = strURL + '&issues=' + objForm.issues.value
	strURL = strURL + '&bwusage=' + objForm.bwusage.value
	strURL = strURL + '&type=' + objForm.type.value
	strURL = strURL + '&totals=' + objForm.totals.checked
	strURL = strURL + '&filter=' + objForm.filter.value
	document.location = strURL
}

function getfromserverMacTrack(baseurl) {
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null) {
		alert ("Get Firefox!")
		return
	}

	xmlHttp.onreadystatechange=stateChangedMacTrack
	xmlHttp.open("GET",baseurl,true)
	xmlHttp.send(null)
}

function scan_device(device_id) {
	url="mactrack_ajax_admin.php?action=rescan&device_id="+device_id
	document.getElementById("r_"+device_id).src="images/view_busy.gif"
	getfromserverMacTrack(url)
}

function site_scan(site_id) {
	url="mactrack_ajax_admin.php?action=site_scan&site_id="+site_id
	document.getElementById("r_"+site_id).src="images/view_busy.gif"
	getfromserverMacTrack(url)
}

function scan_device_interface(device_id, ifName) {
	url="mactrack_ajax_admin.php?action=rescan&device_id="+device_id+"&ifName="+ifName
	document.getElementById("r_"+device_id+"_"+ifName).src="images/view_busy.gif"
	getfromserverMacTrack(url)
}

function clearScanResults() {
	document.getElementById("response").innerHTML="<span/>";
}

function disable_device(device_id) {
	url="mactrack_ajax_admin.php?action=disable&device_id="+device_id
	getfromserverMacTrack(url)
}

function enable_device(device_id) {
	url="mactrack_ajax_admin.php?action=enable&device_id="+device_id
	getfromserverMacTrack(url)
}

function saveGraphSettings() {
	filter=document.form_graph_view.filter.value;
	graph_template_id=document.form_graph_view.filter.value;
	timespan=document.form_timespan_selector.predefined_timespan.value;
	timeshift=document.form_timespan_selector.predefined_timeshift.value;

	url="mactrack_ajax.php?action=save_graph_settings" +
		"&filter=" + filter +
		"graph_template_id=" + graph_template_id +
		"predefined_timespan=" + timespan +
		"predefined_timeshift=" + timeshift;

	getfromserverMacTrack(url)
}

function stateChangedMacTrack() {
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") {
		reply     = xmlHttp.responseText
		reply     = reply.split("!!!!")
		type      = reply[0]

		if ((type == "enable") || (type == "disable")) {
			document.getElementById("row_"+device_id).innerHTML=content
		} else if (type == "sitescan") {
			site_id=reply[1]
			content=reply[2]
			document.getElementById("r_"+site_id).src="images/rescan_site.gif"
			document.getElementById("response").innerHTML=content
		} else if (type == "rescan") {
			device_id=reply[1]
			ifName=reply[2]
			content=reply[3]
			document.getElementById("r_"+device_id+"_"+ifName).src="images/rescan_device.gif"
			document.getElementById("response").innerHTML=content
		}
	}
}

function GetXmlHttpObject() {
	var objXMLHttp=null
	if (window.XMLHttpRequest) {
		objXMLHttp=new XMLHttpRequest()
	}
	else if (window.ActiveXObject) {
		objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP")
	}
	return objXMLHttp
}
