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