// /assets/js/staff_documents.js
(function () {
  'use strict';

  const $ = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

  // --- 1) Delete confirm (event delegation) -------------------------------
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-file');
    if (!btn) return;

    const href = btn.getAttribute('data-delete-href') || btn.getAttribute('href');
    if (!href) return;

    const msg = btn.getAttribute('data-confirm') || 'Delete this file? This cannot be undone.';
    if (!window.confirm(msg)) e.preventDefault();
  });

  // --- 2) Auto-submit filters ---------------------------------------------
  // Auto-submit when the Type dropdown changes
  const filterForm = $('form[action=""], form[action="#"], form[action]:not([method="post"])') || $('form[method="get"]');
  if (filterForm) {
    const typeSel = $('select[name="type"]', filterForm);
    if (typeSel) typeSel.addEventListener('change', () => filterForm.submit());

    // Debounce search input to apply after user pauses typing
    const qInput = $('input[name="q"]', filterForm);
    if (qInput) {
      let t;
      qInput.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => filterForm.submit(), 500);
      });
      // Also submit on Enter for accessibility
      qInput.addEventListener('keydown', (ev) => {
        if (ev.key === 'Enter') { ev.preventDefault(); filterForm.submit(); }
      });
    }
  }

  // --- 3) Upload UX: show names + sizes, enforce optional limits ----------
  // Add data-max-mb / data-max-total-mb on the upload <form> if you want limits (e.g., data-max-mb="50" data-max-total-mb="200")
  const uploadForm = $('form[method="post"][enctype="multipart/form-data"]');
  if (uploadForm) {
    const fileInput = $('input[type="file"][name="files[]"]', uploadForm);
    if (fileInput) {
      const preview = document.createElement('div');
      preview.className = 'mt-2 small text-muted';
      fileInput.insertAdjacentElement('afterend', preview);

      const perFileMB  = Number(uploadForm.dataset.maxMb || 0);        // 0 = no limit
      const totalMB    = Number(uploadForm.dataset.maxTotalMb || 0);   // 0 = no limit

      fileInput.addEventListener('change', () => {
        if (!fileInput.files || fileInput.files.length === 0) {
          preview.textContent = '';
          return;
        }

        let totalBytes = 0;
        const names = [];
        for (const f of fileInput.files) {
          totalBytes += f.size;
          const sizeMB = (f.size / (1024 * 1024)).toFixed(2);
          names.push(`${f.name} (${sizeMB} MB)`);
          if (perFileMB && f.size > perFileMB * 1024 * 1024) {
            alert(`“${f.name}” exceeds the per-file limit of ${perFileMB} MB.`);
            fileInput.value = '';
            preview.textContent = '';
            return;
          }
        }
        if (totalMB && totalBytes > totalMB * 1024 * 1024) {
          alert(`Selected files exceed the total limit of ${totalMB} MB.`);
          fileInput.value = '';
          preview.textContent = '';
          return;
        }
        preview.textContent = names.join(', ');
      });
    }
  }

  // --- 4) Broken preview fallback ----------------------------------------
  // If an <img> thumbnail fails (e.g., missing streamer path), replace with an icon block
  $$('.tablex .col-preview img').forEach((img) => {
    img.addEventListener('error', () => {
      const icon = document.createElement('div');
      icon.className = 'file-thumb file-thumb--icon';
      icon.innerHTML = '<i class="fa-solid fa-file fa-lg" aria-hidden="true"></i>';
      img.replaceWith(icon);
    }, { once: true });
  });

  // --- 5) Auto-hide flash messages ---------------------------------------
  setTimeout(() => { $$('#flash .pill.badge-ok').forEach(el => el.style.display = 'none'); }, 4000);
})();
