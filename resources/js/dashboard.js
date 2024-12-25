import "../css/dashboard.scss"
import Chart from 'chart.js/auto'
import { get } from './ajax'

window.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('yearToDateChart');

    // Get the start and end date values from the input fields
    const startDateInput = document.querySelector('#start-date');
    const endDateInput = document.querySelector('#end-date');



    // Function to get the date range
    function getDateRange() {
        const startDate = startDateInput.value;  // Get the start date value
        const endDate = endDateInput.value;      // Get the end date value

        return { start_date: startDate, end_date: endDate };
    }

    // Add event listener to the button to fetch data when clicked
    document.querySelector('.filter-btn').addEventListener('click', function () {
        // Get the date range
        const { start_date, end_date } = getDateRange();

        // Check if the date values are empty
        if (!start_date || !end_date) {
            alert("Please select both start and end dates.");
            return;
        }

        // Make the GET request with the date range as query parameters
        get(`/stats/ytd?start_date=${start_date}&end_date=${end_date}`)
            .then(response => response.json())
            .then(response => {
                console.log(response);
                let expensesData = Array(12).fill(null);
                let incomeData = Array(12).fill(null);

                // Update chart data based on the response
                response.forEach(({ m, expense, income }) => {
                    expensesData[m - 1] = expense;
                    incomeData[m - 1] = income;
                });

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [
                            {
                                label: 'Expense',
                                data: expensesData,
                                borderWidth: 1,
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                            },
                            {
                                label: 'Income',
                                data: incomeData,
                                borderWidth: 1,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                            }
                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error("Error fetching data:", error);
            });
    });
});


