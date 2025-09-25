<!-- ../includes/header.php -->
<header class="admin-header">
    <div class="logo-section">
        <img src="/assets/images/logo.png" alt="JaniKing Logo" class="brand-logo">
    </div>
    
    <div class="user-section">
        <div class="user-info">
            <div class="user-name">Admin User</div>
            <div class="user-role">Administrator</div>
        </div>
        <div class="user-avatar">AU</div>
    </div>
</header>

<style>
.admin-header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 75px; /* header height */
    background-color: #ffffffff;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #d1d6dc;
    z-index: 1000;
}

.logo-section {
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-logo { height: 200px; width: auto; }
.brand-text { color: #004990; font-size: 22px; font-weight: 700; }

.user-section {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-info { text-align: right; }
.user-name { font-weight: 600; color: #32325d; font-size: 15px; }
.user-role { color: #004990; font-size: 13px; font-weight: 500; }

.user-avatar {
    width: 40px;
    height: 40px;
    background-color: #004990;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 16px;
}

@media (max-width: 576px){
    .admin-header { flex-direction: column; text-align: center; gap: 15px; }
    .user-info { text-align: center; }
}
</style>
