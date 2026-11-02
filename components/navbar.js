/*
 * Navbar Component
 * Handles navigation display, active page detection,
 * and login/account button display.
 */

const navbar = `
<nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
    <div class="container p-2">

        <!-- Brand -->
        <a class="navbar-brand fw-bold fs-4" href="index.php">
            COS30020
        </a>

        <!-- Mobile Menu Button -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation -->
        <div class="collapse navbar-collapse" id="mainNavbar">

            <!-- Left Navigation -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="main_menu.php">
                        Main menu
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="catalog.php">
                        Catalogue
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="activities.php">
                        Activities
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="community.php">
                        Community
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="about.php">
                        About
                    </a>
                </li>
            </ul>

            <!-- Authentication -->
            <div class="d-flex gap-3 align-items-center" id="navbar-auth">
            </div>

        </div>
    </div>
</nav>
`;

document.addEventListener("DOMContentLoaded", () => {
    const navbarContainer = document.getElementById("navbar-container");

    if (!navbarContainer) {
        console.error("Navbar container #navbar-container was not found.");
        return;
    }

    // Insert Navbar
    navbarContainer.innerHTML = navbar;

    /*
     * Automatically detect the current page
     * and apply Bootstrap's active state.
     */
    const currentPage =
        window.location.pathname.split("/").pop() || "index.php";

    const navLinks = navbarContainer.querySelectorAll(".nav-link");

    navLinks.forEach((link) => {
        const href = link.getAttribute("href");

        if (!href) {
            return;
        }

        const linkPage = href
            .split("/")
            .pop()
            .split("?")[0]
            .split("#")[0];

        if (linkPage === currentPage) {
            link.classList.add("active");
            link.setAttribute("aria-current", "page");
        }
    });

    /*
     * Display authentication buttons based on PHP Session.
     */
    const authContainer = document.getElementById("navbar-auth");

    if (window.isLoggedIn === true) {
        // Logged in: show Account button
        authContainer.innerHTML = `
            <a
                href="profile.php"
                class="btn btn-dark btn-lg custom-login-button d-flex align-items-center gap-2"
                aria-label="View Account Profile"
            >
                <i class="bi bi-person-circle"></i>
                <span>Account</span>
            </a>
        `;
    } else {
        // Logged out: show Login and Sign Up buttons
        authContainer.innerHTML = `
            <a
                href="login.php"
                class="btn btn-dark btn-lg custom-login-button navbar-auth-button"
            >
                Login
            </a>

            <a
                href="registration.php"
                class="btn btn-light btn-lg navbar-auth-button"
            >
                Sign Up
            </a>
        `;
    }
});