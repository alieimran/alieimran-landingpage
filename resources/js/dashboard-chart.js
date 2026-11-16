// Traffic chart for the admin dashboard. Registered before Alpine.start().
// Single series (emerald) on a recessive grid; hover shows an exact value, and
// the previous period is drawn as a dashed gray line for context.
export default function registerDashboardChart(Alpine) {
    Alpine.data('trafficChart', (series) => ({
        series,
        range: 14,
        metric: 'views',
        hover: null,
        w: 640,
        h: 220,
        pad: { l: 36, r: 12, t: 12, b: 26 },
        metrics: { views: 'Page views', visitors: 'Visitors', clicks: 'Link clicks' },

        get current() { return this.series.slice(this.series.length - this.range); },
        get previous() { return this.series.slice(this.series.length - this.range * 2, this.series.length - this.range); },
        get values() { return this.current.map((d) => d[this.metric]); },
        get prevValues() { return this.previous.map((d) => d[this.metric]); },
        get total() { return this.values.reduce((a, b) => a + b, 0); },
        get prevTotal() { return this.prevValues.reduce((a, b) => a + b, 0); },
        get delta() {
            if (!this.prevTotal) return this.total ? { text: 'new', dir: 'up' } : { text: 'no change', dir: 'flat' };
            const pct = Math.round(((this.total - this.prevTotal) / this.prevTotal) * 100);
            return { text: (pct > 0 ? '+' : '') + pct + '%', dir: pct > 0 ? 'up' : pct < 0 ? 'down' : 'flat' };
        },
        get peak() {
            const max = Math.max(...this.values, 0);
            const i = this.values.indexOf(max);
            return max ? { value: max, label: this.current[i].label } : null;
        },
        get top() {
            const m = Math.max(...this.values, ...this.prevValues, 1);
            const step = Math.pow(10, Math.floor(Math.log10(m)));
            const nice = [1, 2, 2.5, 5, 10].map((f) => f * step).find((v) => v >= m) || m;
            return nice;
        },
        get ticks() { return [0, 0.5, 1].map((f) => Math.round(this.top * f)); },
        x(i, n = this.values.length) {
            const inner = this.w - this.pad.l - this.pad.r;
            return this.pad.l + (n <= 1 ? inner / 2 : (inner * i) / (n - 1));
        },
        y(v) {
            const inner = this.h - this.pad.t - this.pad.b;
            return this.pad.t + inner - (v / this.top) * inner;
        },
        line(vals) { return vals.map((v, i) => (i ? 'L' : 'M') + this.x(i, vals.length).toFixed(1) + ' ' + this.y(v).toFixed(1)).join(' '); },
        get linePath() { return this.line(this.values); },
        get prevPath() { return this.line(this.prevValues); },
        get areaPath() {
            const n = this.values.length;
            return this.linePath + ' L' + this.x(n - 1).toFixed(1) + ' ' + this.y(0) + ' L' + this.x(0).toFixed(1) + ' ' + this.y(0) + ' Z';
        },
        get xLabels() {
            // Evenly spaced labels anchored on the latest day (at most 8 slots in the template).
            const n = this.current.length;
            const every = n > 20 ? 7 : n > 10 ? 4 : 2;
            const idx = [];
            for (let i = n - 1; i >= 0; i -= every) idx.unshift(i);
            return idx.map((i) => ({ i, text: this.current[i].label.replace(/^\w+ /, '') }));
        },
        move(e) {
            const box = e.currentTarget.getBoundingClientRect();
            const px = ((e.clientX - box.left) / box.width) * this.w;
            const n = this.values.length;
            const inner = this.w - this.pad.l - this.pad.r;
            this.hover = Math.max(0, Math.min(n - 1, Math.round(((px - this.pad.l) / inner) * (n - 1))));
        },
        key(e) {
            const n = this.values.length;
            if (e.key === 'ArrowRight') this.hover = Math.min(n - 1, (this.hover ?? -1) + 1);
            if (e.key === 'ArrowLeft') this.hover = Math.max(0, (this.hover ?? n) - 1);
            if (e.key === 'Escape') this.hover = null;
        },
        get tip() {
            if (this.hover === null) return null;
            const d = this.current[this.hover];
            const p = this.previous[this.hover];
            return { label: d.label, value: d[this.metric], prev: p ? p[this.metric] : null, left: (this.x(this.hover) / this.w) * 100 };
        },
    }));
}
