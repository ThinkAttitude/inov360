// Dynamic theming based on company associated with logged in user
// Fetches /api/me.php and applies CSS class + replaces logo

(function(){
  const API_ME_PATH = '../../api/me.php'; // relative from dashboard pages

  function normaliseSlug(slug){
    if(!slug) return null;
    return slug.toLowerCase().replace(/[^a-z0-9\-]/g,'-');
  }

  function canonicalizeSlug(slug){
    if(!slug) return slug;
    const aliases = {
      'thinkattitude': 'think-attitude',
      'think-attitude': 'think-attitude',
  'think attitude': 'think-attitude',
  // Grupo Inov aliases
  'grupoinov': 'grupo-inov',
  'grupo-inov': 'grupo-inov',
  'grupo inov': 'grupo-inov',
  'grupo_inov': 'grupo-inov',
  'inov': 'grupo-inov'
    };
    return aliases[slug] || slug;
  }

  function clearPreviousTheme(){
    // remove any theme-* classes from body
    const toRemove = [];
    document.body.classList.forEach(cls => { if(cls.startsWith('theme-')) toRemove.push(cls); });
    toRemove.forEach(cls => document.body.classList.remove(cls));
    // reset previously set inline CSS variables (only the ones we use)
    const keys = ['--navy-blue','--light-blue','--background-gray','--gradient-1','--gradient-2'];
    keys.forEach(k => document.documentElement.style.removeProperty(k));
  }

  function setCookie(name, value, days=7){
    try {
      const expires = new Date(Date.now() + days*24*60*60*1000).toUTCString();
      document.cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
    } catch(e){}
  }

  function getCookie(name){
    try {
      const m = document.cookie.match(new RegExp('(?:^|; )'+encodeURIComponent(name)+'=([^;]*)'));
      return m ? decodeURIComponent(m[1]) : null;
    } catch(e){ return null; }
  }

  function applyTheme(company){
    // Always clear first so switching to a user without company/slug restores defaults
    clearPreviousTheme();
    const raw = normaliseSlug(company.slug);
    const slug = canonicalizeSlug(raw);
    if(slug){
      document.body.classList.add('theme-' + slug);
    }

    // If we have explicit color overrides map, apply as inline CSS variables
    const overrides = THEME_COLOR_MAP[slug];
    if(overrides){
      Object.entries(overrides).forEach(([k,v])=>{
        document.documentElement.style.setProperty(k, v);
      });
    }

    // Replace logo if available
    const logoContainer = document.querySelector('.sidebar-header .logo');
    if(logoContainer){
      // persist default markup once, so we can restore when a company doesn't provide a logo
      if(!logoContainer.dataset.defaultHtml){
        logoContainer.dataset.defaultHtml = logoContainer.innerHTML;
      }
      // Use provided company logo or a known fallback for this slug
      const FALLBACK_LOGO_MAP = {
        'grupo-inov': '../../assets/logos/grupoinov.png',
        'think-attitude': '../../assets/logos/think.png',
        'almalusa': '../../assets/logos/alma.png'
      };
      const provided = company.logo && String(company.logo).trim() ? company.logo : null;
      const fallback = FALLBACK_LOGO_MAP[slug] || null;
      const logoSrc = provided || fallback;
      if(logoSrc){
        const logoUrl = logoSrc + (logoSrc.includes('?') ? '&' : '?') + '_=' + Date.now();
        logoContainer.classList.add('has-company-logo');
        logoContainer.innerHTML = `<img src="${logoUrl}" alt="${company.name || 'Company Logo'}" />`;
      } else {
        logoContainer.classList.remove('has-company-logo');
        logoContainer.innerHTML = logoContainer.dataset.defaultHtml;
      }
    }

    // If backend provides gradient colors directly, apply (overrides map)
    if(company.gradient1){
      document.documentElement.style.setProperty('--gradient-1', company.gradient1);
    }
    if(company.gradient2){
      document.documentElement.style.setProperty('--gradient-2', company.gradient2);
    }

    // Optionally show company name in header title if placeholder RH360
    const titleEl = document.querySelector('.sidebar-header h3');
    if(titleEl){
      if(!titleEl.dataset.defaultTitle){ titleEl.dataset.defaultTitle = titleEl.textContent || 'RH360'; }
      if(company.name){
        titleEl.textContent = company.name;
      } else {
        titleEl.textContent = titleEl.dataset.defaultTitle;
      }
    }

    // Persist last applied company for faster next load
    try {
      const minimal = { name: company.name || null, slug: company.slug || null, logo: company.logo || null,
                        gradient1: company.gradient1 || null, gradient2: company.gradient2 || null };
      setCookie('rh360_company', JSON.stringify(minimal));
    } catch(e){}
  }

  // Mapping of custom palettes per company slug using CSS variable names.
  // You can extend this list without changing other code.
  const THEME_COLOR_MAP = {
    'almalusa': {
      '--navy-blue': '#000000',
      '--light-blue': '#D1B08E', /* gold */
  '--background-gray': '#f8f6f2',
  '--gradient-1': '#d4af37',
  '--gradient-2': '#b8860b'
    },
    // Grupo Inov: dark baby blue palette for Operator UI
    'grupo-inov': {
      '--navy-blue': '#2c3e50',
      '--light-blue': '#5DADE2',
      '--background-gray': '#f8fafc',
      '--gradient-1': '#5DADE2',
      '--gradient-2': '#85C1E9'
    },
    'think-attitude': {
      '--navy-blue': '#f59e0b', /* orange primary */
      '--light-blue': '#fbbf24',
  '--background-gray': '#ffffff',
  '--gradient-1': '#f59e0b',
  '--gradient-2': '#fbbf24'
    }
  };

  // Clear any stale theme immediately on load (prevents showing previous company's theme)
  try { clearPreviousTheme(); } catch(e) {}

  // If server injected company info (e.g., operator dashboard), apply it synchronously
  try {
    if (window.__COMPANY__ && (window.__COMPANY__.slug || window.__COMPANY__.name || window.__COMPANY__.logo)) {
      console.debug('[theme] server-injected company:', window.__COMPANY__);
      applyTheme(window.__COMPANY__);
    }
  } catch(e){ console.debug('[theme] server-injected parse error:', e); }

  // Apply cached company theme instantly while we fetch fresh data (unless URL asks to clearTheme)
  try {
    const params = new URLSearchParams(window.location.search);
    const skipCached = params.get('clearTheme') === '1';
    if(!skipCached){
      const cached = getCookie('rh360_company');
      if(cached){
        const obj = JSON.parse(cached);
        if(obj && (obj.slug || obj.name || obj.logo)){
          applyTheme(obj);
        }
      }
    }
  } catch(e){}

  // Fetch user/company info
  // Bust HTTP caching and avoid returning stale company info when switching accounts
  const cacheBuster = (API_ME_PATH.includes('?') ? '&' : '?') + '_=' + Date.now();
  fetch(API_ME_PATH + cacheBuster, { credentials: 'include', cache: 'no-store' })
    .then(r=> r.ok ? r.json() : Promise.reject(new Error('HTTP ' + r.status)))
    .then(data => {
      if(data && data.sucesso && data.user && data.user.company){
        console.debug('[theme] api company:', data.user.company);
        applyTheme(data.user.company);
      }
    })
    .catch((err)=>{ console.warn('[theme] api fetch error:', err); });
})();
