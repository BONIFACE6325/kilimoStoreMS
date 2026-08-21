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
  // Mark active nav
  const path = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-item').forEach(el => {
    if (el.dataset.page === path) {
      el.classList.add('active');
    } else {
      el.classList.remove('active');
    }
  });
}


/* ── CHART HELPERS (Canvas-based, no dependencies) ── */

function getChartTooltip(canvas) {
  let tooltip = canvas.parentElement.querySelector('.av-chart-tooltip');
  if (!tooltip) {
    if (getComputedStyle(canvas.parentElement).position === 'static') {
      canvas.parentElement.style.position = 'relative';
    }
    tooltip = document.createElement('div');
    tooltip.className = 'av-chart-tooltip';
    tooltip.style.cssText = 'position:absolute;pointer-events:none;background:var(--surface);border:1px solid var(--border);padding:10px 14px;border-radius:8px;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);font-size:12.5px;z-index:20;display:none;white-space:nowrap;transition:all 0.15s ease-out;opacity:0;transform:translateY(5px);';
    canvas.parentElement.appendChild(tooltip);
  }
  return tooltip;
}

function showChartTooltip(tooltip, html, x, y, cW, cH) {
  tooltip.innerHTML = html;
  tooltip.style.display = 'block';
  // position tooltip safely within bounds
  requestAnimationFrame(() => {
    let ttX = x + 15;
    let ttY = y - tooltip.offsetHeight / 2;
    if (ttX + tooltip.offsetWidth > cW - 10) ttX = x - tooltip.offsetWidth - 15;
    if (ttY < 10) ttY = 10;
    if (ttY + tooltip.offsetHeight > cH - 10) ttY = cH - tooltip.offsetHeight - 10;
    
    tooltip.style.left = ttX + 'px';
    tooltip.style.top = ttY + 'px';
    tooltip.style.opacity = '1';
    tooltip.style.transform = 'translateY(0)';
  });
}

function hideChartTooltip(tooltip) {
  tooltip.style.opacity = '0';
  tooltip.style.transform = 'translateY(5px)';
}

function drawLineChart(canvasId, labels, datasets, opts = {}) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  
  // Clear any existing old event listeners by cloning
  const newCanvas = canvas.cloneNode(true);
  canvas.parentNode.replaceChild(newCanvas, canvas);
  const activeCanvas = newCanvas;
  const ctxActive = activeCanvas.getContext('2d');

  const W = activeCanvas.width = activeCanvas.offsetWidth * window.devicePixelRatio;
  const H = activeCanvas.height = activeCanvas.offsetHeight * window.devicePixelRatio;
  activeCanvas.style.width = activeCanvas.offsetWidth + 'px';
  activeCanvas.style.height = activeCanvas.offsetHeight + 'px';
  ctxActive.scale(window.devicePixelRatio, window.devicePixelRatio);
  
  const cW = activeCanvas.offsetWidth, cH = activeCanvas.offsetHeight;
  const pad = { top: 20, right: 20, bottom: 44, left: opts.yFormat ? 56 : 40 };
  const plotW = cW - pad.left - pad.right;
  const plotH = cH - pad.top - pad.bottom;
  const isDark = document.documentElement.classList.contains('dark');
  const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
  const textColor = isDark ? '#64748b' : '#94a3b8';
  const surfaceColor = isDark ? '#1e293b' : '#ffffff';

  let allVals = datasets.flatMap(d => d.data);
  const minV = Math.min(0, Math.min(...allVals) * 0.85); 
  const maxV = Math.max(...allVals) * 1.1 || 1;
  const range = maxV - minV;
  const toY = v => pad.top + plotH - ((v - minV) / range) * plotH;
  const toX = i => pad.left + (i / (labels.length - 1)) * plotW;

  function render(hoverIndex = -1) {
    ctxActive.clearRect(0, 0, cW, cH);
    
    // Grid lines
    const gridCount = 5;
    ctxActive.textAlign = 'right'; ctxActive.font = `11px Inter,sans-serif`; ctxActive.fillStyle = textColor;
    for (let i = 0; i <= gridCount; i++) {
      const v = minV + (range / gridCount) * i;
      const y = toY(v);
      ctxActive.strokeStyle = gridColor; ctxActive.lineWidth = 1; ctxActive.setLineDash([4, 4]);
      ctxActive.beginPath(); ctxActive.moveTo(pad.left, y); ctxActive.lineTo(pad.left + plotW, y); ctxActive.stroke();
      ctxActive.fillText(opts.yFormat ? opts.yFormat(v) : Math.round(v), pad.left - 8, y + 4);
    }
    ctxActive.setLineDash([]);

    // X labels
    ctxActive.textAlign = 'center'; ctxActive.fillStyle = textColor;
    labels.forEach((l, i) => {
      if (labels.length > 8 && i % 2 !== 0 && hoverIndex !== i) return;
      ctxActive.fillStyle = hoverIndex === i ? (isDark ? '#e2e8f0' : '#1e293b') : textColor;
      ctxActive.font = hoverIndex === i ? `600 11px Inter,sans-serif` : `11px Inter,sans-serif`;
      ctxActive.fillText(l, toX(i), cH - 10);
    });

    // Draw datasets
    datasets.forEach(ds => {
      const color = ds.color || '#10b981';
      // Gradient
      const grad = ctxActive.createLinearGradient(0, pad.top, 0, pad.top + plotH);
      grad.addColorStop(0, color + '33');
      grad.addColorStop(1, color + '00');
      ctxActive.beginPath();
      ds.data.forEach((v, i) => {
        const x = toX(i), y = toY(v);
        i === 0 ? ctxActive.moveTo(x, y) : ctxActive.lineTo(x, y);
      });
      ctxActive.lineTo(toX(ds.data.length - 1), pad.top + plotH);
      ctxActive.lineTo(toX(0), pad.top + plotH);
      ctxActive.closePath();
      ctxActive.fillStyle = grad; ctxActive.fill();

      // Line
      ctxActive.beginPath(); ctxActive.strokeStyle = color; ctxActive.lineWidth = 2.5; ctxActive.lineJoin = 'round'; ctxActive.lineCap = 'round';
      ds.data.forEach((v, i) => {
        const x = toX(i), y = toY(v);
        i === 0 ? ctxActive.moveTo(x, y) : ctxActive.lineTo(x, y);
      });
      ctxActive.stroke();

      // Dots
      ds.data.forEach((v, i) => {
        const x = toX(i), y = toY(v);
        const isHovered = i === hoverIndex;
        ctxActive.beginPath(); ctxActive.arc(x, y, isHovered ? 6 : 4, 0, Math.PI * 2);
        ctxActive.fillStyle = isHovered ? surfaceColor : color; ctxActive.fill();
        ctxActive.strokeStyle = color; ctxActive.lineWidth = isHovered ? 3 : 2; ctxActive.stroke();
      });
    });
    
    // Hover vertical line
    if (hoverIndex >= 0) {
      const hx = toX(hoverIndex);
      ctxActive.beginPath(); ctxActive.strokeStyle = isDark ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.15)'; 
      ctxActive.lineWidth = 1; ctxActive.setLineDash([4, 4]);
      ctxActive.moveTo(hx, pad.top); ctxActive.lineTo(hx, pad.top + plotH); ctxActive.stroke();
      ctxActive.setLineDash([]);
    }
  }
  
  render();

  const tooltip = getChartTooltip(activeCanvas);
  
  activeCanvas.addEventListener('mousemove', (e) => {
    const rect = activeCanvas.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;
    
    let closestIdx = -1;
    let minDist = Infinity;
    labels.forEach((l, i) => {
      const dist = Math.abs(toX(i) - mouseX);
      if (dist < minDist) { minDist = dist; closestIdx = i; }
    });
    
    if (minDist < (plotW / labels.length)) {
      render(closestIdx);
      let html = `<div style="font-weight:700;margin-bottom:8px;color:var(--text);font-size:13px">${labels[closestIdx]}</div>`;
      datasets.forEach(ds => {
        const val = ds.data[closestIdx];
        const displayVal = opts.yFormat ? opts.yFormat(val) : Math.round(val);
        html += `<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px"><div style="width:10px;height:10px;border-radius:50%;background:${ds.color}"></div><span style="color:var(--text2);font-weight:500">${displayVal}</span></div>`;
      });
      showChartTooltip(tooltip, html, toX(closestIdx), mouseY, cW, cH);
    } else {
      render(-1);
      hideChartTooltip(tooltip);
    }
  });

  activeCanvas.addEventListener('mouseleave', () => {
    render(-1);
    hideChartTooltip(tooltip);
  });
}

function drawBarChart(canvasId, labels, values, colors) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const newCanvas = canvas.cloneNode(true);
  canvas.parentNode.replaceChild(newCanvas, canvas);
  const activeCanvas = newCanvas;
  const ctx = activeCanvas.getContext('2d');
  
  const W = activeCanvas.offsetWidth, H = activeCanvas.offsetHeight;
  activeCanvas.width = W * window.devicePixelRatio; activeCanvas.height = H * window.devicePixelRatio;
  activeCanvas.style.width = W + 'px'; activeCanvas.style.height = H + 'px';
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  
  const pad = { top: 16, right: 16, bottom: 40, left: 48 };
  const plotW = W - pad.left - pad.right, plotH = H - pad.top - pad.bottom;
  const isDark = document.documentElement.classList.contains('dark');
  const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
  const textColor = isDark ? '#64748b' : '#94a3b8';
  const maxV = Math.max(...values) * 1.15 || 1;
  const barW = Math.min(32, (plotW / values.length) * 0.55);
  const gap = plotW / values.length;

  function render(hoverIndex = -1) {
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
      
      const isHovered = i === hoverIndex;
      const grad = ctx.createLinearGradient(0, y, 0, y + barH);
      grad.addColorStop(0, isHovered ? color + 'ee' : color); 
      grad.addColorStop(1, isHovered ? color + 'bb' : color + 'aa');
      
      ctx.beginPath();
      const r = Math.min(6, barW / 3);
      ctx.moveTo(x + r, y); ctx.lineTo(x + barW - r, y);
      ctx.quadraticCurveTo(x + barW, y, x + barW, y + r);
      ctx.lineTo(x + barW, y + barH); ctx.lineTo(x, y + barH); ctx.lineTo(x, y + r);
      ctx.quadraticCurveTo(x, y, x + r, y); ctx.closePath();
      
      if(isHovered) {
        ctx.shadowColor = color; ctx.shadowBlur = 10;
      } else {
        ctx.shadowBlur = 0;
      }
      
      ctx.fillStyle = grad; ctx.fill();
      ctx.shadowBlur = 0;
      
      ctx.fillStyle = isHovered ? (isDark ? '#e2e8f0' : '#1e293b') : textColor; 
      ctx.textAlign = 'center'; 
      ctx.font = isHovered ? '600 11px Inter,sans-serif' : '11px Inter,sans-serif';
      ctx.fillText(labels[i], pad.left + gap * i + gap / 2, H - 10);
    });
  }

  render();
  const tooltip = getChartTooltip(activeCanvas);
  
  activeCanvas.addEventListener('mousemove', (e) => {
    const rect = activeCanvas.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;
    
    let closestIdx = -1;
    let minDist = Infinity;
    values.forEach((v, i) => {
      const centerX = pad.left + gap * i + gap / 2;
      const dist = Math.abs(centerX - mouseX);
      if (dist < minDist) { minDist = dist; closestIdx = i; }
    });
    
    if (minDist < gap / 2) {
      render(closestIdx);
      const color = Array.isArray(colors) ? colors[closestIdx % colors.length] : (colors || '#10b981');
      let html = `<div style="font-weight:700;margin-bottom:6px;color:var(--text);font-size:13px">${labels[closestIdx]}</div>`;
      html += `<div style="display:flex;align-items:center;gap:8px"><div style="width:10px;height:10px;border-radius:3px;background:${color}"></div><span style="color:var(--text2);font-weight:500">${values[closestIdx].toLocaleString()}</span></div>`;
      showChartTooltip(tooltip, html, mouseX, mouseY, W, H);
    } else {
      render(-1);
      hideChartTooltip(tooltip);
    }
  });

  activeCanvas.addEventListener('mouseleave', () => {
    render(-1);
    hideChartTooltip(tooltip);
  });
}

function drawDonutChart(canvasId, data, colors) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const newCanvas = canvas.cloneNode(true);
  canvas.parentNode.replaceChild(newCanvas, canvas);
  const activeCanvas = newCanvas;
  const ctx = activeCanvas.getContext('2d');
  
  const S = Math.min(activeCanvas.offsetWidth, activeCanvas.offsetHeight);
  activeCanvas.width = S * window.devicePixelRatio; activeCanvas.height = S * window.devicePixelRatio;
  activeCanvas.style.width = S + 'px'; activeCanvas.style.height = S + 'px';
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  
  const cx = S / 2, cy = S / 2, outerR = S * 0.42, innerR = S * 0.26;
  const total = data.reduce((a, d) => a + d.value, 0);
  const isDark = document.documentElement.classList.contains('dark');
  
  let slices = [];
  let angle = -Math.PI / 2;
  data.forEach((d, i) => {
    const sliceAngle = (d.value / total) * Math.PI * 2;
    slices.push({ start: angle, end: angle + sliceAngle, data: d, color: colors[i % colors.length] });
    angle += sliceAngle;
  });

  function render(hoverIndex = -1) {
    ctx.clearRect(0, 0, S, S);
    slices.forEach((s, i) => {
      const isHovered = i === hoverIndex;
      const drawOuter = isHovered ? outerR * 1.05 : outerR;
      
      ctx.beginPath(); ctx.moveTo(cx, cy);
      ctx.arc(cx, cy, drawOuter, s.start, s.end);
      ctx.closePath(); 
      ctx.fillStyle = s.color; 
      
      if(isHovered) {
        ctx.shadowColor = s.color; ctx.shadowBlur = 8;
      } else {
        ctx.shadowBlur = 0;
      }
      ctx.fill();
      ctx.shadowBlur = 0;
    });
    
    // Inner circle cutout
    ctx.beginPath(); ctx.arc(cx, cy, innerR, 0, Math.PI * 2);
    ctx.fillStyle = isDark ? '#111827' : '#ffffff'; ctx.fill();
    
    // Add text in center if hovered
    if (hoverIndex >= 0) {
      const s = slices[hoverIndex];
      ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
      ctx.fillStyle = isDark ? '#f8fafc' : '#0f172a';
      ctx.font = '700 13px Inter,sans-serif';
      
      let valText = s.data.value.toLocaleString();
      if(s.data.value > 1000) valText = (s.data.value/1000).toFixed(1) + 'k';
      
      ctx.fillText(valText, cx, cy - 6);
      
      ctx.fillStyle = isDark ? '#94a3b8' : '#64748b';
      ctx.font = '500 10px Inter,sans-serif';
      ctx.fillText(Math.round((s.data.value/total)*100) + '%', cx, cy + 8);
    }
  }

  render();
  const tooltip = getChartTooltip(activeCanvas);

  activeCanvas.addEventListener('mousemove', (e) => {
    const rect = activeCanvas.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;
    const dx = mouseX - cx;
    const dy = mouseY - cy;
    const dist = Math.sqrt(dx*dx + dy*dy);
    
    if (dist >= innerR && dist <= outerR * 1.05) {
      let mouseAngle = Math.atan2(dy, dx);
      if (mouseAngle < -Math.PI/2) mouseAngle += 2*Math.PI;
      
      // Need to normalize for finding correct slice
      let normAngle = mouseAngle;
      
      let foundIdx = -1;
      slices.forEach((s, i) => {
        // Adjust for wrapped angles crossing -PI/2 line
        let sEnd = s.end;
        let cAngle = normAngle;
        if(sEnd > Math.PI * 1.5 && cAngle < 0) cAngle += 2*Math.PI;
        
        if (cAngle >= s.start && cAngle <= s.end) foundIdx = i;
      });
      
      if (foundIdx >= 0) {
        render(foundIdx);
        const s = slices[foundIdx];
        let html = `<div style="display:flex;align-items:center;gap:8px"><div style="width:10px;height:10px;border-radius:50%;background:${s.color}"></div><span style="font-weight:600;color:var(--text);font-size:13px">${s.data.label}</span></div>`;
        html += `<div style="margin-top:6px;color:var(--text2);font-weight:500;padding-left:18px">${s.data.value.toLocaleString()}</div>`;
        showChartTooltip(tooltip, html, mouseX, mouseY, S, S);
      } else {
        render(-1);
        hideChartTooltip(tooltip);
      }
    } else {
      render(-1);
      hideChartTooltip(tooltip);
    }
  });

  activeCanvas.addEventListener('mouseleave', () => {
    render(-1);
    hideChartTooltip(tooltip);
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
