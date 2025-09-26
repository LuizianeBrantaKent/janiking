// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const toggle  = document.getElementById('sidebarToggle');
const backdrop= document.getElementById('backdrop');
function closeSidebar(){ sidebar.classList.remove('show'); backdrop.classList.remove('show'); }
function openSidebar(){ sidebar.classList.add('show'); backdrop.classList.add('show'); }
toggle?.addEventListener('click', () => sidebar.classList.contains('show') ? closeSidebar() : openSidebar());
backdrop?.addEventListener('click', closeSidebar);

// ---- CART LOGIC (client-side; validate again on server at checkout) ----
const TAX_RATE = 0.08;
const $items = document.getElementById('cart-items');
const $count = document.getElementById('cart-count');
const $subtotal = document.getElementById('subtotal');
const $tax = document.getElementById('tax');
const $total = document.getElementById('total');
const $checkoutBtn = document.getElementById('checkoutBtn');
const $modalTotal = document.getElementById('modalTotal');

function loadCart(){ try { return JSON.parse(localStorage.getItem('jk_cart')||'[]'); } catch { return []; } }
function saveCart(cart){ localStorage.setItem('jk_cart', JSON.stringify(cart)); }
function currency(n){ return new Intl.NumberFormat('en-US',{style:'currency',currency:'USD'}).format(n); }

function renderCart(){
  const cart = loadCart();
  $items.innerHTML = '';
  let subtotal = 0;

  cart.forEach((it, idx) => {
    const line = it.price * it.qty;
    subtotal += line;

    const row = document.createElement('div');
    row.className = 'cart-item';
    row.innerHTML = `
      <div class="d-flex align-items-center gap-2">
        <img src="${it.img}" alt="">
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between">
            <strong class="me-2">${it.name}</strong>
            <a href="#" class="text-danger remove-item" data-idx="${idx}" title="Remove"><i class="fa-solid fa-xmark"></i></a>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-1">
            <div class="btn-group btn-group-sm" role="group" aria-label="Qty">
              <button class="btn btn-outline-secondary qty-dec" data-idx="${idx}">−</button>
              <button class="btn btn-light disabled">${it.qty}</button>
              <button class="btn btn-outline-secondary qty-inc" data-idx="${idx}">+</button>
            </div>
            <span class="fw-semibold">${currency(line)}</span>
          </div>
        </div>
      </div>
      <hr class="mt-2 mb-0">
    `;
    $items.appendChild(row);
  });

  const tax = +(subtotal * TAX_RATE).toFixed(2);
  const total = +(subtotal + tax).toFixed(2);

  $count.textContent = cart.reduce((a,b)=>a+b.qty,0);
  $subtotal.textContent = currency(subtotal);
  $tax.textContent = currency(tax);
  $total.textContent = currency(total);
  $modalTotal.textContent = currency(total);
  $checkoutBtn.disabled = cart.length === 0;
}

function addToCart(prod){
  const cart = loadCart();
  const i = cart.findIndex(x => x.id===prod.id);
  if (i>-1) cart[i].qty += 1; else cart.push({...prod, qty:1});
  saveCart(cart); renderCart();
}

// bind add-to-cart buttons
document.querySelectorAll('.add-to-cart').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    addToCart({
      id: +btn.dataset.id,
      name: btn.dataset.name,
      price: +btn.dataset.price,
      img: btn.dataset.img
    });
  });
});

// handle qty +/- and remove inside dropdown (event delegation)
$items.addEventListener('click', (e)=>{
  const cart = loadCart();
  const inc = e.target.closest('.qty-inc');
  const dec = e.target.closest('.qty-dec');
  const rem = e.target.closest('.remove-item');

  if (inc){
    const idx = +inc.dataset.idx; cart[idx].qty++; saveCart(cart); renderCart();
  }
  if (dec){
    const idx = +dec.dataset.idx; cart[idx].qty = Math.max(1, cart[idx].qty-1); saveCart(cart); renderCart();
  }
  if (rem){
    e.preventDefault();
    const idx = +rem.dataset.idx; cart.splice(idx,1); saveCart(cart); renderCart();
  }
});

// Paypal Check out
function getCartTotalAUD() {
  const t = document.getElementById('total')?.textContent || '0';
  const num = parseFloat(String(t).replace(/[^\d.]/g, '')) || 0;
  return num.toFixed(2);
}

let paypalRendered = false;
const checkoutModalEl = document.getElementById('checkoutModal');

if (checkoutModalEl) {
  checkoutModalEl.addEventListener('shown.bs.modal', function () {
    if (paypalRendered) return;
    paypalRendered = true;

    paypal.Buttons({
      style: { layout: 'vertical', color: 'blue', shape: 'rect', label: 'paypal' },

      createOrder: function (data, actions) {
        const amount = getCartTotalAUD();
        if (amount <= 0) {
          const err = document.getElementById('paypal-error');
          if (err) { err.style.display='block'; err.textContent = 'Your cart is empty.'; }
          return actions.reject();
        }
        return actions.order.create({
          purchase_units: [{
            amount: { currency_code: 'AUD', value: amount },
            description: 'JaniKing Franchisee Order'
          }]
        });
      },

      onApprove: function (data, actions) {
        return actions.order.capture().then(function (details) {
          // TODO: optionally POST `details` to your server to record the order

          // Clear cart UI
          try { localStorage.removeItem('jk_cart'); } catch(e){}
          document.getElementById('cart-items')?.replaceChildren();
          const cc = document.getElementById('cart-count'); if (cc) cc.textContent = '0';
          const s  = id => document.getElementById(id);
          s('subtotal') && (s('subtotal').textContent = '$0.00');
          s('tax')      && (s('tax').textContent      = '$0.00');
          s('total')    && (s('total').textContent    = '$0.00');
          s('checkoutBtn') && s('checkoutBtn').setAttribute('disabled','disabled');

          alert('Payment completed by ' + (details.payer?.name?.given_name || 'customer') + '.');
          // bootstrap.Modal.getInstance(checkoutModalEl).hide(); // uncomment to auto-close
        });
      },

      onError: function (err) {
        const box = document.getElementById('paypal-error');
        if (box) { box.style.display='block'; box.textContent = 'Payment error. Please try again.'; }
        console.error('PayPal error', err);
      }
    }).render('#paypal-buttons');
  });
}

// initial
renderCart();

