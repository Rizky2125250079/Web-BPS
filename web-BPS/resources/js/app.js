import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('publikasiPerTahunChart');

    if (!canvas) {
        return;
    }

    let labels = [];
    let values = [];

    try {
        labels = JSON.parse(canvas.dataset.labels || '[]');
        values = JSON.parse(canvas.dataset.values || '[]');
    } catch (e) {
        console.error('Gagal membaca data chart publikasi per tahun:', e);
        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Publikasi',
                data: values,
                backgroundColor: '#1e3a8a',
                hoverBackgroundColor: '#2563eb',
                borderRadius: 6,
                maxBarThickness: 56
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (item) => `${item.formattedValue} publikasi`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
