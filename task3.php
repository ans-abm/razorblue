<?php
	$inputFile = 'technical-test-data.csv';
	// Checking input file exits
	if (!file_exists($inputFile)) {
		die("CSV file not found.");
	}

	if (($handle = fopen($inputFile, 'r')) !== false) { // Opening file in read mode
		
		$header = fgetcsv($handle);
		
		// Importing the CSV
		$paramImport = importRegistrationCsv($header, $handle);
		$validFuelTypes = $paramImport['validFuelType'];
		$invalidCount = $paramImport['invalidCount'];
		
		fclose($handle); 

		// Displaying list of vehicles with valid registration
		displayValidRegistrationList($header, $validFuelTypes);

		// Exporting CSVs based on fuel type
		exportFuelTypeCsv($validFuelTypes, $header);

		// Display the number of invalid registrations
		echo "<p style='color: red'><strong>Number of vehicles with INVALID registration number: $invalidCount</strong></p>";

	} else {
		echo "Failed to open the file.";
	}
	
	// Function to import registration CSV
	function importRegistrationCsv($header, $handle) {
		if (!is_array($header)) {
			die("Invalid or empty header columns.");
		}

		$carReg = array_search('Car Registration', $header);
		$fuelColumn = array_search('Fuel', $header);

		if ($carReg === false || $fuelColumn === false) {
			die("Required columns are not found in the CSV.");
		}

		$regPattern = '/^[A-Z]{2}[0-9]{2} [A-Z]{3}$/i'; // Valid registration pattern
		$readRegs = [];
		$paramImport = [];
		$validFuelTypes = [];
		$invalidCount = 0;

		while (($data = fgetcsv($handle, 1000, ',')) !== false) {
			if (!is_array($data) || !isset($data[$carReg], $data[$fuelColumn])) {
				continue;
			}

			$reg = strtoupper(trim($data[$carReg]));
			$fuelType = ucfirst(strtolower(trim($data[$fuelColumn]))); // Sanitizing fuel type

			// Skipping duplicate entries
			if (isset($readRegs[$reg])) {
				continue;
			}
			$readRegs[$reg] = true;

			// Matching valid registration with the criteria
			if (preg_match($regPattern, $reg)) {
				if (!isset($validFuelTypes[$fuelType])) {
					$validFuelTypes[$fuelType] = [];
				}
				$validFuelTypes[$fuelType][] = $data;
			} else {
				$invalidCount++;
			}
		}
		$paramImport['validFuelType'] = $validFuelTypes;
		$paramImport['invalidCount'] = $invalidCount;
		return $paramImport;
	}
	
	// Function to display list of vehicles with valid registration
	function displayValidRegistrationList($header, $validFuelTypes) {
		echo "<h3 style='color: green'>List of vehicles with Valid Registration</h3>";
		echo "<table width='80%' border='1' itempadding='3'><tr>";
		foreach ($header as $col) {
			echo "<th>" . htmlspecialchars((string)$col) . "</th>";
		}
		echo "</tr>";

		foreach ($validFuelTypes as $rows) {
			foreach ($rows as $row) {
				echo "<tr>";
				foreach ($row as $item) {
					echo "<td>" . htmlspecialchars((string)$item) . "</td>";
				}
				echo "</tr>";
			}
		}
		echo "</table>";
	}
	// Function to export CSVs based on fuel type
	function exportFuelTypeCsv($validFuelTypes, $header) {
		foreach ($validFuelTypes as $fuelType => $rows) {
			$sanitizedFuel = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $fuelType); // Sanitizing fuel name
			$filename = $sanitizedFuel."_vehicles.csv";

			if (($outHandle = fopen($filename, 'w')) !== false) {
				fputcsv($outHandle, $header);
				foreach ($rows as $row) {
					fputcsv($outHandle, $row);
				}
				fclose($outHandle);
				echo "Created the CSV file: <strong>$filename</strong><br>";
			} else {
				echo "Failed to write the CSV file: <strong>$filename</strong><br>";
			}
		}
	}
?>