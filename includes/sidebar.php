<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>

    <div class="text-center my-4">
        <a href="/capstone/index.php" class="sidebar-logo">
            <img src="/capstone/assets/images/logo.png" alt="EventJoin Logo" class="light-logo" style="height: 70px;">
        </a>
    </div>

    <div class="sidebar-menu-area">
        <ul class="sidebar-menu">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <!-- Visitor Menu -->
                <li>
                    <a href="/capstone/index.php">
                        <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                        <span>Browse Events</span>
                    </a>
                </li>
                <li>
                    <a href="/capstone/login.php">
                        <iconify-icon icon="solar:login-2-outline" class="menu-icon"></iconify-icon>
                        <span>Login</span>
                    </a>
                </li>
                <li>
                    <a href="/capstone/signup.php">
                        <iconify-icon icon="solar:user-plus-outline" class="menu-icon"></iconify-icon>
                        <span>Sign-up</span>
                    </a>
                </li>
            <?php else: ?>
                <!-- Logged-in Registered User Menu -->
                <li>
                    <a href="/capstone/index.php">
                        <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                        <span>Home</span>
                    </a>
                </li>
                <?php if ($_SESSION['role'] === 'venue_manager'): ?>
                    <!-- Venue Manager Menu -->
                    <li class="sidebar-divider"></li>
                    <li>
                        <a href="/capstone/venue_manager/admin_dashboard.php">
                            <iconify-icon icon="solar:monitor-outline" class="menu-icon"></iconify-icon>
                            <span>Admin Dashboard</span>
                        </a>
                    </li>
                <?php endif ?>
                <li class="dropdown">
                    <a href="javascript:void(0)">
                        <iconify-icon icon="solar:calendar-outline" class="menu-icon"></iconify-icon>
                        <span>Events</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="/capstone/registered_user/my_events.php">
                                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> My Events
                            </a>
                        </li>
                        <li>
                            <a href="/capstone/registered_user/upcoming_events.php">
                                <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Upcoming Events
                            </a>
                        </li>
                        <li>
                            <a href="/capstone/registered_user/past_events.php">
                                <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Past Events
                            </a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="/capstone/profile.php">
                        <iconify-icon icon="solar:user-id-outline" class="menu-icon"></iconify-icon>
                        <span>Profile</span>
                    </a>
                </li>

                <li>
                    <a href="/capstone/logout.php">
                        <iconify-icon icon="lucide:log-out" class="menu-icon"></iconify-icon>
                        <span>Logout</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</aside>
