<template>
    <div class="main">
        <header id="header" class="header fixed-top d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-between">
                <a
                    :href="route('dashboard')"
                    class="logo d-flex align-items-center"
                >
                <img
                                            src="/backend/assets/img/logo.png"
                                            height="100"
                                            alt="logo"
                                        />
                </a>

                <i class="bi bi-list toggle-sidebar-btn"></i>
            </div>

            <!-- End Search Bar -->

            <nav class="header-nav ms-auto">
                <ul class="d-flex align-items-center">
                    <li class="nav-item dropdown pe-3">
                        <a
                            class="nav-link nav-profile d-flex align-items-center pe-0 dropdown-toggle theme-text-color"
                            href="#"
                            data-bs-toggle="dropdown"
                        >
                            <span class="ps-2 theme-text-color">
                                {{ $page.props.user.name }}
                            </span>
                        </a>

                        <ul
                            class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile"
                        >
                            <li>
                                <a
                                    class="dropdown-item d-flex align-items-center"
                                    href="/profile"
                                >
                                    <i class="bi bi-gear theme-text-color"></i>
                                    <span>Account Settings</span>
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <li>
                                <a
                                    class="dropdown-item d-flex align-items-center"
                                    href="javascript:;"
                                    @click="logout"
                                >
                                    <i
                                        class="bi bi-box-arrow-right theme-text-color"
                                    ></i>
                                    <span>Sign Out</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <!-- End Icons Navigation -->
        </header>
        <!-- End Header -->

        <!-- ======= Sidebar ======= -->
        <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <!-- Dashboard -->
        <li class="nav-item">
            <a
                class="nav-link theme-text-color"
                :class="{ active: isActive('dashboard') }"
                :href="route('dashboard')"
            >
                <i class="bi bi-house-door-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Visa Section (Only for Super Admin & Employees) -->
        <li v-if="$page.props.user.role === 'super admin' || $page.props.user.role === 'employee'" class="nav-item">
            <a
                class="nav-link"
                data-bs-target="#icons-nav"
                data-bs-toggle="collapse"
                href="#"
                :class="{ active: isParentActive(['visa.individual', 'visa.family']) }"
            >
                <i class="bi bi-building"></i>
                <span>Pak Visa</span>
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul
                id="icons-nav"
                class="nav-content collapse"
                data-bs-parent="#sidebar-nav"
                :class="{ show: isParentActive(['visa.individual', 'visa.family']) }"
            >
                <li>
                    <a :href="route('visa.individual')" :class="{ active: isActive('visa.individual') }">
                        <span>Individual</span>
                    </a>
                </li>
                <li>
                    <a :href="route('visa.family')" :class="{ active: isActive('visa.family') }">
                        <span>Family</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Employees (Only for Super Admin) -->
        <li v-if="$page.props.user.role === 'super admin'" class="nav-item">
            <a class="nav-link" :href="route('employees')" :class="{ active: isActive('employees') }">
                <i class="bi bi-people"></i>
                <span>Employees</span>
            </a>
        </li>

        <!-- Referrals (Super Admin & Referrals) -->
        <li v-if="$page.props.user.role === 'super admin'" class="nav-item">
            <a class="nav-link" :href="route('referrals')" :class="{ active: isActive('referrals') }">
                <i class="bi bi-person-lines-fill"></i>
                <span>Referrals</span>
            </a>
        </li>

        <!-- Software Users (Only for Super Admin) -->
        <li v-if="$page.props.user.role === 'super admin'" class="nav-item">
            <a class="nav-link" :href="route('users')" :class="{ active: isActive('users') }">
                <i class="bi bi-person-badge-fill"></i>
                <span>Software Users</span>
            </a>
        </li>
    </ul>
</aside>


        <!-- End Sidebar-->

        <slot></slot>

        <!-- ======= Footer ======= -->
        <footer id="footer" class="footer">
            <div class="copyright">
                &copy; Copyright <strong><span>TTC Global</span></strong
                >. All Rights Reserved
            </div>
            <div class="credits">
                Designed by
                <a href="https://goritmi.co.uk/">Goritmi</a>
            </div>
        </footer>
        <!-- End Footer -->
    </div>
</template>

<script>
 import axios from "axios";

export default {
    mounted() {

        let bootstrapJs = document.createElement("script");
        bootstrapJs.setAttribute(
            "src",
            "/backend/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"
        );
        document.head.appendChild(bootstrapJs);

        let mainJs = document.createElement("script");
        mainJs.setAttribute("src", "/backend/assets/js/main.js");
        document.head.appendChild(mainJs);


         
         
    },

    methods: {
        logout() {
            axios
                .post(route("api.logout"), this.form, {
                    headers: {
                        Authorization: "Bearer " + this.$page.props.auth_token,
                    },
                })
                .then(() => {
                    this.$inertia.get(route("login"));
                })
                .catch((error) => {});
        },

        // Check if route is active
        isParentActive(routeNames) {
            return routeNames.some((route) => {
                const isActive = this.isActive(route);

                return isActive;
            });
        },
        // Check if any submenu should be expanded
        isActive(routeName) {
            const currentPath = window.location.pathname;
            const routePath = new URL(
                this.route(routeName),
                window.location.origin
            ).pathname;

            return (
                currentPath === routePath || currentPath.startsWith(routePath)
            );
        },
    },
};
</script>

<style>
@import url("public/backend/assets/vendor/bootstrap/css/bootstrap.min.css");
@import url("public/backend/assets/vendor/bootstrap-icons/bootstrap-icons.css");
@import url("public/backend/assets/vendor/boxicons/css/boxicons.min.css");
@import url("public/backend/assets/vendor/remixicon/remixicon.css");
@import url("public/backend/assets/vendor/simple-datatables/style.css");
@import url("public/backend/assets/css/style.css");
@import url("public/backend/assets/css/custom.css");

@import "toastr";
</style>

<style>
@import "@vueform/multiselect/themes/default.css";
/* custom style that should be moved into the custom.css file later */
.section-title {
    text-align: left;
    padding-bottom: 5px;
}

.section-title h5 {
    font-size: 22px;
    font-weight: bold;
    text-transform: uppercase;
    position: relative;
    color: #3c444d;
}
/* .section-title h5::before {
    content: "";
    position: absolute;
    display: block;
    width: 120px;
    height: 1px;
    background: #ddd;
    bottom: 1px;
    left: calc(50% - 60px);
}
.section-title h5::after {
    content: "";
    position: absolute;
    display: block;
    width: 40px;
    height: 3px;
    background: #3C444D;
    bottom: 0;
    left: calc(50% - 20px);
} */
.c-file-padding {
    padding: 1rem 0.75rem !important;
}
.invalid-feedback {
    display: block !important;
}
.invalid-bg {
    border-color: #f8d4d4 !important;
    background-color: #f8d4d4 !important;
}

.btn-success {
    background-color: #3c444d !important;
    border-color: #3c444d !important;
}

.btn-success:hover {
    background-color: #3c444d !important;
    border-color: #3c444d !important;
}

.btn-success:active {
    background-color: #3c444d !important;
    border-color: #3c444d !important;
}

.btn-success:focus {
    color: #fff !important;
    box-shadow: none !important;
}
.toast-success {
    background-color: #3c444d !important;
}
.theme-text-color {
    color: #3c444d !important;
    font-weight: bold;
}
.theme-bg-color {
    background-color: #3c444d !important;
}
.sidebar-nav .nav-link.active {
    background-color: #3c444d !important; /* Theme color */
    color: white !important;
    font-weight: bold;
}
.sidebar-nav .nav-link.active {
    background-color: #c3c7cc42 !important;
    color: black !important;
    font-weight: bold;
}
.sidebar-nav .nav-link {
    background: white !important;
}
.sidebar-nav .nav-link i {
    color: black !important;
}
.sidebar-nav .nav-content a {
    color: #3c444d !important;
}
.sidebar-nav .nav-content a.active {
    color: black !important;
    font-size: 15px !important;
    background-color: #c3c7cc42 !important;
}
.sidebar-nav .nav-link {
    color: #3c444d !important;
}
.sidebar-nav .nav-link:hover {
    color: black !important;
}
.sidebar-nav .nav-content a {
    color: #3c444d;
}
.sidebar-nav .nav-content a:hover {
    color: black !important;
}

.logo img {
    max-height: 40px;
    margin-right: 10px;
}
.multiselect-option.is-selected {
    background: #3c444d !important;
    color: var(--ms-option-color-selected, #fff);
}
.multiselect-option.is-selected.is-pointed {
    background: #3c444d !important;
    color: var(--ms-option-color-selected, #fff);
}
</style>
