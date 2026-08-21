// AgroVault ERP — shared JS utilities, charts, navigation
// ────────────────────────────────────────────────────────

/* ── THEME ── */
function initTheme() {
  if (localStorage.getItem('av_theme') === 'dark') {
    document.documentElement.classList.add('dark');
  }
}
function toggleTheme() {
  const isDark = document.documentElement.classList.toggle('dark');
  localStorage.setItem('av_theme', isDark ? 'dark' : 'light');
  const icon = document.getElementById('themeIcon');
  if (icon) icon.textContent = isDark ? '☀️' : '🌙';
}

/* ── SIDEBAR ── */
function initSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const menuBtn = document.getElementById('menuBtn');
  if (menuBtn) menuBtn.addEventListener('click', () => toggleSidebar(sidebar, overlay));
  if (overlay) overlay.addEventListener('click', () => closeSidebar(sidebar, overlay));

  // Mark active nav
  const path = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-item').forEach(el => {
    if (el.dataset.page === path) el.classList.add('active');
  });
}
function toggleSidebar(sb, ov) {
  sb.classList.toggle('open'); ov.classList.toggle('active');
}
function closeSidebar(sb, ov) {
  sb.classList.remove('open'); ov.classList.remove('active');
}

/* ── CHART HELPERS (Canvas-based, no dependencies) ── */
function drawLineChart(canvasId, labels, datasets, opts = {}) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width = canvas.offsetWidth * window.devicePixelRatio;
  const H = canvas.height = canvas.offsetHeight * window.devicePixelRatio;
  canvas.style.width = canvas.offsetWidth + 'px';
  canvas.style.height = canvas.offsetHeight + 'px';
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  const cW = canvas.offsetWidth, cH = canvas.offsetHeight;
  const pad = { top: 20, right: 20, bottom: 44, left: 52 };
  const plotW = cW - pad.left - pad.right;
  const plotH = cH - pad.top - pad.bottom;
  const isDark = document.documentElement.classList.contains('dark');
  const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
  const textColor = isDark ? '#64748b' : '#94a3b8';

  // Compute scale
  let allVals = datasets.flatMap(d => d.data);
  const minV = Math.min(...allVals) * 0.85, maxV = Math.max(...allVals) * 1.1;
  const range = maxV - minV || 1;
  const toY = v => pad.top + plotH - ((v - minV) / range) * plotH;
  const toX = i => pad.left + (i / (labels.length - 1)) * plotW;

  ctx.clearRect(0, 0, cW, cH);

  // Grid lines
  const gridCount = 5;
  ctx.textAlign = 'right'; ctx.font = `11px Inter,sans-serif`; ctx.fillStyle = textColor;
  for (let i = 0; i <= gridCount; i++) {
    const v = minV + (range / gridCount) * i;
    const y = toY(v);
    ctx.strokeStyle = gridColor; ctx.lineWidth = 1; ctx.setLineDash([4, 4]);
    ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(pad.left + plotW, y); ctx.stroke();
    ctx.fillText(opts.yFormat ? opts.yFormat(v) : Math.round(v), pad.left - 8, y + 4);
  }
  ctx.setLineDash([]);

  // X labels
  ctx.textAlign = 'center'; ctx.fillStyle = textColor;
  labels.forEach((l, i) => {
    if (labels.length > 8 && i % 2 !== 0) return;
    ctx.fillText(l, toX(i), cH - 10);
  });

  // Datasets
  datasets.forEach(ds => {
    const color = ds.color || '#10b981';
    // Gradient fill
    const grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + plotH);
    grad.addColorStop(0, color + '28');
    grad.addColorStop(1, color + '00');
    ctx.beginPath();
    ds.data.forEach((v, i) => {
      const x = toX(i), y = toY(v);
      i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.lineTo(toX(ds.data.length - 1), pad.top + plotH);
    ctx.lineTo(toX(0), pad.top + plotH);
    ctx.closePath();
    ctx.fillStyle = grad; ctx.fill();

    // Line
    ctx.beginPath(); ctx.strokeStyle = color; ctx.lineWidth = 2.5; ctx.lineJoin = 'round'; ctx.lineCap = 'round';
    ds.data.forEach((v, i) => {
      const x = toX(i), y = toY(v);
      i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.stroke();

    // Dots
    ds.data.forEach((v, i) => {
      const x = toX(i), y = toY(v);
      ctx.beginPath(); ctx.arc(x, y, 4, 0, Math.PI * 2);
      ctx.fillStyle = color; ctx.fill();
      ctx.strokeStyle = isDark ? '#111827' : '#fff'; ctx.lineWidth = 2; ctx.stroke();
    });
  });
}

function drawBarChart(canvasId, labels, values, colors) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.offsetWidth, H = canvas.offsetHeight;
  canvas.width = W * window.devicePixelRatio; canvas.height = H * window.devicePixelRatio;
  canvas.style.width = W + 'px'; canvas.style.height = H + 'px';
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  const pad = { top: 16, right: 16, bottom: 40, left: 48 };
  const plotW = W - pad.left - pad.right, plotH = H - pad.top - pad.bottom;
  const isDark = document.documentElement.classList.contains('dark');
  const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
  const textColor = isDark ? '#64748b' : '#94a3b8';
  const maxV = Math.max(...values) * 1.15 || 1;
  const barW = Math.min(32, (plotW / values.length) * 0.55);
  const gap = plotW / values.length;
  ctx.clearRect(0, 0, W, H);

  // Grid
  for (let i = 0; i <= 4; i++) {
    const y = pad.top + plotH * (1 - i / 4);
    ctx.strokeStyle = gridColor; ctx.lineWidth = 1; ctx.setLineDash([4, 4]);
    ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(pad.left + plotW, y); ctx.stroke();
    ctx.setLineDash([]); ctx.fillStyle = textColor; ctx.textAlign = 'right'; ctx.font = '11px Inter,sans-serif';
    ctx.fillText(Math.round(maxV * i / 4), pad.left - 6, y + 4);
  }

  // Bars
  values.forEach((v, i) => {
    const x = pad.left + gap * i + gap / 2 - barW / 2;
    const barH = (v / maxV) * plotH;
    const y = pad.top + plotH - barH;
    const color = Array.isArray(colors) ? colors[i % colors.length] : (colors || '#10b981');
    const grad = ctx.createLinearGradient(0, y, 0, y + barH);
    grad.addColorStop(0, color); grad.addColorStop(1, color + 'aa');
    ctx.beginPath();
    const r = Math.min(6, barW / 3);
    ctx.moveTo(x + r, y); ctx.lineTo(x + barW - r, y);
    ctx.quadraticCurveTo(x + barW, y, x + barW, y + r);
    ctx.lineTo(x + barW, y + barH); ctx.lineTo(x, y + barH); ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y); ctx.closePath();
    ctx.fillStyle = grad; ctx.fill();
    ctx.fillStyle = textColor; ctx.textAlign = 'center'; ctx.font = '11px Inter,sans-serif';
    ctx.fillText(labels[i], pad.left + gap * i + gap / 2, H - 10);
  });
}

function drawDonutChart(canvasId, data, colors) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const S = Math.min(canvas.offsetWidth, canvas.offsetHeight);
  canvas.width = S * window.devicePixelRatio; canvas.height = S * window.devicePixelRatio;
  canvas.style.width = S + 'px'; canvas.style.height = S + 'px';
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  const cx = S / 2, cy = S / 2, outerR = S * 0.42, innerR = S * 0.26;
  const total = data.reduce((a, d) => a + d.value, 0);
  let angle = -Math.PI / 2;
  data.forEach((d, i) => {
    const slice = (d.value / total) * Math.PI * 2;
    ctx.beginPath(); ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, outerR, angle, angle + slice);
    ctx.closePath(); ctx.fillStyle = colors[i % colors.length]; ctx.fill();
    ctx.beginPath(); ctx.arc(cx, cy, innerR, 0, Math.PI * 2);
    ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#111827' : '#fff'; ctx.fill();
    angle += slice;
  });
}

/* ── PROGRESS BARS ── */
function animateProgress() {
  document.querySelectorAll('.progress-fill[data-width]').forEach(el => {
    setTimeout(() => { el.style.width = el.dataset.width; }, 200);
  });
}

/* ── NOTIFICATIONS ── */
function showToast(message, type = 'success') {
  const colors = { success:'#10b981', error:'#ef4444', warning:'#f59e0b', info:'#6366f1' };
  const toast = document.createElement('div');
  toast.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;padding:14px 20px;border-radius:12px;background:${colors[type]};color:white;font-size:14px;font-weight:600;box-shadow:0 8px 30px rgba(0,0,0,.25);transform:translateY(80px);transition:transform .3s cubic-bezier(.34,1.56,.64,1);display:flex;align-items:center;gap:10px;font-family:Inter,sans-serif;max-width:360px;`;
  toast.innerHTML = `<span>${message}</span>`;
  document.body.appendChild(toast);
  requestAnimationFrame(() => { toast.style.transform = 'translateY(0)'; });
  setTimeout(() => { toast.style.transform = 'translateY(80px)'; setTimeout(() => toast.remove(), 300); }, 3000);
}

/* ── INIT ── */
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSidebar();
  animateProgress();
});
