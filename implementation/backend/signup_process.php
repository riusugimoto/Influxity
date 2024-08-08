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

$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];
$userType = $_POST['userType']; 

// Hashing is somewhat annoying when we have to deal with populating users using our script.
// For the sake of the demo, we can remove password hashing.
// $hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $query = "SELECT Email FROM Users WHERE Email = :email";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":email", $email);
    oci_execute($stid);

    if (oci_fetch($stid)) {
        echo "Email already exists. Please use a different email.";
        oci_free_statement($stid);
        oci_close($conn);
        exit;
    }
    oci_free_statement($stid);

    $query = "INSERT INTO Users (UserID, Email) VALUES (User_seq.NEXTVAL, :email)";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":email", $email);
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Error inserting into Users table: " . htmlentities($e['message'], ENT_QUOTES));
    }
    oci_free_statement($stid);

    $query = "SELECT User_seq.CURRVAL AS UserID FROM dual";
    $stid = oci_parse($conn, $query);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);
    $userID = $row['USERID'];
    oci_free_statement($stid);

    $query = "INSERT INTO UserEmailUsername (Email, Username) VALUES (:email, :username)";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":email", $email);
    oci_bind_by_name($stid, ":username", $username);
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Error inserting into UserEmailUsername table: " . htmlentities($e['message'], ENT_QUOTES));
    }
    oci_free_statement($stid);

    $query = "INSERT INTO UserEmailPassword (Email, Password) VALUES (:email, :password)";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":email", $email);
    oci_bind_by_name($stid, ":password", $password);
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Error inserting into UserEmailPassword table: " . htmlentities($e['message'], ENT_QUOTES));
    }
    oci_free_statement($stid);

    // User type check. 
    if ($userType === 'individual') {
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $dob = $_POST['dob'];

        $query = "INSERT INTO IndividualUser (UserID, FirstName, LastName, DateOfBirth) VALUES (:userID, :firstName, :lastName, TO_DATE(:dob, 'YYYY-MM-DD'))";
        $stid = oci_parse($conn, $query);
        oci_bind_by_name($stid, ":userID", $userID);
        oci_bind_by_name($stid, ":firstName", $firstName);
        oci_bind_by_name($stid, ":lastName", $lastName);
        oci_bind_by_name($stid, ":dob", $dob);
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Error inserting into IndividualUser table: " . htmlentities($e['message'], ENT_QUOTES));
        }
        oci_free_statement($stid);

        $query = "INSERT INTO IndividualUserName (FirstName, LastName, DateOfBirth, UserID) VALUES (:firstName, :lastName, TO_DATE(:dob, 'YYYY-MM-DD'), :userID)";
        $stid = oci_parse($conn, $query);
        oci_bind_by_name($stid, ":firstName", $firstName);
        oci_bind_by_name($stid, ":lastName", $lastName);
        oci_bind_by_name($stid, ":dob", $dob);
        oci_bind_by_name($stid, ":userID", $userID);
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Error inserting into IndividualUserName table: " . htmlentities($e['message'], ENT_QUOTES));
        }
        oci_free_statement($stid);
    } elseif ($userType === 'corporate') {
        $companyName = $_POST['companyName'];
        $industry = $_POST['industry'];
        $companySize = $_POST['companySize'];

        $query = "INSERT INTO CorporateUser (UserID, CompanyName) VALUES (:userID, :companyName)";
        $stid = oci_parse($conn, $query);
        oci_bind_by_name($stid, ":userID", $userID);
        oci_bind_by_name($stid, ":companyName", $companyName);
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Error inserting into CorporateUser table: " . htmlentities($e['message'], ENT_QUOTES));
        }
        oci_free_statement($stid);

        $query = "INSERT INTO CompanyNameIndustry (CompanyName, Industry) VALUES (:companyName, :industry)";
        $stid = oci_parse($conn, $query);
        oci_bind_by_name($stid, ":companyName", $companyName);
        oci_bind_by_name($stid, ":industry", $industry);
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Error inserting into CompanyNameIndustry table: " . htmlentities($e['message'], ENT_QUOTES));
        }
        oci_free_statement($stid);

        $query = "INSERT INTO CompanyNameSize (CompanyName, CompanySize) VALUES (:companyName, :companySize)";
        $stid = oci_parse($conn, $query);
        oci_bind_by_name($stid, ":companyName", $companyName);
        oci_bind_by_name($stid, ":companySize", $companySize);
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Error inserting into CompanyNameSize table: " . htmlentities($e['message'], ENT_QUOTES));
        }
        oci_free_statement($stid);

        $query = "INSERT INTO Company (CompanyID, Name, Industry, ContactInfo) VALUES (:userID, :companyName, :industry, :email)";
        $stid = oci_parse($conn, $query);
        oci_bind_by_name($stid, ":userID", $userID);
        oci_bind_by_name($stid, ":companyName", $companyName);
        oci_bind_by_name($stid, ":industry", $industry);
        oci_bind_by_name($stid, ":email", $email);
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Error inserting into CompanyNameSize table: " . htmlentities($e['message'], ENT_QUOTES));
        }
        oci_free_statement($stid);
    }

    if (!oci_commit($conn)) {
        $e = oci_error($conn);
        throw new Exception("Error committing transaction: " . htmlentities($e['message'], ENT_QUOTES));
    }

    echo "Sign up successful!";
} catch (Exception $e) {
    oci_rollback($conn);
    echo "Sign up failed: " . $e->getMessage();
}

oci_close($conn);
?>
