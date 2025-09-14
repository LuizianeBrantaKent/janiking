document.addEventListener('DOMContentLoaded', function() {
    // Only run this script if we're on the Book Appointment page
    const appointmentDateInput = document.getElementById('appointmentDate');
    const timeSlotsContainer = document.getElementById('timeSlots');
    const appointmentTimeInput = document.getElementById('appointmentTime');
    const submitButton = document.querySelector('button[type="submit"]');

    if (!appointmentDateInput || !timeSlotsContainer || !appointmentTimeInput || !submitButton) {
        console.log('Not on Book Appointment page. Exiting book_appointment.js.');
        return; // Exit if required elements are missing
    }

    // Disable submit until time is selected
    submitButton.disabled = true;

    // Initialize datepicker
    if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
        $(appointmentDateInput).datepicker({
            format: 'yyyy-mm-dd',
            startDate: '0d',
            autoclose: true,
            daysOfWeekDisabled: [0, 6]
        }).on('changeDate', function(e) {
            const selectedDate = e.format('yyyy-mm-dd');
            appointmentDateInput.value = selectedDate;
            console.log('Selected date:', selectedDate);

            // Populate time slots
            const timeSlots = ['9:00 AM', '10:30 AM', '2:30 PM', '4:00 PM'];
            let slotsHtml = '';
            timeSlots.forEach(slot => {
                slotsHtml += `<button type="button" class="btn btn-outline-primary time-slot">${slot}</button>`;
            });
            timeSlotsContainer.innerHTML = slotsHtml;
        });
    } else {
        console.error('jQuery or datepicker not loaded.');
    }

    // Handle time slot selection
    timeSlotsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('time-slot')) {
            document.querySelectorAll('.time-slot').forEach(btn => btn.classList.remove('btn-primary'));
            e.target.classList.add('btn-primary');
            appointmentTimeInput.value = e.target.textContent;
            console.log('Selected time:', appointmentTimeInput.value);

            // Enable submit button
            submitButton.disabled = false;
        }
    });

    // Close modal functionality
    if (typeof $ !== 'undefined') {
        $('.close').on('click', function() {
            $(this).closest('.modal').modal('hide');
        });
    }
});
