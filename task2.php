<?php

	// IP Address filtering function
	function ipAddressFilter($input, $storedList) {
		$ip2longSafe = function($ip) {
			return filter_var($ip, FILTER_VALIDATE_IP) ? ip2long($ip) : false;
		};

		$parseInput = function($str) use ($ip2longSafe) {
			$str = trim($str);

			if (strpos($str, '/') !== false) {
				list($subnet, $mask) = explode('/', $str, 2);
				if (!filter_var($subnet, FILTER_VALIDATE_IP) || !is_numeric($mask)) return false;
				$mask = (int)$mask;

				$subnetLong = $ip2longSafe($subnet);
				if ($subnetLong === false) return false;

				$maskLong = ~((1 << (32 - $mask)) - 1);

				$start = $subnetLong & $maskLong;
				$end = $start + (~$maskLong);

				return ['start' => $start, 'end' => $end];
			} elseif (strpos($str, '-') !== false) {
				list($startIp, $endIp) = array_map('trim', explode('-', $str));
				$start = $ip2longSafe($startIp);
				$end = $ip2longSafe($endIp);

				if ($start === false || $end === false) return false;
				if ($start > $end) list($start, $end) = [$end, $start];

				return ['start' => $start, 'end' => $end];
			} else {
				$ipLong = $ip2longSafe($str);
				if ($ipLong === false) return false;
				return ['start' => $ipLong, 'end' => $ipLong];
			}
		};

		$inputRange = $parseInput($input);
		if ($inputRange === false) return false;

		foreach ($storedList as $stored) {
			$storedRange = $parseInput($stored);
			if ($storedRange === false) continue;

			if (!($inputRange['end'] < $storedRange['start'] || $inputRange['start'] > $storedRange['end'])) {
				return true; // overlap found
			}
		}

		return false;
	}

	// Input IP 
	$testIp = '192.168.1.0 – 192.168.1.255'; 
	//$testIp = '192.168.1.1';

	try {
		/*$pdo = new PDO('mysql:host=localhost;dbname=razorblue', 'root', '');
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// Fetch IP rules
		$stmt = $pdo->query("SELECT ip_address_range FROM ip_address_list");
		$ipRanges = $stmt->fetchAll(PDO::FETCH_COLUMN); */
		//print_r($ipRanges);
		
		$ipRanges = [
		'192.168.1.1',
		'203.0.113.42',
		'192.168.2.5',
		'172.16.5.10',             
		'198.51.100.50',           
		'198.51.101.1',            
		'192.168.1.0 – 192.168.1.255',  
		'192.168.1.0/24',          
		'10.1.2.3/32',            
		];

		// Check IP
		if (ipAddressFilter($testIp, $ipRanges)) {
			echo "$testIp is ALLOWED.";
		} else {
			echo "$testIp is NOT ALLOWED.";
		}

	} catch (PDOException $e) {
		die("Database error: " . $e->getMessage());
	}
?>