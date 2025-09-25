// Contact Us Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const phoneInput = document.getElementById('phone');
    const startLiveChatBtn = document.getElementById('startLiveChat');

    // Sanitize and format phone number
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d+]/g, ''); // Keep only digits and +
        if (value.startsWith('+61')) {
            value = value.slice(0, 12);
        } else if (value.startsWith('0')) {
            value = '+61' + value.slice(1, 11);
        } else if (value.startsWith('4')) {
            value = '+61' + value.slice(0, 10);
        } else {
            value = '+61' + value.slice(0, 10);
        }

        // Format nicely
        if (value.length >= 12) {
            const digits = value.replace('+61', '');
            if (digits.startsWith('4')) {
                e.target.value = `+61 ${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6, 10)}`;
            } else if (digits.startsWith('0')) {
                e.target.value = `+61 ${digits.slice(0, 1)} ${digits.slice(1, 5)} ${digits.slice(5, 9)}`;
            }
        } else {
            e.target.value = value;
        }
    });

    // Submit form validation
    form.addEventListener('submit', function(e) {
        const phone = phoneInput.value;
        const phoneRegex = /^\+?61\s?(4\d{2}|0[2-8])\s?\d{3}\s?\d{3}$/;
        if (!phoneRegex.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid Australian phone number (e.g., +61 4XX XXX XXX or +61 2 XXXX XXXX).');
            return;
        }
    });

    // Trigger Tawk.to chat on button click
    startLiveChatBtn.addEventListener('click', function() {
        if (typeof Tawk_API !== 'undefined' && Tawk_API.maximize) {
            Tawk_API.maximize();
        } else {
            alert('Live chat is not available. Please try again later or contact us at info@janiking.com.');
        }
    });

    // =========================
    // Learn More Buttons → Bootstrap Modal
    // =========================
    const learnMoreButtons = document.querySelectorAll('.learn-more');

    // Create Bootstrap modal structure dynamically
    const modalHtml = `
        <div class="modal fade" id="learnMoreModal" tabindex="-1" role="dialog" aria-labelledby="learnMoreModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="learnMoreModalLabel">More Information</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body"></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modalBody = document.querySelector('#learnMoreModal .modal-body');
    const modalTitle = document.querySelector('#learnMoreModalLabel');

    learnMoreButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            let title = '';
            let text = '';

            switch (target) {
                case 'franchise-support':
                    title = 'Franchise Support';
                    text = 'JaniKing provides ongoing franchise support, including marketing assistance, operational guidance, and access to national accounts to ensure your success.';
                    break;
                case 'training-programs':
                    title = 'Training Programs';
                    text = 'Our comprehensive training programs cover cleaning techniques, business management, and safety protocols, preparing you for a thriving franchise.';
                    break;
                case 'franchise-faq':
                    title = 'Franchise FAQ';
                    text = 'Frequently Asked Questions include investment details, training duration, and support options. Visit our FAQ page for more info.';
                    break;
            }

            modalTitle.textContent = title;
            modalBody.textContent = text;

            // Show modal (Bootstrap)
            $('#learnMoreModal').modal('show');
        });
    });

    // =========================
    // Leaflet Map
    // =========================
    const map = L.map('map').setView([20.0, 0.0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    const locations = [
        { lat: 32.7767, lng: -96.7970, title: "Dallas, USA" },
        { lat: 43.6510, lng: -79.3470, title: "Toronto, Canada" },
        { lat: 51.5074, lng: -0.1278, title: "London, UK" },
        { lat: -33.8688, lng: 151.2093, title: "Sydney, Australia" },
        { lat: 35.6762, lng: 139.6503, title: "Tokyo, Japan" },
    ];

    locations.forEach((location) => {
        L.marker([location.lat, location.lng]).addTo(map)
            .bindPopup(location.title);
    });

    const bounds = L.latLngBounds(locations.map(loc => [loc.lat, loc.lng]));
    map.fitBounds(bounds);
});
