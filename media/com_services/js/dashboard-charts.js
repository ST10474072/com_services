/**
 * Dashboard Charts JavaScript for Services Component
 * Handles Chart.js initialization and data visualization
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dashboard charts
    initializeServiceStatsChart();
    initializeReviewsChart();
    initializeMessagesChart();
    initializeMonthlyStatsChart();
});

/**
 * Initialize Services Statistics Chart
 */
function initializeServiceStatsChart() {
    const canvas = document.getElementById('servicesStatsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Get data from canvas dataset or use defaults
    const data = JSON.parse(canvas.dataset.chartData || '{"total":0,"active":0,"pending":0,"inactive":0}');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Pending', 'Inactive'],
            datasets: [{
                data: [data.active, data.pending, data.inactive],
                backgroundColor: [
                    '#28a745', // Green for active
                    '#ffc107', // Yellow for pending
                    '#dc3545'  // Red for inactive
                ],
                borderColor: [
                    '#1e7e34',
                    '#d39e00',
                    '#bd2130'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize Reviews Chart
 */
function initializeReviewsChart() {
    const canvas = document.getElementById('reviewsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Get data from canvas dataset or use defaults
    const data = JSON.parse(canvas.dataset.chartData || '{"approved":0,"pending":0,"rejected":0}');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                label: 'Reviews',
                data: [data.approved, data.pending, data.rejected],
                backgroundColor: [
                    '#28a745cc', // Green for approved (with transparency)
                    '#ffc107cc', // Yellow for pending
                    '#dc3545cc'  // Red for rejected
                ],
                borderColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return 'Reviews: ' + context[0].label;
                        },
                        label: function(context) {
                            return 'Count: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize Messages Chart
 */
function initializeMessagesChart() {
    const canvas = document.getElementById('messagesChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Get data from canvas dataset or use defaults
    const data = JSON.parse(canvas.dataset.chartData || '{"unread":0,"read":0}');
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Unread', 'Read'],
            datasets: [{
                data: [data.unread, data.read],
                backgroundColor: [
                    '#dc3545', // Red for unread
                    '#28a745'  // Green for read
                ],
                borderColor: [
                    '#bd2130',
                    '#1e7e34'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize Monthly Statistics Chart
 */
function initializeMonthlyStatsChart() {
    const canvas = document.getElementById('monthlyStatsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Get data from canvas dataset or use defaults
    const rawData = canvas.dataset.chartData || '{"months":[],"services":[],"reviews":[],"messages":[]}';
    const data = JSON.parse(rawData);
    
    // If no data, create sample data for last 6 months
    if (data.months.length === 0) {
        const now = new Date();
        for (let i = 5; i >= 0; i--) {
            const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
            data.months.push(date.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' }));
            data.services.push(Math.floor(Math.random() * 10));
            data.reviews.push(Math.floor(Math.random() * 15));
            data.messages.push(Math.floor(Math.random() * 25));
        }
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.months,
            datasets: [
                {
                    label: 'Services',
                    data: data.services,
                    borderColor: '#007bff',
                    backgroundColor: '#007bff33',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Reviews',
                    data: data.reviews,
                    borderColor: '#28a745',
                    backgroundColor: '#28a74533',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Messages',
                    data: data.messages,
                    borderColor: '#ffc107',
                    backgroundColor: '#ffc10733',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: '#ddd',
                    borderWidth: 1
                }
            }
        }
    });
}

/**
 * Update chart data dynamically
 */
function updateChartData(chartId, newData) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    // Store new data in dataset for persistence
    canvas.dataset.chartData = JSON.stringify(newData);
    
    // Re-initialize the chart
    switch(chartId) {
        case 'servicesStatsChart':
            initializeServiceStatsChart();
            break;
        case 'reviewsChart':
            initializeReviewsChart();
            break;
        case 'messagesChart':
            initializeMessagesChart();
            break;
        case 'monthlyStatsChart':
            initializeMonthlyStatsChart();
            break;
    }
}

/**
 * Refresh all dashboard charts with AJAX
 */
function refreshDashboardCharts() {
    // Show loading state
    const charts = document.querySelectorAll('.chart-container canvas');
    charts.forEach(function(canvas) {
        canvas.style.opacity = '0.5';
    });
    
    // Make AJAX request to get fresh data
    fetch('index.php?option=com_services&task=dashboard.getChartData&format=json', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            // Non-JSON (likely an HTML response); avoid throwing to keep dashboard functional
            return { success: false };
        }
    })
    .then(data => {
        if (data && data.success && data.charts) {
            // Update each chart with new data
            Object.keys(data.charts).forEach(function(chartId) {
                updateChartData(chartId, data.charts[chartId]);
            });
        }
    })
    .catch(() => {
        // Swallow errors to avoid console noise
    })
    .finally(() => {
        // Remove loading state
        charts.forEach(function(canvas) {
            canvas.style.opacity = '1';
        });
    });
}