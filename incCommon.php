<?php

	#########################################################
	/*
	~~~~~~ LIST OF FUNCTIONS ~~~~~~
		get_table_groups() -- returns an associative array (table_group => tables_array)
		getTablePermissions($tn) -- returns an array of permissions allowed for logged member to given table (allowAccess, allowInsert, allowView, allowEdit, allowDelete) -- allowAccess is set to true if any access level is allowed
		get_sql_fields($tn) -- returns the SELECT part of the table view query
		get_sql_from($tn[, true, [, false]]) -- returns the FROM part of the table view query, with full joins (unless third paramaeter is set to true), optionally skipping permissions if true passed as 2nd param.
		get_joined_record($table, $id[, true]) -- returns assoc array of record values for given PK value of given table, with full joins, optionally skipping permissions if true passed as 3rd param.
		get_defaults($table) -- returns assoc array of table fields as array keys and default values (or empty), excluding automatic values as array values
		htmlUserBar() -- returns html code for displaying user login status to be used on top of pages.
		showNotifications($msg, $class) -- returns html code for displaying a notification. If no parameters provided, processes the GET request for possible notifications.
		parseMySQLDate(a, b) -- returns a if valid mysql date, or b if valid mysql date, or today if b is true, or empty if b is false.
		parseCode(code) -- calculates and returns special values to be inserted in automatic fields.
		addFilter(i, filterAnd, filterField, filterOperator, filterValue) -- enforce a filter over data
		clearFilters() -- clear all filters
		loadView($view, $data) -- passes $data to templates/{$view}.php and returns the output
		loadTable($table, $data) -- loads table template, passing $data to it
		br2nl($text) -- replaces all variations of HTML <br> tags with a new line character
		entitiesToUTF8($text) -- convert unicode entities (e.g. &#1234;) to actual UTF8 characters, requires multibyte string PHP extension
		func_get_args_byref() -- returns an array of arguments passed to a function, by reference
		permissions_sql($table, $level) -- returns an array containing the FROM and WHERE additions for applying permissions to an SQL query
		error_message($msg[, $back_url]) -- returns html code for a styled error message .. pass explicit false in second param to suppress back button
		toMySQLDate($formattedDate, $sep = datalist_date_separator, $ord = datalist_date_format)
		reIndex(&$arr) -- returns a copy of the given array, with keys replaced by 1-based numeric indices, and values replaced by original keys
		get_embed($provider, $url[, $width, $height, $retrieve]) -- returns embed code for a given url (supported providers: [auto-detect], or explicitly pass one of: youtube, vimeo, googlemap, dailymotion, videofileurl)
		check_record_permission($table, $id, $perm = 'view') -- returns true if current user has the specified permission $perm ('view', 'edit' or 'delete') for the given recors, false otherwise
		NavMenus($options) -- returns the HTML code for the top navigation menus. $options is not implemented currently.
		StyleSheet() -- returns the HTML code for included style sheet files to be placed in the <head> section.
		PrepareUploadedFile($FieldName, $MaxSize, $FileTypes={image file types}, $NoRename=false, $dir="") -- validates and moves uploaded file for given $FieldName into the given $dir (or the default one if empty)
		get_home_links($homeLinks, $default_classes, $tgroup) -- process $homeLinks array and return custom links for homepage. Applies $default_classes to links if links have classes defined, and filters links by $tgroup (using '*' matches all table_group values)
		quick_search_html($search_term, $label, $separate_dv = true) -- returns HTML code for the quick search box.
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	*/

	#########################################################

	function get_table_groups($skip_authentication = false) {
		$tables = getTableList($skip_authentication);
		$all_groups = ['VTAP', '6Ws', '4Hs'];

		$groups = [];
		foreach($all_groups as $grp) {
			foreach($tables as $tn => $td) {
				if($td[3] && $td[3] == $grp) $groups[$grp][] = $tn;
				if(!$td[3]) $groups[0][] = $tn;
			}
		}

		return $groups;
	}

	#########################################################

	function getTablePermissions($tn) {
		static $table_permissions = [];
		if(isset($table_permissions[$tn])) return $table_permissions[$tn];

		$groupID = getLoggedGroupID();
		$memberID = makeSafe(getLoggedMemberID());
		$res_group = sql("SELECT `tableName`, `allowInsert`, `allowView`, `allowEdit`, `allowDelete` FROM `membership_grouppermissions` WHERE `groupID`='{$groupID}'", $eo);
		$res_user  = sql("SELECT `tableName`, `allowInsert`, `allowView`, `allowEdit`, `allowDelete` FROM `membership_userpermissions`  WHERE LCASE(`memberID`)='{$memberID}'", $eo);

		while($row = db_fetch_assoc($res_group)) {
			$table_permissions[$row['tableName']] = [
				1 => intval($row['allowInsert']),
				2 => intval($row['allowView']),
				3 => intval($row['allowEdit']),
				4 => intval($row['allowDelete']),
				'insert' => intval($row['allowInsert']),
				'view' => intval($row['allowView']),
				'edit' => intval($row['allowEdit']),
				'delete' => intval($row['allowDelete'])
			];
		}

		// user-specific permissions, if specified, overwrite his group permissions
		while($row = db_fetch_assoc($res_user)) {
			$table_permissions[$row['tableName']] = [
				1 => intval($row['allowInsert']),
				2 => intval($row['allowView']),
				3 => intval($row['allowEdit']),
				4 => intval($row['allowDelete']),
				'insert' => intval($row['allowInsert']),
				'view' => intval($row['allowView']),
				'edit' => intval($row['allowEdit']),
				'delete' => intval($row['allowDelete'])
			];
		}

		// if user has any type of access, set 'access' flag
		foreach($table_permissions as $t => $p) {
			$table_permissions[$t]['access'] = $table_permissions[$t][0] = false;

			if($p['insert'] || $p['view'] || $p['edit'] || $p['delete']) {
				$table_permissions[$t]['access'] = $table_permissions[$t][0] = true;
			}
		}

		return $table_permissions[$tn] ?? [];
	}

	#########################################################

	function get_sql_fields($table_name) {
		$sql_fields = [
			'madb' => "`madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated'",
			'whats' => "`whats`.`whats_id` as 'whats_id', `whats`.`whats_what1` as 'whats_what1', `whats`.`whats_what2` as 'whats_what2', `whats`.`whats_what3` as 'whats_what3', `whats`.`whats_description` as 'whats_description', `whats`.`whats_created` as 'whats_created', `whats`.`whats_updated` as 'whats_updated'",
			'whos' => "`whos`.`whos_id` as 'whos_id', `whos`.`whos_who1` as 'whos_who1', `whos`.`whos_who2` as 'whos_who2', `whos`.`whos_who3` as 'whos_who3', `whos`.`whos_description` as 'whos_description', `whos`.`whos_swg_token` as 'whos_swg_token', `whos`.`whos_swg_email` as 'whos_swg_email', IF(    CHAR_LENGTH(`premises1`.`premises_name`) || CHAR_LENGTH(`premises1`.`premises_radius`), CONCAT_WS('',   `premises1`.`premises_name`, '-', `premises1`.`premises_radius`), '') as 'whos_premise', `whos`.`whos_created` as 'whos_created', `whos`.`whos_updated` as 'whos_updated'",
			'whens' => "`whens`.`whens_id` as 'whens_id', `whens`.`whens_when1` as 'whens_when1', `whens`.`whens_when2` as 'whens_when2', TIME_FORMAT(`whens`.`whens_when3`, '%r') as 'whens_when3', `whens`.`whens_description` as 'whens_description', `whens`.`whens_created` as 'whens_created', `whens`.`whens_updated` as 'whens_updated'",
			'whichs' => "`whichs`.`whichs_id` as 'whichs_id', `whichs`.`whichs_which1` as 'whichs_which1', `whichs`.`whichs_which2` as 'whichs_which2', `whichs`.`whichs_which3` as 'whichs_which3', `whichs`.`whichs_description` as 'whichs_description', `whichs`.`whichs_created` as 'whichs_created', `whichs`.`whichs_updated` as 'whichs_updated'",
			'wheres' => "`wheres`.`wheres_id` as 'wheres_id', `wheres`.`wheres_where1` as 'wheres_where1', `wheres`.`wheres_where2` as 'wheres_where2', `wheres`.`wheres_where3` as 'wheres_where3', `wheres`.`wheres_description` as 'wheres_description', `wheres`.`wheres_created` as 'wheres_created', `wheres`.`wheres_updated` as 'wheres_updated'",
			'whys' => "`whys`.`whys_id` as 'whys_id', `whys`.`whys_why1` as 'whys_why1', `whys`.`whys_why2` as 'whys_why2', `whys`.`whys_why3` as 'whys_why3', `whys`.`whys_description` as 'whys_description', `whys`.`whys_created` as 'whys_created', `whys`.`whys_updated` as 'whys_updated'",
			'howrs' => "`howrs`.`howrs_id` as 'howrs_id', `howrs`.`howrs_howr1` as 'howrs_howr1', `howrs`.`howrs_howr2` as 'howrs_howr2', `howrs`.`howrs_howr3` as 'howrs_howr3', `howrs`.`howrs_description` as 'howrs_description', `howrs`.`howrs_created` as 'howrs_created', `howrs`.`howrs_updated` as 'howrs_updated'",
			'howss' => "`howss`.`howss_id` as 'howss_id', `howss`.`howss_hows1` as 'howss_hows1', `howss`.`howss_hows2` as 'howss_hows2', `howss`.`howss_hows3` as 'howss_hows3', `howss`.`howss_hows4` as 'howss_hows4', `howss`.`howss_description` as 'howss_description', `howss`.`howss_created` as 'howss_created', `howss`.`howss_updated` as 'howss_updated'",
			'howqs' => "`howqs`.`howqs_id` as 'howqs_id', `howqs`.`howqs_howq1` as 'howqs_howq1', `howqs`.`howqs_howq2` as 'howqs_howq2', `howqs`.`howqs_howq3` as 'howqs_howq3', `howqs`.`howqs_description` as 'howqs_description', `howqs`.`howqs_created` as 'howqs_created', `howqs`.`howqs_updated` as 'howqs_updated'",
			'howts' => "`howts`.`howts_id` as 'howts_id', `howts`.`howts_howt1` as 'howts_howt1', `howts`.`howts_howt2` as 'howts_howt2', `howts`.`howts_howt3` as 'howts_howt3', `howts`.`howrs_description` as 'howrs_description', `howts`.`howrs_created` as 'howrs_created', `howts`.`howrs_updated` as 'howrs_updated'",
			'utedb' => "`utedb`.`utedb_id` as 'utedb_id', IF(    CHAR_LENGTH(`madb1`.`madb_id`) || CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `madb1`.`madb_id`, '-', `whats1`.`whats_what1`), '') as 'utedb_madb', `utedb`.`utedb_madb_who1` as 'utedb_madb_who1', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'utedb_premises_id', `utedb`.`utedb_proof_image` as 'utedb_proof_image', `utedb`.`utedb_ai_audit` as 'utedb_ai_audit', `utedb`.`utedb_pc_audit` as 'utedb_pc_audit', `utedb`.`utedb_rda_audit` as 'utedb_rda_audit', `utedb`.`utedb_bb_audit` as 'utedb_bb_audit', `utedb`.`utedb_car` as 'utedb_car', `utedb`.`utedb_car_vid` as 'utedb_car_vid', `utedb`.`utedb_col_tf` as 'utedb_col_tf', `utedb`.`utedb_delta_flag` as 'utedb_delta_flag', `utedb`.`utedb_hows1` as 'utedb_hows1', IF(    CHAR_LENGTH(`whos1`.`whos_who1`) || CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who1`, '-', `whos1`.`whos_who2`), '') as 'utedb_elairda_id', `utedb`.`utedb_created` as 'utedb_created', `utedb`.`utedb_updated` as 'utedb_updated'",
			'premises' => "`premises`.`premises_id` as 'premises_id', `premises`.`premises_name` as 'premises_name', `premises`.`premises_latitude` as 'premises_latitude', `premises`.`premises_longitude` as 'premises_longitude', `premises`.`premises_radius` as 'premises_radius', `premises`.`premises_created` as 'premises_created', `premises`.`premises_updated` as 'premises_updated'",
			'pnb' => "`pnb`.`pnb_id` as 'pnb_id', `pnb`.`pnb_type` as 'pnb_type', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'pnb_premises_id', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'pnb_whos_id', `pnb`.`pnb_delta_flag` as 'pnb_delta_flag', `pnb`.`pnb_admin` as 'pnb_admin', `pnb`.`pnb_comments` as 'pnb_comments', `pnb`.`pnb_created` as 'pnb_created', `pnb`.`pnb_updated` as 'pnb_updated'",
		];

		if(isset($sql_fields[$table_name])) return $sql_fields[$table_name];

		return false;
	}

	#########################################################

	function get_sql_from($table_name, $skip_permissions = false, $skip_joins = false, $lower_permissions = false) {
		$sql_from = [
			'madb' => "`madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` ",
			'whats' => "`whats` ",
			'whos' => "`whos` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`whos`.`whos_premise` ",
			'whens' => "`whens` ",
			'whichs' => "`whichs` ",
			'wheres' => "`wheres` ",
			'whys' => "`whys` ",
			'howrs' => "`howrs` ",
			'howss' => "`howss` ",
			'howqs' => "`howqs` ",
			'howts' => "`howts` ",
			'utedb' => "`utedb` LEFT JOIN `madb` as madb1 ON `madb1`.`madb_id`=`utedb`.`utedb_madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb1`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`utedb`.`utedb_elairda_id` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb1`.`madb_premises_id` ",
			'premises' => "`premises` ",
			'pnb' => "`pnb` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`pnb`.`pnb_premises_id` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`pnb`.`pnb_whos_id` ",
		];

		$pkey = [
			'madb' => 'madb_id',
			'whats' => 'whats_id',
			'whos' => 'whos_id',
			'whens' => 'whens_id',
			'whichs' => 'whichs_id',
			'wheres' => 'wheres_id',
			'whys' => 'whys_id',
			'howrs' => 'howrs_id',
			'howss' => 'howss_id',
			'howqs' => 'howqs_id',
			'howts' => 'howts_id',
			'utedb' => 'utedb_id',
			'premises' => 'premises_id',
			'pnb' => 'pnb_id',
		];

		if(!isset($sql_from[$table_name])) return false;

		$from = ($skip_joins ? "`{$table_name}`" : $sql_from[$table_name]);

		if($skip_permissions) return $from . ' WHERE 1=1';

		// mm: build the query based on current member's permissions
		// allowing lower permissions if $lower_permissions set to 'user' or 'group'
		$perm = getTablePermissions($table_name);
		if($perm['view'] == 1 || ($perm['view'] > 1 && $lower_permissions == 'user')) { // view owner only
			$from .= ", `membership_userrecords` WHERE `{$table_name}`.`{$pkey[$table_name]}`=`membership_userrecords`.`pkValue` AND `membership_userrecords`.`tableName`='{$table_name}' AND LCASE(`membership_userrecords`.`memberID`)='" . getLoggedMemberID() . "'";
		} elseif($perm['view'] == 2 || ($perm['view'] > 2 && $lower_permissions == 'group')) { // view group only
			$from .= ", `membership_userrecords` WHERE `{$table_name}`.`{$pkey[$table_name]}`=`membership_userrecords`.`pkValue` AND `membership_userrecords`.`tableName`='{$table_name}' AND `membership_userrecords`.`groupID`='" . getLoggedGroupID() . "'";
		} elseif($perm['view'] == 3) { // view all
			$from .= ' WHERE 1=1';
		} else { // view none
			return false;
		}

		return $from;
	}

	#########################################################

	function get_joined_record($table, $id, $skip_permissions = false) {
		$sql_fields = get_sql_fields($table);
		$sql_from = get_sql_from($table, $skip_permissions);

		if(!$sql_fields || !$sql_from) return false;

		$pk = getPKFieldName($table);
		if(!$pk) return false;

		$safe_id = makeSafe($id, false);
		$sql = "SELECT {$sql_fields} FROM {$sql_from} AND `{$table}`.`{$pk}`='{$safe_id}'";
		$eo = ['silentErrors' => true];
		$res = sql($sql, $eo);
		if($row = db_fetch_assoc($res)) return $row;

		return false;
	}

	#########################################################

	function get_defaults($table) {
		/* array of tables and their fields, with default values (or empty), excluding automatic values */
		$defaults = [
			'madb' => [
				'madb_id' => '',
				'madb_what1' => '',
				'madb_who1' => '',
				'madb_when1' => '',
				'madb_which1' => '',
				'madb_where1' => '',
				'madb_why1' => '',
				'madb_howr1' => '',
				'madb_hows1' => '',
				'madb_howq1' => '',
				'madb_howt1' => '',
				'madb_why2' => '',
				'madb_why3' => '',
				'madb_where2' => '',
				'madb_where3' => '',
				'madb_which2' => '',
				'madb_which3' => '',
				'madb_when2' => '',
				'madb_when3' => '',
				'madb_who2' => '',
				'madb_who3' => '',
				'madb_what2' => '',
				'madb_what3' => '',
				'madb_howr2' => '',
				'madb_howr3' => '',
				'madb_hows2' => '',
				'madb_hows3' => '',
				'madb_howq2' => '',
				'madb_howq3' => '',
				'madb_howt2' => '',
				'madb_howt3' => '',
				'madb_premises_id' => '',
				'madb_status' => 'Active',
				'madb_created' => '',
				'madb_updated' => '',
			],
			'whats' => [
				'whats_id' => '',
				'whats_what1' => '',
				'whats_what2' => '',
				'whats_what3' => '',
				'whats_description' => '',
				'whats_created' => '',
				'whats_updated' => '',
			],
			'whos' => [
				'whos_id' => '',
				'whos_who1' => '',
				'whos_who2' => '',
				'whos_who3' => '',
				'whos_description' => '',
				'whos_swg_token' => '',
				'whos_swg_email' => '',
				'whos_premise' => '',
				'whos_created' => '',
				'whos_updated' => '',
			],
			'whens' => [
				'whens_id' => '',
				'whens_when1' => '',
				'whens_when2' => '',
				'whens_when3' => '',
				'whens_description' => '',
				'whens_created' => '',
				'whens_updated' => '',
			],
			'whichs' => [
				'whichs_id' => '',
				'whichs_which1' => '',
				'whichs_which2' => '',
				'whichs_which3' => '',
				'whichs_description' => '',
				'whichs_created' => '',
				'whichs_updated' => '',
			],
			'wheres' => [
				'wheres_id' => '',
				'wheres_where1' => '',
				'wheres_where2' => '',
				'wheres_where3' => '',
				'wheres_description' => '',
				'wheres_created' => '',
				'wheres_updated' => '',
			],
			'whys' => [
				'whys_id' => '',
				'whys_why1' => '',
				'whys_why2' => '',
				'whys_why3' => '',
				'whys_description' => '',
				'whys_created' => '',
				'whys_updated' => '',
			],
			'howrs' => [
				'howrs_id' => '',
				'howrs_howr1' => '',
				'howrs_howr2' => '',
				'howrs_howr3' => '',
				'howrs_description' => '',
				'howrs_created' => '',
				'howrs_updated' => '',
			],
			'howss' => [
				'howss_id' => '',
				'howss_hows1' => '',
				'howss_hows2' => '',
				'howss_hows3' => '',
				'howss_hows4' => '',
				'howss_description' => '',
				'howss_created' => '',
				'howss_updated' => '',
			],
			'howqs' => [
				'howqs_id' => '',
				'howqs_howq1' => '',
				'howqs_howq2' => '',
				'howqs_howq3' => '',
				'howqs_description' => '',
				'howqs_created' => '',
				'howqs_updated' => '',
			],
			'howts' => [
				'howts_id' => '',
				'howts_howt1' => '',
				'howts_howt2' => '',
				'howts_howt3' => '',
				'howrs_description' => '',
				'howrs_created' => '',
				'howrs_updated' => '',
			],
			'utedb' => [
				'utedb_id' => '',
				'utedb_madb' => '',
				'utedb_madb_who1' => '',
				'utedb_premises_id' => '',
				'utedb_proof_image' => '',
				'utedb_ai_audit' => '',
				'utedb_pc_audit' => '',
				'utedb_rda_audit' => '',
				'utedb_bb_audit' => '',
				'utedb_car' => '',
				'utedb_car_vid' => '',
				'utedb_col_tf' => '',
				'utedb_delta_flag' => '',
				'utedb_hows1' => '',
				'utedb_elairda_id' => '',
				'utedb_created' => '',
				'utedb_updated' => '',
			],
			'premises' => [
				'premises_id' => '',
				'premises_name' => '',
				'premises_latitude' => '',
				'premises_longitude' => '',
				'premises_radius' => '',
				'premises_created' => '',
				'premises_updated' => '',
			],
			'pnb' => [
				'pnb_id' => '',
				'pnb_type' => '',
				'pnb_premises_id' => '',
				'pnb_whos_id' => '',
				'pnb_delta_flag' => '',
				'pnb_admin' => '',
				'pnb_comments' => '',
				'pnb_created' => '',
				'pnb_updated' => '',
			],
		];

		return isset($defaults[$table]) ? $defaults[$table] : [];
	}

	#########################################################

	function htmlUserBar() {
		if(Request::val('Embedded')) return ''; // skip if in embedded mode

		global $Translation;
		if(!defined('PREPEND_PATH')) define('PREPEND_PATH', '');

		$mi = getMemberInfo();
		$home_page = defined('HOMEPAGE') && HOMEPAGE;

		$navMenu = getUserData('navMenu');
		if(!$navMenu) {
			setUserData('navMenu', DEFAULT_NAV_MENU);
			$navMenu = DEFAULT_NAV_MENU;
		}

		ob_start();
		?>
		<nav class="navbar navbar-default navbar-fixed-top hidden-print" role="navigation">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="glyphicon glyphicon-menu-hamburger"></span>
				</button>
				<!-- application title is obtained from the name besides the yellow database icon in AppGini, use underscores for spaces -->
				<a class="navbar-brand" href="<?php echo PREPEND_PATH; ?>index.php"><i class="glyphicon glyphicon-home"></i> <?php echo APP_TITLE; ?></a>
				<p class="navbar-text pull-left navbar-sub-brand-separator hidden">/</p>
				<a class="navbar-brand navbar-sub-brand title-link hidden"></a>
			</div>
			<div class="collapse navbar-collapse">

				<?php if(!Authentication::isGuest()) { ?>
					<ul class="nav navbar-nav visible-xs">
						<div class="btn-group">
							<a class="btn navbar-btn btn-default btn-lg signed-in-as" href="<?php echo PREPEND_PATH; ?>membership_profile.php">
								<i class="glyphicon glyphicon-user"></i>
								<strong class="username"><?php echo $mi['username']; ?></strong>
							</a>
							<a class="btn navbar-btn btn-default btn-lg" href="<?php echo PREPEND_PATH; ?>index.php?signOut=1">
								<i class="glyphicon glyphicon-log-out"></i>
							</a>
						</div>
					</ul>
				<?php } ?>

				<?php if(!$home_page || HOMEPAGE_NAVMENUS) { ?>
					<ul class="nav navbar-nav horizontal-navlinks<?php echo $navMenu != 'horizontal' ? ' hidden' : ''; ?>"><?php echo NavMenus(); ?></ul>
				<?php } ?>

				<?php if(!Request::val('signIn') && !Request::val('loginFailed')) { ?>
					<?php if(Authentication::isGuest()) { ?>
						<p class="navbar-text navbar-right hidden-xs">&nbsp;</p>
						<a href="#" class="btn btn-default navbar-btn hidden-xs hidden-browser navbar-right hspacer-lg exit-pwa" title="<?php echo html_attr($Translation['exit']); ?>">
							<i class="glyphicon glyphicon-remove"></i> <?php echo $Translation['exit']; ?>
						</a>
						<a href="<?php echo PREPEND_PATH; ?>index.php?signIn=1" class="btn btn-success navbar-btn navbar-right hidden-xs"><?php echo $Translation['sign in']; ?></a>
						<p class="navbar-text navbar-right hidden-xs">
							<?php echo $Translation['not signed in']; ?>
						</p>
						<a href="<?php echo PREPEND_PATH; ?>index.php?signIn=1" class="btn btn-success btn-block btn-lg navbar-btn visible-xs">
							<?php echo $Translation['not signed in']; ?>
							<i class="glyphicon glyphicon-chevron-right"></i>
							<?php echo $Translation['sign in']; ?>
						</a>
					<?php } else { ?>
						<ul class="nav navbar-nav navbar-right hidden-xs">
							<!-- logged user profile menu -->
							<li class="dropdown" title="<?php echo html_attr("{$Translation['signed as']} {$mi['username']}"); ?>">
								<a href="#" class="dropdown-toggle profile-menu-icon" data-toggle="dropdown"><i class="glyphicon glyphicon-user icon"></i><span class="profile-menu-text"><?php echo $mi['username']; ?></span><b class="caret"></b></a>
								<ul class="dropdown-menu profile-menu">
									<li class="user-profile-menu-item" title="<?php echo html_attr($Translation['Your info']); ?>">
										<a href="<?php echo PREPEND_PATH; ?>membership_profile.php"><i class="glyphicon glyphicon-user"></i> <?php echo $Translation['my account']; ?> <span class="label label-default username"><?php echo $mi['username']; ?></span></a>
									</li>
									<li class="keyboard-shortcuts-menu-item hidden-xs" title="<?php echo html_attr($Translation['keyboard shortcuts']); ?>">
										<a href="#" class="help-shortcuts-launcher">
											<img src="<?php echo PREPEND_PATH; ?>resources/images/keyboard.png">
											<?php echo html_attr($Translation['keyboard shortcuts']); ?>
										</a>
									</li>
									<li class="sign-out-menu-item" title="<?php echo html_attr($Translation['sign out']); ?>">
										<a href="<?php echo PREPEND_PATH; ?>index.php?signOut=1"><i class="glyphicon glyphicon-log-out"></i> <?php echo $Translation['sign out']; ?></a>
									</li>
									<li class="hidden-browser">
										<a href="#" class="exit-pwa" title="<?php echo html_attr($Translation['exit']); ?>">
											<i class="glyphicon glyphicon-remove"></i> <?php echo $Translation['exit']; ?>
										</a>
									</li>
								</ul>
							</li>
						</ul>
						<script>
							/* periodically check if user is still signed in */
							setInterval(function() {
								$j.ajax({
									url: '<?php echo PREPEND_PATH; ?>ajax_check_login.php',
									success: function(username) {
										if(!username.length) window.location = '<?php echo PREPEND_PATH; ?>index.php?signIn=1';
									}
								});
							}, 60000);
						</script>
					<?php } ?>
				<?php } ?>

				<?php if(getLoggedAdmin() !== false) { ?>
					<ul class="nav navbar-nav navbar-right">
						<a href="<?php echo PREPEND_PATH; ?>admin/pageHome.php" class="btn btn-danger navbar-btn hidden-xs btn-admin-area" title="<?php echo html_attr($Translation['admin area']); ?>"><i class="glyphicon glyphicon-cog"></i> <?php echo $Translation['admin area']; ?></a>
						<a href="<?php echo PREPEND_PATH; ?>admin/pageHome.php" class="btn btn-danger navbar-btn visible-xs btn-lg btn-admin-area" title="<?php echo html_attr($Translation['admin area']); ?>"><i class="glyphicon glyphicon-cog"></i> <?php echo $Translation['admin area']; ?></a>
					</ul>
				<?php } ?>

				<?php if(userCanImport()){ ?>
					<ul class="nav navbar-nav navbar-right">
						<a href="<?php echo PREPEND_PATH; ?>import-csv.php" class="btn btn-default navbar-btn hidden-xs btn-import-csv" title="<?php echo html_attr($Translation['import csv file']); ?>"><i class="glyphicon glyphicon-th"></i> <?php echo $Translation['import CSV']; ?></a>
						<a href="<?php echo PREPEND_PATH; ?>import-csv.php" class="btn btn-default navbar-btn visible-xs btn-lg btn-import-csv" title="<?php echo html_attr($Translation['import csv file']); ?>"><i class="glyphicon glyphicon-th"></i> <?php echo $Translation['import CSV']; ?></a>
					</ul>
				<?php } ?>

				<ul class="nav navbar-nav">
					<a href="#" title="<?php echo html_attr($Translation['exit']); ?>" class="btn btn-default navbar-btn btn-lg visible-xs hidden-browser exit-pwa">
						<i class="glyphicon glyphicon-remove"></i>
						<?php echo $Translation['exit']; ?>
					</a>
				</ul>

				<a href="#" class="btn btn-default navbar-btn hidden navbar-right hidden-xs install-pwa-btn" title="<?php echo html_attr($Translation['install mobile app']); ?>">
					<i class="glyphicon glyphicon-cloud-download"></i>
				</a>
				<a href="#" class="btn btn-default btn-block btn-lg navbar-btn hidden hidden-sm hidden-md hidden-lg install-pwa-btn" title="<?php echo html_attr($Translation['install mobile app']); ?>">
					<i class="glyphicon glyphicon-cloud-download"></i> <?php echo $Translation['install mobile app']; ?>
				</a>
				<script>
					// when browser detects that site is installable as PWA, show install button
					window.addEventListener('beforeinstallprompt', function(e) {
						e.preventDefault();

						// To override default silent period, set AppGini.config.PWAInstallPromptSilentPeriodDays to the number of days
						let silentPeriod = 86400000; // default silent period is 10 days
						if(AppGini.config.PWAInstallPromptSilentPeriodDays) {
							silentPeriod = parseInt(AppGini.config.PWAInstallPromptSilentPeriodDays) * 10 * 60 * 60 * 24 * 1000;
						}

						// if user dismissed the install prompt, don't show it again for some time
						if(
							AppGini.localStorage.getItem('PWApromptDismissedAt')
							&& (new Date().getTime() - AppGini.localStorage.getItem('PWApromptDismissedAt')) < silentPeriod
						) return;

						// unhide .install-pwa-btn by removing .hidden
						document.querySelectorAll('.install-pwa-btn').forEach(function(el) {
							el.classList.remove('hidden');

							// install on click
							el.addEventListener('click', function(ce) {
								ce.preventDefault();
								e.prompt();

								// add a localStorage item to prevent showing the install button for some time
								AppGini.localStorage.setItem('PWApromptDismissedAt', new Date().getTime());
							});
						});
					});
					$j('.exit-pwa').on('click', function(e) {
						e.preventDefault();
						window.close();
						alert(AppGini.Translate._map['click mobile home button to exit']);
					});
				</script>
			</div>
		</nav>
		<div style="min-height: 70px;" class="hidden-print top-margin-adjuster"></div>
		<?php

		return ob_get_clean();
	}

	#########################################################

	function showNotifications($msg = '', $class = '', $fadeout = true) {
		global $Translation;
		if($error_message = strip_tags(Request::val('error_message')))
			$error_message = '<div class="text-bold">' . $error_message . '</div>';

		if(!$msg) { // if no msg, use url to detect message to display
			if(Request::val('record-added-ok')) {
				$msg = $Translation['new record saved'];
				$class = 'alert-success';
			} elseif(Request::val('record-added-error')) {
				$msg = $Translation['Couldn\'t save the new record'] . $error_message;
				$class = 'alert-danger';
				$fadeout = false;
			} elseif(Request::val('record-updated-ok')) {
				$msg = $Translation['record updated'];
				$class = 'alert-success';
			} elseif(Request::val('record-updated-error')) {
				$msg = $Translation['Couldn\'t save changes to the record'] . $error_message;
				$class = 'alert-danger';
				$fadeout = false;
			} elseif(Request::val('record-deleted-ok')) {
				$msg = $Translation['The record has been deleted successfully'];
				$class = 'alert-success';
			} elseif(Request::val('record-deleted-error')) {
				$msg = $Translation['Couldn\'t delete this record'] . $error_message;
				$class = 'alert-danger';
				$fadeout = false;
			} else {
				return '';
			}
		}
		$id = 'notification-' . rand();

		ob_start();
		// notification template
		?>
		<div id="%%ID%%" class="alert alert-dismissable %%CLASS%%" style="opacity: 1; padding-top: 6px; padding-bottom: 6px; animation: fadeIn 1.5s ease-out; z-index: 100; position: relative;">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			%%MSG%%
		</div>
		<script>
			$j(function() {
				var autoDismiss = <?php echo $fadeout ? 'true' : 'false'; ?>,
					embedded = !$j('nav').length,
					messageDelay = 10, fadeDelay = 1.5;

				if(!autoDismiss) {
					if(embedded)
						$j('#%%ID%%').before('<div class="modal-top-spacer"></div>');
					else
						$j('#%%ID%%').css({ margin: '0 0 1rem' });

					return;
				}

				// below code runs only in case of autoDismiss

				if(embedded)
					$j('#%%ID%%').css({ margin: '1rem 0 -1rem' });
				else
					$j('#%%ID%%').css({ margin: '-15px 0 -20px' });

				setTimeout(function() {
					$j('#%%ID%%').css({    animation: 'fadeOut ' + fadeDelay + 's ease-out' });
				}, messageDelay * 1000);

				setTimeout(function() {
					$j('#%%ID%%').css({    visibility: 'hidden' });
				}, (messageDelay + fadeDelay) * 1000);
			})
		</script>
		<style>
			@keyframes fadeIn {
				0%   { opacity: 0; }
				100% { opacity: 1; }
			}
			@keyframes fadeOut {
				0%   { opacity: 1; }
				100% { opacity: 0; }
			}
		</style>

		<?php
		$out = ob_get_clean();

		$out = str_replace('%%ID%%', $id, $out);
		$out = str_replace('%%MSG%%', $msg, $out);
		$out = str_replace('%%CLASS%%', $class, $out);

		return $out;
	}

	#########################################################

	function validMySQLDate($date) {
		$date = trim($date);

		try {
			$dtObj = new DateTime($date);
		} catch(Exception $e) {
			return false;
		}

		$parts = explode('-', $date);
		return (
			count($parts) == 3
			// see https://dev.mysql.com/doc/refman/8.0/en/datetime.html
			&& intval($parts[0]) >= 1000
			&& intval($parts[0]) <= 9999
			&& intval($parts[1]) >= 1
			&& intval($parts[1]) <= 12
			&& intval($parts[2]) >= 1
			&& intval($parts[2]) <= 31
		);
	}

	#########################################################

	function parseMySQLDate($date, $altDate) {
		// is $date valid?
		if(validMySQLDate($date)) return trim($date);

		if($date != '--' && validMySQLDate($altDate)) return trim($altDate);

		if($date != '--' && $altDate && is_numeric($altDate))
			return @date('Y-m-d', @time() + ($altDate >= 1 ? $altDate - 1 : $altDate) * 86400);

		return '';
	}

	#########################################################

	function parseCode($code, $isInsert = true, $rawData = false) {
		$mi = Authentication::getUser();

		if($isInsert) {
			$arrCodes = [
				'<%%creatorusername%%>' => $mi['username'],
				'<%%creatorgroupid%%>' => $mi['groupId'],
				'<%%creatorip%%>' => $_SERVER['REMOTE_ADDR'],
				'<%%creatorgroup%%>' => $mi['group'],

				'<%%creationdate%%>' => ($rawData ? date('Y-m-d') : date(app_datetime_format('phps'))),
				'<%%creationtime%%>' => ($rawData ? date('H:i:s') : date(app_datetime_format('phps', 't'))),
				'<%%creationdatetime%%>' => ($rawData ? date('Y-m-d H:i:s') : date(app_datetime_format('phps', 'dt'))),
				'<%%creationtimestamp%%>' => ($rawData ? date('Y-m-d H:i:s') : time()),
			];
		} else {
			$arrCodes = [
				'<%%editorusername%%>' => $mi['username'],
				'<%%editorgroupid%%>' => $mi['groupId'],
				'<%%editorip%%>' => $_SERVER['REMOTE_ADDR'],
				'<%%editorgroup%%>' => $mi['group'],

				'<%%editingdate%%>' => ($rawData ? date('Y-m-d') : date(app_datetime_format('phps'))),
				'<%%editingtime%%>' => ($rawData ? date('H:i:s') : date(app_datetime_format('phps', 't'))),
				'<%%editingdatetime%%>' => ($rawData ? date('Y-m-d H:i:s') : date(app_datetime_format('phps', 'dt'))),
				'<%%editingtimestamp%%>' => ($rawData ? date('Y-m-d H:i:s') : time()),
			];
		}

		$pc = str_ireplace(array_keys($arrCodes), array_values($arrCodes), $code);

		return $pc;
	}

	#########################################################

	function parseMySQLDateTime($datetime, $altDateTime) {
		// is $datetime valid?
		if(mysql_datetime($datetime)) return mysql_datetime($datetime);

		if($altDateTime === '') return '';

		// is $altDateTime valid?
		if(mysql_datetime($altDateTime)) return mysql_datetime($altDateTime);

		/* parse $altDateTime */
		$matches = [];
		if(!preg_match('/^([+-])(\d+)(s|m|h|d)(0)?$/', $altDateTime, $matches))
			return '';

		$sign = ($matches[1] == '-' ? -1 : 1);
		$unit = $matches[3];
		$qty = $matches[2];

		// m0 means increment minutes, set seconds to 0
		// h0 means increment hours, set minutes and seconds to 0
		// d0 means increment days, set time to 00:00:00
		$zeroTime = $matches[4] == '0';

		switch($unit) {
			case 's':
				$seconds = $qty * $sign;
				break;
			case 'm':
				$seconds = $qty * 60 * $sign;
				if($zeroTime) return @date('Y-m-d H:i:00', @time() + $seconds);
				break;
			case 'h':
				$seconds = $qty * 3600 * $sign;
				if($zeroTime) return @date('Y-m-d H:00:00', @time() + $seconds);
				break;
			case 'd':
				$seconds = $qty * 86400 * $sign;
				if($zeroTime) return @date('Y-m-d 00:00:00', @time() + $seconds);
				break;
		}

		return @date('Y-m-d H:i:s', @time() + $seconds);
	}

	#########################################################

	function addFilter($index, $filterAnd, $filterField, $filterOperator, $filterValue) {
		// validate input
		if($index < 1 || $index > FILTER_GROUPS * FILTERS_PER_GROUP || !is_int($index)) return false;
		if($filterAnd != 'or')   $filterAnd = 'and';
		$filterField = intval($filterField);

		/* backward compatibility */
		if(in_array($filterOperator, FILTER_OPERATORS)) {
			$filterOperator = array_search($filterOperator, FILTER_OPERATORS);
		}

		if(!in_array($filterOperator, array_keys(FILTER_OPERATORS))) {
			$filterOperator = 'like';
		}

		if(!$filterField) {
			$filterOperator = '';
			$filterValue = '';
		}

		$_REQUEST['FilterAnd'][$index] = $filterAnd;
		$_REQUEST['FilterField'][$index] = $filterField;
		$_REQUEST['FilterOperator'][$index] = $filterOperator;
		$_REQUEST['FilterValue'][$index] = $filterValue;

		return true;
	}

	#########################################################

	function clearFilters() {
		for($i = 1; $i <= FILTER_GROUPS * FILTERS_PER_GROUP; $i++) {
			addFilter($i, '', 0, '', '');
		}
	}

	#########################################################

	/**
	* Loads a given view from the templates folder, passing the given data to it
	* @param $view the name of a php file (without extension) to be loaded from the 'templates' folder
	* @param $the_data_to_pass_to_the_view (optional) associative array containing the data to pass to the view
	* @return string the output of the parsed view
	*/
	function loadView($view, $the_data_to_pass_to_the_view = false) {
		global $Translation;

		$view = __DIR__ . "/templates/$view.php";
		if(!is_file($view)) return false;

		if(is_array($the_data_to_pass_to_the_view)) {
			foreach($the_data_to_pass_to_the_view as $data_k => $data_v)
				$$data_k = $data_v;
		}
		unset($the_data_to_pass_to_the_view, $data_k, $data_v);

		ob_start();
		@include($view);
		return ob_get_clean();
	}

	#########################################################

	/**
	* Loads a table template from the templates folder, passing the given data to it
	* @param $table_name the name of the table whose template is to be loaded from the 'templates' folder
	* @param $the_data_to_pass_to_the_table associative array containing the data to pass to the table template
	* @return the output of the parsed table template as a string
	*/
	function loadTable($table_name, $the_data_to_pass_to_the_table = []) {
		$dont_load_header = $the_data_to_pass_to_the_table['dont_load_header'];
		$dont_load_footer = $the_data_to_pass_to_the_table['dont_load_footer'];

		$header = $table = $footer = '';

		if(!$dont_load_header) {
			// try to load tablename-header
			if(!($header = loadView("{$table_name}-header", $the_data_to_pass_to_the_table))) {
				$header = loadView('table-common-header', $the_data_to_pass_to_the_table);
			}
		}

		$table = loadView($table_name, $the_data_to_pass_to_the_table);

		if(!$dont_load_footer) {
			// try to load tablename-footer
			if(!($footer = loadView("{$table_name}-footer", $the_data_to_pass_to_the_table))) {
				$footer = loadView('table-common-footer', $the_data_to_pass_to_the_table);
			}
		}

		return "{$header}{$table}{$footer}";
	}

	#########################################################

	function br2nl($text) {
		return  preg_replace('/\<br(\s*)?\/?\>/i', "\n", $text);
	}

	#########################################################

	function entitiesToUTF8($input) {
		return preg_replace_callback('/(&#[0-9]+;)/', '_toUTF8', $input);
	}

	function _toUTF8($m) {
		if(function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
		} else {
			return $m[1];
		}
	}

	#########################################################

	function func_get_args_byref() {
		if(!function_exists('debug_backtrace')) return false;

		$trace = debug_backtrace();
		return $trace[1]['args'];
	}

	#########################################################

	function permissions_sql($table, $level = 'all') {
		if(!in_array($level, ['user', 'group'])) { $level = 'all'; }
		$perm = getTablePermissions($table);
		$from = '';
		$where = '';
		$pk = getPKFieldName($table);

		if($perm['view'] == 1 || ($perm['view'] > 1 && $level == 'user')) { // view owner only
			$from = 'membership_userrecords';
			$where = "(`$table`.`$pk`=membership_userrecords.pkValue and membership_userrecords.tableName='$table' and lcase(membership_userrecords.memberID)='" . getLoggedMemberID() . "')";
		} elseif($perm['view'] == 2 || ($perm['view'] > 2 && $level == 'group')) { // view group only
			$from = 'membership_userrecords';
			$where = "(`$table`.`$pk`=membership_userrecords.pkValue and membership_userrecords.tableName='$table' and membership_userrecords.groupID='" . getLoggedGroupID() . "')";
		} elseif($perm['view'] == 3) { // view all
			// no further action
		} elseif($perm['view'] == 0) { // view none
			return false;
		}

		return ['where' => $where, 'from' => $from, 0 => $where, 1 => $from];
	}

	#########################################################

	function error_message($msg, $back_url = '', $full_page = true) {
		global $Translation;

		ob_start();

		if($full_page) include(__DIR__ . '/header.php');

		echo '<div class="panel panel-danger">';
			echo '<div class="panel-heading"><h3 class="panel-title">' . $Translation['error:'] . '</h3></div>';
			echo '<div class="panel-body"><p class="text-danger">' . $msg . '</p>';
			if($back_url !== false) { // explicitly passing false suppresses the back link completely
				echo '<div class="text-center">';
				if($back_url) {
					echo '<a href="' . $back_url . '" class="btn btn-danger btn-lg vspacer-lg"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['< back'] . '</a>';
				// in embedded mode, close modal window
				} elseif(Request::val('Embedded')) {
					echo '<button class="btn btn-danger btn-lg" type="button" onclick="AppGini.closeParentModal();"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['< back'] . '</button>';
				} else {
					echo '<a href="#" class="btn btn-danger btn-lg vspacer-lg" onclick="history.go(-1); return false;"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['< back'] . '</a>';
				}
				echo '</div>';
			}
			echo '</div>';
		echo '</div>';

		if($full_page) include(__DIR__ . '/footer.php');

		return ob_get_clean();
	}

	#########################################################

	function toMySQLDate($formattedDate, $sep = datalist_date_separator, $ord = datalist_date_format) {
		// extract date elements
		$de=explode($sep, $formattedDate);
		$mySQLDate=intval($de[strpos($ord, 'Y')]).'-'.intval($de[strpos($ord, 'm')]).'-'.intval($de[strpos($ord, 'd')]);
		return $mySQLDate;
	}

	#########################################################

	function reIndex(&$arr) {
		$i=1;
		foreach($arr as $n=>$v) {
			$arr2[$i]=$n;
			$i++;
		}
		return $arr2;
	}

	#########################################################

	function get_embed($provider, $url, $max_width = '', $max_height = '', $retrieve = 'html') {
		global $Translation;
		if(!$url) return '';

		$providers = [
			'youtube' => ['oembed' => 'https://www.youtube.com/oembed', 'regex' => '/^http.*(youtu\.be|youtube\.com)\/.*/i'],
			'vimeo' => ['oembed' => 'https://vimeo.com/api/oembed.json', 'regex' => '/^http.*vimeo\.com\/.*/i'],
			'googlemap' => ['oembed' => '', 'regex' => '/^http.*\.google\..*maps/i'],
			'dailymotion' => ['oembed' => 'https://www.dailymotion.com/services/oembed', 'regex' => '/^http.*(dailymotion\.com|dai\.ly)\/.*/i'],
			'videofileurl' => ['oembed' => '', 'regex' => '/\.(mp4|webm|ogg|ogv)$/i'],
		];

		if(!$max_height) $max_height = 360;
		if(!$max_width) $max_width = 480;

		if(!isset($providers[$provider])) {
			// try detecting provider from URL based on regex
			foreach($providers as $p => $opts) {
				if(preg_match($opts['regex'], $url)) {
					$provider = $p;
					break;
				}
			}

			if(!isset($providers[$provider]))
				return '<div class="text-danger">' . $Translation['invalid provider'] . '</div>';
		}

		if(isset($providers[$provider]['regex']) && !preg_match($providers[$provider]['regex'], $url)) {
			return '<div class="text-danger">' . $Translation['invalid url'] . '</div>';
		}

		if($providers[$provider]['oembed']) {
			$oembed = $providers[$provider]['oembed'] . '?url=' . urlencode($url) . "&amp;maxwidth={$max_width}&amp;maxheight={$max_height}&amp;format=json";
			$data_json = request_cache($oembed);

			$data = json_decode($data_json, true);
			if($data === null) {
				/* an error was returned rather than a json string */
				if($retrieve == 'html') return "<div class=\"text-danger\">{$data_json}\n<!-- {$oembed} --></div>";
				return '';
			}

			// if html data not empty, apply max width and height in place of provided height and width
			$provided_width = $data['width'] ?? null;
			$provided_height = $data['height'] ?? null;
			if($provided_width && $provided_height) {
				$aspect_ratio = $provided_width / $provided_height;
				if($max_width / $aspect_ratio < $max_height) {
					$max_height = intval($max_width / $aspect_ratio);
				} else {
					$max_width = intval($max_height * $aspect_ratio);
				}

				$data['html'] = str_replace("width=\"{$provided_width}\"", "width=\"{$max_width}\"", $data['html']);
				$data['html'] = str_replace("height=\"{$provided_height}\"", "height=\"{$max_height}\"", $data['html']);
			}

			return (isset($data[$retrieve]) ? $data[$retrieve] : $data['html']);
		}

		/* special cases (where there is no oEmbed provider) */
		if($provider == 'googlemap') return get_embed_googlemap($url, $max_width, $max_height, $retrieve);
		if($provider == 'videofileurl') return get_embed_videofileurl($url, $max_width, $max_height, $retrieve);

		return '<div class="text-danger">' . $Translation['invalid provider'] . '</div>';
	}

	#########################################################

	function get_embed_videofileurl($url, $max_width = '', $max_height = '', $retrieve = 'html') {
		global $Translation;

		$allowed_exts = ['mp4', 'webm', 'ogg', 'ogv'];
		$ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));

		if(!in_array($ext, $allowed_exts)) {
			return '<div class="text-danger">' . $Translation['invalid url'] . '</div>';
		}

		$video = "<video controls style=\"max-width: 100%%; height: auto;\" src=\"%s\"></video>";

		switch($retrieve) {
			case 'html':
				return sprintf($video, $url);
			default: // 'thumbnail'
				return '';
		}
	}

	#########################################################

	function get_embed_googlemap($url, $max_width = '', $max_height = '', $retrieve = 'html') {
		global $Translation;
		$url_parts = parse_url($url);
		$coords_regex = '/-?\d+(\.\d+)?[,+]-?\d+(\.\d+)?(,\d{1,2}z)?/'; /* https://stackoverflow.com/questions/2660201 */

		if(!preg_match($coords_regex, $url_parts['path'] . '?' . $url_parts['query'], $m))
			return '<div class="text-danger">' . $Translation['cant retrieve coordinates from url'] . '</div>';

		list($lat, $long, $zoom) = explode(',', $m[0]);
		$zoom = intval($zoom);
		if(!$zoom) $zoom = 15; /* default zoom */
		if(!$max_height) $max_height = 360;
		if(!$max_width) $max_width = 480;

		$api_key = config('adminConfig')['googleAPIKey'];

		// if max_height is all numeric, append 'px' to it
		$frame_height = $max_height;
		if(is_numeric($frame_height)) $frame_height .= 'px';

		$embed_url = 'https://www.google.com/maps/embed/v1/%s?' . http_build_query([
			'key' => $api_key,
			'zoom' => $zoom,
			'maptype' => 'roadmap',
		], '', '&amp;');

		$thumbnail_url = 'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query([
			'key' => $api_key,
			'zoom' => $zoom,
			'maptype' => 'roadmap',
			'size' => "{$max_width}x{$max_height}",
			'center' => "$lat,$long",
		], '', '&amp;');

		$iframe = "<iframe allowfullscreen loading=\"lazy\" style=\"border: none; width: 100%%; height: $frame_height;\" src=\"%s\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>";

		switch($retrieve) {
			case 'html':
				$embed_url = sprintf($embed_url, 'view') . '&amp;' . http_build_query(['center' => "$lat,$long"]);
				return sprintf($iframe, $embed_url);
			case 'html-pinpoint':
				$embed_url = sprintf($embed_url, 'place') . '&amp;' . http_build_query(['q' => "$lat,$long"]);
				return sprintf($iframe, $embed_url);
			case 'thumbnail-pinpoint':
				return $thumbnail_url . '&amp;' . http_build_query(['markers' => "$lat,$long"]);
			default: // 'thumbnail'
				return $thumbnail_url;
		}
	}

	#########################################################

	function request_cache($request, $force_fetch = false) {
		static $cache_table_exists = null;
		$max_cache_lifetime = 7 * 86400; /* max cache lifetime in seconds before refreshing from source */

		// force fetching request if no cache table exists
		if($cache_table_exists === null)
			$cache_table_exists = sqlValue("show tables like 'membership_cache'");

		if(!$cache_table_exists)
			return request_cache($request, true);

		/* retrieve response from cache if exists */
		if(!$force_fetch) {
			$res = sql("select response, request_ts from membership_cache where request='" . md5($request) . "'", $eo);
			if(!$row = db_fetch_array($res)) return request_cache($request, true);

			$response = $row[0];
			$response_ts = $row[1];
			if($response_ts < time() - $max_cache_lifetime) return request_cache($request, true);
		}

		/* if no response in cache, issue a request */
		if(!$response || $force_fetch) {
			$response = @file_get_contents($request);
			if($response === false) {
				$error = error_get_last();
				$error_message = preg_replace('/.*: (.*)/', '$1', $error['message']);
				return $error_message;
			} elseif($cache_table_exists) {
				/* store response in cache */
				$ts = time();
				sql("replace into membership_cache set request='" . md5($request) . "', request_ts='{$ts}', response='" . makeSafe($response, false) . "'", $eo);
			}
		}

		return $response;
	}

	#########################################################

	function check_record_permission($table, $id, $perm = 'view') {
		if($perm != 'edit' && $perm != 'delete') $perm = 'view';

		$perms = getTablePermissions($table);
		if(!$perms[$perm]) return false;

		$safe_id = makeSafe($id);
		$safe_table = makeSafe($table);

		// fix for zero-fill: quote id only if not numeric
		if(!is_numeric($safe_id)) $safe_id = "'$safe_id'";

		if($perms[$perm] == 1) { // own records only
			$username = getLoggedMemberID();
			$owner = sqlValue("select memberID from membership_userrecords where tableName='{$safe_table}' and pkValue={$safe_id}");
			if($owner == $username) return true;
		} elseif($perms[$perm] == 2) { // group records
			$group_id = getLoggedGroupID();
			$owner_group_id = sqlValue("select groupID from membership_userrecords where tableName='{$safe_table}' and pkValue={$safe_id}");
			if($owner_group_id == $group_id) return true;
		} elseif($perms[$perm] == 3) { // all records
			return true;
		}

		return false;
	}

	#########################################################

	function tablesHiddenInNavMenu() {
		return [];
	}

	#########################################################

	function tablesHiddenInHomepage() {
		return [];
	}


	#########################################################

	function tablesWithAddNewInHomepage() {
		return ['madb','whats','whos','whens','whichs','wheres','whys','howrs','howqs','howts','utedb','premises','pnb',];
	}

	#########################################################

	function tablesToFilterBeforeTV() {
		return [];
	}

	#########################################################

	function NavMenus($options = []) {
		if(!defined('PREPEND_PATH')) define('PREPEND_PATH', '');
		global $Translation;
		$prepend_path = PREPEND_PATH;

		/* default options */
		if(empty($options)) {
			$options = ['tabs' => 7];
		}

		$table_group_name = array_keys(get_table_groups()); /* 0 => group1, 1 => group2 .. */
		/* if only one group named 'None', set to translation of 'select a table' */
		if((count($table_group_name) == 1 && $table_group_name[0] == 'None') || count($table_group_name) < 1) $table_group_name[0] = $Translation['select a table'];
		$table_group_index = array_flip($table_group_name); /* group1 => 0, group2 => 1 .. */
		$menu = array_fill(0, count($table_group_name), '');

		$t = time();
		$arrTables = getTableList();
		if(is_array($arrTables)) {
			foreach($arrTables as $tn => $tc) {
				/* ---- list of tables where hide link in nav menu is set ---- */
				$tChkHL = array_search($tn, tablesHiddenInNavMenu());

				/* ---- list of tables where filter first is set ---- */
				$tChkFF = array_search($tn, tablesToFilterBeforeTV());
				if($tChkFF !== false && $tChkFF !== null) {
					$searchFirst = '&Filter_x=1';
				} else {
					$searchFirst = '';
				}

				/* when no groups defined, $table_group_index['None'] is NULL, so $menu_index is still set to 0 */
				$menu_index = intval($table_group_index[$tc[3]]);
				if(!$tChkHL && $tChkHL !== 0) $menu[$menu_index] .= "<li><a href=\"{$prepend_path}{$tn}_view.php?t={$t}{$searchFirst}\"><img src=\"{$prepend_path}" . ($tc[2] ? $tc[2] : 'blank.gif') . "\" height=\"32\"> {$tc[0]}</a></li>";
			}
		}

		// custom nav links, as defined in hooks/links-navmenu.php
		global $navLinks;
		if(is_array($navLinks)) {
			$memberInfo = getMemberInfo();
			$links_added = [];
			foreach($navLinks as $link) {
				if(!isset($link['url']) || !isset($link['title'])) continue;
				if(getLoggedAdmin() !== false || $link['groups'] == '*' || @in_array($memberInfo['group'], $link['groups']) || @in_array('*', $link['groups'])) {
					$menu_index = intval($link['table_group']);
					if(!$links_added[$menu_index]) $menu[$menu_index] .= '<li class="divider"></li>';

					/* add prepend_path to custom links if they aren't absolute links */
					if(!preg_match('/^(http|\/\/)/i', $link['url'])) $link['url'] = $prepend_path . $link['url'];
					if(!preg_match('/^(http|\/\/)/i', $link['icon']) && $link['icon']) $link['icon'] = $prepend_path . $link['icon'];

					$menu[$menu_index] .= "<li><a href=\"{$link['url']}\"><img src=\"" . ($link['icon'] ? $link['icon'] : "{$prepend_path}blank.gif") . "\" height=\"32\"> {$link['title']}</a></li>";
					$links_added[$menu_index]++;
				}
			}
		}

		$menu_wrapper = '';
		for($i = 0; $i < count($menu); $i++) {
			$menu_wrapper .= <<<EOT
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">{$table_group_name[$i]} <b class="caret"></b></a>
					<ul class="dropdown-menu" role="menu">{$menu[$i]}</ul>
				</li>
EOT;
		}

		return $menu_wrapper;
	}

	#########################################################

	function StyleSheet() {
		if(!defined('PREPEND_PATH')) define('PREPEND_PATH', '');
		$prepend_path = PREPEND_PATH;
		$mtime = filemtime( __DIR__ . '/dynamic.css');
		$theme = getUserTheme();
		$theme3d = ($theme == 'bootstrap' && BOOTSTRAP_3D_EFFECTS ? '<link rel="stylesheet" href="' . PREPEND_PATH . 'resources/initializr/css/bootstrap-theme.css">' . "\n" : '');

		$css_links = <<<EOT

			<link rel="stylesheet" href="{$prepend_path}resources/initializr/css/{$theme}.css">
			{$theme3d}
			<link rel="stylesheet" href="{$prepend_path}resources/select2/select2.css" media="screen">
			<link rel="stylesheet" href="{$prepend_path}resources/timepicker/bootstrap-timepicker.min.css" media="screen">
			<link rel="stylesheet" href="{$prepend_path}dynamic.css?{$mtime}">
EOT;

		return $css_links;
	}

	#########################################################

	function PrepareUploadedFile($FieldName, $MaxSize, $FileTypes = 'jpg|jpeg|gif|png|webp', $NoRename = false, $dir = '') {
		global $Translation;
		$f = $_FILES[$FieldName];
		if($f['error'] == 4 || !$f['name']) return '';

		$dir = getUploadDir($dir);

		/* get php.ini upload_max_filesize in bytes */
		$php_upload_size_limit = toBytes(ini_get('upload_max_filesize'));
		$MaxSize = min($MaxSize, $php_upload_size_limit);

		if($f['size'] > $MaxSize || $f['error']) {
			echo error_message(str_replace(['<MaxSize>', '{MaxSize}'], intval($MaxSize / 1024), $Translation['file too large']));
			exit;
		}
		if(!preg_match('/\.(' . $FileTypes . ')$/i', $f['name'], $ft)) {
			echo error_message(str_replace(['<FileTypes>', '{FileTypes}'], str_replace('|', ', ', $FileTypes), $Translation['invalid file type']));
			exit;
		}

		$name = str_replace(' ', '_', $f['name']);
		if(!$NoRename) $name = substr(md5(microtime() . rand(0, 100000)), -17) . $ft[0];

		if(!file_exists($dir)) @mkdir($dir, 0777);

		if(!@move_uploaded_file($f['tmp_name'], $dir . $name)) {
			echo error_message("Couldn't save the uploaded file. Try chmoding the upload folder '{$dir}' to 777.");
			exit;
		}

		@chmod($dir . $name, 0666);
		return $name;
	}

	#########################################################

	function get_home_links($homeLinks, $default_classes, $tgroup = '') {
		if(!is_array($homeLinks) || !count($homeLinks)) return '';

		$memberInfo = getMemberInfo();

		ob_start();
		foreach($homeLinks as $link) {
			if(!isset($link['url']) || !isset($link['title'])) continue;
			if($tgroup != $link['table_group'] && $tgroup != '*') continue;

			/* fall-back classes if none defined */
			if(!$link['grid_column_classes']) $link['grid_column_classes'] = $default_classes['grid_column'];
			if(!$link['panel_classes']) $link['panel_classes'] = $default_classes['panel'];
			if(!$link['link_classes']) $link['link_classes'] = $default_classes['link'];

			if(getLoggedAdmin() !== false || @in_array($memberInfo['group'], $link['groups']) || @in_array('*', $link['groups'])) {
				?>
				<div class="col-xs-12 <?php echo $link['grid_column_classes']; ?>">
					<div class="panel <?php echo $link['panel_classes']; ?>">
						<div class="panel-body">
							<a class="btn btn-block btn-lg <?php echo $link['link_classes']; ?>" title="<?php echo preg_replace("/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", html_attr(strip_tags($link['description']))); ?>" href="<?php echo $link['url']; ?>"><?php echo ($link['icon'] ? '<img src="' . $link['icon'] . '">' : ''); ?><strong><?php echo $link['title']; ?></strong></a>
							<div class="panel-body-description"><?php echo $link['description']; ?></div>
						</div>
					</div>
				</div>
				<?php
			}
		}

		return ob_get_clean();
	}

	#########################################################

	function quick_search_html($search_term, $label, $separate_dv = true) {
		global $Translation;

		$safe_search = html_attr($search_term);
		$safe_label = html_attr($label);
		$safe_clear_label = html_attr($Translation['Reset Filters']);

		if($separate_dv) {
			$reset_selection = "document.forms[0].SelectedID.value = '';";
		} else {
			$reset_selection = "document.forms[0].setAttribute('novalidate', 'novalidate');";
		}
		$reset_selection .= ' document.forms[0].NoDV.value=1; return true;';

		$html = <<<EOT
		<div class="input-group" id="quick-search">
			<input type="text" id="SearchString" name="SearchString" value="{$safe_search}" class="form-control" placeholder="{$safe_label}">
			<span class="input-group-btn">
				<button name="Search_x" value="1" id="Search" type="submit" onClick="{$reset_selection}" class="btn btn-default" title="{$safe_label}"><i class="glyphicon glyphicon-search"></i></button>
				<button name="ClearQuickSearch" value="1" id="ClearQuickSearch" type="submit" onClick="\$j('#SearchString').val(''); {$reset_selection}" class="btn btn-default" title="{$safe_clear_label}"><i class="glyphicon glyphicon-remove-circle"></i></button>
			</span>
		</div>
EOT;
		return $html;
	}

	#########################################################

	function getLookupFields($skipPermissions = false, $filterByPermission = 'view') {
		$pcConfig = [
			'madb' => [
				'madb_what1' => [
					'parent-table' => 'whats',
					'parent-primary-key' => 'whats_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(What 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_who1' => [
					'parent-table' => 'whos',
					'parent-primary-key' => 'whos_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(Who 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_when1' => [
					'parent-table' => 'whens',
					'parent-primary-key' => 'whens_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(When 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_which1' => [
					'parent-table' => 'whichs',
					'parent-primary-key' => 'whichs_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(Which 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_where1' => [
					'parent-table' => 'wheres',
					'parent-primary-key' => 'wheres_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(Where 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_why1' => [
					'parent-table' => 'whys',
					'parent-primary-key' => 'whys_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(Why 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_howr1' => [
					'parent-table' => 'howrs',
					'parent-primary-key' => 'howrs_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(How (R) 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_hows1' => [
					'parent-table' => 'howss',
					'parent-primary-key' => 'howss_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(How (S) 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_howq1' => [
					'parent-table' => 'howqs',
					'parent-primary-key' => 'howqs_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(How (Q) 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_howt1' => [
					'parent-table' => 'howts',
					'parent-primary-key' => 'howts_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Madb <span class="hidden child-label-madb child-field-caption">(How (T) 1)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
				'madb_premises_id' => [
					'parent-table' => 'premises',
					'parent-primary-key' => 'premises_id',
					'child-primary-key' => 'madb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Master Anatomy DB <span class="hidden child-label-madb child-field-caption">(Premises Id)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'What 1', 2 => 'Who 1', 3 => 'When 1', 4 => 'Which 1', 5 => 'Where 1', 6 => 'Why 1', 7 => 'How (R) 1', 8 => 'How (S) 1', 9 => 'How (Q) 1', 10 => 'How (T) 1', 11 => 'Why 2', 12 => 'Why 3', 13 => 'Where 2', 14 => 'Where 3', 15 => 'Which 2', 16 => 'Which 3', 17 => 'When 2', 18 => 'When 3', 19 => 'Who 2', 20 => 'Who 3', 21 => 'What 2', 22 => 'What 3', 23 => 'How (R) 2', 24 => 'How (R) 3', 25 => 'How (S) 2', 26 => 'How (S) 3', 27 => 'How (Q) 2', 28 => 'How (Q) 3', 29 => 'How (T) 2', 30 => 'How (T) 3', 31 => 'Premises Id', 32 => 'Madb status', 33 => 'Created AT', 34 => 'Updated AT'],
					'display-field-names' => [0 => 'madb_id', 1 => 'madb_what1', 2 => 'madb_who1', 3 => 'madb_when1', 4 => 'madb_which1', 5 => 'madb_where1', 6 => 'madb_why1', 7 => 'madb_howr1', 8 => 'madb_hows1', 9 => 'madb_howq1', 10 => 'madb_howt1', 11 => 'madb_why2', 12 => 'madb_why3', 13 => 'madb_where2', 14 => 'madb_where3', 15 => 'madb_which2', 16 => 'madb_which3', 17 => 'madb_when2', 18 => 'madb_when3', 19 => 'madb_who2', 20 => 'madb_who3', 21 => 'madb_what2', 22 => 'madb_what3', 23 => 'madb_howr2', 24 => 'madb_howr3', 25 => 'madb_hows2', 26 => 'madb_hows3', 27 => 'madb_howq2', 28 => 'madb_howq3', 29 => 'madb_howt2', 30 => 'madb_howt3', 31 => 'madb_premises_id', 32 => 'madb_status', 33 => 'madb_created', 34 => 'madb_updated'],
					'sortable-fields' => [0 => '`madb`.`madb_id`', 1 => '`whats1`.`whats_what1`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => '`whys1`.`whys_why2`', 12 => '`whys1`.`whys_why3`', 13 => '`wheres1`.`wheres_where2`', 14 => '`wheres1`.`wheres_where3`', 15 => '`whichs1`.`whichs_which2`', 16 => '`whichs1`.`whichs_which3`', 17 => '`whens1`.`whens_when2`', 18 => 'time_format(`whens1`.`whens_when3`,\'%r\')', 19 => '`whos1`.`whos_who2`', 20 => '`whos1`.`whos_who3`', 21 => '`whats1`.`whats_what2`', 22 => '`whats1`.`whats_what3`', 23 => '`howrs1`.`howrs_howr2`', 24 => '`howrs1`.`howrs_howr3`', 25 => '`howss1`.`howss_hows2`', 26 => '`howss1`.`howss_hows3`', 27 => '`howqs1`.`howqs_howq3`', 28 => '`howqs1`.`howqs_howq3`', 29 => '`howts1`.`howts_howt2`', 30 => '`howts1`.`howts_howt3`', 31 => 32, 32 => 33, 33 => '`madb`.`madb_created`', 34 => '`madb`.`madb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-madb',
					'template-printable' => 'children-madb-printable',
					'query' => "SELECT `madb`.`madb_id` as 'madb_id', IF(    CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `whats1`.`whats_what1`), '') as 'madb_what1', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'madb_who1', IF(    CHAR_LENGTH(`whens1`.`whens_when2`) || CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   `whens1`.`whens_when2`, '-', if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when1', IF(    CHAR_LENGTH(`whichs1`.`whichs_which1`) || CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which1`, '-', `whichs1`.`whichs_which2`), '') as 'madb_which1', IF(    CHAR_LENGTH(`wheres1`.`wheres_where1`) || CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where1`, '-', `wheres1`.`wheres_where2`), '') as 'madb_where1', IF(    CHAR_LENGTH(`whys1`.`whys_why1`) || CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why1`, '-', `whys1`.`whys_why2`), '') as 'madb_why1', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr1`) || CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr1`, '-', `howrs1`.`howrs_howr2`), '') as 'madb_howr1', IF(    CHAR_LENGTH(`howss1`.`howss_hows1`) || CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows1`, '-', `howss1`.`howss_hows2`), '') as 'madb_hows1', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq1`) || CHAR_LENGTH(`howqs1`.`howqs_howq2`), CONCAT_WS('',   `howqs1`.`howqs_howq1`, '-', `howqs1`.`howqs_howq2`), '') as 'madb_howq1', IF(    CHAR_LENGTH(`howts1`.`howts_howt1`) || CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt1`, '-', `howts1`.`howts_howt2`), '') as 'madb_howt1', IF(    CHAR_LENGTH(`whys1`.`whys_why2`), CONCAT_WS('',   `whys1`.`whys_why2`), '') as 'madb_why2', IF(    CHAR_LENGTH(`whys1`.`whys_why3`), CONCAT_WS('',   `whys1`.`whys_why3`), '') as 'madb_why3', IF(    CHAR_LENGTH(`wheres1`.`wheres_where2`), CONCAT_WS('',   `wheres1`.`wheres_where2`), '') as 'madb_where2', IF(    CHAR_LENGTH(`wheres1`.`wheres_where3`), CONCAT_WS('',   `wheres1`.`wheres_where3`), '') as 'madb_where3', IF(    CHAR_LENGTH(`whichs1`.`whichs_which2`), CONCAT_WS('',   `whichs1`.`whichs_which2`), '') as 'madb_which2', IF(    CHAR_LENGTH(`whichs1`.`whichs_which3`), CONCAT_WS('',   `whichs1`.`whichs_which3`), '') as 'madb_which3', IF(    CHAR_LENGTH(`whens1`.`whens_when2`), CONCAT_WS('',   `whens1`.`whens_when2`), '') as 'madb_when2', IF(    CHAR_LENGTH(if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), CONCAT_WS('',   if(`whens1`.`whens_when3`,time_format(`whens1`.`whens_when3`,'%r'),'')), '') as 'madb_when3', IF(    CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who2`), '') as 'madb_who2', IF(    CHAR_LENGTH(`whos1`.`whos_who3`), CONCAT_WS('',   `whos1`.`whos_who3`), '') as 'madb_who3', IF(    CHAR_LENGTH(`whats1`.`whats_what2`), CONCAT_WS('',   `whats1`.`whats_what2`), '') as 'madb_what2', IF(    CHAR_LENGTH(`whats1`.`whats_what3`), CONCAT_WS('',   `whats1`.`whats_what3`), '') as 'madb_what3', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr2`), CONCAT_WS('',   `howrs1`.`howrs_howr2`), '') as 'madb_howr2', IF(    CHAR_LENGTH(`howrs1`.`howrs_howr3`), CONCAT_WS('',   `howrs1`.`howrs_howr3`), '') as 'madb_howr3', IF(    CHAR_LENGTH(`howss1`.`howss_hows2`), CONCAT_WS('',   `howss1`.`howss_hows2`), '') as 'madb_hows2', IF(    CHAR_LENGTH(`howss1`.`howss_hows3`), CONCAT_WS('',   `howss1`.`howss_hows3`), '') as 'madb_hows3', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq2', IF(    CHAR_LENGTH(`howqs1`.`howqs_howq3`), CONCAT_WS('',   `howqs1`.`howqs_howq3`), '') as 'madb_howq3', IF(    CHAR_LENGTH(`howts1`.`howts_howt2`), CONCAT_WS('',   `howts1`.`howts_howt2`), '') as 'madb_howt2', IF(    CHAR_LENGTH(`howts1`.`howts_howt3`), CONCAT_WS('',   `howts1`.`howts_howt3`), '') as 'madb_howt3', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'madb_premises_id', `madb`.`madb_status` as 'madb_status', `madb`.`madb_created` as 'madb_created', `madb`.`madb_updated` as 'madb_updated' FROM `madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`madb`.`madb_who1` LEFT JOIN `whens` as whens1 ON `whens1`.`whens_id`=`madb`.`madb_when1` LEFT JOIN `whichs` as whichs1 ON `whichs1`.`whichs_id`=`madb`.`madb_which1` LEFT JOIN `wheres` as wheres1 ON `wheres1`.`wheres_id`=`madb`.`madb_where1` LEFT JOIN `whys` as whys1 ON `whys1`.`whys_id`=`madb`.`madb_why1` LEFT JOIN `howrs` as howrs1 ON `howrs1`.`howrs_id`=`madb`.`madb_howr1` LEFT JOIN `howss` as howss1 ON `howss1`.`howss_id`=`madb`.`madb_hows1` LEFT JOIN `howqs` as howqs1 ON `howqs1`.`howqs_id`=`madb`.`madb_howq1` LEFT JOIN `howts` as howts1 ON `howts1`.`howts_id`=`madb`.`madb_howt1` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb`.`madb_premises_id` "
				],
			],
			'whats' => [
			],
			'whos' => [
				'whos_premise' => [
					'parent-table' => 'premises',
					'parent-primary-key' => 'premises_id',
					'child-primary-key' => 'whos_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Whos (Doers) <span class="hidden child-label-whos child-field-caption">(Whos premise)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'Who1 (Doer Role)', 2 => 'Who2 (Doer ID)', 3 => 'Who3 (Buddy ID)', 4 => 'Description', 5 => 'SWG Token', 6 => 'SWG Email', 7 => 'Whos premise', 8 => 'Created AT', 9 => 'Updated AT'],
					'display-field-names' => [0 => 'whos_id', 1 => 'whos_who1', 2 => 'whos_who2', 3 => 'whos_who3', 4 => 'whos_description', 5 => 'whos_swg_token', 6 => 'whos_swg_email', 7 => 'whos_premise', 8 => 'whos_created', 9 => 'whos_updated'],
					'sortable-fields' => [0 => '`whos`.`whos_id`', 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => '`whos`.`whos_created`', 9 => '`whos`.`whos_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-whos',
					'template-printable' => 'children-whos-printable',
					'query' => "SELECT `whos`.`whos_id` as 'whos_id', `whos`.`whos_who1` as 'whos_who1', `whos`.`whos_who2` as 'whos_who2', `whos`.`whos_who3` as 'whos_who3', `whos`.`whos_description` as 'whos_description', `whos`.`whos_swg_token` as 'whos_swg_token', `whos`.`whos_swg_email` as 'whos_swg_email', IF(    CHAR_LENGTH(`premises1`.`premises_name`) || CHAR_LENGTH(`premises1`.`premises_radius`), CONCAT_WS('',   `premises1`.`premises_name`, '-', `premises1`.`premises_radius`), '') as 'whos_premise', `whos`.`whos_created` as 'whos_created', `whos`.`whos_updated` as 'whos_updated' FROM `whos` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`whos`.`whos_premise` "
				],
			],
			'whens' => [
			],
			'whichs' => [
			],
			'wheres' => [
			],
			'whys' => [
			],
			'howrs' => [
			],
			'howss' => [
			],
			'howqs' => [
			],
			'howts' => [
			],
			'utedb' => [
				'utedb_madb' => [
					'parent-table' => 'madb',
					'parent-primary-key' => 'madb_id',
					'child-primary-key' => 'utedb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Master Anatomy DB <span class="hidden child-label-utedb child-field-caption">(MADb Id)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Utedb id', 1 => 'MADb Id', 2 => 'Who 1', 3 => 'Premises Id', 4 => 'Utedb proof image', 5 => 'AI Audit (%) Score', 6 => 'PC Audit', 7 => 'RDA Audit', 8 => 'BB Audit', 9 => 'Customer Review(s)', 10 => 'CAR VIDEO', 11 => 'Collective TF', 12 => 'Delta Flag', 13 => 'Hows Score', 14 => 'ELAI RDA ID', 15 => 'Created AT', 16 => 'Updated AT'],
					'display-field-names' => [0 => 'utedb_id', 1 => 'utedb_madb', 2 => 'utedb_madb_who1', 3 => 'utedb_premises_id', 4 => 'utedb_proof_image', 5 => 'utedb_ai_audit', 6 => 'utedb_pc_audit', 7 => 'utedb_rda_audit', 8 => 'utedb_bb_audit', 9 => 'utedb_car', 10 => 'utedb_car_vid', 11 => 'utedb_col_tf', 12 => 'utedb_delta_flag', 13 => 'utedb_hows1', 14 => 'utedb_elairda_id', 15 => 'utedb_created', 16 => 'utedb_updated'],
					'sortable-fields' => [0 => '`utedb`.`utedb_id`', 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => '`utedb`.`utedb_ai_audit`', 6 => 7, 7 => 8, 8 => 9, 9 => '`utedb`.`utedb_car`', 10 => 11, 11 => 12, 12 => 13, 13 => 14, 14 => 15, 15 => '`utedb`.`utedb_created`', 16 => '`utedb`.`utedb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-utedb',
					'template-printable' => 'children-utedb-printable',
					'query' => "SELECT `utedb`.`utedb_id` as 'utedb_id', IF(    CHAR_LENGTH(`madb1`.`madb_id`) || CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `madb1`.`madb_id`, '-', `whats1`.`whats_what1`), '') as 'utedb_madb', `utedb`.`utedb_madb_who1` as 'utedb_madb_who1', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'utedb_premises_id', `utedb`.`utedb_proof_image` as 'utedb_proof_image', `utedb`.`utedb_ai_audit` as 'utedb_ai_audit', `utedb`.`utedb_pc_audit` as 'utedb_pc_audit', `utedb`.`utedb_rda_audit` as 'utedb_rda_audit', `utedb`.`utedb_bb_audit` as 'utedb_bb_audit', `utedb`.`utedb_car` as 'utedb_car', `utedb`.`utedb_car_vid` as 'utedb_car_vid', `utedb`.`utedb_col_tf` as 'utedb_col_tf', `utedb`.`utedb_delta_flag` as 'utedb_delta_flag', `utedb`.`utedb_hows1` as 'utedb_hows1', IF(    CHAR_LENGTH(`whos1`.`whos_who1`) || CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who1`, '-', `whos1`.`whos_who2`), '') as 'utedb_elairda_id', `utedb`.`utedb_created` as 'utedb_created', `utedb`.`utedb_updated` as 'utedb_updated' FROM `utedb` LEFT JOIN `madb` as madb1 ON `madb1`.`madb_id`=`utedb`.`utedb_madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb1`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`utedb`.`utedb_elairda_id` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb1`.`madb_premises_id` "
				],
				'utedb_elairda_id' => [
					'parent-table' => 'whos',
					'parent-primary-key' => 'whos_id',
					'child-primary-key' => 'utedb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Universal TEDB <span class="hidden child-label-utedb child-field-caption">(ELAI RDA ID)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Utedb id', 1 => 'MADb Id', 2 => 'Who 1', 3 => 'Premises Id', 4 => 'Utedb proof image', 5 => 'AI Audit (%) Score', 6 => 'PC Audit', 7 => 'RDA Audit', 8 => 'BB Audit', 9 => 'Customer Review(s)', 10 => 'CAR VIDEO', 11 => 'Collective TF', 12 => 'Delta Flag', 13 => 'Hows Score', 14 => 'ELAI RDA ID', 15 => 'Created AT', 16 => 'Updated AT'],
					'display-field-names' => [0 => 'utedb_id', 1 => 'utedb_madb', 2 => 'utedb_madb_who1', 3 => 'utedb_premises_id', 4 => 'utedb_proof_image', 5 => 'utedb_ai_audit', 6 => 'utedb_pc_audit', 7 => 'utedb_rda_audit', 8 => 'utedb_bb_audit', 9 => 'utedb_car', 10 => 'utedb_car_vid', 11 => 'utedb_col_tf', 12 => 'utedb_delta_flag', 13 => 'utedb_hows1', 14 => 'utedb_elairda_id', 15 => 'utedb_created', 16 => 'utedb_updated'],
					'sortable-fields' => [0 => '`utedb`.`utedb_id`', 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => '`utedb`.`utedb_ai_audit`', 6 => 7, 7 => 8, 8 => 9, 9 => '`utedb`.`utedb_car`', 10 => 11, 11 => 12, 12 => 13, 13 => 14, 14 => 15, 15 => '`utedb`.`utedb_created`', 16 => '`utedb`.`utedb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-utedb',
					'template-printable' => 'children-utedb-printable',
					'query' => "SELECT `utedb`.`utedb_id` as 'utedb_id', IF(    CHAR_LENGTH(`madb1`.`madb_id`) || CHAR_LENGTH(`whats1`.`whats_what1`), CONCAT_WS('',   `madb1`.`madb_id`, '-', `whats1`.`whats_what1`), '') as 'utedb_madb', `utedb`.`utedb_madb_who1` as 'utedb_madb_who1', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'utedb_premises_id', `utedb`.`utedb_proof_image` as 'utedb_proof_image', `utedb`.`utedb_ai_audit` as 'utedb_ai_audit', `utedb`.`utedb_pc_audit` as 'utedb_pc_audit', `utedb`.`utedb_rda_audit` as 'utedb_rda_audit', `utedb`.`utedb_bb_audit` as 'utedb_bb_audit', `utedb`.`utedb_car` as 'utedb_car', `utedb`.`utedb_car_vid` as 'utedb_car_vid', `utedb`.`utedb_col_tf` as 'utedb_col_tf', `utedb`.`utedb_delta_flag` as 'utedb_delta_flag', `utedb`.`utedb_hows1` as 'utedb_hows1', IF(    CHAR_LENGTH(`whos1`.`whos_who1`) || CHAR_LENGTH(`whos1`.`whos_who2`), CONCAT_WS('',   `whos1`.`whos_who1`, '-', `whos1`.`whos_who2`), '') as 'utedb_elairda_id', `utedb`.`utedb_created` as 'utedb_created', `utedb`.`utedb_updated` as 'utedb_updated' FROM `utedb` LEFT JOIN `madb` as madb1 ON `madb1`.`madb_id`=`utedb`.`utedb_madb` LEFT JOIN `whats` as whats1 ON `whats1`.`whats_id`=`madb1`.`madb_what1` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`utedb`.`utedb_elairda_id` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`madb1`.`madb_premises_id` "
				],
			],
			'premises' => [
			],
			'pnb' => [
				'pnb_premises_id' => [
					'parent-table' => 'premises',
					'parent-primary-key' => 'premises_id',
					'child-primary-key' => 'pnb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Pnb <span class="hidden child-label-pnb child-field-caption">(Premises Id)</span>',
					'auto-close' => true,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'PnB Type', 2 => 'Premises Id', 3 => 'Whos Id', 4 => 'Delta Flag', 5 => 'Admin', 6 => 'Comments', 7 => 'Created AT', 8 => 'Updated AT'],
					'display-field-names' => [0 => 'pnb_id', 1 => 'pnb_type', 2 => 'pnb_premises_id', 3 => 'pnb_whos_id', 4 => 'pnb_delta_flag', 5 => 'pnb_admin', 6 => 'pnb_comments', 7 => 'pnb_created', 8 => 'pnb_updated'],
					'sortable-fields' => [0 => '`pnb`.`pnb_id`', 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => '`pnb`.`pnb_created`', 8 => '`pnb`.`pnb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-pnb',
					'template-printable' => 'children-pnb-printable',
					'query' => "SELECT `pnb`.`pnb_id` as 'pnb_id', `pnb`.`pnb_type` as 'pnb_type', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'pnb_premises_id', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'pnb_whos_id', `pnb`.`pnb_delta_flag` as 'pnb_delta_flag', `pnb`.`pnb_admin` as 'pnb_admin', `pnb`.`pnb_comments` as 'pnb_comments', `pnb`.`pnb_created` as 'pnb_created', `pnb`.`pnb_updated` as 'pnb_updated' FROM `pnb` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`pnb`.`pnb_premises_id` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`pnb`.`pnb_whos_id` "
				],
				'pnb_whos_id' => [
					'parent-table' => 'whos',
					'parent-primary-key' => 'whos_id',
					'child-primary-key' => 'pnb_id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Presence & Being <span class="hidden child-label-pnb child-field-caption">(Whos Id)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'Id', 1 => 'PnB Type', 2 => 'Premises Id', 3 => 'Whos Id', 4 => 'Delta Flag', 5 => 'Admin', 6 => 'Comments', 7 => 'Created AT', 8 => 'Updated AT'],
					'display-field-names' => [0 => 'pnb_id', 1 => 'pnb_type', 2 => 'pnb_premises_id', 3 => 'pnb_whos_id', 4 => 'pnb_delta_flag', 5 => 'pnb_admin', 6 => 'pnb_comments', 7 => 'pnb_created', 8 => 'pnb_updated'],
					'sortable-fields' => [0 => '`pnb`.`pnb_id`', 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => '`pnb`.`pnb_created`', 8 => '`pnb`.`pnb_updated`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'asc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-pnb',
					'template-printable' => 'children-pnb-printable',
					'query' => "SELECT `pnb`.`pnb_id` as 'pnb_id', `pnb`.`pnb_type` as 'pnb_type', IF(    CHAR_LENGTH(`premises1`.`premises_id`) || CHAR_LENGTH(`premises1`.`premises_name`), CONCAT_WS('',   `premises1`.`premises_id`, '-', `premises1`.`premises_name`), '') as 'pnb_premises_id', IF(    CHAR_LENGTH(`whos1`.`whos_who2`) || CHAR_LENGTH(`whos1`.`whos_description`), CONCAT_WS('',   `whos1`.`whos_who2`, '-', `whos1`.`whos_description`), '') as 'pnb_whos_id', `pnb`.`pnb_delta_flag` as 'pnb_delta_flag', `pnb`.`pnb_admin` as 'pnb_admin', `pnb`.`pnb_comments` as 'pnb_comments', `pnb`.`pnb_created` as 'pnb_created', `pnb`.`pnb_updated` as 'pnb_updated' FROM `pnb` LEFT JOIN `premises` as premises1 ON `premises1`.`premises_id`=`pnb`.`pnb_premises_id` LEFT JOIN `whos` as whos1 ON `whos1`.`whos_id`=`pnb`.`pnb_whos_id` "
				],
			],
		];

		if($skipPermissions) return $pcConfig;

		if(!in_array($filterByPermission, ['access', 'insert', 'edit', 'delete'])) $filterByPermission = 'view';

		/**
		* dynamic configuration based on current user's permissions
		* $userPCConfig array is populated only with parent tables where the user has access to
		* at least one child table
		*/
		$userPCConfig = [];
		foreach($pcConfig as $tn => $lookupFields) {
			$perm = getTablePermissions($tn);
			if(!$perm[$filterByPermission]) continue;

			foreach($lookupFields as $fn => $ChildConfig) {
				$permParent = getTablePermissions($ChildConfig['parent-table']);
				if(!$permParent[$filterByPermission]) continue;

				$userPCConfig[$tn][$fn] = $pcConfig[$tn][$fn];
				// show add new only if configured above AND the user has insert permission
				$userPCConfig[$tn][$fn]['display-add-new'] = ($perm['insert'] && $pcConfig[$tn][$fn]['display-add-new']);
			}
		}

		return $userPCConfig;
	}

	#########################################################

	function getChildTables($parentTable, $skipPermissions = false, $filterByPermission = 'view') {
		$pcConfig = getLookupFields($skipPermissions, $filterByPermission);
		$childTables = [];
		foreach($pcConfig as $tn => $lookupFields)
			foreach($lookupFields as $fn => $ChildConfig)
				if($ChildConfig['parent-table'] == $parentTable)
					$childTables[$tn][$fn] = $ChildConfig;

		return $childTables;
	}

	#########################################################

	function isDetailViewEnabled($tn) {
		$tables = ['madb', 'whats', 'whos', 'whens', 'whichs', 'wheres', 'whys', 'howrs', 'howss', 'howqs', 'howts', 'utedb', 'premises', 'pnb', ];
		return in_array($tn, $tables);
	}

	#########################################################

	function appDir($path = '') {
		// if path not empty and doesn't start with a slash, add it
		if($path && $path[0] != '/') $path = '/' . $path;
		return __DIR__ . $path;
	}

	#########################################################

	/**
	 * Inserts a new record in a table, performing various before and after tasks
	 * @param string $tableName the name of the table to insert into
	 * @param array $data associative array of field names and values to insert
	 * @param string $recordOwner the username of the record owner
	 * @param string $errorMessage error message to be set in case of failure
	 *
	 * @return mixed the ID of the inserted record if successful, false otherwise
	 */
	function tableInsert($tableName, $data, $recordOwner, &$errorMessage = '') {
		global $Translation;

		// mm: can member insert record?
		if(!getTablePermissions($tableName)['insert']) {
			$errorMessage = $Translation['no insert permission'];
			return false;
		}

		$memberInfo = getMemberInfo();

		// check for required fields
		$fields = get_table_fields($tableName);
		$notNullFields = notNullFields($tableName);
		foreach($notNullFields as $fieldName) {
			if($data[$fieldName] !== '') continue;

			$errorMessage = "{$fields[$fieldName]['info']['caption']}: {$Translation['field not null']}";
			return false;
		}

		@include_once(__DIR__ . "/hooks/{$tableName}.php");

		// hook: before_insert
		$beforeInsertFunc = "{$tableName}_before_insert";
		if(function_exists($beforeInsertFunc)) {
			$args = [];
			if(!$beforeInsertFunc($data, $memberInfo, $args)) {
				if(isset($args['error_message'])) $errorMessage = $args['error_message'];
				return false;
			}
		}

		$pkIsAutoInc = pkIsAutoIncrement($tableName);
		$pkField = getPKFieldName($tableName) ?: '';

		$error = '';
		// set empty fields to NULL
		$data = array_map(function($v) { return ($v === '' ? NULL : $v); }, $data);
		insert($tableName, backtick_keys_once($data), $error);
		if($error) {
			$errorMessage = $error;
			return false;
		}

		$recID = $pkIsAutoInc ? db_insert_id() : ($data[$pkField] ?? false);

		update_calc_fields($tableName, $recID, calculated_fields()[$tableName]);

		// hook: after_insert
		$afterInsertFunc = "{$tableName}_after_insert";
		if(function_exists($afterInsertFunc)) {
			if($row = getRecord($tableName, $recID)) {
				$data = array_map('makeSafe', $row);
			}
			$data['selectedID'] = makeSafe($recID);
			$args = [];
			if(!$afterInsertFunc($data, $memberInfo, $args)) { return $recID; }
		}

		// mm: save ownership data
		// record owner is current user
		set_record_owner($tableName, $recID, $recordOwner);

		return $recID;
	}

	#########################################################

	/**
	 * Checks whether the primary key of a table is auto-increment
	 * @param string $tn the name of the table
	 *
	 * @return bool true if the primary key is auto-increment, false otherwise
	 */
	function pkIsAutoIncrement($tn) {
		// caching
		static $cache = [];

		if(isset($cache[$tn])) return $cache[$tn];

		$pk = getPKFieldName($tn);
		if(!$pk) {
			$cache[$tn] = false;
			return false;
		}

		$isAutoInc = sqlValue("SHOW COLUMNS FROM `$tn` WHERE Field='{$pk}' AND Extra LIKE '%auto_increment%'");
		$cache[$tn] = $isAutoInc ? true : false;
		return $cache[$tn];
	}

	#########################################################

	/**
	 * @return bool true if the current user is an admin and revealing SQL is allowed, false otherwise
	 */
	function showSQL() {
		$allowAdminShowSQL = true;
		return $allowAdminShowSQL && getLoggedAdmin() !== false;
	}

	#########################################################

	/**
	 * Compact filters by removing empty conditions and groups
	 * @param array $FilterAnd array of filter AND/OR conditions, passed by reference
	 * @param array $FilterField array of filter field indices, passed by reference
	 * @param array $filterOperator array of filter operators, passed by reference
	 * @param array $FilterValue array of filter values, passed by reference
	 */
	function compactFilters(&$FilterAnd, &$FilterField, &$FilterOperator, &$FilterValue) {

		$filterConditionIsEmpty = function($i) use ($FilterField, $FilterOperator) {
			// check if filter is empty
			return !$FilterField[$i] || !$FilterOperator[$i];
		};

		$filterGroupIsEmpty = function($i) use ($filterConditionIsEmpty) {
			// check if filter group is empty
			for($j = 1; $j <= FILTERS_PER_GROUP; $j++) {
				if(!$filterConditionIsEmpty(($i - 1) * FILTERS_PER_GROUP + $j)) {
					return false;
				}
			}
			return true;
		};

		// 'compact' filter conditions by removing gaps inside each group and removing empty groups
		$compactedGroups = [];
		for($gi = 1; $gi <= FILTER_GROUPS; $gi++) {
			$compactedGroups[$gi] = [];
			for($fi = 1; $fi <= FILTERS_PER_GROUP; $fi++) {
				$filterIndex = (($gi - 1) * FILTERS_PER_GROUP) + $fi;
				if(!$filterConditionIsEmpty($filterIndex)) {
					$compactedGroups[$gi][] = $filterIndex;
				}
			}
		}

		// rmove empty groups
		$compactedGroups = array_filter($compactedGroups, function($group) {
			return count($group) > 0;
		});

		// re-index groups
		$compactedGroups = array_values($compactedGroups);

		// now rebuild filters based on the compacted groups
		$newFilterAnd = $newFilterField = $newFilterOperator = $newFilterValue = [];
		foreach($compactedGroups as $gi0b => $group) {
			foreach($group as $fi0b => $fi) {
				$filterIndex = $gi0b * FILTERS_PER_GROUP + $fi0b + 1;
				$newFilterAnd[$filterIndex] = $FilterAnd[$fi];
				$newFilterField[$filterIndex] = $FilterField[$fi];
				$newFilterOperator[$filterIndex] = $FilterOperator[$fi];
				$newFilterValue[$filterIndex] = $FilterValue[$fi];
			}
		}

		// update filter variables
		$FilterAnd = $newFilterAnd;
		$FilterField = $newFilterField;
		$FilterOperator = $newFilterOperator;
		$FilterValue = $newFilterValue;
	}

