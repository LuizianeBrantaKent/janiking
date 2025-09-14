// Join Us Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Investment Chart
    const ctx = document.getElementById('investmentChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Initial Franchise Fee', 'Training Costs', 'Equipment & Supplies', 'Marketing'],
            datasets: [{
                data: [15000, 5000, 10000, 3000],
                backgroundColor: ['#004990', '#00A1D6', '#FFD700', '#FF6F61'],
                borderColor: ['#ffffff', '#ffffff', '#ffffff', '#ffffff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { color: '#333', font: { size: 14 } } },
                title: { display: true, text: 'Estimated Investment Breakdown (USD)', color: '#333', font: { size: 18 } }
            }
        }
    });
});

// Ensure Bootstrap carousel and other JS components work
$(document).ready(function() {
    $('.carousel').carousel({
        interval: 5000
    });
});