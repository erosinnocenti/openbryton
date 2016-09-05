javascript:(

function() {
	
	// Create request document
	var result = '<?xml version="1.0" encoding="UTF-8" ?>';
	result += '<xls:XLS xmlns:xls="http://www.opengis.net/xls" xsi:schemaLocation="http://www.opengis.net/xls http://schemas.opengis.net/ols/1.1.0/RouteService.xsd" xmlns:sch="http://www.ascc.net/xml/schematron" xmlns:gml="http://www.opengis.net/gml" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.1" xls:lang="it">';
	result += '<xls:RequestHeader>';
	result += '</xls:RequestHeader>';
	result += '<xls:Request methodName="RouteRequest" version="1.1" requestID="00" maximumResponses="15">';
	result += '<xls:DetermineRouteRequest distanceUnit="M">';
	result += '<xls:RoutePlan>';
	result += '<xls:RoutePreference>Pedestrian</xls:RoutePreference>';
	result += '<xls:ExtendedRoutePreference>';
	result += '<xls:WeightingMethod>Fastest</xls:WeightingMethod>';
	result += '</xls:ExtendedRoutePreference>';
	result += '<xls:WayPointList>';
	
	var list = document.querySelectorAll('[data-layer="layerRoutePoints"]');
	var finalList = [];
	for (var i = 0; i < list.length; i++) {
		var dataPosition = list[i].getAttribute('data-position');

		if(dataPosition != undefined) {
			finalList.push(dataPosition);
		}
	}

	for (var i = 0; i < finalList.length; i++) {
		var dataPosition = finalList[i];

		var splitted = dataPosition.split(" ");
		var gmlCoordinates = splitted[1] + " " + splitted[0];

		var tagName;
		if(i == 0) {
			tagName = 'StartPoint';
		} else if(i == (finalList.length - 1)) {
			tagName = 'EndPoint'
		} else {
			tagName = 'ViaPoint';
		}

		result += '<xls:' + tagName + '>';
		result += '<xls:Position>';
		result += '<gml:Point xmlns:gml="http://www.opengis.net/gml">';
		result += '<gml:pos srsName="EPSG:4326">' + gmlCoordinates + '</gml:pos>';
		result += '</gml:Point>';
		result += '</xls:Position>';
		result += '</xls:' + tagName + '>';
	}

	result += '</xls:WayPointList>';
	result += '<xls:AvoidList />';
	result += '</xls:RoutePlan>';
	result += '<xls:RouteInstructionsRequest provideGeometry="true" />';
	result += '<xls:RouteGeometryRequest>';
	result += '</xls:RouteGeometryRequest>';
	result += '</xls:DetermineRouteRequest>';
	result += '</xls:Request>';
	result += '</xls:XLS>';

	// Send request via POST at openls.geog.uni-heidelberg.de
	var http = new XMLHttpRequest();
	var url = 'http://openls.geog.uni-heidelberg.de/routing?api_key=ee0b8233adff52ce9fd6afc2a2859a28';
	var data = new FormData();
	data.append('REQUEST', unescape(encodeURIComponent(result)));
	http.open('POST', url, true);
	http.setRequestHeader('Content-type', 'multipart/form-data');
	http.onreadystatechange = function() {
	    if(http.readyState == 4 && http.status == 200) {
			var responseText = http.responseText;
			elab(responseText);
	    }
	}
	http.send(data);

	// Elaborate the request and send obtained document to our OpenBryton web service
	function elab(httpResponse) {
		http = new XMLHttpRequest();
		url = 'http://www.newtechweb.it/apps/openbryton/elaborate.php';
		data = new FormData();
		data.append('REQUEST', unescape(encodeURIComponent(httpResponse)));
		http.open('POST', url, true);
		http.setRequestHeader('Content-type', 'multipart/form-data');
		http.onreadystatechange = function() {
		    if(http.readyState == 4 && http.status == 200) {
		    	var splitted = http.responseText.split("\n");
		
		    	var url = splitted[0];
				var link = document.createElement('a');
				link.download = 'data.zip';
				link.href = url;
				link.click();
				window.URL.revokeObjectURL(url);
		    }
		}
		http.send(data);
	}
}
)();