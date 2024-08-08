// admin_scripts.js

function viewAllData() {
    console.log('viewAllData function called');
    fetch('../backend/admin_dashboard.php?action=viewAllData')
        .then(response => response.json())
        .then(data => {
            console.log('Data fetched:', data);
            const contentSection = document.getElementById('content-section');
            contentSection.innerHTML = '<h2>All Data</h2>';
            for (const table in data) {
                const tableHTML = generateTableHTML(data[table]);
                contentSection.innerHTML += `<h3>${table}</h3>${tableHTML}`;
            }
        })
        .catch(error => console.error('Error fetching data:', error));
}

function searchByKey() {
    const key = document.getElementById('searchKeyDropdown').value;
    if (!key) {
        alert('Please select a key');
        return;
    }
    console.log('searchByKey function called with key:', key);

    // Fetch data from the backend
    fetch(`../backend/admin_dashboard.php?action=searchByKey&key=${key}`)
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data fetched:', data);
            const contentSection = document.getElementById('content-section');
            contentSection.innerHTML = '<h2>Search Results</h2>';
            for (const table in data) {
                const tableHTML = generateTableHTML(data[table]);
                contentSection.innerHTML += `<h3>${table}</h3>${tableHTML}`;
            }
        })
        .catch(error => console.error('Error fetching data:', error));
}

function searchCompleteData() {
    const selectedOption = document.getElementById('completeDataDropdown').value;
    let type = '';

    if (selectedOption === 'usersCompleteData') {
        type = 'usersCompleteData';
    } else if (selectedOption === 'companiesAllCategories') {
        type = 'companiesAllCategories';
    }

    if (type) {
        fetch(`../backend/admin_dashboard.php?action=searchCompleteData&type=${type}`)
            .then(response => response.json())
            .then(data => {
                console.log('Data fetched:', data);
                const contentSection = document.getElementById('content-section');
                contentSection.innerHTML = `<h2>${selectedOption === 'usersCompleteData' ? 'Users with Complete Data' : 'Companies with All Category Requests'}</h2>`;
                const tableHTML = generateTableHTML(data);
                contentSection.innerHTML += tableHTML;
            })
            .catch(error => console.error('Error fetching data:', error));
    }
}

function viewTotalRequestsPerCategory() {
    console.log('viewTotalRequestsPerCategory function called');
    fetch('../backend/admin_dashboard.php?action=viewTotalRequestsPerCategory')
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data fetched:', data);
            const contentSection = document.getElementById('content-section');
            contentSection.innerHTML = '<h2>Total Data Requests per Category</h2>';
            const tableHTML = generateTableHTML(data);
            contentSection.innerHTML += tableHTML;
        })
        .catch(error => console.error('Error fetching data:', error));
}

function generateTableHTML(data) {
    let html = '<table border="1"><tr>';
    if (data.length > 0) {
        Object.keys(data[0]).forEach(key => {
            html += `<th>${key}</th>`;
        });
        html += '</tr>';
        data.forEach(row => {
            html += '<tr>';
            Object.values(row).forEach(value => {
                html += `<td>${value}</td>`;
            });
            html += '</tr>';
        });
    } else {
        html += '<tr><td>No data available</td></tr>';
    }
    html += '</table>';
    return html;
}
