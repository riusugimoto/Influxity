
<?php
// Database connection details
$username = "ora_nanrey";
$password = "a22597249";
$connection_string = "dbhost.students.cs.ubc.ca:1522/stu";


$conn = oci_connect($username, $password, $connection_string);

if (!$conn) {
    $e = oci_error();
    echo "Connection failed: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Query to find users who have provided data for all categories
$query = "SELECT UserID
            FROM Users u
            WHERE NOT EXISTS (
                SELECT dc.CategoryID
                FROM DataCategory dc
                EXCEPT
                SELECT dr.CategoryID
                FROM DataRequest dr
                JOIN TransactionIDDataRequestID tdr ON dr.DataRequestID = tdr.DataRequestID
                JOIN TransactionIDUserID tuu ON tdr.TransactionID = tuu.TransactionID
                WHERE tuu.UserID = u.UserID
            )";




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
