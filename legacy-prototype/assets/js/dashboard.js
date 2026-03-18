(function (window, document) {
    "use strict";

    const app = window.EClassRecordApp;

    function statCard(title, value, caption, toneClass, iconName) {
        return '' +
            '<div class="glass-card glass-card-3d stat-card">' +
                '<div class="stat-card-inner">' +
                    '<div class="stat-info">' +
                        '<h3>' + app.escapeHtml(title) + '</h3>' +
                        '<div class="stat-value">' + app.escapeHtml(String(value)) + '</div>' +
                        '<span class="stat-change ' + toneClass + '">' + app.escapeHtml(caption) + '</span>' +
                    '</div>' +
                    '<div class="stat-icon ' + toneClass + '">' + app.navIcon(iconName) + '</div>' +
                '</div>' +
            '</div>';
    }

    function buildTeacherActivity(snapshot) {
        const gradeItems = snapshot.recentGrades.slice(0, 3).map(function (grade) {
            return '' +
                '<div class="activity-item">' +
                    '<div class="activity-avatar success">' + app.escapeHtml(grade.studentName.slice(0, 2).toUpperCase()) + '</div>' +
                    '<div class="activity-content">' +
                        '<div class="activity-text"><strong>' + app.escapeHtml(grade.studentName) + '</strong> received ' + app.escapeHtml(grade.percentage + '%') + ' in ' + app.escapeHtml(grade.title) + '.</div>' +
                        '<div class="activity-time">' + app.escapeHtml(app.formatDateTime(grade.recordedAt)) + '</div>' +
                    '</div>' +
                '</div>';
        }).join("");

        const attendanceItems = snapshot.recentAttendance.slice(0, 3).map(function (record) {
            return '' +
                '<div class="activity-item">' +
                    '<div class="activity-avatar warning">' + app.escapeHtml(record.studentName.slice(0, 2).toUpperCase()) + '</div>' +
                    '<div class="activity-content">' +
                        '<div class="activity-text"><strong>' + app.escapeHtml(record.studentName) + '</strong> was marked ' + app.escapeHtml(record.statusLabel.toLowerCase()) + ' for ' + app.escapeHtml(record.topic) + '.</div>' +
                        '<div class="activity-time">' + app.escapeHtml(app.formatDate(record.date)) + '</div>' +
                    '</div>' +
                '</div>';
        }).join("");

        if (!gradeItems && !attendanceItems) {
            return app.emptyStateMarkup(
                "No recent activity yet",
                "Attendance and grading updates will appear here after you save records for your sections."
            );
        }

        return gradeItems + attendanceItems;
    }

    function buildSectionCards(sections) {
        if (!sections.length) {
            return app.emptyStateMarkup(
                "No sections available",
                "Create or restore a section to view class-level summaries on the dashboard."
            );
        }

        return '<div class="quick-action-grid">' + sections.map(function (section) {
            return '' +
                '<article class="action-card">' +
                    '<div class="feature-badges">' +
                        '<span class="feature-badge">' + app.escapeHtml(section.strand) + '</span>' +
                        '<span class="feature-badge">' + app.escapeHtml(section.room) + '</span>' +
                    '</div>' +
                    '<h3>' + app.escapeHtml(section.name) + '</h3>' +
                    '<p>' + app.escapeHtml(section.schedule) + '</p>' +
                    '<div class="recent-session-meta">' +
                        '<span class="feature-badge">' + section.studentCount + ' learners</span>' +
                        '<span class="feature-badge">' + section.attendanceRate + '% attendance</span>' +
                        '<span class="feature-badge">' + section.averageGrade + '% average</span>' +
                    '</div>' +
                '</article>';
        }).join("") + '</div>';
    }

    function buildRecentGradeCards(grades) {
        if (!grades.length) {
            return '<div class="empty-state"><div><h3>No grade entries yet</h3><p>Newly recorded assessments will appear here.</p></div></div>';
        }

        return '<div class="recent-session-list">' + grades.slice(0, 4).map(function (grade) {
            return '' +
                '<div class="recent-session-card">' +
                    '<span class="status-pill ' + app.performanceTone(grade.percentage) + '">' + app.escapeHtml(app.performanceLabel(grade.percentage)) + '</span>' +
                    '<h3>' + app.escapeHtml(grade.studentName) + '</h3>' +
                    '<p>' + app.escapeHtml(grade.title + " - " + grade.category) + '</p>' +
                    '<div class="recent-session-meta">' +
                        '<span class="feature-badge">' + grade.score + ' / ' + grade.maxScore + '</span>' +
                        '<span class="feature-badge">' + grade.percentage + '%</span>' +
                    '</div>' +
                '</div>';
        }).join("") + '</div>';
    }

    function buildClassmateCards(classmates) {
        if (!classmates.length) {
            return app.emptyStateMarkup(
                "No classmates found",
                "Classmate cards will appear here once more learners are linked to your section."
            );
        }

        return '<div class="mini-card-grid">' + classmates.slice(0, 4).map(function (record) {
            return '' +
                '<article class="mini-summary-card">' +
                    '<h3>' + app.escapeHtml(record.student.name) + '</h3>' +
                    '<p>' + app.escapeHtml(record.student.focus) + '</p>' +
                    '<div class="recent-session-meta">' +
                        '<span class="feature-badge">' + record.gradeAverage + '% average</span>' +
                        '<span class="feature-badge">' + record.attendanceSummary.rate + '% attendance</span>' +
                    '</div>' +
                '</article>';
        }).join("") + '</div>';
    }

    function drawTeacherCharts(snapshot) {
        app.drawBarChart(
            document.getElementById("teacher-section-chart"),
            snapshot.sections.map(function (section) { return section.name.replace("Section ", "S"); }),
            snapshot.sections.map(function (section) { return section.averageGrade; }),
            { max: 100, barColor: "#d4a574" }
        );

        app.drawLineChart(
            document.getElementById("teacher-grade-chart"),
            snapshot.recentGrades.slice(0, 6).reverse().map(function (grade, index) { return "G" + (index + 1); }),
            snapshot.recentGrades.slice(0, 6).reverse().map(function (grade) { return grade.percentage; }),
            { max: 100, lineColor: "#34d399", areaColor: "rgba(52, 211, 153, 0.14)" }
        );
    }

    function drawStudentCharts(snapshot) {
        app.drawLineChart(
            document.getElementById("student-grade-chart"),
            snapshot.grades.slice(0, 6).reverse().map(function (grade, index) { return "A" + (index + 1); }),
            snapshot.grades.slice(0, 6).reverse().map(function (grade) { return app.getGradePercentage(grade); }),
            { max: 100, lineColor: "#34d399", areaColor: "rgba(52, 211, 153, 0.14)" }
        );

        app.drawBarChart(
            document.getElementById("student-category-chart"),
            snapshot.categoryAverages.map(function (item) { return item.category; }),
            snapshot.categoryAverages.map(function (item) { return item.average; }),
            { max: 100, barColor: "#38bdf8" }
        );
    }

    function renderTeacherDashboard(user) {
        const snapshot = app.getTeacherSnapshot(user);

        if (!snapshot.sections.length) {
            app.renderProtectedShell({
                activePage: "dashboard",
                title: "Dashboard",
                subtitle: "Monitor sections, attendance performance, and recently recorded grades from one teacher workspace.",
                headerMeta: [
                    "0 learners",
                    "0 active sections",
                    "Create your first section"
                ],
                headerActions: '' +
                    '<a href="class-list.html" class="btn btn-primary btn-fit">Create Section</a>' +
                    '<a href="settings.html" class="btn btn-outline btn-fit">Open Settings</a>',
                content: app.emptyStateMarkup(
                    "No sections are available yet",
                    "The teacher dashboard needs at least one section before it can show attendance and grading summaries.",
                    '<a href="class-list.html" class="btn btn-primary btn-fit">Go to Class List</a>'
                )
            });

            return;
        }

        app.renderProtectedShell({
            activePage: "dashboard",
            title: "Dashboard",
            subtitle: "Monitor sections, attendance performance, and recently recorded grades from one teacher workspace.",
            headerMeta: [
                snapshot.totalStudents + " learners",
                snapshot.sections.length + " active sections",
                snapshot.pendingGrades + " pending grade slots"
            ],
            headerActions: '' +
                '<a href="class-list.html" class="btn btn-primary btn-fit">Open Class List</a>' +
                '<a href="grading.html" class="btn btn-outline btn-fit">Open Grading</a>',
            content: '' +
                '<section class="stats-grid">' +
                    statCard("Total Learners", snapshot.totalStudents, "Across advisory sections", "emerald", "students") +
                    statCard("Grade Average", snapshot.averageGrade + "%", app.performanceLabel(snapshot.averageGrade), "gold", "grading") +
                    statCard("Attendance Rate", snapshot.averageAttendanceRate + "%", "Present + late counted", "blue", "students") +
                    statCard("Pending Grades", snapshot.pendingGrades, "Based on seeded assessments", "rose", "settings") +
                '</section>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Section Snapshot</h2><p>Overview cards for each managed class section.</p></div></div>' +
                        buildSectionCards(snapshot.sections) +
                    '</article>' +
                    '<article class="glass-card activity-card">' +
                        '<div class="section-head"><div><h2>Recent Activity</h2><p>Latest grading and attendance updates across your sections.</p></div></div>' +
                        '<div class="activity-list">' + buildTeacherActivity(snapshot) + '</div>' +
                    '</article>' +
                '</section>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card chart-card"><div class="section-head"><div><h2>Section Average by Class</h2><p>Current section grade averages based on seeded and newly recorded assessments.</p></div></div><canvas id="teacher-section-chart"></canvas></article>' +
                    '<article class="glass-card chart-card"><div class="section-head"><div><h2>Recent Grade Trend</h2><p>The latest recorded grade percentages entered into the system.</p></div></div><canvas id="teacher-grade-chart"></canvas></article>' +
                '</section>' +
                '<section class="glass-card">' +
                    '<div class="section-head"><div><h2>Recent Gradebook Entries</h2><p>Most recent assessments recorded across sections.</p></div></div>' +
                    buildRecentGradeCards(snapshot.recentGrades) +
                '</section>'
        });

        drawTeacherCharts(snapshot);
    }

    function renderStudentDashboard(user) {
        const snapshot = app.getStudentSnapshot(user);

        if (!snapshot || !snapshot.student) {
            app.renderProtectedShell({
                activePage: "dashboard",
                title: "Dashboard",
                subtitle: "View your class section, attendance rate, grade summary, and recent academic record updates.",
                headerMeta: [
                    app.getRoleLabel(user.role),
                    "Profile unavailable"
                ],
                headerActions: '' +
                    '<a href="settings.html" class="btn btn-primary btn-fit">Open Settings</a>' +
                    '<a href="login.html" class="btn btn-outline btn-fit" data-logout>Log Out</a>',
                content: app.emptyStateMarkup(
                    "Student profile not found",
                    "Your account is signed in, but the linked learner profile is missing. Open Settings or sign out and register again."
                )
            });

            return;
        }

        app.renderProtectedShell({
            activePage: "dashboard",
            title: "Dashboard",
            subtitle: "View your class section, attendance rate, grade summary, and recent academic record updates.",
            headerMeta: [
                snapshot.section.name,
                snapshot.section.schedule,
                snapshot.completedAssessments + " recorded assessments"
            ],
            headerActions: '' +
                '<a href="students.html" class="btn btn-primary btn-fit">My Records</a>' +
                '<a href="grading.html" class="btn btn-outline btn-fit">View Grades</a>',
            content: '' +
                '<section class="stats-grid">' +
                    statCard("Assigned Section", snapshot.section.name, snapshot.section.room, "emerald", "class-list") +
                    statCard("Grade Average", snapshot.gradeAverage + "%", app.performanceLabel(snapshot.gradeAverage), "gold", "grading") +
                    statCard("Attendance Rate", snapshot.attendanceSummary.rate + "%", snapshot.attendanceSummary.present + " present records", "blue", "students") +
                    statCard("Assessments", snapshot.completedAssessments, "Saved in local record", "rose", "dashboard") +
                '</section>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>My Section</h2><p>' + app.escapeHtml(snapshot.section.description) + '</p></div></div>' +
                        '<ul class="detail-list">' +
                            '<li><strong>Section:</strong> ' + app.escapeHtml(snapshot.section.name + " - " + snapshot.section.strand) + '</li>' +
                            '<li><strong>Adviser:</strong> ' + app.escapeHtml(snapshot.section.adviser) + '</li>' +
                            '<li><strong>Schedule:</strong> ' + app.escapeHtml(snapshot.section.schedule) + '</li>' +
                            '<li><strong>Room:</strong> ' + app.escapeHtml(snapshot.section.room) + '</li>' +
                        '</ul>' +
                    '</article>' +
                    '<article class="glass-card">' +
                        '<div class="section-head"><div><h2>Recent Assessments</h2><p>Your latest recorded grades inside the class record system.</p></div></div>' +
                        buildRecentGradeCards(snapshot.grades.map(app.decorateGradeEntry)) +
                    '</article>' +
                '</section>' +
                '<section class="section-grid two-column">' +
                    '<article class="glass-card chart-card"><div class="section-head"><div><h2>Recent Grade Trend</h2><p>Your latest assessment percentages plotted over time.</p></div></div><canvas id="student-grade-chart"></canvas></article>' +
                    '<article class="glass-card chart-card"><div class="section-head"><div><h2>Category Averages</h2><p>Grouped by quiz, exam, and project records.</p></div></div><canvas id="student-category-chart"></canvas></article>' +
                '</section>' +
                '<section class="glass-card">' +
                    '<div class="section-head"><div><h2>Classmates Snapshot</h2><p>Peer overview inside your assigned section.</p></div></div>' +
                    buildClassmateCards(snapshot.classmates) +
                '</section>'
        });

        drawStudentCharts(snapshot);
    }

    function renderDashboardPage() {
        const user = app.requireAuth();

        if (!user) {
            return;
        }

        if (user.role === "teacher") {
            renderTeacherDashboard(user);
            return;
        }

        renderStudentDashboard(user);
    }

    document.addEventListener("DOMContentLoaded", function () {
        if (document.body.getAttribute("data-page") === "dashboard") {
            renderDashboardPage();
        }
    });
}(window, document));
