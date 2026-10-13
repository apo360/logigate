const instances = new Map();
const colors = ['#1d4ed8', '#0f766e', '#b45309', '#6d28d9', '#be123c', '#475569'];
const stateColors = { 'Aberto': '#64748b', 'Em analise': '#2563eb', 'Aguardando documentos': '#b45309', 'Em despacho': '#4f46e5', 'Finalizado': '#15803d', 'Cancelado': '#b91c1c', 'Por licenciar': '#b45309', 'Em licenciamento': '#2563eb', 'Licenciado': '#15803d' };
function destroy() { instances.forEach(chart => chart.destroy()); instances.clear(); }
function initialise() {
    destroy();
    document.querySelectorAll('[data-operational-chart]').forEach(canvas => {
        try {
            if (!window.Chart) throw new Error('Chart.js unavailable');
            const spec = JSON.parse(canvas.dataset.operationalChart);
            const doughnut = spec.type === 'doughnut';
            const monetary = spec.unit !== 'registos';
            const number = value => value === null || value === undefined ? 'Dado ausente' : new Intl.NumberFormat('pt-AO', { maximumFractionDigits: monetary ? 2 : 0 }).format(value);
            const datasets = spec.datasets.map((set, index) => ({ ...set, data: set.data.map(value => value === null ? null : Number(value)),
                borderColor: stateColors[set.label] || colors[index % colors.length],
                backgroundColor: doughnut ? spec.labels.map(label => stateColors[label] || '#64748b') : (stateColors[set.label] || colors[index % colors.length]),
                fill: false, borderWidth: spec.type === 'line' ? 2 : 1, pointRadius: 3, lineTension: 0.15, spanGaps: false }));
            const numericAxis = { stacked: !!spec.stacked, ticks: { beginAtZero: true, precision: monetary ? undefined : 0 } };
            const categoricalAxis = { stacked: !!spec.stacked, ticks: { autoSkip: true } };
            instances.set(canvas, new window.Chart(canvas.getContext('2d'), { type: spec.type, data: { labels: spec.labels, datasets }, options: {
                responsive: true, maintainAspectRatio: false, animation: { duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 200 },
                legend: { display: doughnut || datasets.length > 1, position: 'bottom' },
                tooltips: { callbacks: { label: item => { const set = datasets[item.datasetIndex]; return `${set.label}: ${number(set.data[item.index])} ${spec.unit}`; } } },
                scales: doughnut ? {} : { xAxes: [spec.type === 'horizontalBar' ? numericAxis : categoricalAxis], yAxes: [spec.type === 'horizontalBar' ? categoricalAxis : numericAxis] },
            } }));
        } catch {
            canvas.closest('article').querySelector('[data-chart-error]')?.removeAttribute('hidden');
        }
    });
    document.querySelectorAll('[data-analytics-filter]').forEach(form => {
        if (form.dataset.bound) return;
        form.dataset.bound = 'true';
        form.addEventListener('submit', () => { form.setAttribute('aria-busy', 'true'); const button = form.querySelector('button[type=submit]'); button.disabled = true; button.textContent = 'A actualizar…'; });
    });
}
document.addEventListener('livewire:navigating', destroy);
document.addEventListener('livewire:navigated', initialise);
window.addEventListener('beforeunload', destroy);
window.addEventListener('pageshow', () => document.querySelectorAll('[data-analytics-filter]').forEach(form => { form.removeAttribute('aria-busy'); const button = form.querySelector('button[type=submit]'); button.disabled = false; button.textContent = 'Aplicar filtros'; }));
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialise, { once: true }); else initialise();
