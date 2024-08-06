document.addEventListener('DOMContentLoaded', () => {
    const dataRequestForm = document.getElementById('dataRequestForm');
    const dataCategoryFilter = document.getElementById('dataCategoryFilter');
    const userSearch = document.getElementById('userSearch');

    if (dataCategoryFilter) {
        dataCategoryFilter.addEventListener('change', fetchCompanyDashboardData);
    }

    if (userSearch) {
        userSearch.addEventListener('keyup', fetchCompanyDashboardData);
    }

    fetchCompanyDashboardData();

    if (dataRequestForm) {
        dataRequestForm.onsubmit = async function (event) {
            event.preventDefault();
            const formData = new FormData(dataRequestForm);

            try {
                const response = await fetch(dataRequestForm.action, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.text();
                console.log('Form submission response:', result);
                alert(result);
                fetchCompanyDashboardData();
            } catch (error) {
                console.error('Error submitting data request:', error);
                alert(`An error occurred while submitting the data request: ${error.message}`);
            }
        };
    }
});

async function fetchCompanyDashboardData() {
    const url = `../backend/company_dashboard_data.php`;

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

        updateUserData(data.transactions || []);
        updateDataRequests(data.data_requests || []);
        updateMetrics(data.data_requests.length, data.transactions.length, data.total_compensation);
    } catch (error) {
        console.error('Fetch error:', error);
    }
}

function updateUserData(transactions) {
    const userDataTableBody = document.getElementById('userDataTable').querySelector('tbody');
    const dataCategoryFilterValue = document.getElementById('dataCategoryFilter').value;
    const userSearchValue = document.getElementById('userSearch').value.toLowerCase();

    userDataTableBody.innerHTML = '';

    transactions
        .filter(transaction => {
            const matchesCategory = !dataCategoryFilterValue || transaction.CATEGORYNAME.toLowerCase() === dataCategoryFilterValue.toLowerCase();
            const matchesUser = !userSearchValue || transaction.USERID.toString().toLowerCase().includes(userSearchValue);
            return matchesCategory && matchesUser;
        })
        .forEach(transaction => {
            const row = userDataTableBody.insertRow();
            row.innerHTML = `
                <td>${transaction.USERID}</td>
                <td>${transaction.DATATEXT}</td>
                <td>${transaction.DATAPURPOSE}</td>
                <td>${transaction.CATEGORYNAME}</td>
                <td>${transaction.REQUESTEDCOMPENSATION}</td>
                <td>
                    <form class="reviewForm" action="../backend/review_data.php" method="POST">
                        <input type="hidden" name="transactionID" value="${transaction.TRANSACTIONID}">
                        <button type="submit" name="action" value="accept" class="btn">Accept</button>
                        <button type="submit" name="action" value="reject" class="btn">Reject</button>
                        <input type="number" name="compensation" placeholder="Compensation" required>
                        <button type="submit" name="action" value="accept_with_compensation" class="btn">Accept with Compensation</button>
                    </form>
                </td>
            `;
        });
}

function updateDataRequests(dataRequests) {
    const dataRequestsTableBody = document.getElementById('dataRequestsTable').querySelector('tbody');

    dataRequestsTableBody.innerHTML = '';

    dataRequests.forEach(request => {
        const row = dataRequestsTableBody.insertRow();
        row.innerHTML = `
            <td>${request.DATAPURPOSE}</td>
            <td>${request.COMPENSATION}</td>
            <td>${request.CATEGORYNAME}</td>
            <td><button class="delete-request btn" data-request-id="${request.DATAREQUESTID}">Delete</button></td> 
        `;
    });
}

dataRequestsTable.addEventListener('click', async (event) => {
    if (event.target.classList.contains('delete-request')) {
        event.preventDefault();
        const dataRequestId = event.target.dataset.requestId;

        try {
            const response = await fetch('../backend/delete_data_request.php', {
                method: 'POST',
                body: new URLSearchParams({ dataRequestId }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            });
            console.log('Parsed data:', data);
            if (response.ok) {
                fetchCompanyDashboardData(); // Refresh the dashboard
            } else {
                console.error('Error deleting data request:', response.statusText);
            }
        } catch (error) {
            console.error('Error deleting data request:', error);
        }
    }
});

function updateMetrics(activeDataRequestsCount, reviewedDataCount, totalCompensation) {
    document.getElementById('active-data-requests-count').textContent = activeDataRequestsCount;
    document.getElementById('reviewed-data-count').textContent = reviewedDataCount;
    document.getElementById('total-compensation').textContent = `$${totalCompensation}`;
}

//removed calculateTotalCompensation because it was redundant and I hated it :).
