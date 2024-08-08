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
    echo json_encode(['error' => "Connection failed: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}
$dataRequestId = $_POST['dataRequestId'];

$query = "DELETE FROM DataRequest WHERE DataRequestID = :dataRequestId";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":dataRequestId", $dataRequestId);

if (oci_execute($stid)) {
    echo 'Data request deleted successfully';
} else {
    $e = oci_error($stid);
    echo json_encode(['error' => "Error fetching submitted data history: " . htmlentities($e['message'], ENT_QUOTES)]);
}

if (!oci_commit($conn)) {
    $e = oci_error($conn);
    echo "Error committing transaction: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}
oci_close($conn);
?>