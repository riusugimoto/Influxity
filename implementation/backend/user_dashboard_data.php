<?php
// Enable error reporting
include "db_auth.php";
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to Oracle database
$conn = db_log_in();

//there is prob a better way of doing this, but idk what that way is.. so here we are.
header('Content-Type: application/json');

if (!$conn) {
    $e = oci_error();
    echo "Connection failed: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

session_start();
if (!isset($_SESSION['userID'])) {
    echo "userID not found: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

$userID = $_SESSION['userID'];

// Fetch companies requesting data
$query = "SELECT dr.*, c.Name AS CompanyName, dc.CategoryName 
          FROM DataRequest dr 
          JOIN Company c ON dr.CompanyID = c.CompanyID 
          JOIN DataCategory dc ON dr.CategoryID = dc.CategoryID 
          WHERE dr.Status = 'Pending'";
$stid = oci_parse($conn, $query);
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo json_encode(['error' => "Error fetching companies requesting data: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

$companies_requesting_data = [];
while ($row = oci_fetch_assoc($stid)) {
    $companies_requesting_data[] = $row;
}
oci_free_statement($stid);

// Fetch granted data history
$query = "SELECT tuu.TransactionID, tuu.UserID, dr.DataPurpose 
          FROM TransactionIDUserID tuu 
          JOIN TransactionIDDataRequestID tddr ON tuu.TransactionID = tddr.TransactionID 
          JOIN DataRequest dr ON tddr.DataRequestID = dr.DataRequestID 
          WHERE tuu.UserID = :userID";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":userID", $userID);
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo json_encode(['error' => "Error fetching granted data history: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

$granted_data_history = [];
while ($row = oci_fetch_assoc($stid)) {
    $granted_data_history[] = $row;
}
oci_free_statement($stid);

// Fetch compensation history
$query = "SELECT u1.*, u2.Currency, u3.Timestamp 
          FROM TransactionIDAmount u1 
          JOIN TransactionIDCurrency u2 ON u1.TransactionID = u2.TransactionID 
          JOIN TransactionIDTimestamp u3 ON u1.TransactionID = u3.TransactionID 
          WHERE u1.TransactionID IN (SELECT TransactionID FROM TransactionIDUserID WHERE UserID = :userID)";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":userID", $userID);
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo "failed to load compensation history: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

$compensation_history = [];
while ($row = oci_fetch_assoc($stid)) {
    $compensation_history[] = $row;
}
oci_free_statement($stid);

$query = "SELECT tr.*, rgo.GeneratedOn 
          FROM TransparencyReport tr 
          JOIN ReportGeneratedOn rgo ON tr.ReportID = rgo.ReportID 
          WHERE tr.UserID = :userID";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":userID", $userID);
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo "failed to load report history: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

$transparency_reports = [];
while ($row = oci_fetch_assoc($stid)) {
    $transparency_reports[] = $row;
}
oci_free_statement($stid);


$query = "SELECT 
          tuu.TransactionID, 
          c.Name AS CompanyName, 
          dr.DataPurpose,
          r.Status,
          r.Compensation
          FROM TransactionIDUserID tuu 
          JOIN TransactionIDDataRequestID tddr ON tuu.TransactionID = tddr.TransactionID 
          JOIN DataRequest dr ON tddr.DataRequestID = dr.DataRequestID
          JOIN Company c ON dr.CompanyID = c.CompanyID
          LEFT JOIN Review r ON tuu.TransactionID = r.TransactionID
          WHERE tuu.UserID = :userID";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":userID", $userID);
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo json_encode(['error' => "Error fetching submitted data history: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

$submitted_data_history = [];
while ($row = oci_fetch_assoc($stid)) {
    $submitted_data_history[] = $row;
}
oci_free_statement($stid);

oci_close($conn);

echo json_encode([
    'companies_requesting_data' => $companies_requesting_data,
    'granted_data_history' => $granted_data_history,
    'compensation_history' => $compensation_history,
    'submitted_data_history' => $submitted_data_history,
    'transparency_reports' => $transparency_reports
]);
?>
