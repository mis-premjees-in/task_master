<?php
	// check this file's MD5 to make sure it wasn't called before
	$tenantId = Authentication::tenantIdPadded();
	$setupHash = __DIR__ . "/setup{$tenantId}.md5";

	$prevMD5 = @file_get_contents($setupHash);
	$thisMD5 = md5_file(__FILE__);

	// check if this setup file already run
	if($thisMD5 != $prevMD5) {
		// set up tables
		setupTable('madb', []);
		setupIndexes('madb', ['madb_what1','madb_who1','madb_when1','madb_which1','madb_where1','madb_why1','madb_howr1','madb_hows1','madb_howq1','madb_howt1','madb_premises_id',]);

		setupTable('whats', [
				" ALTER TABLE `whats` CHANGE `whats_what1` `whats_what1` TEXT NOT NULL ",
				" ALTER TABLE `whats` CHANGE `whats_what2` `whats_what2` TEXT NOT NULL ",
				" ALTER TABLE `whats` CHANGE `whats_what3` `whats_what3` TEXT NULL ",
				" ALTER TABLE `whats` CHANGE `whats_description` `whats_description` TEXT NULL ",
			]);

		setupTable('whos', [
				" ALTER TABLE `whos` CHANGE `whos_description` `whos_description` TEXT NULL ",
				" ALTER TABLE `whos` CHANGE `whos_profile_img` `whos_profile_img` TINYTEXT NULL ",
				" ALTER TABLE `whos` CHANGE `whos_profile_img` `whos_profile_img` VARCHAR(40) NULL ",
				" ALTER TABLE `whos` CHANGE `whos_profile_img` `whos_profile_img` VARCHAR(255) NULL ",
			]);
		setupIndexes('whos', ['whos_premise',]);

		setupTable('whens', [
				" ALTER TABLE `whens` CHANGE `whens_description` `whens_description` TEXT NULL ",
			]);

		setupTable('whichs', [
				" ALTER TABLE `whichs` CHANGE `whichs_which1` `whichs_which1` VARCHAR(255) NOT NULL ",
				" ALTER TABLE `whichs` CHANGE `whichs_which2` `whichs_which2` VARCHAR(255) NOT NULL ",
				" ALTER TABLE `whichs` CHANGE `whichs_which3` `whichs_which3` VARCHAR(255) NULL ",
				" ALTER TABLE `whichs` CHANGE `whichs_description` `whichs_description` TEXT NULL ",
			]);

		setupTable('wheres', [
				" ALTER TABLE `wheres` CHANGE `wheres_where1` `wheres_where1` VARCHAR(255) NOT NULL ",
				" ALTER TABLE `wheres` CHANGE `wheres_where2` `wheres_where2` VARCHAR(255) NOT NULL ",
				" ALTER TABLE `wheres` CHANGE `wheres_where3` `wheres_where3` VARCHAR(255) NULL ",
				" ALTER TABLE `wheres` CHANGE `wheres_description` `wheres_description` TEXT NULL ",
			]);

		setupTable('whys', [
				" ALTER TABLE `whys` CHANGE `whys_why1` `whys_why1` VARCHAR(255) NOT NULL ",
				" ALTER TABLE `whys` CHANGE `whys_why2` `whys_why2` VARCHAR(255) NOT NULL ",
				" ALTER TABLE `whys` CHANGE `whys_why3` `whys_why3` VARCHAR(255) NULL ",
				" ALTER TABLE `whys` CHANGE `whys_description` `whys_description` TEXT NULL ",
			]);

		setupTable('howrs', [
				" ALTER TABLE `howrs` CHANGE `howrs_howr1` `howrs_howr1` VARCHAR(255) NOT NULL ",
				" ALTER TABLE `howrs` CHANGE `howrs_howr1` `howrs_howr1` TINYTEXT NOT NULL ",
				" ALTER TABLE `howrs` CHANGE `howrs_howr2` `howrs_howr2` TINYTEXT NOT NULL ",
				" ALTER TABLE `howrs` CHANGE `howrs_description` `howrs_description` TEXT NULL ",
			]);

		setupTable('howss', [
				" ALTER TABLE `howss` CHANGE `howss_hows1` `howss_hows1` TEXT NOT NULL ",
				" ALTER TABLE `howss` CHANGE `howss_hows2` `howss_hows2` TEXT NULL ",
				" ALTER TABLE `howss` CHANGE `howss_hows3` `howss_hows3` TEXT NULL ",
				" ALTER TABLE `howss` CHANGE `howss_hows4` `howss_hows4` TEXT NULL ",
				" ALTER TABLE `howss` CHANGE `howss_description` `howss_description` TEXT NULL ",
			]);

		setupTable('howqs', [
				" ALTER TABLE `howqs` CHANGE `howqs_howq1` `howqs_howq1` TINYTEXT NOT NULL ",
				" ALTER TABLE `howqs` CHANGE `howqs_description` `howqs_description` TEXT NULL ",
			]);

		setupTable('howts', [
				" ALTER TABLE `howts` CHANGE `howts_howt1` `howts_howt1` TINYTEXT NOT NULL ",
				" ALTER TABLE `howts` CHANGE `howrs_description` `howrs_description` TEXT NULL ",
			]);

		setupTable('utedb', [
				" ALTER TABLE `utedb` CHANGE `utedb_hows1` `utedb_hows1` TEXT NULL ",
				" ALTER TABLE `utedb` CHANGE `utedb_proof_image` `utedb_proof_image` TINYTEXT NULL ",
				" ALTER TABLE `utedb` CHANGE `utedb_rda_audit` `utedb_rda_audit` TINYTEXT NULL ",
				" ALTER TABLE `utedb` CHANGE `utedb_bb_audit` `utedb_bb_audit` TINYTEXT NULL ",
				" ALTER TABLE `utedb` CHANGE `utedb_car` `utedb_car` INT NULL ",
				" ALTER TABLE `utedb` CHANGE `utedb_car_vid` `utedb_car_vid` TINYTEXT NULL ",
				" ALTER TABLE `utedb` CHANGE `utedb_car_vid` `utedb_car_vid` TEXT NULL ",
				" ALTER TABLE `utedb` CHANGE `utedb_admin` `utedb_admin` TEXT NULL ",
				" ALTER TABLE `utedb` CHANGE `utedb_proof_image` `utedb_proof_image` VARCHAR(40) NULL ",
				" ALTER TABLE `utedb` CHANGE `utedb_proof_image` `utedb_proof_image` VARCHAR(255) NULL ",
			]);
		setupIndexes('utedb', ['utedb_madb','utedb_whos_id','utedb_premises_id',]);

		setupTable('premises', [
				" ALTER TABLE `premises` CHANGE `premises_name` `premises_name` TEXT NOT NULL ",
				" ALTER TABLE `premises` CHANGE `premises_latitude` `premises_latitude` DECIMAL(10,10) NOT NULL ",
				" ALTER TABLE `premises` CHANGE `premises_longitude` `premises_longitude` DECIMAL(10,10) NOT NULL ",
			]);
		setupIndexes('premises', ['premises_opening','premises_closing',]);

		setupTable('pnb', [
				" ALTER TABLE `pnb` CHANGE `pnb_type` `pnb_type` TINYTEXT NOT NULL ",
				" ALTER TABLE `pnb` CHANGE `pnb_admin` `pnb_admin` TINYTEXT NULL ",
				" ALTER TABLE `pnb` CHANGE `pnb_comments` `pnb_comments` TEXT NULL ",
			]);
		setupIndexes('pnb', ['pnb_premises_id','pnb_whos_id',]);

		setupTable('ilct_info', [
				" ALTER TABLE `ilct_info` CHANGE `ilct_info_title` `ilct_info_title` TINYTEXT NOT NULL ",
				" ALTER TABLE `ilct_info` CHANGE `ilct_info_description` `ilct_info_description` TEXT NULL ",
				" ALTER TABLE `ilct_info` CHANGE `ilct_info_link` `ilct_info_link` TEXT NULL ",
				" ALTER TABLE `ilct_info` CHANGE `ilct_info_user` `ilct_info_user` TINYTEXT NULL ",
				" ALTER TABLE `ilct_info` CHANGE `ilct_info_pass` `ilct_info_pass` TINYTEXT NULL ",
				" ALTER TABLE `ilct_info` CHANGE `ilct_info_token` `ilct_info_token` TINYTEXT NULL ",
				" ALTER TABLE `ilct_info` CHANGE `ilct_info_pass_code` `ilct_info_pass_code` TINYTEXT NULL ",
				" ALTER TABLE `ilct_info` CHANGE `ilct_info_related_table` `ilct_info_related_table` TINYTEXT NULL ",
			]);



		// set up internal tables
		setupTable('appgini_query_log', []);
		setupTable('appgini_csv_import_jobs', []);

		// save MD5
		@file_put_contents($setupHash, $thisMD5);
	}


	function setupIndexes($tableName, $arrFields) {
		if(!is_array($arrFields) || !count($arrFields)) return false;

		foreach($arrFields as $fieldName) {
			if(!$res = @db_query("SHOW COLUMNS FROM `$tableName` like '$fieldName'")) continue;
			if(!$row = @db_fetch_assoc($res)) continue;
			if($row['Key']) continue;

			@db_query("ALTER TABLE `$tableName` ADD INDEX `$fieldName` (`$fieldName`)");
		}
	}


	function setupTable($tableName, $arrAlter = []) {
		global $Translation;
		$oldTableName = '';

		$createSQL = createTableIfNotExists($tableName, true);
		ob_start();

		echo '<div style="padding: 5px; border-bottom:solid 1px silver; font-family: verdana, arial; font-size: 10px;">';

		// is there a table rename query?
		if(!empty($arrAlter)) {
			$matches = [];
			if(preg_match("/ALTER TABLE `(.*)` RENAME `$tableName`/i", $arrAlter[0], $matches)) {
				$oldTableName = $matches[1];
			}
		}

		if($res = @db_query("SELECT COUNT(1) FROM `$tableName`")) { // table already exists
			if($row = @db_fetch_array($res)) {
				echo str_replace(['<TableName>', '<NumRecords>'], [$tableName, $row[0]], $Translation['table exists']);
				if(!empty($arrAlter)) {
					echo '<br>';
					foreach($arrAlter as $alter) {
						if($alter != '') {
							echo "$alter ... ";
							if(!@db_query($alter)) {
								echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
								echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . db_error(db_link()) . '</div>';
							} else {
								echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
							}
						}
					}
				} else {
					echo $Translation['table uptodate'];
				}
			} else {
				echo str_replace('<TableName>', $tableName, $Translation['couldnt count']);
			}
		} else { // given tableName doesn't exist

			if($oldTableName != '') { // if we have a table rename query
				if($ro = @db_query("SELECT COUNT(1) FROM `$oldTableName`")) { // if old table exists, rename it.
					$renameQuery = array_shift($arrAlter); // get and remove rename query

					echo "$renameQuery ... ";
					if(!@db_query($renameQuery)) {
						echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
						echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . db_error(db_link()) . '</div>';
					} else {
						echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
					}

					if(!empty($arrAlter)) setupTable($tableName, $arrAlter); // execute Alter queries on renamed table ...
				} else { // if old tableName doesn't exist (nor the new one since we're here), then just create the table.
					setupTable($tableName); // no Alter queries passed ...
				}
			} else { // tableName doesn't exist and no rename, so just create the table
				echo str_replace("<TableName>", $tableName, $Translation["creating table"]);
				if(!@db_query($createSQL)) {
					echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
					echo '<div class="text-danger">' . $Translation['mysql said'] . db_error(db_link()) . '</div>';

					// create table with a dummy field
					@db_query("CREATE TABLE IF NOT EXISTS `$tableName` (`_dummy_deletable_field` TINYINT)");
				} else {
					echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
				}
			}

			// set Admin group permissions for newly created table if membership_grouppermissions exists
			if($ro = @db_query("SELECT COUNT(1) FROM `membership_grouppermissions`")) {
				// get Admins group id
				$ro = @db_query("SELECT `groupID` FROM `membership_groups` WHERE `name`='Admins'");
				if($ro) {
					$adminGroupID = intval(db_fetch_row($ro)[0]);
					if($adminGroupID) @db_query("INSERT IGNORE INTO `membership_grouppermissions` SET
						`groupID`='$adminGroupID',
						`tableName`='$tableName',
						`allowInsert`=1, `allowView`=1, `allowEdit`=1, `allowDelete`=1
					");
				}
			}
		}

		echo '</div>';

		$out = ob_get_clean();
		if(defined('APPGINI_SETUP') && APPGINI_SETUP) echo $out;
	}
