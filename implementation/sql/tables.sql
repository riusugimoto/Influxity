-- Begin the PL/SQL block to drop tables
BEGIN
    FOR t IN (SELECT table_name FROM user_tables WHERE table_name IN (
        'CURRENCYEXCHANGERATE', 'TRANSACTIONIDEXCHANGERATE', 'TRANSACTIONIDCURRENCY',
        'TRANSACTIONIDTIMESTAMP', 'TRANSACTIONIDAMOUNT', 'TRANSACTIONIDDATAREQUESTID',
        'TRANSACTIONIDUSERID', 'DATABELONGCATEGORY', 'DATACATEGORY', 'DATAREQUEST',
        'COMPANY', 'USERGENERATEDREPORTDETAILS', 'REPORTGENERATEDON', 'TRANSPARENCYREPORT',
        'USERACTIVITYDETAILS', 'USERACTIVITYTYPE', 'ACTIVITYTIMESTAMP', 'ACTIVITY',
        'COMPANYNAMESIZE', 'COMPANYNAMEINDUSTRY', 'CORPORATEUSER', 'INDIVIDUALUSERNAME',
        'INDIVIDUALUSER', 'USEREMAILPASSWORD', 'USEREMAILUSERNAME', 'USERS', 'REVIEW'
    )) LOOP
        EXECUTE IMMEDIATE 'DROP TABLE ' || t.table_name || ' CASCADE CONSTRAINTS';
    END LOOP;
END;
/

-- Drop sequences if they exist
BEGIN
    EXECUTE IMMEDIATE 'DROP SEQUENCE DataRequest_seq';
EXCEPTION
    WHEN OTHERS THEN
        NULL; -- Ignore if the sequence does not exist
END;
/

BEGIN
    EXECUTE IMMEDIATE 'DROP SEQUENCE User_seq';
EXCEPTION
    WHEN OTHERS THEN
        NULL; -- Ignore if the sequence does not exist
END;
/

BEGIN
    EXECUTE IMMEDIATE 'DROP SEQUENCE TransactionID_seq';
EXCEPTION
    WHEN OTHERS THEN
        NULL; -- Ignore if the sequence does not exist
END;
/


BEGIN
    EXECUTE IMMEDIATE 'DROP SEQUENCE Review_seq';
EXCEPTION
    WHEN OTHERS THEN
        NULL; -- Ignore if the sequence does not exist
END;
/

-- User Table
CREATE TABLE Users (
    UserID INTEGER PRIMARY KEY,
    Email VARCHAR(100) NOT NULL UNIQUE
);

-- UserEmailUsername Table
CREATE TABLE UserEmailUsername (
    Email VARCHAR(255) PRIMARY KEY,
    Username VARCHAR(255) NOT NULL,
    FOREIGN KEY (Email) REFERENCES Users(Email) ON DELETE CASCADE
);

-- UserEmailPassword Table
CREATE TABLE UserEmailPassword (
    Email VARCHAR(255) PRIMARY KEY,
    Password VARCHAR(255) NOT NULL,
    FOREIGN KEY (Email) REFERENCES Users(Email) ON DELETE CASCADE
);

-- IndividualUser Table
CREATE TABLE IndividualUser (
    UserID INTEGER PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    DateOfBirth DATE NOT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- IndividualUserName Table
CREATE TABLE IndividualUserName (
    FirstName VARCHAR(50),
    LastName VARCHAR(50),
    DateOfBirth DATE,
    UserID INTEGER,
    PRIMARY KEY (FirstName, LastName, DateOfBirth),
    FOREIGN KEY (UserID) REFERENCES IndividualUser(UserID) ON DELETE CASCADE
);

-- CorporateUser Table
CREATE TABLE CorporateUser (
    UserID INTEGER PRIMARY KEY,
    CompanyName VARCHAR(100) NOT NULL UNIQUE,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- CompanyNameIndustry Table
CREATE TABLE CompanyNameIndustry (
    CompanyName VARCHAR(100) NOT NULL,
    Industry VARCHAR(50) NOT NULL,
    FOREIGN KEY (CompanyName) REFERENCES CorporateUser(CompanyName) ON DELETE CASCADE
);

-- CompanyNameSize Table
CREATE TABLE CompanyNameSize (
    CompanyName VARCHAR(100) PRIMARY KEY,
    CompanySize VARCHAR(20) NOT NULL,
    FOREIGN KEY (CompanyName) REFERENCES CorporateUser(CompanyName) ON DELETE CASCADE
);

-- Activity Table
CREATE TABLE Activity (
    ActivityID INTEGER PRIMARY KEY,
    UserID INTEGER NOT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- ActivityTimestamp Table
CREATE TABLE ActivityTimestamp (
    ActivityID INTEGER PRIMARY KEY,
    Timestamp TIMESTAMP NOT NULL,
    FOREIGN KEY (ActivityID) REFERENCES Activity(ActivityID) ON DELETE CASCADE
);

-- UserActivityType Table
CREATE TABLE UserActivityType (
    UserID INTEGER,
    Timestamp TIMESTAMP,
    ActivityType VARCHAR(50) NOT NULL,
    PRIMARY KEY (UserID, Timestamp),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- UserActivityDetails Table
CREATE TABLE UserActivityDetails (
    UserID INTEGER,
    Timestamp TIMESTAMP,
    ActivityDetails CLOB,
    PRIMARY KEY (UserID, Timestamp),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- TransparencyReport Table
CREATE TABLE TransparencyReport (
    ReportID INTEGER PRIMARY KEY,
    UserID INTEGER NOT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- ReportGeneratedOn Table
CREATE TABLE ReportGeneratedOn (
    ReportID INTEGER PRIMARY KEY,
    GeneratedOn TIMESTAMP NOT NULL,
    FOREIGN KEY (ReportID) REFERENCES TransparencyReport(ReportID) ON DELETE CASCADE
);

-- UserGeneratedReportDetails Table
CREATE TABLE UserGeneratedReportDetails (
    UserID INTEGER,
    GeneratedOn TIMESTAMP,
    ReportDetails CLOB,
    PRIMARY KEY (UserID, GeneratedOn),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- Company Table
CREATE TABLE Company (
    CompanyID INT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL UNIQUE,
    Industry VARCHAR(255) NOT NULL,
    ContactInfo VARCHAR(255) NOT NULL,
    FOREIGN KEY (Name) REFERENCES CorporateUser(CompanyName) ON DELETE CASCADE
);

-- DataCategory Table
CREATE TABLE DataCategory (
    CategoryID INT PRIMARY KEY,
    CategoryName VARCHAR(255) NOT NULL UNIQUE,
    Description CLOB NOT NULL
);

-- DataRequest Table
CREATE TABLE DataRequest (
    DataRequestID INT PRIMARY KEY,
    CompanyID INT,
    Compensation DECIMAL(10, 2) NOT NULL,
    DataPurpose VARCHAR(255) NOT NULL, 
    CategoryID INT NOT NULL,
    Status VARCHAR(50) NOT NULL,
    FOREIGN KEY (CompanyID) REFERENCES Company(CompanyID) ON DELETE CASCADE,
    FOREIGN KEY (CategoryID) REFERENCES DataCategory(CategoryID) ON DELETE CASCADE
);

-- DataBelongCategory Table
CREATE TABLE DataBelongCategory (
    CategoryID INT NOT NULL,
    DataRequestID INT,
    CompanyID INT,
    PRIMARY KEY (CategoryID, DataRequestID),
    FOREIGN KEY (CategoryID) REFERENCES DataCategory(CategoryID) ON DELETE CASCADE,
    FOREIGN KEY (DataRequestID) REFERENCES DataRequest(DataRequestID) ON DELETE CASCADE,
    FOREIGN KEY (CompanyID) REFERENCES Company(CompanyID) ON DELETE SET NULL
);

-- TransactionIDUserID Table
CREATE TABLE TransactionIDUserID (
    TransactionID INT NOT NULL,
    UserID INT NOT NULL,
    DataText VARCHAR(255) NOT NULL,
    PRIMARY KEY (TransactionID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
    -- removed UNIQUE constraint to allow multiple transactions for the same user as long as DataText is different.
);

-- TransactionIDDataRequestID Table
CREATE TABLE TransactionIDDataRequestID (
    TransactionID INTEGER NOT NULL,
    DataRequestID INTEGER,
    PRIMARY KEY (TransactionID),
    FOREIGN KEY (DataRequestID) REFERENCES DataRequest(DataRequestID) ON DELETE CASCADE
);

-- TransactionIDAmount Table
CREATE TABLE TransactionIDAmount (
    TransactionID INTEGER NOT NULL,
    Amount INTEGER NOT NULL,
    PRIMARY KEY (TransactionID),
    FOREIGN KEY (TransactionID) REFERENCES TransactionIDUserID(TransactionID)
);

-- TransactionIDTimestamp Table
CREATE TABLE TransactionIDTimestamp (
    TransactionID INTEGER NOT NULL,
    Timestamp DATE NOT NULL,
    PRIMARY KEY (TransactionID),
    FOREIGN KEY (TransactionID) REFERENCES TransactionIDUserID(TransactionID)
);

-- TransactionIDCurrency Table
CREATE TABLE TransactionIDCurrency (
    TransactionID INTEGER NOT NULL,
    Currency CHAR(3) NOT NULL,
    PRIMARY KEY (TransactionID),
    FOREIGN KEY (TransactionID) REFERENCES TransactionIDUserID(TransactionID)
);

-- TransactionIDExchangeRate Table
CREATE TABLE TransactionIDExchangeRate (
    TransactionID INTEGER NOT NULL,
    ExchangeRate INTEGER NOT NULL,
    PRIMARY KEY (TransactionID),
    FOREIGN KEY (TransactionID) REFERENCES TransactionIDUserID(TransactionID)
);

-- CurrencyExchangeRate Table
CREATE TABLE CurrencyExchangeRate (
    Currency CHAR(3) NOT NULL PRIMARY KEY,
    ExchangeRate INTEGER NOT NULL
);

-- New table
CREATE TABLE Review (
    ReviewID INT PRIMARY KEY,
    TransactionID INT NOT NULL,
    UserID INT NOT NULL,
    Status VARCHAR(50) NOT NULL,  -- Accepted, Rejected
    Compensation DECIMAL(10, 2) NOT NULL, -- Compensation offered (if applicable)
    ReviewTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (TransactionID) REFERENCES TransactionIDUserID(TransactionID),
    FOREIGN KEY (UserID) REFERENCES Company(CompanyID) ON DELETE CASCADE,
    UNIQUE (TransactionID, UserID) -- Ensures a unique review per user per transaction
);

CREATE SEQUENCE Review_seq
    START WITH 1 --new table
    INCREMENT BY 1
    NOCACHE;

CREATE SEQUENCE DataRequest_seq
    START WITH 6 --default starts with 6 since we have 5 already
    INCREMENT BY 1
    NOCACHE;

CREATE SEQUENCE User_seq
    START WITH 10 --default starts with 6 since we have 5 already
    INCREMENT BY 1
    NOCACHE;

CREATE SEQUENCE TransactionID_seq
    START WITH 6 --default starts with 6 since we have 5 already
    INCREMENT BY 1
    NOCACHE;

--Table population process -- added new default data to amke user UserID's were not duplicated.
-----------------------------------------------------------------------
-- Users table
INSERT INTO Users (UserID, Email) VALUES (1, 'alice@example.com');
INSERT INTO Users (UserID, Email) VALUES (2, 'bob@example.com');
INSERT INTO Users (UserID, Email) VALUES (3, 'carol@example.com');
INSERT INTO Users (UserID, Email) VALUES (4, 'dave@example.com');
INSERT INTO Users (UserID, Email) VALUES (5, 'eve@example.com');
INSERT INTO Users (UserID, Email) VALUES (6, 'john@techcorp.com');
INSERT INTO Users (UserID, Email) VALUES (7, 'mary@healthinc.com');
INSERT INTO Users (UserID, Email) VALUES (8, 'peter@edusoft.com');
INSERT INTO Users (UserID, Email) VALUES (9, 'lucas@tech.com');
INSERT INTO Users (UserID, Email) VALUES (10, 'sophia@biolab.com');

-- UserEmailUsername table
INSERT INTO UserEmailUsername (Email, Username) VALUES ('alice@example.com', 'alice123');
INSERT INTO UserEmailUsername (Email, Username) VALUES ('bob@example.com', 'bobby');
INSERT INTO UserEmailUsername (Email, Username) VALUES ('carol@example.com', 'carol_w');
INSERT INTO UserEmailUsername (Email, Username) VALUES ('dave@example.com', 'davey');
INSERT INTO UserEmailUsername (Email, Username) VALUES ('eve@example.com', 'eve_93');
INSERT INTO UserEmailUsername (Email, Username) VALUES ('john@techcorp.com', 'john_techcorp');
INSERT INTO UserEmailUsername (Email, Username) VALUES ('mary@healthinc.com', 'mary_health');
INSERT INTO UserEmailUsername (Email, Username) VALUES ('peter@edusoft.com', 'peter_edu');
INSERT INTO UserEmailUsername (Email, Username) VALUES ('lucas@tech.com', 'lucas_tech');
INSERT INTO UserEmailUsername (Email, Username) VALUES ('sophia@biolab.com', 'sophia_bio');

-- UserEmailPassword table
INSERT INTO UserEmailPassword (Email, Password) VALUES ('alice@example.com', 'password1');
INSERT INTO UserEmailPassword (Email, Password) VALUES ('bob@example.com', 'password2');
INSERT INTO UserEmailPassword (Email, Password) VALUES ('carol@example.com', 'password3');
INSERT INTO UserEmailPassword (Email, Password) VALUES ('dave@example.com', 'password4');
INSERT INTO UserEmailPassword (Email, Password) VALUES ('eve@example.com', 'password5');
INSERT INTO UserEmailPassword (Email, Password) VALUES ('john@techcorp.com', 'techpass1');
INSERT INTO UserEmailPassword (Email, Password) VALUES ('mary@healthinc.com', 'healthpass2');
INSERT INTO UserEmailPassword (Email, Password) VALUES ('peter@edusoft.com', 'edupass3');
INSERT INTO UserEmailPassword (Email, Password) VALUES ('lucas@tech.com', 'techpass4');
INSERT INTO UserEmailPassword (Email, Password) VALUES ('sophia@biolab.com', 'biopass5');

-- IndividualUser table
INSERT INTO IndividualUser (UserID, FirstName, LastName, DateOfBirth) VALUES (1, 'Alice', 'Smith', TO_DATE('1990-01-01', 'YYYY-MM-DD'));
INSERT INTO IndividualUser (UserID, FirstName, LastName, DateOfBirth) VALUES (2, 'Bob', 'Johnson', TO_DATE('1985-02-02', 'YYYY-MM-DD'));
INSERT INTO IndividualUser (UserID, FirstName, LastName, DateOfBirth) VALUES (3, 'Carol', 'Williams', TO_DATE('1992-03-03', 'YYYY-MM-DD'));
INSERT INTO IndividualUser (UserID, FirstName, LastName, DateOfBirth) VALUES (4, 'Dave', 'Brown', TO_DATE('1988-04-04', 'YYYY-MM-DD'));
INSERT INTO IndividualUser (UserID, FirstName, LastName, DateOfBirth) VALUES (5, 'Eve', 'Jones', TO_DATE('1995-05-05', 'YYYY-MM-DD'));

-- IndividualUserName table
INSERT INTO IndividualUserName (FirstName, LastName, DateOfBirth, UserID) VALUES ('Alice', 'Smith', TO_DATE('1990-01-01', 'YYYY-MM-DD'), 1);
INSERT INTO IndividualUserName (FirstName, LastName, DateOfBirth, UserID) VALUES ('Bob', 'Johnson', TO_DATE('1985-02-02', 'YYYY-MM-DD'), 2);
INSERT INTO IndividualUserName (FirstName, LastName, DateOfBirth, UserID) VALUES ('Carol', 'Williams', TO_DATE('1992-03-03', 'YYYY-MM-DD'), 3);
INSERT INTO IndividualUserName (FirstName, LastName, DateOfBirth, UserID) VALUES ('Dave', 'Brown', TO_DATE('1988-04-04', 'YYYY-MM-DD'), 4);
INSERT INTO IndividualUserName (FirstName, LastName, DateOfBirth, UserID) VALUES ('Eve', 'Jones', TO_DATE('1995-05-05', 'YYYY-MM-DD'), 5);

-- CorporateUser table
INSERT INTO CorporateUser (UserID, CompanyName) VALUES (6, 'TechCorp');
INSERT INTO CorporateUser (UserID, CompanyName) VALUES (7, 'HealthInc');
INSERT INTO CorporateUser (UserID, CompanyName) VALUES (8, 'EduSoft');
INSERT INTO CorporateUser (UserID, CompanyName) VALUES (9, 'Tech Solutions');
INSERT INTO CorporateUser (UserID, CompanyName) VALUES (10, 'BioLab');

-- CompanyNameIndustry table
INSERT INTO CompanyNameIndustry (CompanyName, Industry) VALUES ('TechCorp', 'Technology');
INSERT INTO CompanyNameIndustry (CompanyName, Industry) VALUES ('HealthInc', 'Healthcare');
INSERT INTO CompanyNameIndustry (CompanyName, Industry) VALUES ('EduSoft', 'Education');
INSERT INTO CompanyNameIndustry (CompanyName, Industry) VALUES ('Tech Solutions', 'Finance');
INSERT INTO CompanyNameIndustry (CompanyName, Industry) VALUES ('BioLab', 'Biotechnology');

-- CompanyNameSize table
INSERT INTO CompanyNameSize (CompanyName, CompanySize) VALUES ('TechCorp', 'Large');
INSERT INTO CompanyNameSize (CompanyName, CompanySize) VALUES ('HealthInc', 'Medium');
INSERT INTO CompanyNameSize (CompanyName, CompanySize) VALUES ('EduSoft', 'Small');
INSERT INTO CompanyNameSize (CompanyName, CompanySize) VALUES ('Tech Solutions', 'Large');
INSERT INTO CompanyNameSize (CompanyName, CompanySize) VALUES ('BioLab', 'Medium');

-- Activity table
INSERT INTO Activity (ActivityID, UserID) VALUES (1, 1);
INSERT INTO Activity (ActivityID, UserID) VALUES (2, 2);
INSERT INTO Activity (ActivityID, UserID) VALUES (3, 3);
INSERT INTO Activity (ActivityID, UserID) VALUES (4, 4);
INSERT INTO Activity (ActivityID, UserID) VALUES (5, 5);
INSERT INTO Activity (ActivityID, UserID) VALUES (6, 6);
INSERT INTO Activity (ActivityID, UserID) VALUES (7, 7);
INSERT INTO Activity (ActivityID, UserID) VALUES (8, 8);
INSERT INTO Activity (ActivityID, UserID) VALUES (9, 9);
INSERT INTO Activity (ActivityID, UserID) VALUES (10, 10);

-- ActivityTimestamp table
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (1, TO_TIMESTAMP('2024-07-01 10:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (2, TO_TIMESTAMP('2024-07-01 11:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (3, TO_TIMESTAMP('2024-07-01 12:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (4, TO_TIMESTAMP('2024-07-01 13:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (5, TO_TIMESTAMP('2024-07-01 14:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (6, TO_TIMESTAMP('2024-07-01 15:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (7, TO_TIMESTAMP('2024-07-01 16:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (8, TO_TIMESTAMP('2024-07-01 17:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (9, TO_TIMESTAMP('2024-07-01 18:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ActivityTimestamp (ActivityID, Timestamp) VALUES (10, TO_TIMESTAMP('2024-07-01 19:00:00', 'YYYY-MM-DD HH24:MI:SS'));

-- UserActivityType table
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (1, TO_TIMESTAMP('2024-07-01 10:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Login');
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (2, TO_TIMESTAMP('2024-07-01 11:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Data Upload');
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (3, TO_TIMESTAMP('2024-07-01 12:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Data Download');
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (4, TO_TIMESTAMP('2024-07-01 13:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Logout');
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (5, TO_TIMESTAMP('2024-07-01 14:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Profile Update');
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (6, TO_TIMESTAMP('2024-07-01 15:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Login');
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (7, TO_TIMESTAMP('2024-07-01 16:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Data Upload');
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (8, TO_TIMESTAMP('2024-07-01 17:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Data Download');
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (9, TO_TIMESTAMP('2024-07-01 18:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Logout');
INSERT INTO UserActivityType (UserID, Timestamp, ActivityType) VALUES (10, TO_TIMESTAMP('2024-07-01 19:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Profile Update');

-- UserActivityDetails table
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (1, TO_TIMESTAMP('2024-07-01 10:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'User logged in from IP 192.168.1.1');
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (2, TO_TIMESTAMP('2024-07-01 11:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Uploaded dataset file1.csv');
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (3, TO_TIMESTAMP('2024-07-01 12:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Downloaded report file2.pdf');
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (4, TO_TIMESTAMP('2024-07-01 13:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'User logged out successfully');
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (5, TO_TIMESTAMP('2024-07-01 14:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Updated profile picture');
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (6, TO_TIMESTAMP('2024-07-01 15:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'User logged in from IP 192.168.1.2');
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (7, TO_TIMESTAMP('2024-07-01 16:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Uploaded dataset file3.csv');
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (8, TO_TIMESTAMP('2024-07-01 17:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Downloaded report file4.pdf');
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (9, TO_TIMESTAMP('2024-07-01 18:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'User logged out successfully');
INSERT INTO UserActivityDetails (UserID, Timestamp, ActivityDetails) VALUES (10, TO_TIMESTAMP('2024-07-01 19:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Updated profile picture');

-- TransparencyReport table
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (1, 1);
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (2, 2);
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (3, 3);
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (4, 4);
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (5, 5);
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (6, 6);
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (7, 7);
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (8, 8);
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (9, 9);
INSERT INTO TransparencyReport (ReportID, UserID) VALUES (10, 10);

-- ReportGeneratedOn table
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (1, TO_TIMESTAMP('2024-07-01 15:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (2, TO_TIMESTAMP('2024-07-01 16:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (3, TO_TIMESTAMP('2024-07-01 17:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (4, TO_TIMESTAMP('2024-07-01 18:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (5, TO_TIMESTAMP('2024-07-01 19:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (6, TO_TIMESTAMP('2024-07-01 20:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (7, TO_TIMESTAMP('2024-07-01 21:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (8, TO_TIMESTAMP('2024-07-01 22:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (9, TO_TIMESTAMP('2024-07-01 23:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO ReportGeneratedOn (ReportID, GeneratedOn) VALUES (10, TO_TIMESTAMP('2024-07-01 23:59:59', 'YYYY-MM-DD HH24:MI:SS'));

-- UserGeneratedReportDetails table
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (1, TO_TIMESTAMP('2024-07-01 15:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Report on data usage for July 2024');
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (2, TO_TIMESTAMP('2024-07-01 16:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Report on data transactions for July 2024');
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (3, TO_TIMESTAMP('2024-07-01 17:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Report on user activity for July 2024');
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (4, TO_TIMESTAMP('2024-07-01 18:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Report on financial transactions for July 2024');
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (5, TO_TIMESTAMP('2024-07-01 19:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Report on profile updates for July 2024');
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (6, TO_TIMESTAMP('2024-07-01 20:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Report on data usage for August 2024');
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (7, TO_TIMESTAMP('2024-07-01 21:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Report on data transactions for August 2024');
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (8, TO_TIMESTAMP('2024-07-01 22:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Report on user activity for August 2024');
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (9, TO_TIMESTAMP('2024-07-01 23:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Report on financial transactions for August 2024');
INSERT INTO UserGeneratedReportDetails (UserID, GeneratedOn, ReportDetails) VALUES (10, TO_TIMESTAMP('2024-07-01 23:59:59', 'YYYY-MM-DD HH24:MI:SS'), 'Report on profile updates for August 2024');

-- Company table
INSERT INTO Company (CompanyID, Name, Industry, ContactInfo) VALUES (1, 'TechCorp', 'Technology', 'john@techcorp.com');
INSERT INTO Company (CompanyID, Name, Industry, ContactInfo) VALUES (2, 'HealthInc', 'Healthcare', 'mary@healthinc.com');
INSERT INTO Company (CompanyID, Name, Industry, ContactInfo) VALUES (3, 'EduSoft', 'Education', 'peter@edusoft.com');
INSERT INTO Company (CompanyID, Name, Industry, ContactInfo) VALUES (4, 'Tech Solutions', 'Finance', 'lucas@fintech.com');
INSERT INTO Company (CompanyID, Name, Industry, ContactInfo) VALUES (5, 'BioLab', 'Environment', 'sophia@biolab.com');


-- DataCategory table
INSERT INTO DataCategory (CategoryID, CategoryName, Description) VALUES (1, 'Demographics', 'Data related to population demographics');
INSERT INTO DataCategory (CategoryID, CategoryName, Description) VALUES (2, 'Health', 'Health-related data');
INSERT INTO DataCategory (CategoryID, CategoryName, Description) VALUES (3, 'Education', 'Educational data');
INSERT INTO DataCategory (CategoryID, CategoryName, Description) VALUES (4, 'Finance', 'Financial data');
INSERT INTO DataCategory (CategoryID, CategoryName, Description) VALUES (5, 'Environment', 'Environmental data');

-- DataRequest table
INSERT INTO DataRequest (DataRequestID, CompanyID, Compensation, DataPurpose, CategoryID, Status) VALUES (1, 1, 1000.00, 'Market Research', 1, 'Pending');
INSERT INTO DataRequest (DataRequestID, CompanyID, Compensation, DataPurpose, CategoryID, Status) VALUES (2, 2, 1500.00, 'Clinical Study', 2, 'Accepted');
INSERT INTO DataRequest (DataRequestID, CompanyID, Compensation, DataPurpose, CategoryID, Status) VALUES (3, 3, 500.00, 'Educational Analysis', 3, 'Accepted');
INSERT INTO DataRequest (DataRequestID, CompanyID, Compensation, DataPurpose, CategoryID, Status) VALUES (4, 4, 2000.00, 'Financial Forecast', 4, 'Pending');
INSERT INTO DataRequest (DataRequestID, CompanyID, Compensation, DataPurpose, CategoryID, Status) VALUES (5, 5, 1200.00, 'Environmental Impact Study', 5, 'Accepted');
INSERT INTO DataRequest (DataRequestID, CompanyID, Compensation, DataPurpose, CategoryID, Status) VALUES (6, 1, 1000.00, 'Market Research', 2, 'Pending');
INSERT INTO DataRequest (DataRequestID, CompanyID, Compensation, DataPurpose, CategoryID, Status) VALUES (7, 1, 1000.00, 'Market Research', 3, 'Pending');
INSERT INTO DataRequest (DataRequestID, CompanyID, Compensation, DataPurpose, CategoryID, Status) VALUES (8, 1, 1000.00, 'Market Research', 4, 'Pending');
INSERT INTO DataRequest (DataRequestID, CompanyID, Compensation, DataPurpose, CategoryID, Status) VALUES (9, 1, 1000.00, 'Market Research', 5, 'Pending');
-- DataBelongCategory table
INSERT INTO DataBelongCategory (CategoryID, DataRequestID, CompanyID) VALUES (1, 1, 1);
INSERT INTO DataBelongCategory (CategoryID, DataRequestID, CompanyID) VALUES (2, 2, 2);
INSERT INTO DataBelongCategory (CategoryID, DataRequestID, CompanyID) VALUES (3, 3, 3);
INSERT INTO DataBelongCategory (CategoryID, DataRequestID, CompanyID) VALUES (4, 4, 4);
INSERT INTO DataBelongCategory (CategoryID, DataRequestID, CompanyID) VALUES (5, 5, 5);

-- TransactionIDUserID table
INSERT INTO TransactionIDUserID (TransactionID, UserID, DataText) VALUES (1, 1, 'Testing upload data request');
INSERT INTO TransactionIDUserID (TransactionID, UserID, DataText) VALUES (2, 2, 'Testing upload data request');
INSERT INTO TransactionIDUserID (TransactionID, UserID, DataText) VALUES (3, 3, 'Testing upload data request');
INSERT INTO TransactionIDUserID (TransactionID, UserID, DataText) VALUES (4, 4, 'Testing upload data request');
INSERT INTO TransactionIDUserID (TransactionID, UserID, DataText) VALUES (5, 5, 'Testing upload data request');

-- TransactionIDDataRequestID table
INSERT INTO TransactionIDDataRequestID (TransactionID, DataRequestID) VALUES (1, 1);
INSERT INTO TransactionIDDataRequestID (TransactionID, DataRequestID) VALUES (2, 2);
INSERT INTO TransactionIDDataRequestID (TransactionID, DataRequestID) VALUES (3, 3);
INSERT INTO TransactionIDDataRequestID (TransactionID, DataRequestID) VALUES (4, 4);
INSERT INTO TransactionIDDataRequestID (TransactionID, DataRequestID) VALUES (5, 5);

-- TransactionIDAmount table
INSERT INTO TransactionIDAmount (TransactionID, Amount) VALUES (1, 500);
INSERT INTO TransactionIDAmount (TransactionID, Amount) VALUES (2, 1500);
INSERT INTO TransactionIDAmount (TransactionID, Amount) VALUES (3, 2500);
INSERT INTO TransactionIDAmount (TransactionID, Amount) VALUES (4, 3500);
INSERT INTO TransactionIDAmount (TransactionID, Amount) VALUES (5, 4500);

-- TransactionIDTimestamp table
INSERT INTO TransactionIDTimestamp (TransactionID, Timestamp) VALUES (1, TO_DATE('2024-01-01', 'YYYY-MM-DD'));
INSERT INTO TransactionIDTimestamp (TransactionID, Timestamp) VALUES (2, TO_DATE('2024-01-02', 'YYYY-MM-DD'));
INSERT INTO TransactionIDTimestamp (TransactionID, Timestamp) VALUES (3, TO_DATE('2024-01-03', 'YYYY-MM-DD'));
INSERT INTO TransactionIDTimestamp (TransactionID, Timestamp) VALUES (4, TO_DATE('2024-01-04', 'YYYY-MM-DD'));
INSERT INTO TransactionIDTimestamp (TransactionID, Timestamp) VALUES (5, TO_DATE('2024-01-05', 'YYYY-MM-DD'));

-- TransactionIDCurrency table
INSERT INTO TransactionIDCurrency (TransactionID, Currency) VALUES (1, 'USD');
INSERT INTO TransactionIDCurrency (TransactionID, Currency) VALUES (2, 'EUR');
INSERT INTO TransactionIDCurrency (TransactionID, Currency) VALUES (3, 'GBP');
INSERT INTO TransactionIDCurrency (TransactionID, Currency) VALUES (4, 'JPY');
INSERT INTO TransactionIDCurrency (TransactionID, Currency) VALUES (5, 'CAD');

-- TransactionIDExchangeRate table
INSERT INTO TransactionIDExchangeRate (TransactionID, ExchangeRate) VALUES (1, 1.00);
INSERT INTO TransactionIDExchangeRate (TransactionID, ExchangeRate) VALUES (2, 0.90);
INSERT INTO TransactionIDExchangeRate (TransactionID, ExchangeRate) VALUES (3, 0.80);
INSERT INTO TransactionIDExchangeRate (TransactionID, ExchangeRate) VALUES (4, 110.00);
INSERT INTO TransactionIDExchangeRate (TransactionID, ExchangeRate) VALUES (5, 1.30);

-- CurrencyExchangeRate table
INSERT INTO CurrencyExchangeRate (Currency, ExchangeRate) VALUES ('USD', 1.00);
INSERT INTO CurrencyExchangeRate (Currency, ExchangeRate) VALUES ('EUR', 0.90);
INSERT INTO CurrencyExchangeRate (Currency, ExchangeRate) VALUES ('GBP', 0.80);
INSERT INTO CurrencyExchangeRate (Currency, ExchangeRate) VALUES ('JPY', 110.00);
INSERT INTO CurrencyExchangeRate (Currency, ExchangeRate) VALUES ('CAD', 1.30);

INSERT INTO Review (ReviewID, TransactionID, UserID, Status, Compensation) VALUES (1, 1, 1, 'Accepted', 500.00);
INSERT INTO Review (ReviewID, TransactionID, UserID, Status, Compensation) VALUES (2, 2, 2, 'Accepted', 1500.00);
INSERT INTO Review (ReviewID, TransactionID, UserID, Status, Compensation) VALUES (3, 3, 3, 'Rejected', 0.00);
INSERT INTO Review (ReviewID, TransactionID, UserID, Status, Compensation) VALUES (4, 4, 4, 'Accepted', 3500.00);
INSERT INTO Review (ReviewID, TransactionID, UserID, Status, Compensation) VALUES (5, 5, 5, 'Rejected', 0.00);

COMMIT;
/