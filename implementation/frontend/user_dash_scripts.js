document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    const dataSubmissionModal = document.getElementById('dataSubmissionModal');
    const closeModal = document.getElementById('closeModal');
    const categoryFilter = document.getElementById('categoryFilter');
    const companySearch = document.getElementById('companySearch');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', fetchUserDashboardData);
    }

    if (companySearch) {
        companySearch.addEventListener('keyup', fetchUserDashboardData);
    }

    fetchUserDashboardData();

    if (closeModal) {
        closeModal.onclick = function () {
            dataSubmissionModal.style.display = 'none';
        };
    }

    window.onclick = function (event) {
        if (event.target === dataSubmissionModal) {
            dataSubmissionModal.style.display = 'none';
        }
    };

    if (dataSubmissionForm) {
        dataSubmissionForm.onsubmit = async function (event) {
            event.preventDefault();
            console.log('Form submission started');

            const formData = new FormData(dataSubmissionForm);

            try {
                const response = await fetch('../backend/upload_data.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                //const result = await response.text();
                //console.log('Form submission response:', result);
                //alert(result);
                dataSubmissionModal.style.display = 'none';
                fetchUserDashboardData();
            } catch (error) {
                console.error('Error submitting data:', error);
                alert(`An error occurred while submitting the data: ${error.message}`);
            }
        };
    }

    const showAnalyticsLink = document.getElementById('showAnalytics');
    const analyticsSection = document.getElementById('analyticsSection');

    if (showAnalyticsLink) {
        showAnalyticsLink.addEventListener('click', () => {
            analyticsSection.style.display = analyticsSection.style.display === 'none' ? 'block' : 'none';
            if (analyticsSection.style.display === 'block') {
                fetchReviewedUserData();
            }
        });
    }
});

async function fetchUserDashboardData() {
    const url = `../backend/user_dashboard_data.php`;

    console.log(`Fetching data from URL: ${url}`);

    try {
        const response = await fetch(url);
        const rawText = await response.text();
        console.log('Raw response:', rawText);

        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.statusText}`);
        }

        const data = JSON.parse(rawText);
        console.log('Parsed data:', data);

        if (data.error) {
            console.error('Data error:', data.error);
            throw new Error(data.error);
        }

        updateDataRequests(data.companies_requesting_data || []);

        updateGrantedDataHistory(data.granted_data_history || []);
        updateCompensationHistory(data.compensation_history || []);
        updateTransparencyReports(data.transparency_reports || []);
        updateSubmittedDataHistory(data.submitted_data_history || []);
    } catch (error) {
        console.error('Fetch error:', error);
    }
}

function updateDataRequests(dataRequests) {
    const dataRequestsTableBody = document.getElementById('dataRequestsTable').querySelector('tbody');
    const categoryFilterValue = document.getElementById('categoryFilter').value;
    const companySearchValue = document.getElementById('companySearch').value.toLowerCase();

    dataRequestsTableBody.innerHTML = '';

    dataRequests
        .filter(request => {
            const matchesCategory = !categoryFilterValue || request.CATEGORYID.toString() === categoryFilterValue;
            const matchesCompany = !companySearchValue || request.COMPANYNAME.toLowerCase().includes(companySearchValue);
            return matchesCategory && matchesCompany;
        })
        .forEach(request => {
            const row = dataRequestsTableBody.insertRow();
            row.innerHTML = `
                <td>${request.COMPANYNAME}</td>
                <td>${request.DATAPURPOSE}</td>
                <td>${request.COMPENSATION}</td>
                <td>${request.CATEGORYNAME}</td>
                <td><button onclick="openDataSubmissionModal(${request.DATAREQUESTID})">Upload Data</button></td>
            `;
        });
}

function openDataSubmissionModal(dataRequestID) {
    const dataSubmissionModal = document.getElementById('dataSubmissionModal');
    const dataRequestIDInput = document.getElementById('dataRequestID');
    dataRequestIDInput.value = dataRequestID;
    dataSubmissionModal.style.display = 'block';
}

function updateGrantedDataHistory(grantedDataHistory) {
    const grantedDataHistorySection = document.getElementById('granted-data-history');
    grantedDataHistorySection.innerHTML = grantedDataHistory.map(history => `
        <div class="history">
            <h4>Transaction ID: ${history.TRANSACTIONID}</h4>
            <p>Data Purpose: ${history.DATAPURPOSE}</p>
        </div>
    `).join('');
}

function updateCompensationHistory(compensationHistory) {
    const compensationHistorySection = document.getElementById('compensation-history');
    compensationHistorySection.innerHTML = compensationHistory.map(comp => `
        <div class="compensation">
            <h4>Transaction ID: ${comp.TRANSACTIONID}</h4>
            <p>Amount: ${comp.AMOUNT}</p>
            <p>Currency: ${comp.CURRENCY}</p>
            <p>Timestamp: ${comp.TIMESTAMP}</p>
        </div>
    `).join('');
}

function updateTransparencyReports(transparencyReports) {
    const transparencyReportsSection = document.getElementById('transparency-reports');
    transparencyReportsSection.innerHTML = transparencyReports.map(report => `
        <div class="report">
            <h4>Report ID: ${report.REPORTID}</h4>
            <p>Generated On: ${report.GENERATEDON}</p>
            <p>Details: ${report.DETAILS || 'No details available'}</p>
        </div>
    `).join('');
}

function updateSubmittedDataHistory(submittedDataHistory) {
    const submittedDataHistorySection = document.getElementById('granted-data-history');
    submittedDataHistorySection.innerHTML = '<h3>Submitted Data History</h3>';

    if (submittedDataHistory.length === 0) {
        submittedDataHistorySection.innerHTML += "<p>No data submission history yet.</p>";
        return;
    }

    const table = document.createElement('table');
    table.classList.add('data-table');

    const headerRow = table.insertRow();
    ['Transaction ID', 'Company', 'Data Purpose', 'Review Status', 'Offered Compensation'].forEach(header => {
        const headerCell = headerRow.insertCell();
        headerCell.textContent = header;
    });

    submittedDataHistory.forEach(item => {
        const row = table.insertRow();
        [
            item.TRANSACTIONID,
            item.COMPANYNAME,
            item.DATAPURPOSE,
            item.STATUS,
            item.COMPENSATION || '-' // Display '-' if no compensation
        ].forEach(value => {
            const cell = row.insertCell();
            cell.textContent = value;
        });
    });

    submittedDataHistorySection.appendChild(table);
}
