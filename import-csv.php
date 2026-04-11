<?php
	define('PREPEND_PATH', '');
	include_once(__DIR__ . '/lib.php');

	// accept a record as an assoc array, return transformed row ready to insert to table
	$transformFunctions = [
		'whys' => function($data, $options = []) {
			if(isset($data['whys_created'])) $data['whys_created'] = guessMySQLDateTime($data['whys_created']);
			if(isset($data['whys_updated'])) $data['whys_updated'] = guessMySQLDateTime($data['whys_updated']);

			return $data;
		},
		'wheres' => function($data, $options = []) {
			if(isset($data['wheres_created'])) $data['wheres_created'] = guessMySQLDateTime($data['wheres_created']);
			if(isset($data['wheres_updated'])) $data['wheres_updated'] = guessMySQLDateTime($data['wheres_updated']);

			return $data;
		},
		'whichs' => function($data, $options = []) {
			if(isset($data['whichs_created'])) $data['whichs_created'] = guessMySQLDateTime($data['whichs_created']);
			if(isset($data['whichs_updated'])) $data['whichs_updated'] = guessMySQLDateTime($data['whichs_updated']);

			return $data;
		},
		'whens' => function($data, $options = []) {
			if(isset($data['whens_created'])) $data['whens_created'] = guessMySQLDateTime($data['whens_created']);
			if(isset($data['whens_updated'])) $data['whens_updated'] = guessMySQLDateTime($data['whens_updated']);

			return $data;
		},
		'whos' => function($data, $options = []) {
			if(isset($data['whos_created'])) $data['whos_created'] = guessMySQLDateTime($data['whos_created']);
			if(isset($data['whos_updated'])) $data['whos_updated'] = guessMySQLDateTime($data['whos_updated']);

			return $data;
		},
		'whats' => function($data, $options = []) {
			if(isset($data['whats_created'])) $data['whats_created'] = guessMySQLDateTime($data['whats_created']);
			if(isset($data['whats_updated'])) $data['whats_updated'] = guessMySQLDateTime($data['whats_updated']);

			return $data;
		},
	];

	// accept a record as an assoc array, return a boolean indicating whether to import or skip record
	$filterFunctions = [
		'whys' => function($data, $options = []) { return true; },
		'wheres' => function($data, $options = []) { return true; },
		'whichs' => function($data, $options = []) { return true; },
		'whens' => function($data, $options = []) { return true; },
		'whos' => function($data, $options = []) { return true; },
		'whats' => function($data, $options = []) { return true; },
	];

	/*
	Hook file for overwriting/amending $transformFunctions and $filterFunctions:
	hooks/import-csv.php
	If found, it's included below

	The way this works is by either completely overwriting any of the above 2 arrays,
	or, more commonly, overwriting a single function, for example:
		$transformFunctions['tablename'] = function($data, $options = []) {
			// new definition here
			// then you must return transformed data
			return $data;
		};

	Another scenario is transforming a specific field and leaving other fields to the default
	transformation. One possible way of doing this is to store the original transformation function
	in GLOBALS array, calling it inside the custom transformation function, then modifying the
	specific field:
		$GLOBALS['originalTransformationFunction'] = $transformFunctions['tablename'];
		$transformFunctions['tablename'] = function($data, $options = []) {
			$data = call_user_func_array($GLOBALS['originalTransformationFunction'], [$data, $options]);
			$data['fieldname'] = 'transformed value';
			return $data;
		};
	*/

	@include(__DIR__ . '/hooks/import-csv.php');

	$ui = new CSVImportUI($transformFunctions, $filterFunctions);
