// Renders sidebar + topbar into the page
function renderNav(activePage) {
  const nav = [
    { id:'index.html', icon:'grid', label:'Dashboard' },
    { id:'farmers.html', icon:'users', label:'Farmers', badge:'2,418' },
    { id:'warehouse.html', icon:'home', label:'Warehouse' },
    { id:'receiving.html', icon:'arrow-down-circle', label:'Receiving', badge:'12', badgeClass:'warn' },
    { id:'drying.html', icon:'sun', label:'Drying' },
    { id:'milling.html', icon:'settings', label:'Milling' },
    { id:'grading.html', icon:'star', label:'Grading' },
    { id:'inventory.html', icon:'package', label:'Inventory' },
    { id:'loans.html', icon:'credit-card', label:'Loans', badge:'7', badgeClass:'warn' },
    { id:'buyers.html', icon:'briefcase', label:'Buyers' },
    { id:'sales.html', icon:'shopping-cart', label:'Sales' },
    { id:'accounting.html', icon:'dollar-sign', label:'Accounting' },
    { id:'reports.html', icon:'bar-chart-2', label:'Reports' },
    { id:'settings.html', icon:'sliders', label:'Settings' },
  ];

  const icons = {
    'grid':'<polyline points="3 3 10 3 10 10 3 10 3 3"/><polyline points="14 3 21 3 21 10 14 10 14 3"/><polyline points="14 14 21 14 21 21 14 21 14 14"/><polyline points="3 14 10 14 10 21 3 21 3 14"/>',
    'users':'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'home':'<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
    'arrow-down-circle':'<circle cx="12" cy="12" r="10"/><polyline points="8 12 12 16 16 12"/><line x1="12" y1="8" x2="12" y2="16"/>',
    'sun':'<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>',
    'settings':'<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    'star':'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
    'package':'<line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
    'credit-card':'<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
    'briefcase':'<rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    'shopping-cart':'<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
    'dollar-sign':'<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6"/>',
    'bar-chart-2':'<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
    'sliders':'<line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>',
    'bell':'<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
    'search':'<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
    'moon':'<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
    'sun2':'<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>',
    'menu':'<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
  };

  function svg(name) {
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icons[name]||''}</svg>`;
  }

  const sidebarHTML = `
<div id="sidebar" class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-mark">${svg('star')}</div>
    <div>
      <div class="logo-text">AgroVault</div>
      <div class="logo-sub">ERP Platform</div>
    </div>
  </div>
  <div style="flex:1;overflow-y:auto;padding-bottom:8px;">
    <div class="nav-section">
      <div class="nav-label">Main</div>
      ${nav.slice(0,2).map(n=>`<a href="${n.id}" class="nav-item${activePage===n.id?' active':''}" data-page="${n.id}">${svg(n.icon)}<span>${n.label}</span>${n.badge?`<span class="nav-badge ${n.badgeClass||''}">${n.badge}</span>`:''}</a>`).join('')}
    </div>
    <div class="nav-section">
      <div class="nav-label">Operations</div>
      ${nav.slice(2,8).map(n=>`<a href="${n.id}" class="nav-item${activePage===n.id?' active':''}" data-page="${n.id}">${svg(n.icon)}<span>${n.label}</span>${n.badge?`<span class="nav-badge ${n.badgeClass||''}">${n.badge}</span>`:''}</a>`).join('')}
    </div>
    <div class="nav-section">
      <div class="nav-label">Finance</div>
      ${nav.slice(8,12).map(n=>`<a href="${n.id}" class="nav-item${activePage===n.id?' active':''}" data-page="${n.id}">${svg(n.icon)}<span>${n.label}</span>${n.badge?`<span class="nav-badge ${n.badgeClass||''}">${n.badge}</span>`:''}</a>`).join('')}
    </div>
    <div class="nav-section">
      <div class="nav-label">System</div>
      ${nav.slice(12).map(n=>`<a href="${n.id}" class="nav-item${activePage===n.id?' active':''}" data-page="${n.id}">${svg(n.icon)}<span>${n.label}</span></a>`).join('')}
    </div>
  </div>
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">JM</div>
      <div style="flex:1;min-width:0">
        <div class="user-name">James Makori</div>
        <div class="user-role">Warehouse Manager</div>
      </div>
      <svg style="width:16px;height:16px;color:var(--text3)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
    </div>
  </div>
</div>
<div id="sidebarOverlay" class="sidebar-overlay" style="display:none"></div>`;

  const topbarHTML = `
<div class="topbar">
  <button id="menuBtn" class="icon-btn lg:hidden" style="display:none">
    ${svg('menu')}
  </button>
  <div class="topbar-search">
    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icons['search']}</svg>
    <input type="text" placeholder="Search farmers, batches, invoices…" onkeyup="handleSearch(this.value)"/>
  </div>
  <div class="topbar-actions">
    <button class="icon-btn" title="Notifications" onclick="showToast('3 pending approvals require attention','warning')">
      ${svg('bell')}
      <span class="badge"></span>
    </button>
    <button class="icon-btn" title="Toggle theme" onclick="toggleTheme()">
      <svg id="themeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icons['moon']}</svg>
    </button>
    <div style="width:1px;height:24px;background:var(--border);margin:0 4px"></div>
    <div class="user-avatar" style="cursor:pointer" onclick="showToast('Profile settings coming soon','info')">JM</div>
  </div>
</div>`;

  const navMount = document.getElementById('nav-mount');
  const topMount = document.getElementById('top-mount');
  if (navMount) navMount.innerHTML = sidebarHTML;
  if (topMount) topMount.innerHTML = topbarHTML;

  // Wire menu button after render
  const menuBtn = document.getElementById('menuBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (menuBtn) {
    menuBtn.style.display = '';
    menuBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
    });
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.style.display = 'none';
    });
  }

  // Theme icon sync
  function syncThemeIcon() {
    const el = document.getElementById('themeIcon');
    if (el) el.innerHTML = document.documentElement.classList.contains('dark') ? icons['sun2'] : icons['moon'];
  }
  syncThemeIcon();
  window._origToggle = window.toggleTheme;
  window.toggleTheme = function() { window._origToggle(); syncThemeIcon(); };
}

function handleSearch(val) {
  if (val.length > 2) showToast(`Searching for "${val}"…`, 'info');
}
