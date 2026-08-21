// Intercept global window & document listeners to clean them up during SPA navigation
const pageListeners = [];
const originalAddEventListener = EventTarget.prototype.addEventListener;

EventTarget.prototype.addEventListener = function(type, listener, options) {
  if (this === document || this === window) {
    pageListeners.push({ target: this, type, listener, options });
  }
  return originalAddEventListener.call(this, type, listener, options);
};

function clearPageListeners() {
  pageListeners.forEach(({ target, type, listener, options }) => {
    target.removeEventListener(type, listener, options);
  });
  pageListeners.length = 0;
}

// Global language state
let currentLang = localStorage.getItem('lang') || 'en';

function toggleLanguage() {
  currentLang = currentLang === 'en' ? 'sw' : 'en';
  localStorage.setItem('lang', currentLang);
  location.reload();
}

// SPA Routing Engine
async function loadPage(url, push = true) {
  try {
    // Show a premium loading progress bar at the top
    let progress = document.getElementById('spa-loader');
    if (!progress) {
      progress = document.createElement('div');
      progress.id = 'spa-loader';
      progress.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:#10b981;z-index:99999;transition:width 0.4s ease;width:0%;box-shadow:0 0 10px rgba(16,185,129,0.5);';
      document.body.appendChild(progress);
    }
    progress.style.width = '30%';

    const res = await fetch(url);
    if (!res.ok) throw new Error(`Could not load ${url}`);
    progress.style.width = '70%';

    const htmlText = await res.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(htmlText, 'text/html');

    // Update the browser page title
    document.title = doc.title;

    // Disconnect the translation MutationObserver before swapping DOM
    if (window.observer) window.observer.disconnect();

    // Clean up old global window/document listeners
    clearPageListeners();

    // Swap document body content
    const fetchedBody = doc.body;
    
    // Find all script tags (even nested ones)
    const scripts = Array.from(fetchedBody.querySelectorAll('script'));
    // Remove them from fetchedBody so they don't get appended as inert nodes
    scripts.forEach(s => s.remove());

    // Clear and build the new body nodes
    document.body.innerHTML = '';
    document.body.appendChild(progress);

    // Transfer body classes & styles
    document.body.className = fetchedBody.className;
    if (fetchedBody.getAttribute('style')) {
      document.body.setAttribute('style', fetchedBody.getAttribute('style'));
    } else {
      document.body.removeAttribute('style');
    }

    // Append everything else
    while (fetchedBody.firstChild) {
      document.body.appendChild(fetchedBody.firstChild);
    }

    // Now run scripts
    for (const s of scripts) {
      const src = s.getAttribute('src');
      if (src && (src.includes('components.js') || src.includes('app.js'))) {
        // Already globally loaded, do not execute again
        continue;
      }
      const script = document.createElement('script');
      Array.from(s.attributes).forEach(attr => script.setAttribute(attr.name, attr.value));
      
      if (s.textContent) {
        // Expose function declarations to window and wrap in block scope to prevent let/const collisions
        const scriptText = s.textContent;
        const functionRegex = /function\s+([a-zA-Z0-9_]+)/g;
        let match;
        const functionsToExpose = [];
        while ((match = functionRegex.exec(scriptText)) !== null) {
          functionsToExpose.push(match[1]);
        }
        script.textContent = `{\n${scriptText}\n${functionsToExpose.map(f => `window.${f} = ${f};`).join('\n')}\n}`;
      }
      
      document.body.appendChild(script);
    }

    // Update history stack
    if (push) {
      history.pushState({ url }, '', url);
    }

    progress.style.width = '100%';
    setTimeout(() => {
      progress.style.width = '0%';
    }, 300);

    // Dispatch simulated DOMContentLoaded event to trigger table loads and scripts
    const dclEvent = new Event('DOMContentLoaded', { bubbles: true, cancelable: true });
    document.dispatchEvent(dclEvent);
    window.dispatchEvent(dclEvent);

  } catch (err) {
    console.error('SPA Route Error:', err);
    // Fallback to traditional redirect if fetch fails
    window.location.href = url;
  }
}

// Global click interceptor for SPA navigation
document.addEventListener('click', (e) => {
  const link = e.target.closest('a');
  if (link) {
    const href = link.getAttribute('href');
    // We only route internal html pages (excluding absolute paths, mailto, tel, anchors or downloads)
    if (
      href && 
      !href.startsWith('http') && 
      !href.startsWith('#') && 
      !href.startsWith('mailto:') && 
      !href.startsWith('tel:') && 
      !link.hasAttribute('download') &&
      href.endsWith('.html') &&
      href !== 'login.html' // Keep login as full reload to refresh all context
    ) {
      e.preventDefault();
      loadPage(href);
    }
  }
});

// Watch browser back & forward navigation
window.addEventListener('popstate', (e) => {
  if (e.state && e.state.url) {
    loadPage(e.state.url, false);
  } else {
    // Default fallback
    const path = window.location.pathname.split('/').pop() || 'index.html';
    loadPage(path, false);
  }
});

// Setup history state for the initial page load
if (!history.state) {
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  history.replaceState({ url: currentPath }, '', window.location.href);
}

// Renders sidebar + topbar into the page
function renderNav(activePage) {
  // English default sidebar labels
  const nav = [
    { id:'index.html', icon:'grid', label:'Dashboard' },
    { id:'farmers.html', icon:'users', label:'Farmers', badge:'2,418' },
    { id:'receiving.html', icon:'arrow-down-circle', label:'Receiving', badge:'12', badgeClass:'warn' },
    { id:'inventory.html', icon:'package', label:'Inventory' },
    { id:'services.html', icon:'tool', label:'Services' },
    { id:'loans.html', icon:'credit-card', label:'Loans', badge:'7', badgeClass:'warn' },
    { id:'buyers.html', icon:'briefcase', label:'Buyers' },
    { id:'sales.html', icon:'shopping-cart', label:'Sales' },
    { id:'accounting.html', icon:'dollar-sign', label:'Income & Expenses' },
    { id:'reports.html', icon:'bar-chart-2', label:'Reports' },
    { id:'settings.html', icon:'sliders', label:'Settings' },
  ];

  // Map Swahili labels if selected
  if (currentLang === 'sw') {
    nav[0].label = 'Mwanzo';
    nav[1].label = 'Wakulima';
    nav[2].label = 'Upokeaji';
    nav[3].label = 'Stoku';
    nav[4].label = 'Huduma';
    nav[5].label = 'Mikopo';
    nav[6].label = 'Wanunuzi';
    nav[7].label = 'Mauzo';
    nav[8].label = 'Mapato & Matumizi';
    nav[9].label = 'Ripoti';
    nav[10].label = 'Mipangilio';
  }

  const icons = {
    'tool':'<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
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
    'log-out':'<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
  };

  function svg(name) {
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icons[name]||''}</svg>`;
  }

  const mainLabel = currentLang === 'sw' ? 'Kuu' : 'Main';
  const opsLabel = currentLang === 'sw' ? 'Uendeshaji' : 'Operations';
  const finLabel = currentLang === 'sw' ? 'Fedha' : 'Finance';
  const sysLabel = currentLang === 'sw' ? 'Mfumo' : 'System';
  const managerLabel = currentLang === 'sw' ? 'Meneja wa Ghala' : 'Warehouse Manager';

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
      <div class="nav-label">${mainLabel}</div>
      ${nav.slice(0,2).map(n=>`<a href="${n.id}" class="nav-item${activePage===n.id?' active':''}" data-page="${n.id}">${svg(n.icon)}<span>${n.label}</span>${n.badge?`<span class="nav-badge ${n.badgeClass||''}">${n.badge}</span>`:''}</a>`).join('')}
    </div>
    <div class="nav-section">
      <div class="nav-label">${opsLabel}</div>
      ${nav.slice(2,6).map(n=>`<a href="${n.id}" class="nav-item${activePage===n.id?' active':''}" data-page="${n.id}">${svg(n.icon)}<span>${n.label}</span>${n.badge?`<span class="nav-badge ${n.badgeClass||''}">${n.badge}</span>`:''}</a>`).join('')}
    </div>
    <div class="nav-section">
      <div class="nav-label">${finLabel}</div>
      ${nav.slice(6,10).map(n=>`<a href="${n.id}" class="nav-item${activePage===n.id?' active':''}" data-page="${n.id}">${svg(n.icon)}<span>${n.label}</span>${n.badge?`<span class="nav-badge ${n.badgeClass||''}">${n.badge}</span>`:''}</a>`).join('')}
    </div>
    <div class="nav-section">
      <div class="nav-label">${sysLabel}</div>
      ${nav.slice(10).map(n=>`<a href="${n.id}" class="nav-item${activePage===n.id?' active':''}" data-page="${n.id}">${svg(n.icon)}<span>${n.label}</span></a>`).join('')}
    </div>
  </div>
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">JM</div>
      <div style="flex:1;min-width:0">
        <div class="user-name">James Makori</div>
        <div class="user-role">${managerLabel}</div>
      </div>
    </div>
  </div>
</div>
<div id="sidebarOverlay" class="sidebar-overlay" style="display:none"></div>`;

  const searchPlaceholder = currentLang === 'sw' ? 'Tafuta wakulima, shehena, ankara…' : 'Search farmers, batches, invoices…';
  const notifTitle = currentLang === 'sw' ? 'Aarifa' : 'Notifications';
  const themeTitle = currentLang === 'sw' ? 'Badili mandhari' : 'Toggle theme';
  const logoutTitle = currentLang === 'sw' ? 'Ondoka' : 'Logout';

  const topbarHTML = `
<div class="topbar">
  <button id="menuBtn" class="icon-btn">
    ${svg('menu')}
  </button>
  <div class="topbar-search">
    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icons['search']}</svg>
    <input type="text" placeholder="${searchPlaceholder}" onkeyup="handleSearch(this.value)"/>
  </div>
  <div class="topbar-actions">
    <button class="btn btn-secondary btn-sm" onclick="toggleLanguage()" style="font-weight:700;font-size:12px;padding:6px 12px;min-width:44px;color:var(--text);border:1px solid var(--border)">
      ${currentLang.toUpperCase()}
    </button>
    <button class="icon-btn" title="${notifTitle}" onclick="showToast(currentLang === 'sw' ? 'Maombi 3 ya mikopo yanahitaji idhini' : '3 pending approvals require attention','warning')">
      ${svg('bell')}
      <span class="topbar-badge">3</span>
    </button>
    <button class="icon-btn" title="${themeTitle}" onclick="toggleTheme()">
      <svg id="themeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icons['moon']}</svg>
    </button>
    <button class="icon-btn" title="${logoutTitle}" onclick="handleLogout()">
      ${svg('log-out')}
    </button>
    <div style="width:1px;height:24px;background:var(--border);margin:0 4px"></div>
    <div class="user-avatar" style="cursor:pointer" onclick="showToast(currentLang === 'sw' ? 'Mipangilio ya wasifu hivi karibuni' : 'Profile settings coming soon','info')">JM</div>
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
  if (!window._origToggle) {
    window._origToggle = window.toggleTheme;
    window.toggleTheme = function() {
      if (typeof window._origToggle === 'function') {
        window._origToggle();
      }
      syncThemeIcon();
    };
  }

  // Trigger DOM Translation
  translateDOM();
}

function handleSearch(val) {
  if (val.length > 2) {
    showToast(currentLang === 'sw' ? `Inatafuta "${val}"…` : `Searching for "${val}"…`, 'info');
  }
}

function handleLogout() {
  showToast(currentLang === 'sw' ? 'Inatoka kwenye mfumo...' : 'Logging out...', 'info');
  localStorage.removeItem('token');
  setTimeout(() => {
    window.location.href = 'login.html';
  }, 800);
}

// Swahili Professional Translation Dictionary
const swDict = {
  // Common terms & Buttons
  'Dashboard': 'Mwanzo',
  'Home': 'Mwanzo',
  'Farmers': 'Wakulima',
  'Warehouse': 'Ghala',
  'Receiving': 'Upokeaji',
  'Drying': 'Kausha Nafaka',
  'Milling': 'Kusaga',
  'Grading': 'Pambanua Daraja',
  'Inventory': 'Stoku',
  'Loans': 'Mikopo',
  'Buyers': 'Wanunuzi',
  'Sales': 'Mauzo',
  'Income & Expenses': 'Mapato & Matumizi',
  'Reports': 'Ripoti',
  'Settings': 'Mipangilio',
  
  // Dashboard page
  'Good morning, James 👋': 'Habari za asubuhi, James 👋',
  "Here's what's happening at your warehouse today — Tuesday, 17 June 2026": "Hivi ndivyo vinavyoendelea kwenye ghala lako leo — Jumanne, 17 Juni 2026",
  'Export': 'Pakua Data',
  'New Transaction': 'Muamala Mpya',
  'Registered Farmers': 'Wakulima Waliosajiliwa',
  'Active accounts': 'Akaunti hai',
  'Total Stock Stored': 'Shehena Ghalani',
  'Grain in warehouse': 'Nafaka iliyo ghalani',
  'Outstanding Loans': 'Mikopo Isiyolipwa',
  'Accrued + Principal': 'Riba + Mtaji',
  'Total Service Revenue': 'Mapato ya Huduma',
  'All processing fees': 'Ada zote za usindikaji',
  'Monthly Revenue': 'Mapato ya Kila Mwezi',
  'Expenses': 'Matumizi',
  'Grain Distribution': 'Mgawanyo wa Nafaka',
  'By type (MT)': 'Kulingana na aina (MT)',
  'Stock Movement Trend': 'Mwenendo wa Mzunguko wa Nafaka',
  'Intake vs dispatch — last 8 months': 'Shehena iliyoingia vs iliyotoka — miezi 8 iliyopita',
  'Warehouse Utilization': 'Ufanisi wa Nafaka Ghalani',
  'All bays — current occupancy': 'Bay zote — kiwango cha nafasi iliyotumika',
  'Top Farmers': 'Wakulima Bora',
  'By stock deposited this season': 'Kwa shehena iliyowekwa msimu huu',
  'View All': 'Tazama Zote',
  'Recent Transactions': 'Miamala ya Hivi Karibuni',
  'Live — last 5 activities': 'Moja kwa moja — shughuli 5 za mwisho',
  'Activity Timeline': 'Mfuatano wa Shughuli',
  "Today's operations log": 'Kumbukumbu za shughuli za leo',
  
  // Farmers Page
  'Farmer Management': 'Usimamizi wa Wakulima',
  '2,418 registered farmers across all regions': 'Wakulima 2,418 waliosajiliwa katika mikoa yote',
  'Add Farmer': 'Sajili Mkulima',
  'Total Farmers': 'Jumla ya Wakulima',
  'Active': 'Hai',
  'Inactive': 'Isiyo hai',
  'With Loans': 'Wenye Mikopo',
  'Regions': 'Mikoa',
  'Search by name, ID, phone…': 'Tafuta kwa jina, kitambulisho, simu…',
  'All Regions': 'Mikoa Yote',
  'All Status': 'Hali Zote',
  'Clear': 'Futa Vichujio',
  'Farmer ID': 'Nambari ya Mkulima',
  'Name': 'Jina',
  'Region': 'Mkoa',
  'Phone': 'Simu',
  'Crop(s)': 'Mazao',
  'Stock (MT)': 'Shehena (MT)',
  'Loan Balance': 'Salio la Mkopo',
  'Status': 'Hali',
  'Actions': 'Vitendo',
  'Showing 1–8 of 2,418 farmers': 'Inaonyesha wakulima 1–8 kati ya 2,418',
  'Register New Farmer': 'Sajili Mkulima Mpya',
  'First Name *': 'Jina la Kwanza *',
  'Last Name *': 'Jina la Mwisho *',
  'Phone Number *': 'Nambari ya Simu *',
  'National ID': 'Nambari ya NIDA',
  'Region *': 'Mkoa *',
  'Primary Crop *': 'Zao Kuu *',
  'Cancel': 'Ghairi',
  'Register Farmer': 'Sajili Mkulima',
  'Farmer Profile Details': 'Maelezo ya Mkulima',
  'Outstanding Loan Balance:': 'Salio la Mkopo Linalodaiwa:',
  'Close': 'Funga',
  'Export List (CSV)': 'Pakua Orodha (CSV)',
  
  // Warehouse Page
  'Warehouse Inventory & Bins': 'Stoku na Bay za Ghala',
  'Real-time bay capacity, grain types, and bin allocation': 'Uwezo wa bay, aina za nafaka, na ugawaji wa bin kwa wakati halisi',
  'Total Warehouse Capacity': 'Uwezo wa Jumla wa Ghala',
  'Occupied Space': 'Nafasi Iliyotumika',
  'Total Active Bins': 'Bin Zote Hai',
  'Bin Occupancy Map': 'Ramani ya Matumizi ya Bin',
  'Visual occupancy and status for all storage bins': 'Kiwango cha matumizi na hali ya bin zote za kuhifadhia nafaka',
  'Capacity Utilization': 'Kiwango cha Matumizi',
  'Occupancy': 'Matumizi',
  'Empty': 'Tupu',
  'Capacity': 'Uwezo',
  'Occupied': 'Inatumika',
  'Full': 'Imejaa',
  
  // Receiving Page
  'Receiving & Intake': 'Upokeaji na Uingizaji',
  'Record inbound grain deliveries, check moisture levels, and issue receipts': 'Rekodi nafaka inayofika, kagua unyevu, na utoe risiti ya upokeaji',
  'Total Intakes': 'Jumla ya Upokeaji',
  'Average Moisture': 'Unyevu wa Wastani',
  'Total Weight Received': 'Jumla ya Uzito Uliopokelewa',
  'New Intake Receipt': 'Upokeaji Mpya',
  'Intake Records': 'Kumbukumbu za Upokeaji',
  'Search batch or farmer…': 'Tafuta kundi au mkulima…',
  'All Crops': 'Mazao Yote',
  'Batch Code': 'Nambari ya Kundi',
  'Farmer Name': 'Jina la Mkulima',
  'Crop Type': 'Aina ya Zao',
  'Variety': 'Aina / Mbegu',
  'Weight (MT)': 'Uzito (MT)',
  'Moisture (%)': 'Unyevu (%)',
  'Date Received': 'Tarehe Iliyopokewa',
  'Print Receipt': 'Piga Chapa Risiti',
  'Record New Intake': 'Rekodi Upokeaji Mpya',
  'Farmer *': 'Mkulima *',
  'Select Farmer...': 'Mchague Mkulima...',
  'Select Crop...': 'Chagua Zao...',
  'Maize': 'Mahindi',
  'Rice': 'Mpunga',
  'Beans': 'Maharage',
  'Sunflower': 'Alizeti',
  'Weight (MT) *': 'Uzito (MT) *',
  'Moisture (%) *': 'Kiwango cha Unyevu (%) *',
  'Storage Bin Placement (Optional)': 'Uwekaji Kwenye Bin (Si lazima)',
  'Auto-Assign Bin': 'Ugawaji wa Bin Kiotomatiki',
  'Record intake': 'Hifadhi Upokeaji',
  'AgroVault Intake Receipt': 'Risiti ya Upokeaji ya AgroVault',
  'Grain Storage & Processing System': 'Mfumo wa Kuhifadhia na Kusindika Nafaka',
  'Farmer Code:': 'Nambari ya Mkulima:',
  'Receipt #:': 'Nambari ya Risiti:',
  'Date:': 'Tarehe:',
  'Net Weight': 'Uzito Halisi',
  'Authorized Signature': 'Sahihi Iliyoidhinishwa',
  'Farmer Signature': 'Sahihi ya Mkulima',
  
  // Drying Page
  'Grain Drying Queue': 'Msururu wa Kukausha Nafaka',
  'Batches exceeding 13.5% moisture are queued for drying services': 'Kundi la nafaka lenye unyevu zaidi ya 13.5% huwekwa kwenye msururu wa kukaushwa',
  'Active Drying Jobs': 'Kazi za Kukausha Zinazoendelea',
  'Pending Moisture Level': 'Kiwango cha Unyevu Kinachosubiriwa',
  'Target Moisture': 'Unyevu Unaolengwa',
  'Drying Queue': 'Msururu wa Kukausha',
  'Search batch…': 'Tafuta kundi...',
  'Initial Moisture': 'Unyevu wa Kwanza',
  'Current Moisture': 'Unyevu wa Sasa',
  'Complete Drying': 'Kamilisha Kukausha',
  'Complete Drying Job': 'Kamilisha Kazi ya Kukausha',
  'Batch Code *': 'Nambari ya Kundi *',
  'Final Moisture Level (%) *': 'Kiwango cha Mwisho cha Unyevu (%) *',
  'Drying Service Fee (TZS) *': 'Ada ya Huduma ya Kukausha (Tsh) *',
  'Save Drying Record': 'Hifadhi Kazi ya Kukausha',
  'Drying active': 'Inakaushwa',
  
  // Milling Page
  'Milling & Processing': 'Kusaga na Usindikaji',
  'Convert stored raw grains into finished flour batches': 'Badilisha nafaka ghafi iliyohifadhiwa kuwa unga uliosindikwa',
  'Active Milling Jobs': 'Kazi za Kusaga Zinazoendelea',
  'Milling Queue': 'Msururu wa Kusaga',
  'Input Weight (MT)': 'Uzito wa Kuingiza (MT)',
  'Complete Milling': 'Kamilisha Kusaga',
  'Process Milling Job': 'Tekeleza Kazi ya Kusaga',
  'Input Weight (MT) *': 'Uzito wa Kuingiza (MT) *',
  'Output Weight (MT) *': 'Uzito wa Unga (MT) *',
  'Milling Service Fee (TZS) *': 'Ada ya Huduma ya Kusaga (Tsh) *',
  'Process & Store': 'Saga na Uhifadhi',
  'Milling active': 'Inasagwa',
  
  // Grading Page
  'Quality Grading': 'Upimaji wa Daraja la Ubora',
  'Analyze and assign official quality grades to grain batches': 'Kagua na uidhinishe daraja rasmi la ubora kwa kila kundi la nafaka',
  'Grading Queue': 'Msururu wa Kupima Daraja',
  'Complete Grading': 'Kamilisha Upimaji',
  'Assign Quality Grade': 'Idhinisha Daraja la Ubora',
  'Assigned Grade *': 'Daraja la Ubora *',
  'Select Grade...': 'Chagua Daraja...',
  'Grade A': 'Daraja la A',
  'Grade B': 'Daraja la B',
  'Grade C': 'Daraja la C',
  'Grading Service Fee (TZS) *': 'Ada ya Huduma ya Kupima Daraja (Tsh) *',
  'Save Grade': 'Hifadhi Daraja',
  'Grading active': 'Inapimwa',
  
  // Loans Page
  'Loan Management': 'Usimamizi wa Mikopo',
  'Track farmer loans, collateral coverage, and repayments': 'Fuatilia mikopo ya wakulima, dhamana za nafaka, na marejesho',
  'Total Outstanding': 'Jumla ya Mikopo Inayodaiwa',
  'Active Borrowers': 'Wazima Mikopo Hai',
  'Pending Approvals': 'Inayosubiri Kuidhinishwa',
  'Overdue Balance': 'Salio Lililopitisha Muda',
  'Collateral Coverage': 'Dhamana Iliyopo',
  'Total Loans Logged': 'Jumla ya Mikopo Yote',
  'Outstanding Loan Requests Queue': 'Msururu wa Maombi ya Mikopo Yanayosubiri',
  'Review, authorize or reject collateralized crop loan applications': 'Kagua, idhinisha au kataa maombi ya mikopo ya wakulima yenye dhamana',
  'Review Queue': 'Kagua Msururu',
  'Search loans by farmer…': 'Tafuta mikopo kwa jina la mkulima…',
  'All': 'Zote',
  'Pending': 'Inayosubiri',
  'Overdue': 'Zilizopitisha Muda',
  'Loan Risk Distribution': 'Mgawanyo wa Hatari ya Mikopo',
  'Upcoming Payments': 'Malipo Yanayokuja',
  'New Loan Request': 'Ombi la Mkopo Mpya',
  'New Loan Application': 'Maombi Mapya ya Mkopo',
  'Loan Amount (TZS) *': 'Kiasi cha Mkopo (Tsh) *',
  'Interest Rate (%)': 'Kiwango cha Riba (%)',
  'Repayment Period': 'Muda wa Kurejesha',
  '3 months': 'Miezi 3',
  '6 months': 'Miezi 6',
  '12 months': 'Miezi 12',
  'Due Date': 'Tarehe ya Mwisho',
  'Collateral: Stock on Hand': 'Dhamana: Stoku Iliyopo Ghalani',
  'The system verifies deposited grain collateral automatically to limit default exposure.': 'Mfumo unakagua dhamana ya nafaka iliyopo ghalani kiotomatiki ili kuzuia hasara ya mikopo.',
  'Submit for Approval': 'Wasilisha Kuidhinishwa',
  'Details': 'Maelezo',
  'Approve': 'Idhinisha',
  'Export Portfolio': 'Hamisha Mikopo',
  'Loan ID': 'Nambari ya Mkopo',
  'Amount': 'Kiasi cha Mkopo',
  'Balance': 'Salio',
  'Rate': 'Riba',
  'Due Date': 'Tarehe ya Mwisho',
  'Risk': 'Hatari',
  
  // Buyers Page
  'Registered Corporate Buyers': 'Wanunuzi wa Mashirika Waliosajiliwa',
  'Manage crop buying corporations, export agents, and contract records': 'Simamia kampuni za wanunuzi wa mazao, mawakala wa kusafirisha nje, na mikataba',
  'Active Buyers': 'Wanunuzi Hai',
  'Contracted Sales MTD': 'Mauzo ya Mkataba MTD',
  'Average Payout Term': 'Muda wa Wastani wa Malipo',
  'Add New Buyer': 'Sajili Mnunuzi Mpya',
  'Buyer Profiles': 'Wasifu wa Wanunuzi',
  'Search buyer name…': 'Tafuta jina la mnunuzi…',
  'Company Name': 'Jina la Kampuni',
  'Contact Person': 'Mtu wa Mawasiliano',
  'Phone Number': 'Nambari ya Simu',
  'Email Address': 'Barua Pepe',
  'History': 'Historia ya Mauzo',
  'Register New Buyer': 'Sajili Mnunuzi Mpya',
  'Company Name *': 'Jina la Kampuni *',
  'Contact Person *': 'Mtu wa Mawasiliano *',
  'Email Address *': 'Barua Pepe *',
  'Register Buyer': 'Sajili Mnunuzi',
  'Buyer Purchase Ledger': 'Daftari la Mauzo la Mnunuzi',
  'Invoice Number': 'Nambari ya Ankara',
  'Subtotal': 'Jumla Ndogo',
  'VAT Amount': 'Kiasi cha KODI (VAT)',
  'Total Amount': 'Jumla ya Malipo',
  
  // Sales Page
  'Sales & Invoicing': 'Mauzo na Ankara',
  'Manage buyer transactions, invoices, and sales analytics': 'Simamia miamala ya wanunuzi, ankara za mauzo, na takwimu za mauzo',
  'Export Ledger': 'Hamisha Daftari',
  'Total Sales Revenue': 'Jumla ya Mapato ya Mauzo',
  'Total Invoices': 'Jumla ya Ankara',
  'Pending Invoices': 'Ankara Zinazodaiwa',
  'Registered Buyers': 'Wanunuzi Waliosajiliwa',
  'Sales Revenue Trend': 'Mwenendo wa Mapato ya Mauzo',
  'Last 8 months (TZS Millions)': 'Miezi 8 iliyopita (Mamilioni ya Tsh)',
  'Corporate Buyers': 'Wanunuzi wa Mashirika',
  'Invoice Ledger': 'Daftari la Ankara',
  'Search invoice…': 'Tafuta ankara…',
  'Unit Price': 'Bei ya Kipimo',
  'Total': 'Jumla',
  'Date': 'Tarehe',
  'View': 'Tazama',
  'Create Tax Invoice': 'Tengeneza Ankara ya KODI',
  'Buyer *': 'Mnunuzi *',
  'Quantity (MT) *': 'Kiasi (MT) *',
  'Unit Price (TZS/MT) *': 'Bei ya Kipimo (Tsh/MT) *',
  'Invoice Date': 'Tarehe ya Ankara',
  'Payment Terms': 'Muda wa Malipo',
  '15 days': 'Siku 15',
  '30 days': 'Siku 30',
  '60 days': 'Siku 60',
  'VAT (18%)': 'KODI (VAT) (18%)',
  'Create Invoice': 'Tengeneza Ankara',
  'Tax Invoice Document': 'Hati ya Ankara ya KODI',
  'TAX INVOICE': 'ANKARA YA KODI',
  'Bill To:': 'Mteja:',
  'Crop Details': 'Maelezo ya Nafaka',
  'Total Due': 'Jumla ya Deni',
  'Print Invoice': 'Piga Chapa Ankara',
  'Invoice #': 'Nambari ya Ankara',
  
  // Accounting Page
  'General Ledger & Invoices': 'Daftari Kuu na Ankara',
  'Gross Revenue (Fees & Interest)': 'Jumla ya Mapato (Ada na Riba)',
  'Warehouse Operating Expenses': 'Matumizi ya Uendeshaji wa Ghala',
  'Net Operating Surplus': 'Salio Halisi la Uendeshaji',
  'Operating Income': 'Mapato ya Uendeshaji',
  'Storage Fees': 'Ada za Uhifadhi',
  'Drying Services': 'Huduma ya Kukausha',
  'Milling Services': 'Huduma ya Kusaga',
  'Grading Services': 'Huduma ya Kupambanua',
  'Accrued Interest': 'Riba Iliyokusanywa',
  'Operating Expenses': 'Matumizi ya Uendeshaji',
  'Electricity & Power': 'Umeme na Nishati',
  'Staff Salaries': 'Mishahara ya Wafanyakazi',
  'Corporate Buyer Invoices': 'Ankara za Wanunuzi wa Mashirika',
  'Search invoice number…': 'Tafuta nambari ya ankara…',
  'View Invoice': 'Tazama Ankara',
  'Maintenance': 'Matengenezo ya Ghala',
  'Diesel Fuel': 'Mafuta ya Dizeli',
  
  // Reports Page
  'Reports & Analytics': 'Ripoti na Uchambuzi',
  'Generate, export, and schedule operational reports': 'Tengeneza, pakua, na panga ripoti za kiutendaji za ghala',
  'Quick Report Builder': 'Zana ya Haraka ya Ripoti',
  'Report Type': 'Aina ya Ripoti',
  'Stock Summary': 'Muhtasari wa Stoku',
  'Farmer Statement': 'Taarifa ya Mkulima',
  'Sales Report': 'Ripoti ya Mauzo',
  'Loan Portfolio': 'Ripoti ya Mikopo',
  'Accounting P&L': 'Uhasibu wa P&L',
  'Date From': 'Kuanzia Tarehe',
  'Date To': 'Hadi Tarehe',
  'Format': 'Mfumo wa Faili',
  'Generate Report': 'Tengeneza Ripoti',
  'Available Reports': 'Ripoti Zinazopatikana',
  'Click any card to generate': 'Bofya kadi yoyote kutengeneza ripoti',
  'Annual Revenue': 'Mapato ya Mwaka',
  'FY 2025/2026 monthly breakdown': 'Mchanganuo wa kila mwezi wa FY 2025/2026',
  'Stock Throughput': 'Mzunguko wa Stoku',
  'MT received vs dispatched': 'MT zilizopokewa vs zilizotolewa',
  'Recent Reports': 'Ripoti za Hivi Karibuni',
  'Report Name': 'Jina la Ripoti',
  'Type': 'Aina',
  'Generated By': 'Imetengenezwa Na',
  'Date & Time': 'Tarehe na Muda',
  'Period': 'Kipindi',
  'Download': 'Pakua',
  'Refresh': 'Chaji Upya',
  'Report Preview': 'Hakiki Ripoti',
  'Export to PDF': 'Pakua Kama PDF',
  
  // Settings Page
  'System Configuration': 'Mipangilio ya Mfumo',
  'Manage warehouse profile, user access, and system-wide service rates': 'Simamia wasifu wa ghala, ufikiaji wa watumiaji, na viwango vya ada za huduma',
  'Warehouse Settings': 'Wasifu wa Ghala',
  'Warehouse Name': 'Jina la Ghala',
  'Arusha Main Warehouse': 'Ghala Kuu la Arusha',
  'Manager Contact Email': 'Barua Pepe ya Meneja',
  'Operational Service Rates': 'Viwango vya Ada za Huduma',
  'Set standard fees charged to farmers for warehouse services': 'Weka ada za kawaida zinazotozwa kwa wakulima kwa huduma za ghala',
  'Storage Fee per MT per day (TZS)': 'Ada ya Uhifadhi kwa MT kwa siku (Tsh)',
  'Drying Fee per MT (TZS)': 'Ada ya Kukausha kwa MT (Tsh)',
  'Milling Fee per MT (TZS)': 'Ada ya Kusaga kwa MT (Tsh)',
  'Grading Fee per batch (TZS)': 'Ada ya Kupima Daraja kwa kundi (Tsh)',
  'Farmer Loan Annual Interest Rate (%)': 'Riba ya Mkopo wa Mkulima kwa Mwaka (%)',
  'System Users & Roles': 'Wafanyakazi na Majukumu Yao',
  'Staff Directory': 'Orodha ya Wafanyakazi',
  'Save Settings': 'Hifadhi Mipangilio',
  'Search staff…': 'Tafuta mfanyakazi…',
  'Role': 'Jukumu',
  'Manager': 'Meneja',
  'Accountant': 'Mhasibu',
  'Operator': 'Mhudumu wa Mashine',
  
  // Badges & Status
  'paid': 'Imelipwa',
  'unpaid': 'Haijalipwa',
  'active': 'Hai',
  'inactive': 'Isiyo hai',
  'stored': 'Imehifadhiwa',
  'drying': 'Inakaushwa',
  'milling': 'Inasagwa',
  'grading': 'Inapimwa',
  'overdue': 'Imepitisha Muda',
  'pending_approval': 'Inasubiri Kuidhinishwa',
  'pending': 'Inasubiri',
  'low': 'Hatari ndogo',
  'high': 'Hatari kubwa',
  
  // Table columns
  'Farmer': 'Mkulima',
  'Qty (MT)': 'Kiasi (MT)',
  'Moisture': 'Unyevu',
  'Variety': 'Mbegu',
  'Milled Unga (MT)': 'Unga Uliosagwa (MT)',
  'Grade': 'Daraja',
  'Total Due': 'Jumla ya Deni',
  'Action': 'Kitendo',
  'Collateral Batch': 'Dhamana ya Kundi',
  'Purpose / Notes': 'Kusudi / Maelezo',
  'Save Draft': 'Hifadhi kama Rasimu',
  'Close': 'Funga'
};

function translateDOM() {
  if (currentLang === 'en') {
    formatCurrencyDOM('Tsh');
    return;
  }
  
  // Recursively translate text nodes
  const walk = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
  let node;
  while (node = walk.nextNode()) {
    const text = node.nodeValue.trim();
    if (swDict[text]) {
      node.nodeValue = node.nodeValue.replace(text, swDict[text]);
    } else {
      // Check for partial replacements (e.g. trailing characters)
      for (const key in swDict) {
        if (text === key) {
          node.nodeValue = swDict[key];
          break;
        }
      }
    }
  }

  // Also translate input and button placeholders & values
  const inputs = document.querySelectorAll('input, select, textarea');
  inputs.forEach(el => {
    if (el.placeholder && swDict[el.placeholder.trim()]) {
      el.placeholder = swDict[el.placeholder.trim()];
    }
  });

  // Translate options in select elements
  const options = document.querySelectorAll('option');
  options.forEach(opt => {
    const txt = opt.textContent.trim();
    if (swDict[txt]) {
      opt.textContent = swDict[txt];
    }
  });

  // Replace currencies
  formatCurrencyDOM('Tsh');
}

function formatCurrencyDOM(symbol) {
  const walk = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
  let node;
  while (node = walk.nextNode()) {
    if (node.nodeValue.includes('TZS')) {
      node.nodeValue = node.nodeValue.replace(/TZS/g, symbol);
    }
  }
}

// Setup MutationObserver to watch for dynamic changes and translate them on the fly
const observer = new MutationObserver((mutations) => {
  // Temporarily disconnect to avoid infinite loops during translation replacements
  observer.disconnect();
  translateDOM();
  observe();
});

function observe() {
  observer.observe(document.body, {
    childList: true,
    subtree: true,
    characterData: true
  });
}

// Initial trigger
document.addEventListener('DOMContentLoaded', () => {
  translateDOM();
  observe();
});
