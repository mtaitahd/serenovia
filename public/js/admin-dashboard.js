(() => {
    const payload = document.getElementById('dashboard-chart-data');
    if (!payload) return;

    let data;
    try { data = JSON.parse(payload.textContent || '{}'); } catch { return; }

    const palette = ['#173426', '#d76e4c', '#8d9b85', '#b79570', '#506b58', '#c4b79f'];
    const fontFamily = 'Nunito, Arial, sans-serif';

    function setupCanvas(canvas, height) {
        if (!canvas) return null;
        const rect = canvas.getBoundingClientRect();
        const width = Math.max(240, rect.width || canvas.parentElement.clientWidth || 240);
        const ratio = Math.max(1, window.devicePixelRatio || 1);
        canvas.width = Math.round(width * ratio);
        canvas.height = Math.round(height * ratio);
        canvas.style.height = `${height}px`;
        const context = canvas.getContext('2d');
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, width, height);
        return { context, width, height };
    }

    function drawTrend() {
        const canvas = document.getElementById('inquiry-trend-chart');
        const setup = setupCanvas(canvas, 250);
        if (!setup) return;
        const { context: ctx, width, height } = setup;
        const rows = data.months || [];
        if (!rows.length) return;

        const pad = { top: 14, right: 18, bottom: 38, left: 34 };
        const plotWidth = width - pad.left - pad.right;
        const plotHeight = height - pad.top - pad.bottom;
        const maxValue = Math.max(...rows.map(row => Number(row.total) || 0));
        const maxY = Math.max(4, Math.ceil(maxValue / 4) * 4);

        ctx.font = `10px ${fontFamily}`;
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        for (let i = 0; i <= 4; i++) {
            const value = maxY - (maxY / 4) * i;
            const y = pad.top + (plotHeight / 4) * i;
            ctx.strokeStyle = '#e7e6de';
            ctx.setLineDash([3, 4]);
            ctx.beginPath();
            ctx.moveTo(pad.left, y);
            ctx.lineTo(width - pad.right, y);
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.fillStyle = '#879087';
            ctx.fillText(Number.isInteger(value) ? String(value) : value.toFixed(1), pad.left - 9, y);
        }

        const points = rows.map((row, index) => ({
            x: pad.left + (rows.length === 1 ? plotWidth / 2 : (plotWidth / (rows.length - 1)) * index),
            y: pad.top + plotHeight - ((Number(row.total) || 0) / maxY) * plotHeight,
            total: Number(row.total) || 0,
        }));

        const fill = ctx.createLinearGradient(0, pad.top, 0, height - pad.bottom);
        fill.addColorStop(0, 'rgba(215,110,76,.20)');
        fill.addColorStop(1, 'rgba(215,110,76,0)');
        ctx.beginPath();
        ctx.moveTo(points[0].x, height - pad.bottom);
        points.forEach(point => ctx.lineTo(point.x, point.y));
        ctx.lineTo(points[points.length - 1].x, height - pad.bottom);
        ctx.closePath();
        ctx.fillStyle = fill;
        ctx.fill();

        ctx.beginPath();
        points.forEach((point, index) => index === 0 ? ctx.moveTo(point.x, point.y) : ctx.lineTo(point.x, point.y));
        ctx.strokeStyle = '#d76e4c';
        ctx.lineWidth = 2.5;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.stroke();

        points.forEach(point => {
            ctx.beginPath();
            ctx.arc(point.x, point.y, 3.5, 0, Math.PI * 2);
            ctx.fillStyle = '#fff';
            ctx.fill();
            ctx.strokeStyle = '#d76e4c';
            ctx.lineWidth = 2;
            ctx.stroke();
        });

        ctx.fillStyle = '#7c857b';
        ctx.font = `10px ${fontFamily}`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        const labelIndexes = [...new Set([0, Math.round((rows.length - 1) / 3), Math.round(2 * (rows.length - 1) / 3), rows.length - 1])];
        labelIndexes.forEach(index => {
            const point = points[index];
            ctx.fillText(rows[index].label, point.x, height - pad.bottom + 13);
        });
    }

    function drawDestinations() {
        const canvas = document.getElementById('destination-chart');
        const legend = document.getElementById('destination-chart-legend');
        const rows = data.destinations || [];
        const setup = setupCanvas(canvas, 220);
        if (!setup || !rows.length) return;
        const { context: ctx, width, height } = setup;
        const values = rows.map(row => Math.max(0, Number(row.total) || 0));
        const total = values.reduce((sum, value) => sum + value, 0);
        const centerX = width / 2;
        const centerY = height / 2;
        const radius = Math.min(width, height) * .39;
        const thickness = Math.max(16, radius * .24);
        let angle = -Math.PI / 2;

        rows.forEach((row, index) => {
            const portion = total ? values[index] / total : 0;
            const next = angle + portion * Math.PI * 2;
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, angle, next - .018);
            ctx.strokeStyle = palette[index % palette.length];
            ctx.lineWidth = thickness;
            ctx.lineCap = 'butt';
            ctx.stroke();
            angle = next;

            const item = document.createElement('li');
            const swatch = document.createElement('i');
            const name = document.createElement('span');
            const count = document.createElement('b');
            swatch.style.backgroundColor = palette[index % palette.length];
            name.textContent = row.label;
            count.textContent = String(values[index]);
            item.append(swatch, name, count);
            legend.appendChild(item);
        });
    }

    function render() {
        drawTrend();
        drawDestinations();
    }

    render();
    let resizeTimer;
    window.addEventListener('resize', () => {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(render, 120);
    });
})();
