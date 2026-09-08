import { ref, computed } from 'vue';

const currentLang = ref(localStorage.getItem('garanoki_lang') || 'sw');

const translations = {
  sw: {
    // Nav Groups
    navMain: 'Kuu',
    navOps: 'Uendeshaji',
    navFin: 'Fedha',
    navSys: 'Mfumo',
    
    // Sidebar Links & Page Titles
    dashboard: 'Dashboard',
    farmers: 'Wakulima',
    cashbook: 'Daftari la Pesa',
    inventory: 'Stoko & Vihenge',
    services: 'Huduma za Kinu',
    loans: 'Mikopo',
    buyers: 'Wanunuzi',
    sales: 'Mauzo & Ankara',
    accounting: 'Mapato na Matumizi',
    reports: 'Ripoti & Hati',
    settings: 'Mipangilio',
    logout: 'Kutoka Mfumoni',
    systemOwner: 'Miliki wa Mfumo',
    
    // Common Actions & Labels
    add: 'Ongeza',
    register: 'Sajili',
    edit: 'Hariri',
    delete: 'Futa',
    save: 'Hifadhi',
    cancel: 'Ghairi',
    close: 'Funga',
    refresh: 'Weka Upya',
    search: 'Tafuta...',
    filter: 'Chuja',
    all: 'Yote',
    actions: 'Vitendo',
    date: 'Tarehe',
    status: 'Hali',
    amount: 'Kiasi (TZS)',
    description: 'Maelezo',
    phone: 'Simu',
    name: 'Jina',
    total: 'Jumla',
    balance: 'Salio',
    paid: 'Imelipwa',
    pending: 'Inasubiri',
    completed: 'Kukamilika',
    notes: 'Kumbukumbu',
    print: 'Chapa',
    export: 'Pakua Data',
    back: 'Rudi Nyuma',
    viewDetails: 'Angalia Undani',
    
    // Header & Controls
    searchPlaceholder: 'Tafuta wakulima, ankara, stakabadhi...',
    notifications: 'Taarifa za Mfumo',
    profileSettings: 'Mipangilio ya Akaunti',
    lightMode: 'Mwangaza (Light)',
    darkMode: 'Giza (Dark)',
    switchTheme: 'Badili Rangi ya Skrini',
    financialControlCenter: 'Kituo cha Utawala wa Kifedha',
    financialOverview: 'Tathmini ya Kifedha',
    
    // Periods
    allTime: 'Muda Wote',
    today: 'Leo',
    thisWeek: 'Wiki Hii',
    thisMonth: 'Mwezi Huu',
    thisYear: 'Mwaka Huu',
    customRange: 'Tarehe Zako',
    
    // Financial Metrics & Dashboard Cards
    grossCropSales: '1. Mauzo ya Mazao',
    serviceRevenue: '2. Ada za Huduma',
    operatingExpenses: '3. Matumizi',
    totalLoansDisbursed: '4. Mikopo Iliyotolewa',
    outstandingDebt: '5. Deni Linalodaiwa',
    netProfit: '6. Faida Halisi',
    financialLedger: 'Daftari la Miamala',
    realtimeAudit: 'Kumbukumbu halisi za miamala na makato',
    fullCashbook: 'Daftari Kamili la Pesa',
    
    // Module Specific Titles
    farmerManagement: 'Usimamizi wa Wakulima',
    farmerSubtitle: 'Orodha ya wakulima waliosajiliwa, stoko yao na mizani ya akaunti',
    registerFarmer: 'Sajili Mkulima Mpya',
    
    serviceManagement: 'Usimamizi wa Huduma za Kinu',
    serviceSubtitle: 'Ada za kukoboa, kuanika, kugiredi, kuchanganya na vipimo vya kinu',
    registerService: 'Sajili Huduma Mpya',
    
    inventoryManagement: 'Stoko & Vihenge vya Kinu',
    inventorySubtitle: 'Viwango vya nafaka, mpunga, sembe, mchele na pumba ghalani',
    
    loanManagement: 'Usimamizi wa Mikopo',
    loanSubtitle: 'Mikopo ya fedha na pembejeo zilizotolewa kwa wakulima',
    issueLoan: 'Toa Mkopo Mpya',
    
    salesManagement: 'Mauzo & Invois za Wanunuzi',
    salesSubtitle: 'Invois zilizotolewa, malipo ya nafaka na stakabadhi za wanunuzi',
    createInvoice: 'Tengeneza Invois Mpya',
    
    accountingManagement: 'Kituo cha Uahasibu & Matumizi',
    accountingSubtitle: 'Kuingiza gharama za uendeshaji, mafuta, matengenezo na mshahara',
    recordExpense: 'Kumba Matumizi Mpya',
    
    reportManagement: 'Kituo cha Ripoti Rasmi & Ankara',
    reportSubtitle: 'Pakua na chapa ripoti safi za PDF, Invois na Risiti rasmi',
    generatePDF: 'Pakua Ripoti ya PDF',

    settingsManagement: 'Mipangilio ya Mfumo & Akaunti',
    settingsSubtitle: 'Badili taarifa za kinu, maelezo ya mmiliki na lugha',

    // Export/Download Buttons
    downloadCSV: 'Pakua CSV / Excel',
    downloadPDF: 'Pakua Official PDF',
    printPreview: 'Chapa / Preview',
    verifyInvoice: 'Hakiki Invois / Hati',
  },
  en: {
    // Nav Groups
    navMain: 'Main',
    navOps: 'Operations',
    navFin: 'Finance',
    navSys: 'System',
    
    // Sidebar Links & Page Titles
    dashboard: 'Dashboard',
    farmers: 'Farmers',
    cashbook: 'Cashbook',
    inventory: 'Inventory & Silos',
    services: 'Milling Services',
    loans: 'Loans',
    buyers: 'Buyers',
    sales: 'Sales & Invoices',
    accounting: 'Income & Expenses',
    reports: 'Reports & Invoices',
    settings: 'Settings',
    logout: 'Logout',
    systemOwner: 'System Owner',
    
    // Common Actions & Labels
    add: 'Add',
    register: 'Register',
    edit: 'Edit',
    delete: 'Delete',
    save: 'Save',
    cancel: 'Cancel',
    close: 'Close',
    refresh: 'Refresh',
    search: 'Search...',
    filter: 'Filter',
    all: 'All',
    actions: 'Actions',
    date: 'Date',
    status: 'Status',
    amount: 'Amount (TZS)',
    description: 'Description',
    phone: 'Phone',
    name: 'Name',
    total: 'Total',
    balance: 'Balance',
    paid: 'Paid',
    pending: 'Pending',
    completed: 'Completed',
    notes: 'Notes',
    print: 'Print',
    export: 'Export Data',
    back: 'Go Back',
    viewDetails: 'View Details',
    
    // Header & Controls
    searchPlaceholder: 'Search farmers, invoices, receipts...',
    notifications: 'System Notifications',
    profileSettings: 'Profile Settings',
    lightMode: 'Light Mode',
    darkMode: 'Dark Mode',
    switchTheme: 'Switch Theme',
    financialControlCenter: 'Financial Control Center',
    financialOverview: 'Financial Overview',
    
    // Periods
    allTime: 'All Time',
    today: 'Today',
    thisWeek: 'This Week',
    thisMonth: 'This Month',
    thisYear: 'This Year',
    customRange: 'Custom Range',
    
    // Financial Metrics & Dashboard Cards
    grossCropSales: '1. Crop Sales',
    serviceRevenue: '2. Service Revenue',
    operatingExpenses: '3. Operating Expenses',
    totalLoansDisbursed: '4. Loans Disbursed',
    outstandingDebt: '5. Outstanding Debt',
    netProfit: '6. Net Profit',
    financialLedger: 'Financial Ledger & Audit Trail',
    realtimeAudit: 'Real-time ledger of settlements and expenses',
    fullCashbook: 'Full Cashbook',
    
    // Module Specific Titles
    farmerManagement: 'Farmers Management',
    farmerSubtitle: 'Registered farmers directory, stored grain inventory, and account balances',
    registerFarmer: 'Register New Farmer',
    
    serviceManagement: 'Milling Services Management',
    serviceSubtitle: 'Hulling, drying, grading, mixing and weighing rates',
    registerService: 'Register New Service',
    
    inventoryManagement: 'Store Inventory & Silos',
    inventorySubtitle: 'Stock levels for paddy, sembe, polished rice, and bran',
    
    loanManagement: 'Loans & Advances',
    loanSubtitle: 'Cash and farm input loans issued to farmers',
    issueLoan: 'Issue New Loan',
    
    salesManagement: 'Sales & Buyer Invoices',
    salesSubtitle: 'Issued invoices, grain buyers, and payment receipts',
    createInvoice: 'Create New Invoice',
    
    accountingManagement: 'Accounting & Expenses Hub',
    accountingSubtitle: 'Record operational costs, fuel, maintenance, and wages',
    recordExpense: 'Record New Expense',
    
    reportManagement: 'Official Reports & Invoices Hub',
    reportSubtitle: 'Download and print professional PDF reports, invoices, and receipts',
    generatePDF: 'Download PDF Report',

    settingsManagement: 'System & Account Settings',
    settingsSubtitle: 'Configure mill parameters, profile info, and language',

    // Export/Download Buttons
    downloadCSV: 'Download CSV / Excel',
    downloadPDF: 'Download Official PDF',
    printPreview: 'Print / Preview',
    verifyInvoice: 'Verify Invoice / Voucher',
  }
};

export function useLanguage() {
  const lang = computed(() => currentLang.value);

  const setLanguage = (newLang) => {
    if (translations[newLang]) {
      currentLang.value = newLang;
      localStorage.setItem('garanoki_lang', newLang);
    }
  };

  const toggleLanguage = () => {
    const nextLang = currentLang.value === 'sw' ? 'en' : 'sw';
    setLanguage(nextLang);
  };

  const t = (key, fallback = '') => {
    return translations[currentLang.value]?.[key] || translations.sw?.[key] || fallback || key;
  };

  return {
    lang,
    currentLang,
    setLanguage,
    toggleLanguage,
    t
  };
}
