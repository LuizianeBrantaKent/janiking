//here starts navbar

document.addEventListener('DOMContentLoaded', function () {
  const navLinks = document.querySelectorAll('.franchisee-navbar a.nav-link, .franchisee-navbar a');

  // clear any previous highlight
  navLinks.forEach(a => a.classList.remove('active'));

  const norm = p =>
    p.replace(/\/index\.php$/i, '')   // normalize index.php
     .replace(/\/+$/, '')             // strip trailing slash
     .toLowerCase();

  let here = norm(location.pathname);

  // If someone lands on the folder itself, treat it as the dashboard file
  if (here === '/janiking/franchisee' || here === '/franchisee') {
    here = here + '/franchisee_dash.php';
  }

  let picked = null;
  navLinks.forEach(a => {
    const href = a.getAttribute('href') || '';
    if (!href) return;

    // resolve relative/absolute hrefs
    const path = norm(new URL(href, location.href).pathname);

    if (here === path) picked = a;    // exact match only
  });

  if (picked) picked.classList.add('active');
});

//here ends navbar

// here starts the messaging section

 (function(){
      const root=document.getElementById('fr-msg'); if(!root) return;
      const ta=root.querySelector('#frmsg_reply_body');
      if(ta){
        const fit=()=>{ ta.style.height='auto'; ta.style.height=(ta.scrollHeight+2)+'px'; };
        ta.addEventListener('input', fit); fit();
      }
    })();

// here ends the messaging section

// here starts the franchisee_products

// === Franchisee Products: grid/list toggle + quick view ===
document.addEventListener('DOMContentLoaded', () => {
  const gridBtn = document.querySelector('#fr-products .js-grid');
  const listBtn = document.querySelector('#fr-products .js-list');
  const gridEl  = document.querySelector('#fr-products .products-grid');

  if (gridBtn && listBtn && gridEl) {
    gridBtn.addEventListener('click', () => {
      gridEl.setAttribute('data-view', 'grid');
      gridBtn.classList.add('active');
      listBtn.classList.remove('active');
    });
    listBtn.addEventListener('click', () => {
      gridEl.setAttribute('data-view', 'list');
      listBtn.classList.add('active');
      gridBtn.classList.remove('active');
    });
  }

  // Quick View (uses products injected via window.FR_PRODUCTS_BOOT)
  const boot = window.FR_PRODUCTS_BOOT || {};
  const modalEl = document.getElementById('productQuickView');
  if (!modalEl) return;

  const qvImage = modalEl.querySelector('#qvImage');
  const qvName  = modalEl.querySelector('#qvName');
  const qvCat   = modalEl.querySelector('#qvCategory');
  const qvDesc  = modalEl.querySelector('#qvDesc');
  const qvPrice = modalEl.querySelector('#qvPrice');
  const qvStock = modalEl.querySelector('#qvStock');
  const reqBtn  = modalEl.querySelector('.js-request-modal');
  const bsModal = typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;

  function fmtPrice(p){
    if (p === null || p === undefined || p === '') return '—';
    const num = Number(p);
    if (Number.isNaN(num)) return '—';
    return '$' + num.toFixed(2);
  }
  function imgSrc(path){
    if (!path) return boot.fallbackImg || '';
    return (boot.imgBase || '') + String(path).replace(/^\/+/, '');
  }

  document.querySelectorAll('#fr-products .js-view').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = Number(btn.dataset.id || 0);
      const item = (boot.products || []).find(p => Number(p.product_id) === id);
      if (!item) return;

      qvImage.src = imgSrc(item.image_path);
      qvImage.alt = item.name || '';
      qvName.textContent = item.name || '';
      qvCat.textContent = item.category || '';
      qvDesc.textContent = item.description || '';
      qvPrice.textContent = fmtPrice(item.price);
      const stock = Number(item.stock_quantity || 0);
      qvStock.innerHTML = stock > 0
        ? '<span class="badge bg-success"><i class="fa-solid fa-check"></i> In stock</span>'
        : '<span class="badge bg-secondary"><i class="fa-regular fa-clock"></i> Out of stock</span>';
      reqBtn.disabled = stock <= 0;

      if (bsModal) bsModal.show();
    });
  });

  // "Request" buttons (stub – you can wire to a cart/request endpoint later)
  function toast(msg){
    if (!('bootstrap' in window) || !bootstrap.Toast) { alert(msg); return; }
    // minimal inline toast (optional)
    alert(msg);
  }
  document.querySelectorAll('#fr-products .js-request').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.closest('.product-card')?.dataset.id;
      toast('Requested product ID ' + id);
    });
  });
  if (reqBtn) {
    reqBtn.addEventListener('click', () => {
      const name = qvName.textContent.trim();
      toast('Requested: ' + name);
    });
  }
});


// here ends the franchisee_products



