// assets/js/staff_training.js

(function () {
  // Show chosen file name + size, and do a quick client-side size check
  const input = document.getElementById('train_file');
  const help = document.getElementById('file_help');
  const form = document.getElementById('training-form');
  const maxMB = Number(form?.dataset?.maxMb || 50);

  if (input && help) {
    input.addEventListener('change', () => {
      if (!input.files || input.files.length === 0) {
        help.textContent = '';
        return;
      }
      const f = input.files[0];
      const sizeMB = (f.size / (1024 * 1024)).toFixed(2);
      help.textContent = `${f.name} — ${sizeMB} MB`;
      if (f.size > maxMB * 1024 * 1024) {
        alert(`This file exceeds the ${maxMB} MB limit set by the server.`);
        input.value = '';
        help.textContent = '';
      }
    });
  }

  // Auto-hide flash messages after a few seconds
  const flash = document.getElementById('flash');
  if (flash) {
    setTimeout(() => { flash.style.display = 'none'; }, 4000);
  }

  // Confirm delete
  document.querySelectorAll('.btn-del[data-confirm]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const msg = btn.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });
})();
