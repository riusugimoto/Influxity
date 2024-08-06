<?php
// Enable error reporting
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
    echo "Connection failed: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Query to find companies that requested data covering all categories
$query = "SELECT c.Name AS CompanyName
          FROM Company c
          JOIN DataRequest dr ON c.CompanyID = dr.CompanyID
          GROUP BY c.CompanyID, c.Name
          HAVING COUNT(DISTINCT dr.CategoryID) = (SELECT COUNT(*) FROM DataCategory)";

$stid = oci_parse($conn, $query);
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo "Query failed: " . htmlentities($e['message'], ENT_QUOTES);
    oci_free_statement($stid);
    oci_close($conn);
    exit;
}

$rows = [];
while ($row = oci_fetch_assoc($stid)) {
    $rows[] = $row;
}

oci_free_statement($stid);
oci_close($conn);

echo json_encode($rows);
?>
