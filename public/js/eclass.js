(function () {
    'use strict';

    const THEME_KEY = 'eclass_theme';
    const DEFAULT_THEME = 'dark';
    let resizeTimer = null;

    function getTheme() {
        return window.localStorage.getItem(THEME_KEY) || DEFAULT_THEME;
    }

    function updateThemeIcons(root) {
        (root || document).querySelectorAll('[data-theme-toggle]').forEach(function (toggle) {
            const sunIcon = toggle.querySelector('.icon-sun');
            const moonIcon = toggle.querySelector('.icon-moon');

            if (!sunIcon || !moonIcon) {
                return;
            }

            if (getTheme() === 'light') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            } else {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            }
        });
    }

    function applyTheme(theme) {
        const normalizedTheme = theme === 'light' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', normalizedTheme);
        window.localStorage.setItem(THEME_KEY, normalizedTheme);
        updateThemeIcons(document);
    }

    function bindThemeToggles() {
        document.querySelectorAll('[data-theme-toggle]').forEach(function (toggle) {
            if (toggle.dataset.bound === 'true') {
                return;
            }

            toggle.dataset.bound = 'true';
            toggle.addEventListener('click', function () {
                applyTheme(getTheme() === 'dark' ? 'light' : 'dark');
            });
        });

        updateThemeIcons(document);
    }

    function bindPasswordToggles() {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            if (button.dataset.bound === 'true') {
                return;
            }

            button.dataset.bound = 'true';
            button.addEventListener('click', function () {
                const field = document.getElementById(button.getAttribute('data-password-toggle'));

                if (!field) {
                    return;
                }

                const isPassword = field.type === 'password';
                field.type = isPassword ? 'text' : 'password';
                button.textContent = isPassword ? 'Hide' : 'Show';
            });
        });
    }

    function setCurrentYear() {
        const year = String(new Date().getFullYear());
        document.querySelectorAll('[data-current-year]').forEach(function (node) {
            node.textContent = year;
        });
    }

    function bindMenu() {
        const toggle = document.querySelector('[data-menu-toggle]');
        const sidebar = document.getElementById('sidebar');

        if (toggle && sidebar && toggle.dataset.bound !== 'true') {
            toggle.dataset.bound = 'true';
            toggle.addEventListener('click', function () {
                sidebar.classList.toggle('open');
            });
        }

        document.addEventListener('click', function (event) {
            const activeSidebar = document.getElementById('sidebar');
            const activeToggle = document.querySelector('[data-menu-toggle]');
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;

            if (!activeSidebar || viewportWidth > 992 || !activeSidebar.classList.contains('open')) {
                return;
            }

            if (!activeSidebar.contains(event.target) && !(activeToggle && activeToggle.contains(event.target))) {
                activeSidebar.classList.remove('open');
            }
        });
    }

    function bindRoleState() {
        const role = document.querySelector('[data-role-select]');
        const helper = document.getElementById('role-helper');
        const sectionField = document.getElementById('section_id');

        if (!role) {
            return;
        }

        function updateState() {
            const isStudent = role.value !== 'teacher';

            document.querySelectorAll('[data-student-only]').forEach(function (field) {
                field.classList.toggle('hidden', !isStudent);
            });

            if (sectionField) {
                sectionField.required = isStudent;
            }

            if (helper) {
                helper.textContent = isStudent
                    ? 'Students can be linked to a section and personal record profile immediately after registration.'
                    : 'Teachers receive a management workspace for sections, attendance, and grading.';
            }
        }

        role.addEventListener('change', updateState);
        updateState();
    }

    function bindDemoButtons() {
        document.querySelectorAll('[data-demo-email]').forEach(function (button) {
            if (button.dataset.bound === 'true') {
                return;
            }

            button.dataset.bound = 'true';
            button.addEventListener('click', function () {
                const email = document.getElementById('email');
                const password = document.getElementById('password');

                if (email) {
                    email.value = button.getAttribute('data-demo-email') || '';
                }

                if (password) {
                    password.value = button.getAttribute('data-demo-password') || '';
                }
            });
        });
    }

    function bindAutoSubmit() {
        document.querySelectorAll('[data-submit-on-change]').forEach(function (element) {
            if (element.dataset.bound === 'true') {
                return;
            }

            element.dataset.bound = 'true';
            element.addEventListener('change', function () {
                if (element.form) {
                    element.form.submit();
                }
            });
        });
    }

    function parseJsonAttribute(value, fallback) {
        if (!value) {
            return fallback;
        }

        try {
            return JSON.parse(value);
        } catch (error) {
            return fallback;
        }
    }

    function prepareCanvas(canvas) {
        const context = canvas.getContext('2d');
        const ratio = window.devicePixelRatio || 1;
        const width = canvas.clientWidth || canvas.parentElement.clientWidth || 320;
        const height = canvas.clientHeight || 280;

        canvas.width = Math.floor(width * ratio);
        canvas.height = Math.floor(height * ratio);
        context.setTransform(ratio, 0, 0, ratio, 0, 0);

        return { context: context, width: width, height: height };
    }

    function chartColors() {
        const isLight = getTheme() === 'light';
        return {
            text: isLight ? 'rgba(15, 23, 42, 0.72)' : 'rgba(245, 245, 244, 0.72)',
            grid: isLight ? 'rgba(15, 23, 42, 0.08)' : 'rgba(255, 255, 255, 0.1)'
        };
    }

    function drawLineChart(canvas, labels, values, maxValue, lineColor, areaColor) {
        const chart = prepareCanvas(canvas);
        const context = chart.context;
        const width = chart.width;
        const height = chart.height;
        const padding = 36;
        const colors = chartColors();

        context.clearRect(0, 0, width, height);
        context.strokeStyle = colors.grid;
        context.fillStyle = colors.text;
        context.font = '12px Outfit';

        for (let index = 0; index <= 5; index += 1) {
            const y = padding + (height - padding * 2) * (index / 5);
            context.beginPath();
            context.moveTo(padding, y);
            context.lineTo(width - padding, y);
            context.stroke();
            context.fillText(String(Math.round(maxValue - (maxValue / 5) * index)), 6, y + 4);
        }

        if (!values.length) {
            context.fillText('No chart data available yet.', padding, height / 2);
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
            context.fillText(labels[index] || '', Math.max(padding, point.x - 16), height - 10);
        });
    }

    function drawBarChart(canvas, labels, values, maxValue, barColor) {
        const chart = prepareCanvas(canvas);
        const context = chart.context;
        const width = chart.width;
        const height = chart.height;
        const padding = 36;
        const colors = chartColors();

        context.clearRect(0, 0, width, height);
        context.strokeStyle = colors.grid;
        context.fillStyle = colors.text;
        context.font = '12px Outfit';

        for (let index = 0; index <= 5; index += 1) {
            const y = padding + (height - padding * 2) * (index / 5);
            context.beginPath();
            context.moveTo(padding, y);
            context.lineTo(width - padding, y);
            context.stroke();
        }

        if (!values.length) {
            context.fillText('No chart data available yet.', padding, height / 2);
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
            context.fillText(String(Math.round((Number(value || 0) + Number.EPSILON) * 10) / 10), x, Math.max(16, y - 8));
            context.fillText(labels[index] || '', x, height - 10);
        });
    }

    function bindCharts() {
        document.querySelectorAll('.chart-canvas').forEach(function (canvas) {
            const labels = parseJsonAttribute(canvas.getAttribute('data-labels'), []);
            const values = parseJsonAttribute(canvas.getAttribute('data-values'), []);
            const type = canvas.getAttribute('data-chart-type') || 'bar';
            const maxValue = Number(canvas.getAttribute('data-max') || 100);
            const color = canvas.getAttribute('data-color') || '#38bdf8';
            const areaColor = canvas.getAttribute('data-area-color') || 'rgba(56, 189, 248, 0.14)';

            if (type === 'line') {
                drawLineChart(canvas, labels, values, maxValue, color, areaColor);
                return;
            }

            drawBarChart(canvas, labels, values, maxValue, color);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getTheme());
        bindThemeToggles();
        bindPasswordToggles();
        bindMenu();
        bindRoleState();
        bindDemoButtons();
        bindAutoSubmit();
        setCurrentYear();
        bindCharts();
    });

    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(bindCharts, 150);
    });
})();