



function viewAllData() {
    console.log('viewAllData function called');
    fetch('../backend/view_all_data.php')
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



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



function searchByKey() {
    const key = document.getElementById('searchKeyDropdown').value;
    if (!key) {
        alert('Please select a key');
        return;
    }
    console.log('searchByKey function called with key:', key);
    
    // Fetch data from the backend
    fetch(`../backend/search_by_key.php?key=${key}`)
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


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function searchCompleteData() {
    const selectedOption = document.getElementById('completeDataDropdown').value;
    let fetchUrl = '';

    if (selectedOption === 'usersCompleteData') {
        fetchUrl = '../backend/users_with_complete_data.php';
    } else if (selectedOption === 'companiesAllCategories') {
        fetchUrl = '../backend/companies_with_all_categories.php';
    }

    if (fetchUrl) {
        fetch(fetchUrl)
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








///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function viewTotalRequestsPerCategory() {
    console.log('viewTotalRequestsPerCategory function called'); 
    fetch('../backend/view_total_requests_per_category.php')
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


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


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