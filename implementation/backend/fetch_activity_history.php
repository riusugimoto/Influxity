<?php
include "db_auth.php";
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Connect to Oracle database
$conn = db_log_in();

if (!$conn) {
    $e = oci_error();
    echo "Connection failed: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Query to fetch activity history
$query = "SELECT a.ActivityID, a.UserID, at.Timestamp, uat.ActivityType, uad.ActivityDetails
          FROM Activity a
          JOIN ActivityTimestamp at ON a.ActivityID = at.ActivityID
          JOIN UserActivityType uat ON a.UserID = uat.UserID AND at.Timestamp = uat.Timestamp
          JOIN UserActivityDetails uad ON a.UserID = uad.UserID AND at.Timestamp = uad.Timestamp";

$stid = oci_parse($conn, $query);
oci_execute($stid);

$rows = [];
while ($row = oci_fetch_assoc($stid)) {
    $rows[] = $row;
}

oci_free_statement($stid);
oci_close($conn);

echo json_encode($rows);
?>
