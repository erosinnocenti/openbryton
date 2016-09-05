<?
	// Copyright (C) 2012, 2016 Eros Innocenti
	// This file is part of OpenBryton.
	//
	// OpenBryton is free software: you can redistribute it and/or modify
	// it under the terms of the GNU General Public License as published by
	// the Free Software Foundation, either version 3 of the License, or
	// (at your option) any later version.
	//
	// OpenBryton is distributed in the hope that it will be useful,
	// but WITHOUT ANY WARRANTY; without even the implied warranty of
	// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	// GNU General Public License for more details.
	//
	// You should have received a copy of the GNU General Public License

	header('Access-Control-Allow-Origin: http://openrouteservice.org'); 
	require_once('utils.php');
	error_reporting(E_ALL);
	ini_set("display_errors", "On");
	libxml_use_internal_errors(true);

	$rawPost = file_get_contents('php://input');
	$result = '';

	$randomString = randomString(15);

	$tinfoFile = $randomString . '-data.tinfo';
	$trackFile = $randomString . '-data.track';
	$smyFile = $randomString . '-data.smy';
	$zipFile = $randomString . '-data.zip';

	foreach(preg_split('/((\r?\n)|(\r\n?))/', $rawPost) as $line) {
    	if(startsWith(trim($line), '<')) {
    		$result = $result . $line . "\n"; 
    	}
	}

	$reader = new XMLReader();
	
	if(trim($result == '')) {
		$reader->open('test.xml');
	} else {
		$reader->XML($result);
	}

	$data['RouteInstruction'] = array();

	while ($reader->read()) {
		if($reader->localName === 'TotalDistance' && $reader->nodeType === XMLREADER::ELEMENT) {
			$data['TotalDistance'] = intval($reader->getAttribute('value'));
		}

		if($reader->localName === 'BoundingBox' && $reader->nodeType === XMLREADER::ELEMENT) {
			$totalFound = 0;

			$data['BoundingBox'] = array();

			while($reader->read() && $totalFound < 2) {
				if($reader->nodeType === XMLREADER::TEXT) {
					$totalFound = $totalFound + 1;

					$val = $reader->value;
					$splitted = explode(' ', $val);

					$point = array();
					$point['lat'] = doubleval($splitted[1]);
					$point['lon'] = doubleval($splitted[0]);

					array_push($data['BoundingBox'], $point);
				}
			}
		}

		if($reader->localName === 'RouteGeometry' && $reader->nodeType === XMLREADER::ELEMENT) {
			$data['RoutePoints'] = array();

			while($reader->read() && !($reader->nodeType === XMLREADER::END_ELEMENT && $reader->localName === 'RouteGeometry')) {
				if($reader->localName === 'pos') {
					while($reader->read() && $reader->nodeType === XMLREADER::TEXT) {
						$val = $reader->value;
						$splitted = explode(' ', $val);

						$point = array();
						$point['lat'] = doubleval($splitted[1]);
						$point['lon'] = doubleval($splitted[0]);

						array_push($data['RoutePoints'], $point);
						break;
					}
				}
			}
		}

		if($reader->localName === 'RouteInstruction' && $reader->nodeType === XMLREADER::ELEMENT) {
			while($reader->read() && !($reader->nodeType === XMLREADER::END_ELEMENT && $reader->localName === 'RouteInstruction')) {
				if($reader->localName === 'DirectionCode') {
					while($reader->read() && $reader->nodeType === XMLREADER::TEXT) {
						$instruction['DirectionCode'] = intval($reader->value);
						break;
					}
				}

				if($reader->localName === 'Instruction') {
					while($reader->read() && $reader->nodeType === XMLREADER::TEXT) {
						$val = $reader->value;

						if(strpos($val, '<b>') !== false) {
							$doc = new DOMDocument();
							@$doc->loadHTML($val);
							$p = $doc->getElementsByTagName('b')->item(0);
							$val = $p->nodeValue;
						}

						$instruction['Instruction'] = $val;
						break;
					}
				}

				if($reader->localName === 'Distance') {
					$instruction['Distance'] = intval($reader->getAttribute('value'));
				}

				if($reader->localName === 'RouteInstructionGeometry' && $reader->nodeType === XMLREADER::ELEMENT) {
					while($reader->read() && !($reader->nodeType === XMLREADER::END_ELEMENT && $reader->localName === 'RouteInstructionGeometry')) {
						if($reader->localName === 'pos') {
							while($reader->read() && $reader->nodeType === XMLREADER::TEXT) {
								$val = $reader->value;
								$splitted = explode(' ', $val);

								$instruction['RouteInstructionGeometry'] = array();
								$instruction['RouteInstructionGeometry']['lat'] = doubleval($splitted[1]);
								$instruction['RouteInstructionGeometry']['lon'] = doubleval($splitted[0]);

								array_push($data['RouteInstruction'], $instruction);
								break;
							}
							break;
						}
					}
				}
			}
		}
	}

	$data['CoordinatesCount'] = count($data['RoutePoints']);

	// SMY file creation (header)
		if (file_exists($smyFile))
			unlink($smyFile);
		$fp = fopen($smyFile, 'w');
		
		// Reserved 01 00
		fwrite($fp, chr(0x01));
		fwrite($fp, chr(0x00));

		// 2 bytes for coordinates count
		fwrite($fp, pack('v', $data['CoordinatesCount']));

		// 4 bytes for each coordinate of the bounding box (ex. 11.2507169 = 11250716)
		// Lat-NE, Lat-SW, Lon-NE, Lon-SW
		$latne = $data['BoundingBox'][1]['lat'] * 1000000;
		$latso = $data['BoundingBox'][0]['lat'] * 1000000;
		$lonne = $data['BoundingBox'][1]['lon'] * 1000000;
		$lonso = $data['BoundingBox'][0]['lon'] * 1000000;
		$latne = intval($latne);
		$latso = intval($latso);
		$lonne = intval($lonne);
		$lonso = intval($lonso);
		fwrite($fp, pack('V', $latne));
		fwrite($fp, pack('V', $latso));
		fwrite($fp, pack('V', $lonne));
		fwrite($fp, pack('V', $lonso));

		// 4 bytes for total distance of the route
		fwrite($fp, pack('V', $data['TotalDistance']));
		fclose($fp);

	// TRACK file creation (coordinates)
		if (file_exists($trackFile))
			unlink($trackFile);
		$fp = fopen($trackFile, 'w');
		
		foreach ($data['RoutePoints'] as $point) {
			// 4 bytes for latitude
	    	$lat = $point['lat'] * 1000000;
	    	$lat = intval($lat);
			fwrite($fp, pack('V', $lat));
		
			// 4 bytes for longitude
			$lon = $point['lon'] * 1000000;
	    	$lon = intval($lon);
	    	fwrite($fp, pack('V', $lon));
		
			// 8 reserved bytes
			fwrite($fp, chr(0x00)); fwrite($fp, chr(0x00));
			fwrite($fp, chr(0x00)); fwrite($fp, chr(0x00));
			fwrite($fp, chr(0x00)); fwrite($fp, chr(0x00));
			fwrite($fp, chr(0x00)); fwrite($fp, chr(0x00));
		}

		fclose($fp);

	// TINFO file creation
		if (file_exists($tinfoFile))
			unlink($tinfoFile);
		$fp = fopen($tinfoFile, 'w');

		foreach ($data['RouteInstruction'] as $instruction) {
			// 2 bytes for coordinate index
			$iLat = $instruction['RouteInstructionGeometry']['lat'];
			$iLon = $instruction['RouteInstructionGeometry']['lon'];
			$i = 0;
			foreach ($data['RoutePoints'] as $point) {
				if($point['lat'] === $iLat && $point['lon'] === $iLon) {
					$index = intval($i);
					fwrite($fp, pack('v', $index));
					break;
				}	
				$i = $i + 1;			
			}

			// 1 byte for direction
			// 0x18 turn-over, 0x1c ferry
			switch($instruction['DirectionCode']) {
				case -3:
					fwrite($fp, chr(0x07)); // close left
					break;
				case -2:
					fwrite($fp, chr(0x03)); // left
					break;
				case -1:
					fwrite($fp, chr(0x05)); // slight left
					break;
				case 0:
					fwrite($fp, chr(0x01)); // go ahead
					break;
				case 1:
					fwrite($fp, chr(0x04)); // slight right
					break;
				case 2:
					fwrite($fp, chr(0x02)); // right
					break;
				case 3:
					fwrite($fp, chr(0x06)); // close right
					break;
				default:
					fwrite($fp, chr(0x01)); // unhandled = go ahead
					break;
			} 

			// 1 byte reserved 0x00
			fwrite($fp, chr(0x00));

			// 2 byte distance in meters
			fwrite($fp, pack('v', $instruction['Distance']));

			// 2 byte reserved 0x00 0x00
			fwrite($fp, chr(0x00)); fwrite($fp, chr(0x00));

			// 2 byte time in seconds
			$timeVal = $instruction['Distance'] * 0.722;
			$timeVal = intval($timeVal);
			fwrite($fp, pack('v', $timeVal));
		
			// 2 byte reserved 0x00 0x00
			fwrite($fp, chr(0x00)); fwrite($fp, chr(0x00));

			// 32 byte instruction description
			$desc = $instruction['Instruction'];

			if(sizeof($desc) < 32)
				$desc = str_pad($desc, 32, chr(0x00));
			else if(sizeof($desc) > 32)
				$desc = substr($desc, 0, 32);

			fwrite($fp, $desc);
		}

		fclose($fp);

	// ZIP file creation
	$zip = new ZipArchive();
	if($zip->open($zipFile, ZIPARCHIVE::OVERWRITE) === true) {
		$zip->addFile($smyFile, $smyFile);
		$zip->addFile($trackFile, $trackFile);
		$zip->addFile($tinfoFile, $tinfoFile);
		$zip->close();
	}

	if (file_exists($tinfoFile))
		unlink($tinfoFile);
		
	if (file_exists($smyFile))
		unlink($smyFile);
		
	if (file_exists($trackFile))
		unlink($trackFile);

	$base_url = 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI'] . '?') . '/';
	$base_url = $base_url . $zipFile;

	echo $base_url;

	// Delete zip files older than 5m from now
	$deleteTime = time() - 300;
	$dir = './';
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ((filetype($dir . '/' . $file) == 'file') && (filemtime($file) < $deleteTime) && (endsWith($file, '.zip'))) {
				unlink($dir . '/' . $file);
			}
		}
		closedir($handle);
	}

	echo "\n\n";

	var_dump($data);
?>
