<?php
// /reporting/dw/potential-problems.php
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
include("../../_includes/start-session.inc.php");
include("../../_includes/config.inc.php");
include("../../_includes/database.inc.php");
include("../../_includes/software.inc.php");
include("../../_includes/auth/auth-check.inc.php");

$page_title = $reporting_section_title;
$page_subtitle = "Data Warehouse Potential Problems Report";
$software_section = "reporting-dw-potential-problems-report";
$report_name = "dw-potential-problems-report";

$generate = $_GET['generate'];
$export = $_GET['export'];

$sql_accounts_without_a_dns_zone = "SELECT domain
									FROM dw_accounts
									WHERE domain NOT IN (SELECT domain 
														 FROM dw_dns_zones)
									ORDER BY domain";
$result_accounts_without_a_dns_zone = mysql_query($sql_accounts_without_a_dns_zone,$connection);
$temp_accounts_without_a_dns_zone = mysql_num_rows($result_accounts_without_a_dns_zone);

$sql_dns_zones_without_an_account = "SELECT domain
									 FROM dw_dns_zones
									 WHERE domain NOT IN (SELECT domain 
									 					  FROM dw_accounts)
									ORDER BY domain";
$result_dns_zones_without_an_account = mysql_query($sql_dns_zones_without_an_account,$connection);
$temp_dns_zones_without_an_account = mysql_num_rows($result_dns_zones_without_an_account);

$sql_suspended_accounts = "SELECT domain
						   FROM dw_accounts
						   WHERE suspended = '1'
						   ORDER BY domain";
$result_suspended_accounts = mysql_query($sql_suspended_accounts,$connection);
$temp_suspended_accounts = mysql_num_rows($result_suspended_accounts);

if ($export == "1") {

	$current_timestamp_unix = strtotime(date("Y-m-d H:i:s"));
	$export_filename = "dw_potential_problems_report_" . $current_timestamp_unix . ".csv";
	include("../../_includes/system/export/header.inc.php");

	$row_content[$count++] = $page_subtitle;
	include("../../_includes/system/export/write-row.inc.php");

	fputcsv($file_content, $blank_line);

	if ($temp_accounts_without_a_dns_zone == 0) {
        
        $accounts_without_a_dns_zone_flag = 1;
        
    } else {

		$row_content[$count++] = "Accounts without a DNS Zone (" . $temp_accounts_without_a_dns_zone . ")";
		include("../../_includes/system/export/write-row.inc.php");

        while ($row_accounts_without_a_dns_zone = mysql_fetch_object($result_accounts_without_a_dns_zone)) {

			$row_content[$count++] = $row_accounts_without_a_dns_zone->domain;

        }
		include("../../_includes/system/export/write-row.inc.php");

		fputcsv($file_content, $blank_line);

    }

    if ($temp_dns_zones_without_an_account == 0) {
        
        $dns_zones_without_an_account_flag = 1;
        
    } else {
    
		$row_content[$count++] = "DNS Zones without an Account (" . $temp_dns_zones_without_an_account . ")";
		include("../../_includes/system/export/write-row.inc.php");

        while ($row_dns_zones_without_an_account = mysql_fetch_object($result_dns_zones_without_an_account)) {

			$row_content[$count++] = $row_dns_zones_without_an_account->domain;

        }
		include("../../_includes/system/export/write-row.inc.php");

		fputcsv($file_content, $blank_line);

    }


    if ($temp_suspended_accounts == 0) {
        
        $suspended_accounts_flag = 1;
        
    } else {
    
		$row_content[$count++] = "Suspended Accounts (" . $temp_suspended_accounts . ")";
		include("../../_includes/system/export/write-row.inc.php");

        while ($row_suspended_accounts = mysql_fetch_object($result_suspended_accounts)) {

			$row_content[$count++] = $row_suspended_accounts->domain;

        }
		include("../../_includes/system/export/write-row.inc.php");

		fputcsv($file_content, $blank_line);

    }

	include("../../_includes/system/export/footer.inc.php");

}
?>
<?php include("../../_includes/doctype.inc.php"); ?>
<html>
<head>
<title><?=$software_title?> :: <?=$page_title?> :: <?=$page_subtitle?></title>
<?php include("../../_includes/layout/head-tags.inc.php"); ?>
</head>
<body>
<?php include("../../_includes/layout/header.inc.php"); ?>
<?php include("../../_includes/layout/reporting-block.inc.php"); ?>
<?php include("../../_includes/layout/table-export-top.inc.php"); ?>
    <form name="export_dw_form" method="post" action="<?=$PHP_SELF?>"> 
        <a href="<?=$PHP_SELF?>?generate=1">Generate</a>
        <?php if ($generate == 1) { ?>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>[<a href="<?=$PHP_SELF?>?export=1&new_start_date=<?=$new_start_date?>&new_end_date=<?=$new_end_date?>&all=<?=$all?>">EXPORT REPORT</a>]</strong>
        <?php } ?>
    </form>
<?php include("../../_includes/layout/table-export-bottom.inc.php"); ?>
<?php if ($generate == 1) { ?>
<BR><font class="subheadline"><?=$page_subtitle?></font><BR>
<BR>
<?php } ?>
<?php
if ($generate == 1) {
	
	if ($temp_accounts_without_a_dns_zone == 0) {
        
        $accounts_without_a_dns_zone_flag = 1;
        
    } else { ?>
    
        <strong>Accounts without a DNS Zone (<?=$temp_accounts_without_a_dns_zone?>)</strong><BR><?php
    
        while ($row_accounts_without_a_dns_zone = mysql_fetch_object($result_accounts_without_a_dns_zone)) {
        
            $account_list_raw .= $row_accounts_without_a_dns_zone->domain . ", ";
        
        }
        
        $account_list = substr($account_list_raw, 0, -2);
    
        if ($account_list != "") { 
        
            echo $account_list;
        
        } else {
        
            echo "n/a";
        
        }
		
		echo "<BR><BR>";
    
    }

    if ($temp_dns_zones_without_an_account == 0) {
        
        $dns_zones_without_an_account_flag = 1;
        
    } else { ?>
    
        <strong>DNS Zones without an Account (<?=$temp_dns_zones_without_an_account?>)</strong><BR><?php
    
        while ($row_dns_zones_without_an_account = mysql_fetch_object($result_dns_zones_without_an_account)) {
        
            $zone_list_raw .= $row_dns_zones_without_an_account->domain . ", ";
        
        }
        
        $zone_list = substr($zone_list_raw, 0, -2);
    
        if ($zone_list != "") { 
        
            echo $zone_list;
        
        } else {
        
            echo "n/a";
        
        }

		echo "<BR><BR>";
    
    }

    if ($temp_suspended_accounts == 0) {
        
        $suspended_accounts_flag = 1;
        
    } else { ?>
    
        <strong>Suspended Accounts (<?=$temp_suspended_accounts?>)</strong><BR><?php
    
        while ($row_suspended_accounts = mysql_fetch_object($result_suspended_accounts)) {
        
            $suspended_list_raw .= $row_suspended_accounts->domain . ", ";
        
        }
        
        $suspended_list = substr($suspended_list_raw, 0, -2);
        
        if ($suspended_list != "") { 
        
            echo $suspended_list;
        
        } else {
        
            echo "n/a";
        
        }
        
    }

} ?>
<?php include("../../_includes/layout/footer.inc.php"); ?>
</body>
</html>
