// ============================
// JaniKing Staff – Announcements JS
// ============================
(function ($) {
  'use strict';

  // API endpoints
  const API_UNREAD          = '/staff/staff_announcement/unread_count.php';
  const API_MARK_READ       = '/staff/staff_announcement/mark_thread_read.php';
  const API_SEARCH_CONTACTS = '/staff/staff_announcement/search_contacts.php';
  const API_RECENT          = '/staff/staff_announcement/list_recent.php'; // replace with correct endpoint if needed

  // DOM elements
  const $recipient      = $('#recipient');
  const $recipientOther = $('#recipientOther');
  const $suggestions    = $('#suggestions');
  const $unreadBadge    = $('#unreadBadge');
  const $recentList     = $('#recentAnnouncements'); // container for recent announcements
  const $inboxAccordion = $('#inboxAccordion');

  // Show/hide external email box
  function toggleExternalEmail() {
    const show = ($recipient.val() === 'Others');
    $recipientOther.toggle(show);
    if (!show) $suggestions.hide();
  }

  // Bind recipient UI + email suggestions
  function bindRecipientUI() {
    $recipient.on('change', toggleExternalEmail);

    $recipientOther.on('input', function () {
      const v = $(this).val();
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

  // Refresh unread badge
  function refreshUnread() {
    $.get(API_UNREAD, function (res) {
      const n = (res && res.unread) ? parseInt(res.unread, 10) : 0;
      if (n > 0) $unreadBadge.text(n).removeClass('d-none');
      else $unreadBadge.addClass('d-none');
    }, 'json');
  }

  function startUnreadPolling() {
    refreshUnread();
    setInterval(refreshUnread, 15000);
  }

  // Mark a thread as read when it opens
  function bindThreadOpenMarksRead() {
    $inboxAccordion.on('show.bs.collapse', function (e) {
      const $btn = $(e.target).prev().find('button[data-thread-id]');
      const id = $btn.data('thread-id');
      if (!id) return;
      $.get(API_MARK_READ, { thread_id: id }, function () {
        refreshUnread();
        $btn.find('.badge-danger').remove();
      });
    });
  }

  // Load recent announcements
  async function loadRecentAnnouncements() {
    try {
      const res = await fetch(API_RECENT);
      const data = await res.json();
      if (!data || !Array.isArray(data.items)) return;

      $recentList.empty();
      data.items.forEach(item => {
        const html = `
          <div class="announcement-card">
            <div class="announcement-header">
              <h5 class="announcement-title">${item.title}</h5>
              <span class="announcement-date">${item.created}</span>
            </div>
            <div class="announcement-content">${item.content}</div>
          </div>
        `;
        $recentList.append(html);
      });
    } catch (err) {
      console.error('Failed to load recent announcements:', err);
    }
  }

  // Compose announcement submission
  $('#composeAnnouncement').on('submit', async function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    const res = await fetch('/staff/api/announcements_create.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      loadRecentAnnouncements(); // reload recent list
      this.reset();
    }
  });

  // Initialize all
  $(function () {
    bindRecipientUI();
    bindThreadOpenMarksRead();
    startUnreadPolling();
    loadRecentAnnouncements();
  });

})(jQuery);
