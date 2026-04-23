// ===== SHARED ICONS =====
const ICONS = {
  'Books & Notes': '📚',
  'Electronics & Gadgets': '💻',
  'Project Files / Source Code': '🗂️',
  'Games & Accessories': '🎮',
  'Miscellaneous': '📦'
};

// ===== GET LOGGED IN USER =====
function getUser() {
  const u = localStorage.getItem('user');
  return u ? JSON.parse(u) : null;
}

// ===== LOGOUT =====
function logout() {
  localStorage.removeItem('user');
  window.location.href = 'index.html';
}

// ===== CHECK LOGIN STATUS & UPDATE NAVBAR =====
function checkLoginStatus() {
  const user = getUser();
  const loginBtn   = document.getElementById('nav-login');
  const logoutBtn  = document.getElementById('nav-logout');
  const profileBtn = document.getElementById('nav-profile');
  const adminBtn   = document.getElementById('nav-admin');

  if (user) {
    if (loginBtn)   loginBtn.style.display  = 'none';
    if (logoutBtn)  logoutBtn.style.display = 'inline-block';
    if (profileBtn) {
      profileBtn.style.display = 'inline-block';
      profileBtn.textContent   = user.name.split(' ')[0];
    }
    if (adminBtn && user.role === 'admin') {
      adminBtn.style.display = 'inline-block';
    }
  } else {
    if (loginBtn)   loginBtn.style.display  = 'inline-block';
    if (logoutBtn)  logoutBtn.style.display = 'none';
    if (profileBtn) profileBtn.style.display = 'none';
    if (adminBtn)   adminBtn.style.display   = 'none';
  }
}

// ===== BADGE HTML =====
function badgeHtml(condition, status) {
  if (status === 'Reserved') return '<span class="badge badge-reserved">Reserved</span>';
  if (condition === 'Digital') return '<span class="badge badge-digital">Digital</span>';
  if (condition === 'New')     return '<span class="badge badge-new">New</span>';
  return '<span class="badge badge-used">Used</span>';
}

// ===== CARD HTML — clicking goes to product detail page =====
function cardHtml(item) {
  const icon = ICONS[item.category] || '📦';
  return `
    <div class="card" onclick="window.location.href='product-detail.html?id=${item.id}'">
      <div class="card-img">${icon}</div>
      <div class="card-body">
        <div class="card-title">${item.title}</div>
        <div class="card-price">৳ ${parseInt(item.price).toLocaleString()}</div>
        <div class="card-footer">
          ${badgeHtml(item.condition_type, item.status)}
          <span class="card-dept">${item.department || ''}</span>
        </div>
      </div>
    </div>`;
}

// ===== SHOW TOAST NOTIFICATION =====
function showToast(msg) {
  let toast = document.getElementById('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = 'toast';
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.classList.add('show');
  setTimeout(function() { toast.classList.remove('show'); }, 2500);
}