<?php
// Author    : @MonokaiJs
// Facebook  : https://fb.me/MonokaiJsp
// Home      : https://nstudio.pw

session_start();
require 'vendor/autoload.php';
//					host					usename				pass		database
$conn = new mysqli("den1.mysql6.gear.host", "datadrivevn", "Mp2l~6Q0-fiz	", "datadrivevn"); //MySQL Connection.

function getSettings($sett_name) {
  global $conn;
  $check = $conn->query("SELECT * FROM `settings` WHERE `name` = '$sett_name'");
  if ($check->num_rows == 1) {
    return ($check->fetch_assoc()['value']);
  } else {
    return false;
  }
}
function setSettings($sett_name, $sett_value) {
  global $conn;
  return $conn->query("INSERT INTO `settings` (`name`, `value`) VALUES ('$sett_name', '$sett_value') ON DUPLICATE KEY UPDATE `value` = '$sett_value'");
}
?>