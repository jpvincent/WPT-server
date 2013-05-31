<?php
  require("login/login.php");
  include 'monitor.inc';
  include 'db_utils.inc';
  $id = $_REQUEST['id'];
  displayErrorIfNotAdmin();

  deleteRecord("wptjob_wptlocation","wptlocationid",$id);
  deleteRecord("WPTLocation","Id",$id);

  header("Location: listLocations.php");
?>