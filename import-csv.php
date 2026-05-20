<?php
	define('PREPEND_PATH', '');
	include_once(__DIR__ . '/lib.php');

	// accept a record as an assoc array, return transformed row ready to insert to table
	$transformFunctions = [
		'madb' => function($data, $options = []) {
			if(isset($data['madb_what1'])) $data['madb_what1'] = pkGivenLookupText($data['madb_what1'], 'madb', 'madb_what1');
			if(isset($data['madb_who1'])) $data['madb_who1'] = pkGivenLookupText($data['madb_who1'], 'madb', 'madb_who1');
			if(isset($data['madb_when1'])) $data['madb_when1'] = pkGivenLookupText($data['madb_when1'], 'madb', 'madb_when1');
			if(isset($data['madb_which1'])) $data['madb_which1'] = pkGivenLookupText($data['madb_which1'], 'madb', 'madb_which1');
			if(isset($data['madb_where1'])) $data['madb_where1'] = pkGivenLookupText($data['madb_where1'], 'madb', 'madb_where1');
			if(isset($data['madb_why1'])) $data['madb_why1'] = pkGivenLookupText($data['madb_why1'], 'madb', 'madb_why1');
			if(isset($data['madb_howr1'])) $data['madb_howr1'] = pkGivenLookupText($data['madb_howr1'], 'madb', 'madb_howr1');
			if(isset($data['madb_hows1'])) $data['madb_hows1'] = pkGivenLookupText($data['madb_hows1'], 'madb', 'madb_hows1');
			if(isset($data['madb_howq1'])) $data['madb_howq1'] = pkGivenLookupText($data['madb_howq1'], 'madb', 'madb_howq1');
			if(isset($data['madb_howt1'])) $data['madb_howt1'] = pkGivenLookupText($data['madb_howt1'], 'madb', 'madb_howt1');
			if(isset($data['madb_premises_id'])) $data['madb_premises_id'] = pkGivenLookupText($data['madb_premises_id'], 'madb', 'madb_premises_id');
			if(isset($data['madb_created'])) $data['madb_created'] = guessMySQLDateTime($data['madb_created']);
			if(isset($data['madb_updated'])) $data['madb_updated'] = guessMySQLDateTime($data['madb_updated']);
			if(isset($data['madb_why2'])) $data['madb_why2'] = thisOr($data['madb_why1'], pkGivenLookupText($data['madb_why2'], 'madb', 'madb_why2'));
			if(isset($data['madb_why3'])) $data['madb_why3'] = thisOr($data['madb_why1'], pkGivenLookupText($data['madb_why3'], 'madb', 'madb_why3'));
			if(isset($data['madb_where2'])) $data['madb_where2'] = thisOr($data['madb_where1'], pkGivenLookupText($data['madb_where2'], 'madb', 'madb_where2'));
			if(isset($data['madb_where3'])) $data['madb_where3'] = thisOr($data['madb_where1'], pkGivenLookupText($data['madb_where3'], 'madb', 'madb_where3'));
			if(isset($data['madb_which2'])) $data['madb_which2'] = thisOr($data['madb_which1'], pkGivenLookupText($data['madb_which2'], 'madb', 'madb_which2'));
			if(isset($data['madb_which3'])) $data['madb_which3'] = thisOr($data['madb_which1'], pkGivenLookupText($data['madb_which3'], 'madb', 'madb_which3'));
			if(isset($data['madb_when2'])) $data['madb_when2'] = thisOr($data['madb_when1'], pkGivenLookupText($data['madb_when2'], 'madb', 'madb_when2'));
			if(isset($data['madb_when3'])) $data['madb_when3'] = thisOr($data['madb_when1'], pkGivenLookupText($data['madb_when3'], 'madb', 'madb_when3'));
			if(isset($data['madb_who2'])) $data['madb_who2'] = thisOr($data['madb_who1'], pkGivenLookupText($data['madb_who2'], 'madb', 'madb_who2'));
			if(isset($data['madb_who3'])) $data['madb_who3'] = thisOr($data['madb_who1'], pkGivenLookupText($data['madb_who3'], 'madb', 'madb_who3'));
			if(isset($data['madb_what2'])) $data['madb_what2'] = thisOr($data['madb_what1'], pkGivenLookupText($data['madb_what2'], 'madb', 'madb_what2'));
			if(isset($data['madb_what3'])) $data['madb_what3'] = thisOr($data['madb_what1'], pkGivenLookupText($data['madb_what3'], 'madb', 'madb_what3'));
			if(isset($data['madb_howr2'])) $data['madb_howr2'] = thisOr($data['madb_howr1'], pkGivenLookupText($data['madb_howr2'], 'madb', 'madb_howr2'));
			if(isset($data['madb_howr3'])) $data['madb_howr3'] = thisOr($data['madb_howr1'], pkGivenLookupText($data['madb_howr3'], 'madb', 'madb_howr3'));
			if(isset($data['madb_hows2'])) $data['madb_hows2'] = thisOr($data['madb_hows1'], pkGivenLookupText($data['madb_hows2'], 'madb', 'madb_hows2'));
			if(isset($data['madb_hows3'])) $data['madb_hows3'] = thisOr($data['madb_hows1'], pkGivenLookupText($data['madb_hows3'], 'madb', 'madb_hows3'));
			if(isset($data['madb_howq2'])) $data['madb_howq2'] = thisOr($data['madb_howq1'], pkGivenLookupText($data['madb_howq2'], 'madb', 'madb_howq2'));
			if(isset($data['madb_howq3'])) $data['madb_howq3'] = thisOr($data['madb_howq1'], pkGivenLookupText($data['madb_howq3'], 'madb', 'madb_howq3'));
			if(isset($data['madb_howt2'])) $data['madb_howt2'] = thisOr($data['madb_howt1'], pkGivenLookupText($data['madb_howt2'], 'madb', 'madb_howt2'));
			if(isset($data['madb_howt3'])) $data['madb_howt3'] = thisOr($data['madb_howt1'], pkGivenLookupText($data['madb_howt3'], 'madb', 'madb_howt3'));

			return $data;
		},
		'whats' => function($data, $options = []) {
			if(isset($data['whats_created'])) $data['whats_created'] = guessMySQLDateTime($data['whats_created']);
			if(isset($data['whats_updated'])) $data['whats_updated'] = guessMySQLDateTime($data['whats_updated']);

			return $data;
		},
		'whos' => function($data, $options = []) {
			if(isset($data['whos_created'])) $data['whos_created'] = guessMySQLDateTime($data['whos_created']);
			if(isset($data['whos_updated'])) $data['whos_updated'] = guessMySQLDateTime($data['whos_updated']);

			return $data;
		},
		'whens' => function($data, $options = []) {
			if(isset($data['whens_created'])) $data['whens_created'] = guessMySQLDateTime($data['whens_created']);
			if(isset($data['whens_updated'])) $data['whens_updated'] = guessMySQLDateTime($data['whens_updated']);

			return $data;
		},
		'whichs' => function($data, $options = []) {
			if(isset($data['whichs_created'])) $data['whichs_created'] = guessMySQLDateTime($data['whichs_created']);
			if(isset($data['whichs_updated'])) $data['whichs_updated'] = guessMySQLDateTime($data['whichs_updated']);

			return $data;
		},
		'wheres' => function($data, $options = []) {
			if(isset($data['wheres_created'])) $data['wheres_created'] = guessMySQLDateTime($data['wheres_created']);
			if(isset($data['wheres_updated'])) $data['wheres_updated'] = guessMySQLDateTime($data['wheres_updated']);

			return $data;
		},
		'whys' => function($data, $options = []) {
			if(isset($data['whys_created'])) $data['whys_created'] = guessMySQLDateTime($data['whys_created']);
			if(isset($data['whys_updated'])) $data['whys_updated'] = guessMySQLDateTime($data['whys_updated']);

			return $data;
		},
		'howrs' => function($data, $options = []) {
			if(isset($data['howrs_created'])) $data['howrs_created'] = guessMySQLDateTime($data['howrs_created']);
			if(isset($data['howrs_updated'])) $data['howrs_updated'] = guessMySQLDateTime($data['howrs_updated']);

			return $data;
		},
		'howss' => function($data, $options = []) {
			if(isset($data['howss_created'])) $data['howss_created'] = guessMySQLDateTime($data['howss_created']);
			if(isset($data['howss_updated'])) $data['howss_updated'] = guessMySQLDateTime($data['howss_updated']);

			return $data;
		},
		'howqs' => function($data, $options = []) {
			if(isset($data['howqs_created'])) $data['howqs_created'] = guessMySQLDateTime($data['howqs_created']);
			if(isset($data['howqs_updated'])) $data['howqs_updated'] = guessMySQLDateTime($data['howqs_updated']);

			return $data;
		},
		'howts' => function($data, $options = []) {
			if(isset($data['howrs_created'])) $data['howrs_created'] = guessMySQLDateTime($data['howrs_created']);
			if(isset($data['howrs_updated'])) $data['howrs_updated'] = guessMySQLDateTime($data['howrs_updated']);

			return $data;
		},
		'utedb' => function($data, $options = []) {
			if(isset($data['utedb_madb'])) $data['utedb_madb'] = pkGivenLookupText($data['utedb_madb'], 'utedb', 'utedb_madb');
			if(isset($data['utedb_created'])) $data['utedb_created'] = guessMySQLDateTime($data['utedb_created']);
			if(isset($data['utedb_updated'])) $data['utedb_updated'] = guessMySQLDateTime($data['utedb_updated']);
			if(isset($data['utedb_who1'])) $data['utedb_who1'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_who1'], 'utedb', 'utedb_who1'));
			if(isset($data['utedb_when1'])) $data['utedb_when1'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_when1'], 'utedb', 'utedb_when1'));
			if(isset($data['utedb_which1'])) $data['utedb_which1'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_which1'], 'utedb', 'utedb_which1'));
			if(isset($data['utedb_where1'])) $data['utedb_where1'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_where1'], 'utedb', 'utedb_where1'));
			if(isset($data['utedb_why1'])) $data['utedb_why1'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_why1'], 'utedb', 'utedb_why1'));
			if(isset($data['utedb_howr1'])) $data['utedb_howr1'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_howr1'], 'utedb', 'utedb_howr1'));
			if(isset($data['utedb_hows1'])) $data['utedb_hows1'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_hows1'], 'utedb', 'utedb_hows1'));
			if(isset($data['utedb_howq1'])) $data['utedb_howq1'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_howq1'], 'utedb', 'utedb_howq1'));
			if(isset($data['utedb_howt1'])) $data['utedb_howt1'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_howt1'], 'utedb', 'utedb_howt1'));
			if(isset($data['utedb_why2'])) $data['utedb_why2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_why2'], 'utedb', 'utedb_why2'));
			if(isset($data['utedb_why3'])) $data['utedb_why3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_why3'], 'utedb', 'utedb_why3'));
			if(isset($data['utedb_where2'])) $data['utedb_where2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_where2'], 'utedb', 'utedb_where2'));
			if(isset($data['utedb_where3'])) $data['utedb_where3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_where3'], 'utedb', 'utedb_where3'));
			if(isset($data['utedb_which2'])) $data['utedb_which2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_which2'], 'utedb', 'utedb_which2'));
			if(isset($data['utedb_which3'])) $data['utedb_which3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_which3'], 'utedb', 'utedb_which3'));
			if(isset($data['utedb_when2'])) $data['utedb_when2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_when2'], 'utedb', 'utedb_when2'));
			if(isset($data['utedb_when3'])) $data['utedb_when3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_when3'], 'utedb', 'utedb_when3'));
			if(isset($data['utedb_who2'])) $data['utedb_who2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_who2'], 'utedb', 'utedb_who2'));
			if(isset($data['utedb_who3'])) $data['utedb_who3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_who3'], 'utedb', 'utedb_who3'));
			if(isset($data['utedb_what2'])) $data['utedb_what2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_what2'], 'utedb', 'utedb_what2'));
			if(isset($data['utedb_what3'])) $data['utedb_what3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_what3'], 'utedb', 'utedb_what3'));
			if(isset($data['utedb_howr2'])) $data['utedb_howr2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_howr2'], 'utedb', 'utedb_howr2'));
			if(isset($data['utedb_howr3'])) $data['utedb_howr3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_howr3'], 'utedb', 'utedb_howr3'));
			if(isset($data['utedb_hows2'])) $data['utedb_hows2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_hows2'], 'utedb', 'utedb_hows2'));
			if(isset($data['utedb_hows3'])) $data['utedb_hows3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_hows3'], 'utedb', 'utedb_hows3'));
			if(isset($data['utedb_howq2'])) $data['utedb_howq2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_howq2'], 'utedb', 'utedb_howq2'));
			if(isset($data['utedb_howq3'])) $data['utedb_howq3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_howq3'], 'utedb', 'utedb_howq3'));
			if(isset($data['utedb_howt2'])) $data['utedb_howt2'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_howt2'], 'utedb', 'utedb_howt2'));
			if(isset($data['utedb_howt3'])) $data['utedb_howt3'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_howt3'], 'utedb', 'utedb_howt3'));
			if(isset($data['utedb_premises_id'])) $data['utedb_premises_id'] = thisOr($data['utedb_madb'], pkGivenLookupText($data['utedb_premises_id'], 'utedb', 'utedb_premises_id'));

			return $data;
		},
		'premises' => function($data, $options = []) {
			if(isset($data['premises_created'])) $data['premises_created'] = guessMySQLDateTime($data['premises_created']);
			if(isset($data['premises_updated'])) $data['premises_updated'] = guessMySQLDateTime($data['premises_updated']);

			return $data;
		},
		'pnb' => function($data, $options = []) {
			if(isset($data['pnb_premises_id'])) $data['pnb_premises_id'] = pkGivenLookupText($data['pnb_premises_id'], 'pnb', 'pnb_premises_id');
			if(isset($data['pnb_whos_id'])) $data['pnb_whos_id'] = pkGivenLookupText($data['pnb_whos_id'], 'pnb', 'pnb_whos_id');
			if(isset($data['pnb_created'])) $data['pnb_created'] = guessMySQLDateTime($data['pnb_created']);
			if(isset($data['pnb_updated'])) $data['pnb_updated'] = guessMySQLDateTime($data['pnb_updated']);

			return $data;
		},
	];

	// accept a record as an assoc array, return a boolean indicating whether to import or skip record
	$filterFunctions = [
		'madb' => function($data, $options = []) { return true; },
		'whats' => function($data, $options = []) { return true; },
		'whos' => function($data, $options = []) { return true; },
		'whens' => function($data, $options = []) { return true; },
		'whichs' => function($data, $options = []) { return true; },
		'wheres' => function($data, $options = []) { return true; },
		'whys' => function($data, $options = []) { return true; },
		'howrs' => function($data, $options = []) { return true; },
		'howss' => function($data, $options = []) { return true; },
		'howqs' => function($data, $options = []) { return true; },
		'howts' => function($data, $options = []) { return true; },
		'utedb' => function($data, $options = []) { return true; },
		'premises' => function($data, $options = []) { return true; },
		'pnb' => function($data, $options = []) { return true; },
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
