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

// /staff/assets/js/staff.js
(function ($) {
  'use strict';

  var $recipient      = $('#recipient');
  var $recipientOther = $('#recipientOther');
  var $suggestions    = $('#suggestions');
  var $unreadBadge    = $('#unreadBadge');
  var $inboxAccordion = $('#inboxAccordion');

  // Show/hide external email box
  function toggleExternalEmail() {
    var show = ($recipient.val() === 'Others');
    $recipientOther.toggle(show);
    if (!show) $suggestions.hide();
  }

  // Bind recipient UI + email suggestions
  function bindRecipientUI() {
    $recipient.on('change', toggleExternalEmail);

    $recipientOther.on('input', function () {
      var v = $(this).val();
      if (v.length < 3) return $suggestions.hide();

      $.get(API_SEARCH_CONTACTS, { q: v }, function (data) {
        $suggestions.empty();
        if (Array.isArray(data) && data.length) {
          data.forEach(function (email) {
            $suggestions.append('<div class="suggestion">' + email + '</div>');
          });
          $suggestions.show();
        } else {
          $suggestions.hide();
        }
      }, 'json');
    });

    $(document).on('click', '.suggestion', function () {
      $recipientOther.val($(this).text());
      $suggestions.hide();
    });

    $(document).on('click', function (e) {
      if (!$(e.target).closest('#recipientOther, #suggestions').length) {
        $suggestions.hide();
      }
    });

    toggleExternalEmail();
  }


// /assets/js/staff_booking_report.js
(function () {
  'use strict';

  // Toggle collapsible detail rows
  document.addEventListener('click', function (e) {
    const t = e.target.closest('.toggle-details');
    if (!t) return;
    const id = t.getAttribute('data-target');
    const row = document.getElementById(id);
    if (!row) return;
    row.classList.toggle('open');
    const icon = t.querySelector('.arrow-icon');
    if (icon) icon.classList.toggle('open');
  });

})});

// /assets/js/staff_reports.js
(function () {
  'use strict';

  // Auto-submit on filter change for common selects to speed UX
  const forms = document.querySelectorAll('.cardx form');
  forms.forEach((form) => {
    const autos = form.querySelectorAll('select');
    autos.forEach((sel) => {
      sel.addEventListener('change', () => form.submit());
    });
  });

  // Highlight table rows based on stock (client side only; server already marks badges)
  const report = (document.body.getAttribute('data-active-report') || '').toLowerCase();
  if (report === 'inventory') {
    document.querySelectorAll('tr[data-stock]').forEach((tr) => {
      const q = parseInt(tr.getAttribute('data-stock') || '0', 10);
      if (q <= 0) {
        tr.classList.add('row-out');       // style in your CSS if you like
      } else if (q <= 10) {
        tr.classList.add('row-low');       // style in your CSS if you like
      }
    });
  }

  // Nothing else fancy for now; table sorting/export handled server side (CSV link).
})();