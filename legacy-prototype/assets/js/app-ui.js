(function (window, document) {
    "use strict";

    const app = window.EClassRecordApp || {};
    const THEME_KEY = "eclass_theme";
    const DEFAULT_THEME = "dark";
    const NOTICE_KEY_PREFIX = "eclass_notice_";
    let mobileMenuDocumentBound = false;

    function escapeHtml(value) {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function getTheme() {
        return window.localStorage.getItem(THEME_KEY) || DEFAULT_THEME;
    }

    function updateThemeIcons(root) {
        const scope = root || document;

        scope.querySelectorAll("[data-theme-toggle]").forEach(function (toggle) {
            const sunIcon = toggle.querySelector(".icon-sun");
            const moonIcon = toggle.querySelector(".icon-moon");

            if (!sunIcon || !moonIcon) {
                return;
            }

            if (getTheme() === "light") {
                sunIcon.style.display = "none";
                moonIcon.style.display = "block";
            } else {
                sunIcon.style.display = "block";
                moonIcon.style.display = "none";
            }
        });
    }

    function applyTheme(theme) {
        const normalizedTheme = theme === "light" ? "light" : "dark";
        document.documentElement.setAttribute("data-theme", normalizedTheme);
        window.localStorage.setItem(THEME_KEY, normalizedTheme);
        updateThemeIcons(document);
    }

    function bindThemeToggles(root) {
        const scope = root || document;

        scope.querySelectorAll("[data-theme-toggle]").forEach(function (toggle) {
            if (toggle.dataset.bound === "true") {
                return;
            }

            toggle.dataset.bound = "true";
            toggle.addEventListener("click", function () {
                applyTheme(getTheme() === "dark" ? "light" : "dark");
            });
        });

        updateThemeIcons(scope);
    }

    function setCurrentYear() {
        const currentYear = String(new Date().getFullYear());

        document.querySelectorAll("[data-current-year]").forEach(function (node) {
            node.textContent = currentYear;
        });
    }

    function showMessage(target, type, message) {
        if (!target) {
            return;
        }

        target.innerHTML = '<div class="alert alert-' + escapeHtml(type) + '">' + escapeHtml(message) + "</div>";
    }

    function clearMessage(target) {
        if (target) {
            target.innerHTML = "";
        }
    }

    function noticeKey(pageKey) {
        return NOTICE_KEY_PREFIX + String(pageKey || "global");
    }

    function setPageNotice(pageKey, type, message) {
        window.sessionStorage.setItem(noticeKey(pageKey), JSON.stringify({
            type: type,
            message: message
        }));
    }

    function consumePageNotice(pageKey) {
        const key = noticeKey(pageKey);
        const value = window.sessionStorage.getItem(key);

        if (!value) {
            return null;
        }

        window.sessionStorage.removeItem(key);

        try {
            return JSON.parse(value);
        } catch (error) {
            return null;
        }
    }

    function renderPageNotice(pageKey, target) {
        const notice = consumePageNotice(pageKey);

        if (!notice || !notice.message) {
            return;
        }

        showMessage(target, notice.type || "info", notice.message);
    }

    function emptyStateMarkup(title, description, actionsHtml) {
        return '' +
            '<div class="empty-state">' +
                '<div>' +
                    '<h3>' + escapeHtml(title || "Nothing to show yet") + '</h3>' +
                    '<p>' + escapeHtml(description || "Data will appear here once it becomes available.") + '</p>' +
                    (actionsHtml ? '<div class="button-row no-print">' + actionsHtml + '</div>' : "") +
                '</div>' +
            '</div>';
    }

    function themeToggleMarkup() {
        return '' +
            '<button class="nav-btn" type="button" data-theme-toggle title="Toggle theme">' +
                '<svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                    '<circle cx="12" cy="12" r="4"></circle>' +
                    '<path d="M12 2v2"></path><path d="M12 20v2"></path>' +
                    '<path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path>' +
                    '<path d="M2 12h2"></path><path d="M20 12h2"></path>' +
                    '<path d="M6.34 17.66l-1.41 1.41"></path><path d="M19.07 4.93l-1.41 1.41"></path>' +
                '</svg>' +
                '<svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">' +
                    '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>' +
                '</svg>' +
            '</button>';
    }

    function navIcon(name) {
        const icons = {
            dashboard: '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect></svg>',
            "class-list": '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13"></path><path d="M8 12h13"></path><path d="M8 18h13"></path><path d="M3 6h.01"></path><path d="M3 12h.01"></path><path d="M3 18h.01"></path></svg>',
            students: '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>',
            grading: '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>',
            settings: '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.33 1V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-.33-1A1.65 1.65 0 0 0 8 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1-.33H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1-.33A1.65 1.65 0 0 0 4.6 8a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8 4.6c.36 0 .71-.13 1-.37.28-.24.46-.58.5-.94V3a2 2 0 1 1 4 0v.09c.04.36.22.7.5.94.29.24.64.37 1 .37.49 0 .96-.19 1.31-.53l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06c-.34.35-.53.82-.53 1.31 0 .36.13.71.37 1 .24.29.58.46.94.5H21a2 2 0 1 1 0 4h-.09c-.36.04-.7.22-.94.5-.24.29-.37.64-.37 1z"></path></svg>'
        };

        return icons[name] || icons.dashboard;
    }

    function getNavigation(role) {
        if (role === "teacher") {
            return [
                { key: "dashboard", label: "Dashboard", href: "dashboard.html" },
                { key: "class-list", label: "Class List", href: "class-list.html" },
                { key: "students", label: "Students", href: "students.html" },
                { key: "grading", label: "Grading", href: "grading.html" },
                { key: "settings", label: "Settings", href: "settings.html" }
            ];
        }

        return [
            { key: "dashboard", label: "Dashboard", href: "dashboard.html" },
            { key: "class-list", label: "My Class", href: "class-list.html" },
            { key: "students", label: "My Records", href: "students.html" },
            { key: "grading", label: "Grades", href: "grading.html" },
            { key: "settings", label: "Settings", href: "settings.html" }
        ];
    }

    function bindLogoutButtons(root) {
        const scope = root || document;

        scope.querySelectorAll("[data-logout]").forEach(function (button) {
            if (button.dataset.bound === "true") {
                return;
            }

            button.dataset.bound = "true";
            button.addEventListener("click", function (event) {
                event.preventDefault();
                app.logoutUser();
                window.location.href = "login.html";
            });
        });
    }

    function bindMobileMenu(root) {
        const scope = root || document;
        const menuToggle = scope.querySelector("[data-menu-toggle]");
        const sidebar = scope.querySelector("#sidebar");

        if (menuToggle && sidebar && menuToggle.dataset.bound !== "true") {
            menuToggle.dataset.bound = "true";
            menuToggle.addEventListener("click", function () {
                sidebar.classList.toggle("open");
            });
        }

        if (sidebar) {
            sidebar.dataset.bound = "true";
        }

        if (!mobileMenuDocumentBound) {
            mobileMenuDocumentBound = true;
            document.addEventListener("click", function (event) {
                const activeSidebar = document.querySelector("#sidebar");
                const activeMenuToggle = document.querySelector("[data-menu-toggle]");
                const viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;

                if (!activeSidebar) {
                    return;
                }

                if (viewportWidth > 992 || !activeSidebar.classList.contains("open")) {
                    return;
                }

                if (!activeSidebar.contains(event.target) && !(activeMenuToggle && activeMenuToggle.contains(event.target))) {
                    activeSidebar.classList.remove("open");
                }
            });
        }
    }

    function refreshCommonUI() {
        app.ensureSeededData();
        applyTheme(getTheme());
        bindThemeToggles(document);
        bindLogoutButtons(document);
        bindMobileMenu(document);
        setCurrentYear();
    }

    function renderProtectedShell(options) {
        const target = document.getElementById("app-shell");
        const user = app.getCurrentUser();

        if (!target || !user) {
            return;
        }

        const headerMeta = (options.headerMeta || []).map(function (item) {
            return '<span class="status-pill">' + escapeHtml(item) + "</span>";
        }).join("");
        const navigation = getNavigation(user.role).map(function (item) {
            const activeClass = item.key === options.activePage ? " active" : "";
            return '' +
                '<li class="nav-item">' +
                    '<a href="' + item.href + '" class="nav-link' + activeClass + '">' +
                        navIcon(item.key) +
                        escapeHtml(item.label) +
                    '</a>' +
                '</li>';
        }).join("");

        target.innerHTML = '' +
            '<div class="background"></div>' +
            '<div class="orb orb-1"></div>' +
            '<div class="orb orb-2"></div>' +
            '<div class="orb orb-3"></div>' +
            '<div class="dashboard">' +
                '<aside class="sidebar" id="sidebar">' +
                    '<div class="sidebar-header">' +
                        '<div class="logo">EC</div>' +
                        '<span class="logo-text">E-Class Record</span>' +
                    '</div>' +
                    '<ul class="nav-menu">' +
                        '<li class="nav-section">' +
                            '<span class="nav-section-title">Navigation</span>' +
                            '<ul>' + navigation + '</ul>' +
                        '</li>' +
                    '</ul>' +
                    '<div class="sidebar-footer">' +
                        '<div class="user-profile">' +
                            '<div class="user-avatar">' + escapeHtml(app.getInitials(user.name)) + '</div>' +
                            '<div class="user-info">' +
                                '<div class="user-name">' + escapeHtml(user.name) + '</div>' +
                                '<div class="user-role">' + escapeHtml(app.getRoleLabel(user.role)) + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</aside>' +
                '<main class="main-content">' +
                    '<nav class="navbar app-navbar">' +
                        '<div class="navbar-left">' +
                            '<button class="mobile-menu-toggle" type="button" data-menu-toggle aria-label="Open navigation">' +
                                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                                    '<line x1="3" y1="12" x2="21" y2="12"></line>' +
                                    '<line x1="3" y1="6" x2="21" y2="6"></line>' +
                                    '<line x1="3" y1="18" x2="21" y2="18"></line>' +
                                '</svg>' +
                            '</button>' +
                            '<div>' +
                                '<h1 class="page-title">' + escapeHtml(options.title || "Dashboard") + '</h1>' +
                                '<p class="page-subtitle">' + escapeHtml(options.subtitle || "") + '</p>' +
                            '</div>' +
                        '</div>' +
                        '<div class="navbar-right">' +
                            '<div class="navbar-user-chip">' +
                                '<span class="user-chip-avatar">' + escapeHtml(app.getInitials(user.name)) + '</span>' +
                                '<span>' + escapeHtml(user.name) + '</span>' +
                            '</div>' +
                            themeToggleMarkup() +
                        '</div>' +
                    '</nav>' +
                    '<section class="glass-card hero-panel">' +
                        '<div>' +
                            '<span class="eyebrow">' + escapeHtml(options.bannerLabel || (user.role === "teacher" ? "Teacher Workspace" : "Student Workspace")) + '</span>' +
                            '<div class="hero-meta">' + headerMeta + '</div>' +
                        '</div>' +
                        '<div class="hero-actions no-print">' + (options.headerActions || "") + '</div>' +
                    '</section>' +
                    (options.content || "") +
                    '<footer class="site-footer app-footer">' +
                        '<p>E-Class Record System - Local frontend prototype - <span data-current-year></span></p>' +
                    '</footer>' +
                '</main>' +
            '</div>';

        refreshCommonUI();
    }

    function redirectIfAuthenticated() {
        app.ensureSeededData();

        if (app.getCurrentUser()) {
            window.location.href = "dashboard.html";
        }
    }

    function requireAuth() {
        app.ensureSeededData();

        const user = app.getCurrentUser();

        if (!user) {
            window.location.href = "login.html";
            return null;
        }

        return user;
    }

    function syncShellUser(user) {
        if (!user) {
            return;
        }

        document.querySelectorAll(".user-name").forEach(function (node) {
            node.textContent = user.name;
        });

        document.querySelectorAll(".user-role").forEach(function (node) {
            node.textContent = app.getRoleLabel(user.role);
        });

        document.querySelectorAll(".user-chip-avatar, .user-avatar, .profile-avatar").forEach(function (node) {
            node.textContent = app.getInitials(user.name);
        });
    }

    function prepareCanvas(canvas) {
        const context = canvas.getContext("2d");
        const ratio = window.devicePixelRatio || 1;
        const width = canvas.clientWidth;
        const height = canvas.clientHeight;

        canvas.width = Math.floor(width * ratio);
        canvas.height = Math.floor(height * ratio);
        context.setTransform(ratio, 0, 0, ratio, 0, 0);

        return {
            context: context,
            width: width,
            height: height
        };
    }

    function getChartColors() {
        const isLight = getTheme() === "light";
        return {
            text: isLight ? "rgba(15, 23, 42, 0.72)" : "rgba(245, 245, 244, 0.72)",
            grid: isLight ? "rgba(15, 23, 42, 0.08)" : "rgba(255, 255, 255, 0.1)"
        };
    }

    function drawLineChart(canvas, labels, values, config) {
        if (!canvas) {
            return;
        }

        const chart = prepareCanvas(canvas);
        const context = chart.context;
        const width = chart.width;
        const height = chart.height;
        const padding = 36;
        const maxValue = Math.max((config && config.max) || 100, 1);
        const colors = getChartColors();
        const lineColor = (config && config.lineColor) || "#34d399";
        const areaColor = (config && config.areaColor) || "rgba(52, 211, 153, 0.14)";

        context.clearRect(0, 0, width, height);
        context.strokeStyle = colors.grid;
        context.fillStyle = colors.text;
        context.font = "12px Outfit";

        for (let index = 0; index <= 5; index += 1) {
            const y = padding + (height - padding * 2) * (index / 5);
            context.beginPath();
            context.moveTo(padding, y);
            context.lineTo(width - padding, y);
            context.stroke();
            context.fillText(String(Math.round(maxValue - (maxValue / 5) * index)), 6, y + 4);
        }

        if (!values.length) {
            context.fillText("No chart data available yet.", padding, height / 2);
            return;
        }

        const step = values.length === 1 ? 0 : (width - padding * 2) / (values.length - 1);
        const points = values.map(function (value, index) {
            return {
                x: padding + (step * index),
                y: height - padding - ((Number(value || 0) / maxValue) * (height - padding * 2))
            };
        });

        context.beginPath();
        points.forEach(function (point, index) {
            if (index === 0) {
                context.moveTo(point.x, point.y);
            } else {
                context.lineTo(point.x, point.y);
            }
        });
        context.strokeStyle = lineColor;
        context.lineWidth = 3;
        context.stroke();

        context.beginPath();
        context.moveTo(points[0].x, height - padding);
        points.forEach(function (point) {
            context.lineTo(point.x, point.y);
        });
        context.lineTo(points[points.length - 1].x, height - padding);
        context.closePath();
        context.fillStyle = areaColor;
        context.fill();

        points.forEach(function (point, index) {
            context.beginPath();
            context.arc(point.x, point.y, 5, 0, Math.PI * 2);
            context.fillStyle = lineColor;
            context.fill();
            context.fillStyle = colors.text;
            context.fillText(labels[index] || "", Math.max(padding, point.x - 16), height - 10);
        });
    }

    function drawBarChart(canvas, labels, values, config) {
        if (!canvas) {
            return;
        }

        const chart = prepareCanvas(canvas);
        const context = chart.context;
        const width = chart.width;
        const height = chart.height;
        const padding = 36;
        const maxValue = Math.max((config && config.max) || 100, 1);
        const colors = getChartColors();
        const barColor = (config && config.barColor) || "#d4a574";

        context.clearRect(0, 0, width, height);
        context.strokeStyle = colors.grid;
        context.fillStyle = colors.text;
        context.font = "12px Outfit";

        for (let index = 0; index <= 5; index += 1) {
            const y = padding + (height - padding * 2) * (index / 5);
            context.beginPath();
            context.moveTo(padding, y);
            context.lineTo(width - padding, y);
            context.stroke();
        }

        if (!values.length) {
            context.fillText("No chart data available yet.", padding, height / 2);
            return;
        }

        const columnWidth = (width - padding * 2) / values.length;
        const barWidth = Math.min(48, columnWidth * 0.55);

        values.forEach(function (value, index) {
            const x = padding + (index * columnWidth) + ((columnWidth - barWidth) / 2);
            const barHeight = (Number(value || 0) / maxValue) * (height - padding * 2);
            const y = height - padding - barHeight;

            context.fillStyle = barColor;
            context.fillRect(x, y, barWidth, barHeight);
            context.fillStyle = colors.text;
            context.fillText(String(app.round(value, 1)), x, Math.max(16, y - 8));
            context.fillText(labels[index] || "", x, height - 10);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        refreshCommonUI();
    });

    Object.assign(app, {
        escapeHtml: escapeHtml,
        getTheme: getTheme,
        applyTheme: applyTheme,
        bindThemeToggles: bindThemeToggles,
        showMessage: showMessage,
        clearMessage: clearMessage,
        setPageNotice: setPageNotice,
        consumePageNotice: consumePageNotice,
        renderPageNotice: renderPageNotice,
        emptyStateMarkup: emptyStateMarkup,
        refreshCommonUI: refreshCommonUI,
        renderProtectedShell: renderProtectedShell,
        redirectIfAuthenticated: redirectIfAuthenticated,
        requireAuth: requireAuth,
        syncShellUser: syncShellUser,
        drawLineChart: drawLineChart,
        drawBarChart: drawBarChart,
        navIcon: navIcon
    });

    window.EClassRecordApp = app;
}(window, document));
