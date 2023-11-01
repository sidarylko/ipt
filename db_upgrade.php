<?php
include 'init.php';
$dbh = $_INFO['hostname'];
$dbu = $_INFO['username'];
$dbp = $_INFO['password'];
$dbn = $_INFO['dbname'];
   $db = mysqli_connect($dbh, $dbu, $dbp,$dbn);
   if(! $db ) {
      die('Could not connect: ' . mysqli_error());
   }
$db->query("ALTER TABLE multics_licence MODIFY licence_key tinytext");
$date = time();
$sqla = "ALTER TABLE `multics_master_servers` ADD `oscam_readers` text AFTER `monitor_enabled`";
$db->query($sqla);
$sqla = "ALTER TABLE `multics_master_servers` ADD `server_type` varchar(50) DEFAULT NULL AFTER `oscam_readers`";
$resa = $db->query($sqla);
$sqlab = "ALTER TABLE `server_news` ADD `member_groups` int(11) DEFAULT NULL AFTER `date`";
$resa = $db->query($sqlab);
$sql="SELECT * FROM `cronjobs` where filename = 'status_cronjob.php'";
  $result = $db->query($sql);
  if (($result) && ($result->num_rows >= 1))
    { } else {
$sqlc = "INSERT INTO `cronjobs` (`id`, `description`, `filename`, `run_per_mins`, `enabled`) VALUES
(6, 'Oscam Config Update', 'oscam_cronjob.php', 1, 1)";
	$resc = $db->query($sqlc); }
$sql="SELECT * FROM `cronjobs` where filename = 'status_cronjob.php'";
  $result = $db->query($sql);
  if (($result) && ($result->num_rows >= 1))
    { } else {
$sqlca = "INSERT INTO `cronjobs` (`id`, `description`, `filename`, `run_per_mins`, `enabled`) VALUES
(7, 'Oscam Line Status Update', 'status_cronjob.php', 1, 1)";
	$resca = $db->query($sqlca); }
$sql="SELECT * FROM `member_groups` where group_name = 'Pro-Reseller'";
  $result = $db->query($sql);
  if (($result) && ($result->num_rows >= 1))
    { } else {
{$sql = "INSERT INTO `member_groups` (`group_name`, `group_color`, `is_banned`, `is_admin`, `percent_discount`, `total_testlines`, `can_delete`) VALUES
('Pro-Reseller', '#d46000', 0, 0, 0, 50, 0),
('Sub-Reseller', '#049312', 0, 0, 0, 10, 0)"; }
$res = $db->query($sql);
$sqld = "UPDATE `cronjobs` SET `enabled` = '0' WHERE `id` = '1'";
$resd = $db->query($sqld);}
$sqlr = "ALTER TABLE `users` ADD `ref_id` tinytext AFTER `lang_id`";
$resr = $db->query($sqlr);
$sqlrb = "ALTER TABLE `users` ADD `iptv_balance` tinytext AFTER `ref_id`";
$resr = $db->query($sqlrb);
$sqlr = "ALTER TABLE `member_groups` ADD `iptv` tinytext AFTER `can_delete`";
$resr = $db->query($sqlr);
$sqlra = "ALTER TABLE `lines_options` ADD `oscam_val` text DEFAULT null AFTER `monitor_exclude`";
$resra = $db->query($sqlra);
$sqlra = "ALTER TABLE `lines` ADD `oscam_val` text DEFAULT null AFTER `notes`";
$resra = $db->query($sqlra);
if($resa)
{echo "Database Updated";}
else
{echo "Database Already Updated";}
?>