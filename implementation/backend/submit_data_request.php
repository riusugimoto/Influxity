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

session_start();
$company_id = $_SESSION['company_id']; 
$dataPurpose = $_POST['dataPurpose'];
$compensation = $_POST['compensation'];
$categoryID = $_POST['categoryID'];
$status = 'Pending';

// had to set this just to double check valid session :/
if (!$company_id) {
    echo "Company ID is not set. Please log in again.";
    exit;
}

$query = "INSERT INTO DataRequest (DataRequestID, CompanyID, DataPurpose, Compensation, CategoryID, Status) VALUES (DataRequest_seq.NEXTVAL, :company_id, :dataPurpose, :compensation, :categoryID, :status)";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":company_id", $company_id);
oci_bind_by_name($stid, ":dataPurpose", $dataPurpose);
oci_bind_by_name($stid, ":compensation", $compensation);
oci_bind_by_name($stid, ":categoryID", $categoryID);
oci_bind_by_name($stid, ":status", $status);

if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo "Error inserting into DataRequest table: " . htmlentities($e['message'], ENT_QUOTES);
} else {
    echo "Data request submitted successfully!";
}

oci_free_statement($stid);

if (!oci_commit($conn)) {
    $e = oci_error($conn);
    echo "Error committing transaction: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

oci_close($conn);
?>
