<?php
// /cron/dw.php
// 
// DomainMOD is an open source application written in PHP & MySQL used to track and manage your web resources.
// Copyright (C) 2010 Greg Chetcuti
// 
// DomainMOD is free software; you can redistribute it and/or modify it under the terms of the GNU General
// Public License as published by the Free Software Foundation; either version 2 of the License, or (at your
// option) any later version.
// 
// DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
// implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
// 
// You should have received a copy of the GNU General Public License along with DomainMOD. If not, please see
// http://www.gnu.org/licenses/
?>
<?php
include("../_includes/config.inc.php");
include("../_includes/database.inc.php");
include("../_includes/software.inc.php");
include("../_includes/timestamps/current-timestamp.inc.php");

$direct = $_GET['direct'];

if ($direct == "1") {

	echo "Rebuilding the Data Warehouse...<BR><BR>";

}

include("../_includes/config-demo.inc.php");

if ($demo_install != "1") {

	$sql_server = "SELECT id, host, protocol, port, username, hash
				   FROM dw_servers
				   ORDER BY name";
	$result_server = mysql_query($sql_server,$connection);
	
	if (mysql_num_rows($result_server) == 0) {
		
		if ($direct == "1") {
			
			echo "You have not yet added any Servers to " . $software_title . ".<BR><BR>";
			exit;
			
		}
		
	} else {

		$build_start_time_overall = date("Y-m-d H:i:s");

		$sql = "DROP TABLE IF EXISTS dw_accounts";
		$result = mysql_query($sql,$connection) or die(mysql_error());
	
		$sql = "DROP TABLE IF EXISTS dw_dns_zones";
		$result = mysql_query($sql,$connection) or die(mysql_error());
	
		$sql = "DROP TABLE IF EXISTS dw_dns_records";
		$result = mysql_query($sql,$connection) or die(mysql_error());

		$sql = "UPDATE dw_servers
				SET build_status_overall = '0',
					build_start_time_overall = '" . $build_start_time_overall . "',
					build_end_time_overall = '0000-00-00 00:00:00',
					build_time = '0',
					build_status = '0',
					build_start_time = '0000-00-00 00:00:00',
					build_end_time = '0000-00-00 00:00:00',
					build_time_overall = '0',
					dw_accounts = '0',
					dw_dns_zones = '0',
					dw_dns_records = '0'";
		$result = mysql_query($sql,$connection);

		$sql = "CREATE TABLE IF NOT EXISTS dw_accounts (
				id int(10) NOT NULL auto_increment,
				server_id int(10) NOT NULL,
				domain varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				ip varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				owner varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				user varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				email varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				plan varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				theme varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				shell varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				partition varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				disklimit varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				diskused varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				maxaddons varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				maxftp varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				maxlst varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				maxparked varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				maxpop varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				maxsql varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				maxsub varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				startdate varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				unix_startdate int(10) NOT NULL,
				suspended int(1) NOT NULL,
				suspendreason varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				suspendtime int(10) NOT NULL,
				MAX_EMAIL_PER_HOUR int(10) NOT NULL,
				MAX_DEFER_FAIL_PERCENTAGE int(10) NOT NULL,
				MIN_DEFER_FAIL_TO_TRIGGER_PROTECTION int(10) NOT NULL,
				insert_time datetime NOT NULL,
				PRIMARY KEY  (id)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
		$result = mysql_query($sql,$connection) or die(mysql_error());

		$sql = "CREATE TABLE IF NOT EXISTS dw_dns_zones (
				id int(10) NOT NULL auto_increment,
				server_id int(10) NOT NULL,
				domain varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				zonefile varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				insert_time datetime NOT NULL,
				PRIMARY KEY  (id)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
		$result = mysql_query($sql,$connection) or die(mysql_error());

		$sql = "CREATE TABLE IF NOT EXISTS dw_dns_records (
				id int(10) NOT NULL auto_increment,
				server_id int(10) NOT NULL,
				dns_zone_id int(10) NOT NULL,
				domain varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				zonefile varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL default '',
				new_order int(10) NOT NULL,
				mname varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				rname varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				serial int(20) NOT NULL,
				refresh int(10) NOT NULL,
				retry int(10) NOT NULL,
				expire varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				minimum int(10) NOT NULL,
				nsdname varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				ttl int(10) NOT NULL,
				class varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				type varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				address varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				cname varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				exchange varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				preference int(10) NOT NULL,
				txtdata varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				line int(10) NOT NULL,
				nlines int(10) NOT NULL,
				raw longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				insert_time datetime NOT NULL,
				PRIMARY KEY  (id)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
		$result = mysql_query($sql,$connection) or die(mysql_error());

		while ($row_server = mysql_fetch_object($result_server)) {

			$build_start_time = date("Y-m-d H:i:s");

			$sql = "UPDATE dw_servers
					SET build_start_time = '" . $build_start_time . "',
						build_status = '0'
					WHERE id = '" . $row_server->id . "'";
			$result = mysql_query($sql,$connection);

			$api_call = "/xml-api/listaccts?searchtype=domain&search=";
			include("api/dw.whm.inc.php");

			if ($result != false) {
				
				$xml = simplexml_load_string($result);
		
				foreach($xml->acct as $hit) {
			
					$disklimit_formatted = rtrim($hit->disklimit, 'M');
					$diskused_formatted = rtrim($hit->diskused, 'M');
	
					$sql = "INSERT INTO dw_accounts 
							(server_id, domain, ip, owner, user, email, plan, theme, shell, partition, disklimit, diskused, maxaddons, maxftp, maxlst, maxparked, maxpop, maxsql, maxsub, startdate, unix_startdate, suspended, suspendreason, suspendtime, MAX_EMAIL_PER_HOUR, MAX_DEFER_FAIL_PERCENTAGE, MIN_DEFER_FAIL_TO_TRIGGER_PROTECTION, insert_time) VALUES 
							('" . $row_server->id . "', '" . $hit->domain . "', '" . $hit->ip . "', '" . $hit->owner . "', '" . $hit->user . "', '" . $hit->email . "', '" . $hit->plan . "', '" . $hit->theme . "', '" . $hit->shell . "', '" . $hit->partition . "', '" . $disklimit_formatted . "', '" . $diskused_formatted . "', '" . $hit->maxaddons . "', '" . $hit->maxftp . "', '" . $hit->maxlst . "', '" . $hit->maxparked. "', '" . $hit->maxpop . "', '" . $hit->maxsql . "', '" . $hit->maxsub . "', '" . $hit->startdate . "', '" . $hit->unix_startdate . "', '" . $hit->suspended . "', '" . $hit->suspendreason . "', '" . $hit->suspendtime . "', '" . $hit->MAX_EMAIL_PER_HOUR . "', '" . $hit->MAX_DEFER_FAIL_PERCENTAGE . "', '" . $hit->MIN_DEFER_FAIL_TO_TRIGGER_PROTECTION . "', '" . date("Y-m-d H:i:s") . "')";
					$result = mysql_query($sql,$connection) or die(mysql_error());
				
				}
				
			}
		
			$api_call = "/xml-api/listzones";
			include("api/dw.whm.inc.php");

			if ($result != false) {
				
				$xml = simplexml_load_string($result);
			
				foreach($xml->zone as $hit) {
				
					$sql = "INSERT INTO dw_dns_zones 
							(server_id, domain, zonefile, insert_time) VALUES 
							('" . $row_server->id . "', '" . $hit->domain . "', '" . $hit->zonefile . "', '" . date("Y-m-d H:i:s") . "')";
					$result = mysql_query($sql,$connection) or die(mysql_error());
				
				}
				
			}

			$sql_temp = "SELECT id, domain
						 FROM dw_dns_zones
						 WHERE server_id = '" . $row_server->id . "'
						 ORDER BY domain";
			$result_temp = mysql_query($sql_temp,$connection) or die(mysql_error());
			
			while ($row_temp = mysql_fetch_object($result_temp)) {
			
				$api_call = "/xml-api/dumpzone?domain=" . $row_temp->domain . "";
				include("api/dw.whm.inc.php");

				if ($result != false) {
					
					$xml = simplexml_load_string($result);
				
					foreach($xml->result->record as $hit) {
				
						$sql = "INSERT INTO dw_dns_records 
								(server_id, dns_zone_id, domain, mname, rname, serial, refresh, retry, expire, minimum, nsdname, name, ttl, class, type, address, cname, exchange, preference, txtdata, line, nlines, raw, insert_time) VALUES 
								('" . $row_server->id . "', '" . $row_temp->id . "', '" . $row_temp->domain . "', '" . $hit->mname . "', '" . $hit->rname . "', '" . $hit->serial . "', '" . $hit->refresh . "', '" . $hit->retry . "', '" . $hit->expire . "', '" . $hit->minimum . "', '" . $hit->nsdname . "', '" . $hit->name . "', '" . $hit->ttl . "', '" . $hit->class . "', '" . $hit->type . "', '" . $hit->address . "', '" . $hit->cname . "', '" . $hit->exchange . "', '" . $hit->preference . "', '" . $hit->txtdata . "', '" . $hit->Line . "', '" . $hit->Lines . "', '" . $hit->raw . "', '" . date("Y-m-d H:i:s") . "')";
						$result = mysql_query($sql,$connection) or die(mysql_error());
					
					}
					
				}
			
			}
			
			$build_end_time = date("Y-m-d H:i:s");
			
			$total_build_time = (strtotime($build_end_time) - strtotime($build_start_time));

			$sql = "UPDATE dw_servers
					SET build_status = '1',
						build_end_time = '" . $build_end_time . "',
						build_time = '" . $total_build_time . "',
						has_ever_been_built = '1'
					WHERE id = '" . $row_server->id . "'";
			$result = mysql_query($sql,$connection);

		}

		$sql = "DELETE FROM dw_dns_records 
				WHERE type = ':RAW'
				  AND raw = ''";
		$result = mysql_query($sql,$connection) or die(mysql_error());
	
		$sql = "UPDATE dw_dns_records 
				SET type = 'COMMENT'
				WHERE type = ':RAW'";
		$result = mysql_query($sql,$connection) or die(mysql_error());
		
		$sql = "UPDATE dw_dns_records 
				SET type = 'ZONE TTL'
				WHERE type = '\$TTL'";
		$result = mysql_query($sql,$connection) or die(mysql_error());
	
		$sql = "UPDATE dw_dns_records 
				SET nlines = '1'
				WHERE nlines = '0'";
		$result = mysql_query($sql,$connection) or die(mysql_error());
		
		$sql = "SELECT domain, zonefile
				FROM dw_dns_zones";
		$result = mysql_query($sql,$connection);
		
		while ($row = mysql_fetch_object($result)) {
			
			$sql_update_dns_records = "UPDATE dw_dns_records
									   SET zonefile = '" . $row->zonefile . "'
									   WHERE domain = '" . $row->domain . "'";
			$result_update_dns_records = mysql_query($sql_update_dns_records,$connection);	
			
		}
	
		$type_order = array();
		$count = 0;
		$new_order = 1;
		$type_order[$count++] = 'COMMENT';
		$type_order[$count++] = 'ZONE TTL';
		$type_order[$count++] = 'SOA';
		$type_order[$count++] = 'NS';
		$type_order[$count++] = 'MX';
		$type_order[$count++] = 'A';
		$type_order[$count++] = 'CNAME';
		$type_order[$count++] = 'TXT';
		$type_order[$count++] = 'SRV';

		foreach($type_order AS $key) {
	
			$sql = "UPDATE dw_dns_records
					SET new_order = '" . $new_order++ . "'
					WHERE type = '" . $key . "'";
			$result = mysql_query($sql,$connection);
	
		}
	
	}

	$sql_server_totals = "SELECT id, host, protocol, port, username, hash
						  FROM dw_servers
						  ORDER BY name";
	$result_server_totals = mysql_query($sql_server_totals,$connection);
	
	while ($row_server_totals = mysql_fetch_object($result_server_totals)) {

		$sql = "SELECT count(*) AS total_dw_accounts
				FROM dw_accounts
				WHERE server_id = '" . $row_server_totals->id . "'";
		$result = mysql_query($sql,$connection) or die(mysql_error());
		while ($row = mysql_fetch_object($result)) $temp_total_dw_accounts = $row->total_dw_accounts;
	
		$sql = "SELECT count(*) AS total_dw_dns_zones
				FROM dw_dns_zones
				WHERE server_id = '" . $row_server_totals->id . "'";
		$result = mysql_query($sql,$connection) or die(mysql_error());
		while ($row = mysql_fetch_object($result)) $temp_total_dw_dns_zones = $row->total_dw_dns_zones;
	
		$sql = "SELECT count(*) AS total_dw_dns_records
				FROM dw_dns_records
				WHERE server_id = '" . $row_server_totals->id . "'";
		$result = mysql_query($sql,$connection) or die(mysql_error());
		while ($row = mysql_fetch_object($result)) $temp_total_dw_dns_records = $row->total_dw_dns_records;
	
		$sql = "UPDATE dw_servers
				SET dw_accounts = '" . $temp_total_dw_accounts . "',
					dw_dns_zones = '" . $temp_total_dw_dns_zones . "',
					dw_dns_records = '" . $temp_total_dw_dns_records . "'
				WHERE id = '" . $row_server_totals->id . "'";
		$result = mysql_query($sql,$connection) or die(mysql_error());
		
		$temp_total_dw_accounts = "";
		$temp_total_dw_dns_zones = "";
		$temp_total_dw_dns_records = "";
		
	}

	include("_includes/dw-update-totals.inc.php");

	$build_end_time_overall = date("Y-m-d H:i:s");

	$total_build_time_overall = (strtotime($build_end_time_overall) - strtotime($build_start_time_overall));

	$sql = "UPDATE dw_servers
			SET build_status_overall = '1',
				build_end_time_overall = '" . $build_end_time_overall . "',
				build_time_overall = '" . $total_build_time_overall . "',
				has_ever_been_built_overall = '1'";
	$result = mysql_query($sql,$connection);

	$sql = "SELECT dw_accounts, dw_dns_zones, dw_dns_records
			FROM dw_server_totals";
	$result = mysql_query($sql,$connection);
	
	while ($row = mysql_fetch_object($result)) {
		$temp_dw_accounts = $row->dw_accounts;
		$temp_dw_dns_zones = $row->dw_dns_zones;
		$temp_dw_dns_records = $row->dw_dns_records;
	}
	
	if ($temp_dw_accounts == "0" && $temp_dw_dns_zones == "0" && $temp_dw_dns_records == "0") {

		$sql = "UPDATE dw_servers
				SET build_status_overall = '0',
					build_start_time_overall = '0000-00-00 00:00:00',
					build_end_time_overall = '0000-00-00 00:00:00',
					build_time = '0',
					build_status = '0',
					build_start_time = '0000-00-00 00:00:00',
					build_end_time = '0000-00-00 00:00:00',
					build_time_overall = '0',
					build_status_overall = '0',
					dw_accounts = '0',
					dw_dns_zones = '0',
					dw_dns_records = '0'";
		$result = mysql_query($sql,$connection) or die(mysql_error());
		
	}

}

if ($direct == "1") {
	
	echo "The Data Warehouse has been rebuilt.";
	
}
?>
