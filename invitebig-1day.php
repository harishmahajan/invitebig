#!/usr/bin/php -q
<?php
//script to test cron on adam's cloud server:

//require_once('/var/www/myinvitebig.com/cal_cron_sync.php');
//require_once('/var/www/invitebig/cron/invitebig-1day.sh');
exec "/var/www/invitebig/cron/invitebig-1day";
?>

