// ===== Admin JavaScript Functions =====

// Highlight active navigation link
document.addEventListener('DOMContentLoaded', function() {
    highlightActiveNavLink();
    initializeDetailToggles();
});

// Function to highlight the active navigation link
function highlightActiveNavLink() {
    const navLinks = document.querySelectorAll('.admin-navbar a.nav-link');
    const currentPage = window.location.pathname.split('/').pop(); 
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop();
        link.classList.remove('active');
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });
}

// Toggle details rows
function initializeDetailToggles() {
    document.addEventListener("click", function (e) {
        const cell = e.target.closest(".toggle-details");
        if (!cell) return;
        
        const id = cell.getAttribute("data-target");
        if (!id) return;

        const row = document.getElementById(id);
        if (row) {
            row.classList.toggle("open");
            cell.classList.toggle("open"); // toggles arrow rotation
        }
    });
}
