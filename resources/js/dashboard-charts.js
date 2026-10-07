import Chart from 'chart.js/auto';

// Renders every <canvas data-chart="key"> from the JSON in #dashboard-chart-data.
// Series shape: { labels: [], values: [] }. Options per canvas:
//   data-chart-type="column|bar"  (column = vertical, bar = horizontal)
//   data-chart-format="currency|count"
//   data-chart-critical="Label"   (that bar uses the critical status color)

const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

const peso = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 2 });
const pesoCompact = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', notation: 'compact', maximumFractionDigits: 1 });
const count = new Intl.NumberFormat('en-PH', { maximumFractionDigits: 0 });

const renderChart = (canvas, series) => {
    const horizontal = canvas.dataset.chartType === 'bar';
    const isCurrency = canvas.dataset.chartFormat === 'currency';
    const critical = canvas.dataset.chartCritical;

    const series1 = token('--color-chart-1');
    const colors = series.labels.map((label) => (label === critical ? token('--color-chart-critical') : series1));

    const valueAxis = {
        beginAtZero: true,
        border: { display: false },
        grid: { color: token('--color-chart-grid'), drawTicks: false },
        ticks: {
            color: token('--color-chart-muted'),
            padding: 8,
            precision: isCurrency ? undefined : 0,
            maxTicksLimit: 6,
            callback: (value) => (isCurrency ? pesoCompact.format(value) : count.format(value)),
        },
    };

    const categoryAxis = {
        border: { color: token('--color-chart-axis') },
        grid: { display: false },
        ticks: { color: token('--color-chart-muted'), autoSkip: !horizontal, maxRotation: 0 },
    };

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: series.labels,
            datasets: [{
                label: canvas.getAttribute('aria-label') || '',
                data: series.values,
                backgroundColor: colors,
                hoverBackgroundColor: colors,
                // Rounded data-end only; the end touching the baseline stays square
                borderRadius: 4,
                borderSkipped: 'start',
                categoryPercentage: 0.75,
                barPercentage: 0.8,
                maxBarThickness: horizontal ? 22 : 36,
            }],
        },
        options: {
            indexAxis: horizontal ? 'y' : 'x',
            maintainAspectRatio: false,
            animation: reduceMotion ? false : { duration: 400 },
            interaction: { mode: 'index', intersect: false },
            scales: horizontal ? { x: valueAxis, y: categoryAxis } : { x: categoryAxis, y: valueAxis },
            plugins: {
                // One series per chart: the card title names it, so no legend box
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0b0b0b',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: (context) => {
                            const value = horizontal ? context.parsed.x : context.parsed.y;
                            return isCurrency ? peso.format(value) : `${count.format(value)} claim${value === 1 ? '' : 's'}`;
                        },
                    },
                },
            },
        },
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const source = document.getElementById('dashboard-chart-data');
    if (!source) return;

    let data = {};
    try {
        data = JSON.parse(source.textContent);
    } catch (error) {
        console.error('Invalid dashboard chart data', error);
        return;
    }

    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.font.size = 12;

    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        const series = data[canvas.dataset.chart];
        if (series?.labels?.length) {
            renderChart(canvas, series);
        }
    });
});
