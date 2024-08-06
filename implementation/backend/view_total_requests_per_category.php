<?php
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


$query = "SELECT CategoryID, COUNT(*) AS TotalRequests
          FROM DataRequest
          GROUP BY CategoryID";



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
