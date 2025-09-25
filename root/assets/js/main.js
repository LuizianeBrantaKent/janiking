// Tiny utility helpers and page hooks
(function () {
  // Sidebar active link is handled server-side via $active.
  // Add any client interactions below.

  // Drag & drop for upload dropzone (if present)
  const dz = document.querySelector('.dropzone');
  if (dz) {
    ['dragenter','dragover'].forEach(ev =>
      dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('drag'); })
    );
    ['dragleave','drop'].forEach(ev =>
      dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('drag'); })
    );
    dz.addEventListener('drop', e => {
      const files = Array.from(e.dataTransfer.files || []);
      console.log('Dropped files:', files);
      // TODO: implement upload logic
    });
  }

  // Example filter change handler
  document.querySelectorAll('.js-filter').forEach(el => {
    el.addEventListener('change', () => {
      // TODO: submit filters or fetch() update
      console.log('Filter changed:', el.name, el.value);
    });
  });
})();
// Upload tab: submit form
document.querySelectorAll('[data-upload-form]')?.forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const res = await fetch(`${window.APP_BASE}/staff/api/upload_document.php`, { method: 'POST', body: fd });
    const json = await res.json();
    // TODO: refresh recent uploads list
  });
});

// Recent uploads table
async function refreshUploads(){
  const res = await fetch(`${window.APP_BASE}/staff/api/list_recent_uploads.php`);
  const {items} = await res.json();
  // TODO: render into the table body to mirror the UI on the Upload Files screen  :contentReference[oaicite:14]{index=14}
}
refreshUploads();

// Reports pagination (page 1..N)
async function loadReports(page=1){
  const res = await fetch(`${window.APP_BASE}/staff/api/reports_list.php?page=${page}`);
  const {items} = await res.json();
  // TODO: populate "Recent Reports" rows with format badges like in your mock  :contentReference[oaicite:15]{index=15}
}

// Communication: post announcement
document.querySelector('#composeAnnouncement')?.addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  const res = await fetch(`${window.APP_BASE}/staff/api/announcements_create.php`, { method:'POST', body: fd });
  if ((await res.json()).ok) { /* Reload left list to match screenshot */ }
});

// Training overview
async function loadTraining(){
  const res = await fetch(`${window.APP_BASE}/staff/api/training_overview.php`);
  const data = await res.json();
  // Fill KPI tiles and table just like the reference screen  :contentReference[oaicite:16]{index=16}
}
// ===== JaniKing Staff – Global JS (light helpers) =====

// File picker bridges (Upload page)
document.querySelectorAll('[data-target]').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = document.querySelector(btn.getAttribute('data-target'));
    if (target) target.click();
  });
});

// Drag & drop highlight (Upload page)
document.querySelectorAll('.dropzone').forEach(zone => {
  ['dragenter','dragover'].forEach(ev =>
    zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('drag'); })
  );
  ['dragleave','drop'].forEach(ev =>
    zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove('drag'); })
  );
  zone.addEventListener('drop', e => {
    // TODO: handle files -> send to backend
    // const files = e.dataTransfer.files;
  });
});

// "Select all" (Documents page)
const selectAll = document.getElementById('selectAll');
if (selectAll) {
  const checks = document.querySelectorAll('.row-check');
  selectAll.addEventListener('change', e => checks.forEach(c => c.checked = e.target.checked));
}
