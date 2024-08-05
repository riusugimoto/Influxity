<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$username = "ora_nanrey";
$password = "a22597249";
$connection_string = "dbhost.students.cs.ubc.ca:1522/stu";

// Connect to Oracle database
$conn = oci_connect($username, $password, $connection_string);

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
    echo 'Error deleting data request: ' . htmlentities($e['message'], ENT_QUOTES);
}

if (!oci_commit($conn)) {
    $e = oci_error($conn);
    echo "Error committing transaction: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}
oci_close($conn);
?>