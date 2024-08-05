document.addEventListener('DOMContentLoaded', () => {
    fetchReviewedUserData();
});

async function fetchReviewedUserData() {
    const url = `../backend/analytics_data.php`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.error) {
            console.error('Data error:', data.error);
            throw new Error(data.error);
        }

        updateReviewedUserData(data.transactions || []);
    } catch (error) {
        console.error('Fetch error:', error);
    }
}

function updateReviewedUserData(transactions) {
    const reviewedUserDataTableBody = document.getElementById('reviewedUserDataTable').querySelector('tbody');

    reviewedUserDataTableBody.innerHTML = '';

    transactions.forEach(transaction => {
        const row = reviewedUserDataTableBody.insertRow();
        row.innerHTML = `
            <td>${transaction.USERID}</td>
            <td>${transaction.DATATEXT}</td>
            <td>${transaction.DATAPURPOSE}</td>
            <td>${transaction.CATEGORYNAME}</td>
            <td>${transaction.REQUESTEDCOMPENSATION}</td>
            <td>${transaction.STATUS}</td>
            <td>${transaction.OFFEREDCOMPENSATION}</td>
        `;
    });
}