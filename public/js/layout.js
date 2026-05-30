// Injects shared sidebar + navbar into pages that include #ers-layout-root
(function(){
  const root = document.getElementById('ers-layout-root');
  if (!root) return;
  const pageTitle = root.dataset.title || 'Dashboard';
  const activePage = root.dataset.active || 'dashboard';

  const links = [
    { key:'dashboard', href:'dashboard', icon:'bi-speedometer2', label:'Dashboard' },
    { key:'users',     href:'users',     icon:'bi-people',       label:'Users Management' },
    { key:'employees', href:'employees', icon:'bi-person-badge', label:'Employee Records' },
  ];

  const sidebar = `
    <aside class="ers-sidebar">
      <div class="brand"><i class="bi bi-grid-1x2-fill"></i> Employee Records</div>
      <nav class="nav flex-column mt-2">
        ${links.map(l => `
          <a class="nav-link ${l.key===activePage?'active':''}" href="${l.href}" data-page="${l.key}">
            <i class="bi ${l.icon}"></i><span>${l.label}</span>
          </a>`).join('')}
      </nav>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-close></div>
  `;

  const userName = window.currentUser?.name || 'John Doe';
  const userInitials = userName.split(' ').map(w => w[0] || '').join('').slice(0, 2).toUpperCase();

  const navbar = `
    <header class="ers-navbar">
      <div class="d-flex align-items-center gap-2">
        <button class="icon-btn d-lg-none" data-sidebar-toggle aria-label="Toggle sidebar">
          <i class="bi bi-list"></i>
        </button>
        <h1 class="title">${pageTitle}</h1>
      </div>
      <div class="nav-actions">
        <div class="dropdown">
          <button class="profile-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="avatar">${userInitials}</span>
            <span class="d-none d-sm-inline small fw-semibold">${userName}</span>
            <i class="bi bi-chevron-down small text-muted"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="profile"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="login"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
      </div>
    </header>
  `;

  // Inject before page content
  const main = document.createElement('div');
  main.className = 'ers-main';
  main.innerHTML = navbar + `<div class="ers-content"></div>`;

  const content = root.innerHTML;
  root.innerHTML = '';
  root.classList.add('ers-wrapper');
  root.insertAdjacentHTML('afterbegin', sidebar);
  root.appendChild(main);
  main.querySelector('.ers-content').innerHTML = content;
})();
