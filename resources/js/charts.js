// Chart.js configuration and management
class DashboardCharts {
    constructor() {
        this.charts = {};
        this.colors = {
            primary: 'rgb(59, 130, 246)',
            success: 'rgb(34, 197, 94)',
            warning: 'rgb(245, 158, 11)',
            danger: 'rgb(239, 68, 68)',
            info: 'rgb(14, 165, 233)',
            purple: 'rgb(139, 92, 246)',
            pink: 'rgb(236, 72, 153)',
            emerald: 'rgb(16, 185, 129)'
        };
    }

    // Initialize all charts
    async init() {
        await this.loadTrendChart();
        await this.loadAreaDistributionChart();
        await this.loadSlaComplianceChart();
        await this.loadAttendantPerformanceChart();
        await this.loadStatusDistributionChart();
    }

    // Load trend chart (line chart)
    async loadTrendChart() {
        try {
            const response = await fetch('/api/charts/trend');
            const data = await response.json();
            
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;

            this.charts.trend = new Chart(ctx, {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Tendência dos Últimos 30 Dias'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        } catch (error) {
            console.error('Error loading trend chart:', error);
        }
    }

    // Load area distribution chart (doughnut chart)
    async loadAreaDistributionChart() {
        try {
            const response = await fetch('/api/charts/area-distribution');
            const data = await response.json();
            
            const ctx = document.getElementById('areaDistributionChart');
            if (!ctx) return;

            this.charts.areaDistribution = new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Distribuição por Área'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error loading area distribution chart:', error);
        }
    }

    // Load SLA compliance chart (gauge-like doughnut)
    async loadSlaComplianceChart() {
        try {
            const response = await fetch('/api/charts/sla-compliance');
            const data = await response.json();
            
            const ctx = document.getElementById('slaComplianceChart');
            if (!ctx) return;

            // Create gauge-like chart
            const gaugeData = {
                labels: ['Em Dia', 'Vencidos'],
                datasets: [{
                    data: [data.onTime, data.overdue],
                    backgroundColor: [this.colors.success, this.colors.danger],
                    borderWidth: 0
                }]
            };

            this.charts.slaCompliance = new Chart(ctx, {
                type: 'doughnut',
                data: gaugeData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: `SLA Compliance: ${data.complianceRate}%`
                        }
                    }
                }
            });

            // Add center text
            this.addCenterText(ctx, `${data.complianceRate}%`);
        } catch (error) {
            console.error('Error loading SLA compliance chart:', error);
        }
    }

    // Load attendant performance chart (bar chart)
    async loadAttendantPerformanceChart() {
        try {
            const response = await fetch('/api/charts/attendant-performance');
            const data = await response.json();
            
            const ctx = document.getElementById('attendantPerformanceChart');
            if (!ctx) return;

            this.charts.attendantPerformance = new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Top 10 Atendentes'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error loading attendant performance chart:', error);
        }
    }

    // Load status distribution chart (pie chart)
    async loadStatusDistributionChart() {
        try {
            const response = await fetch('/api/charts/status-distribution');
            const data = await response.json();
            
            const ctx = document.getElementById('statusDistributionChart');
            if (!ctx) return;

            this.charts.statusDistribution = new Chart(ctx, {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Distribuição por Status'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error loading status distribution chart:', error);
        }
    }

    // Add center text to gauge chart
    addCenterText(canvas, text) {
        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        
        ctx.font = 'bold 24px Arial';
        ctx.fillStyle = '#374151';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, centerX, centerY);
    }

    // Refresh all charts
    async refresh() {
        for (const chartName in this.charts) {
            if (this.charts[chartName]) {
                this.charts[chartName].destroy();
            }
        }
        await this.init();
    }
}

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const dashboardCharts = new DashboardCharts();
    dashboardCharts.init();
    
    // Make it globally available for manual refresh
    window.dashboardCharts = dashboardCharts;
});










