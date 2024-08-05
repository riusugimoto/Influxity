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
        updateMetrics(data.data_requests.length, data.transactions.length, calculateTotalCompensation(data.compensations));
    } catch (error) {
        console.error('Fetch error:', error);
    }
}
 //Have to find a way to calculate total transaction amount and display the data dynamically.
 //prob will add comments after the file is done idk.