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

session_start();

$email = $_POST['email'];
$password = $_POST['password']; // For the sake of the demo, we removed hash checking and just check the password row. password_verify($password, $row['PASSWORD'])

$query = "SELECT * FROM Users u
          JOIN UserEmailPassword up ON u.Email = up.Email
          JOIN UserEmailUsername uu ON u.Email = uu.Email
          WHERE u.Email = :email";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":email", $email);
oci_execute($stid);

if ($row = oci_fetch_assoc($stid)) {
    if ($password == $row['PASSWORD']) {
        // Set session variables
        $_SESSION['userID'] = $row['USERID'];
        $_SESSION['email'] = $row['EMAIL'];
        $_SESSION['username'] = $row['USERNAME'];

        // Check if the user is an individual user
        $userTypeQuery = "SELECT * FROM IndividualUser WHERE UserID = :userID";
        $userTypeStid = oci_parse($conn, $userTypeQuery);
        oci_bind_by_name($userTypeStid, ":userID", $row['USERID']);
        oci_execute($userTypeStid);
        if ($individualUser = oci_fetch_assoc($userTypeStid)) {
            $_SESSION['userType'] = 'individual';
            oci_free_statement($userTypeStid);
            oci_close($conn);
            header("Location: ../frontend/user_dashboard.html");
            exit;
        }

        // Check if the user is a corporate user and retrieve the company id from the Company table
        $userTypeQuery = "SELECT c.CompanyID, c.Name AS CompanyName 
                          FROM CorporateUser cu
                          JOIN Company c ON cu.CompanyName = c.Name
                          WHERE cu.UserID = :userID";
        $userTypeStid = oci_parse($conn, $userTypeQuery);
        oci_bind_by_name($userTypeStid, ":userID", $row['USERID']);
        oci_execute($userTypeStid);
        if ($corporateUser = oci_fetch_assoc($userTypeStid)) {
            $_SESSION['company_name'] = $corporateUser['COMPANYNAME'];
            $_SESSION['company_id'] = $corporateUser['COMPANYID'];
            oci_free_statement($userTypeStid);
            oci_close($conn);
            header("Location: ../frontend/company_dashboard.html");
           // exit;
        }
    } else {
        echo "Invalid password";
    }
} else {
    echo "No user found with this email";
}

oci_free_statement($stid);
oci_close($conn);
?>
