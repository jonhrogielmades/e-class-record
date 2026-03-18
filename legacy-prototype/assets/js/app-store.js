(function (window) {
    "use strict";

    const app = window.EClassRecordApp || {};

    const STORAGE_KEYS = {
        users: "eclass_users",
        currentUser: "eclass_current_user",
        schoolData: "eclass_school_data"
    };

    const STATUS_LABELS = {
        present: "Present",
        late: "Late",
        absent: "Absent"
    };

    const EMPTY_SCHOOL_DATA = {
        sections: [],
        students: [],
        attendance: [],
        grades: []
    };

    function safeParse(value, fallback) {
        if (!value) {
            return fallback;
        }

        try {
            return JSON.parse(value);
        } catch (error) {
            return fallback;
        }
    }

    function clone(value) {
        return safeParse(JSON.stringify(value), value);
    }

    function readStorageState(key, fallback) {
        const rawValue = window.localStorage.getItem(key);

        if (rawValue === null) {
            return {
                exists: false,
                isValid: true,
                value: fallback
            };
        }

        try {
            return {
                exists: true,
                isValid: true,
                value: JSON.parse(rawValue)
            };
        } catch (error) {
            return {
                exists: true,
                isValid: false,
                value: fallback
            };
        }
    }

    function readStorage(key, fallback) {
        return readStorageState(key, fallback).value;
    }

    function writeStorage(key, value) {
        window.localStorage.setItem(key, JSON.stringify(value));
    }

    function average(values) {
        if (!values.length) {
            return 0;
        }

        return values.reduce(function (sum, value) {
            return sum + Number(value || 0);
        }, 0) / values.length;
    }

    function round(value, precision) {
        const multiplier = Math.pow(10, precision || 1);
        return Math.round(Number(value || 0) * multiplier) / multiplier;
    }

    function createId(prefix) {
        return [
            prefix || "id",
            Date.now().toString(36),
            Math.random().toString(36).slice(2, 8)
        ].join("-");
    }

    function normalizeEmail(email) {
        return String(email || "").trim().toLowerCase();
    }

    function createFallbackSection(sectionId) {
        return {
            id: sectionId || "",
            name: "Unassigned Section",
            strand: "Section unavailable",
            room: "Room not assigned",
            schedule: "Schedule unavailable",
            adviser: "Adviser not assigned",
            description: "This learner profile is not currently linked to an available section."
        };
    }

    function formatDate(dateString) {
        if (!dateString) {
            return "Not available";
        }

        return new Intl.DateTimeFormat("en-US", {
            year: "numeric",
            month: "long",
            day: "numeric"
        }).format(new Date(dateString));
    }

    function formatDateTime(dateString) {
        if (!dateString) {
            return "Not available";
        }

        return new Intl.DateTimeFormat("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "numeric",
            minute: "2-digit"
        }).format(new Date(dateString));
    }

    function getInitials(name) {
        const tokens = String(name || "EC")
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2);

        return tokens.length
            ? tokens.map(function (token) { return token.charAt(0).toUpperCase(); }).join("")
            : "EC";
    }

    function getRoleLabel(role) {
        return role === "teacher" ? "Teacher" : "Student";
    }

    function getUsers() {
        const users = readStorage(STORAGE_KEYS.users, []);
        return Array.isArray(users) ? users : [];
    }

    function saveUsers(users) {
        writeStorage(STORAGE_KEYS.users, users);
    }

    function getSchoolData() {
        const data = readStorage(STORAGE_KEYS.schoolData, EMPTY_SCHOOL_DATA) || EMPTY_SCHOOL_DATA;

        return {
            sections: Array.isArray(data.sections) ? data.sections : [],
            students: Array.isArray(data.students) ? data.students : [],
            attendance: Array.isArray(data.attendance) ? data.attendance : [],
            grades: Array.isArray(data.grades) ? data.grades : []
        };
    }

    function saveSchoolData(data) {
        writeStorage(STORAGE_KEYS.schoolData, data);
    }

    function ensureSeededData() {
        const seedUsers = clone((app.seed && app.seed.users) || []);
        const seedSchoolData = app.seed && typeof app.seed.buildSchoolData === "function"
            ? app.seed.buildSchoolData()
            : clone(EMPTY_SCHOOL_DATA);

        const usersState = readStorageState(STORAGE_KEYS.users, []);
        if (!usersState.exists || !usersState.isValid || !Array.isArray(usersState.value)) {
            saveUsers(seedUsers);
        }

        const schoolDataState = readStorageState(STORAGE_KEYS.schoolData, clone(EMPTY_SCHOOL_DATA));
        const schoolData = schoolDataState.value || EMPTY_SCHOOL_DATA;
        const hasValidSchoolData = schoolDataState.exists
            && schoolDataState.isValid
            && Array.isArray(schoolData.sections)
            && Array.isArray(schoolData.students)
            && Array.isArray(schoolData.attendance)
            && Array.isArray(schoolData.grades);

        if (!hasValidSchoolData) {
            saveSchoolData(seedSchoolData);
        }
    }

    function getCurrentUserId() {
        return window.localStorage.getItem(STORAGE_KEYS.currentUser);
    }

    function setCurrentUserId(userId) {
        if (!userId) {
            window.localStorage.removeItem(STORAGE_KEYS.currentUser);
            return;
        }

        window.localStorage.setItem(STORAGE_KEYS.currentUser, userId);
    }

    function getCurrentUser() {
        const userId = getCurrentUserId();

        if (!userId) {
            return null;
        }

        const user = getUsers().find(function (user) {
            return user.id === userId;
        }) || null;

        if (!user) {
            setCurrentUserId("");
        }

        return user;
    }

    function getUserById(userId) {
        return getUsers().find(function (user) {
            return user.id === userId;
        }) || null;
    }

    function updateUser(userId, changes) {
        const users = getUsers();
        const index = users.findIndex(function (user) {
            return user.id === userId;
        });

        if (index === -1) {
            return null;
        }

        users[index] = Object.assign({}, users[index], typeof changes === "function" ? changes(clone(users[index])) : changes);
        saveUsers(users);
        return users[index];
    }

    function updateStudent(studentId, changes) {
        const data = getSchoolData();
        const index = data.students.findIndex(function (student) {
            return student.id === studentId;
        });

        if (index === -1) {
            return null;
        }

        data.students[index] = Object.assign({}, data.students[index], typeof changes === "function" ? changes(clone(data.students[index])) : changes);
        saveSchoolData(data);

        if (data.students[index].userId) {
            updateUser(data.students[index].userId, function (user) {
                return {
                    name: data.students[index].name || user.name,
                    email: normalizeEmail(data.students[index].email || user.email),
                    phone: data.students[index].contact || user.phone
                };
            });
        }

        return data.students[index];
    }

    function createSection(payload) {
        const data = getSchoolData();
        const section = {
            id: createId("section"),
            name: String(payload.name || "").trim(),
            strand: String(payload.strand || "").trim(),
            room: String(payload.room || "").trim(),
            schedule: String(payload.schedule || "").trim(),
            adviser: String(payload.adviser || "").trim(),
            description: String(payload.description || "").trim()
        };

        data.sections.push(section);
        saveSchoolData(data);
        return section;
    }

    function updateSection(sectionId, changes) {
        const data = getSchoolData();
        const index = data.sections.findIndex(function (section) {
            return section.id === sectionId;
        });

        if (index === -1) {
            return null;
        }

        data.sections[index] = Object.assign({}, data.sections[index], typeof changes === "function" ? changes(clone(data.sections[index])) : changes);
        saveSchoolData(data);
        return data.sections[index];
    }

    function deleteSection(sectionId) {
        const data = getSchoolData();
        const remainingCount = data.sections.filter(function (section) {
            return section.id !== sectionId;
        }).length;
        const hasStudents = data.students.some(function (student) {
            return student.sectionId === sectionId;
        });

        if (remainingCount === 0) {
            return {
                success: false,
                message: "At least one section must remain in the system."
            };
        }

        if (hasStudents) {
            return {
                success: false,
                message: "Remove or move all learners in this section before deleting it."
            };
        }

        data.sections = data.sections.filter(function (section) {
            return section.id !== sectionId;
        });
        saveSchoolData(data);

        saveUsers(getUsers().map(function (user) {
            if (user.role !== "teacher") {
                return user;
            }

            return Object.assign({}, user, {
                advisorySections: (user.advisorySections || []).filter(function (item) {
                    return item !== sectionId;
                })
            });
        }));

        return {
            success: true
        };
    }

    function getSections() {
        return getSchoolData().sections.slice();
    }

    function getStudents() {
        return getSchoolData().students.slice();
    }

    function getGrades() {
        return getSchoolData().grades.slice().sort(function (left, right) {
            return new Date(right.recordedAt).getTime() - new Date(left.recordedAt).getTime();
        });
    }

    function getAttendance() {
        return getSchoolData().attendance.slice().sort(function (left, right) {
            return new Date(right.date).getTime() - new Date(left.date).getTime();
        });
    }

    function getSectionById(sectionId) {
        return getSections().find(function (section) {
            return section.id === sectionId;
        }) || null;
    }

    function getStudentById(studentId) {
        return getStudents().find(function (student) {
            return student.id === studentId;
        }) || null;
    }

    function getLinkedStudent(user) {
        const currentUser = user || getCurrentUser();

        if (!currentUser || currentUser.role !== "student") {
            return null;
        }

        return getStudents().find(function (student) {
            return student.userId === currentUser.id || student.id === currentUser.linkedStudentId;
        }) || null;
    }

    function getStudentGrades(studentId) {
        return getGrades().filter(function (grade) {
            return grade.studentId === studentId;
        });
    }

    function getStudentAttendance(studentId) {
        return getAttendance().filter(function (record) {
            return record.studentId === studentId;
        });
    }

    function getGradePercentage(grade) {
        const maxScore = Number(grade && grade.maxScore || 0);
        const score = Number(grade && grade.score || 0);
        return maxScore ? round((score / maxScore) * 100, 1) : 0;
    }

    function summarizeAttendance(records) {
        const summary = records.reduce(function (result, record) {
            const key = record.status || "present";
            result[key] = (result[key] || 0) + 1;
            result.total += 1;
            return result;
        }, {
            present: 0,
            late: 0,
            absent: 0,
            total: 0
        });

        return {
            present: summary.present,
            late: summary.late,
            absent: summary.absent,
            total: summary.total,
            rate: summary.total ? round(((summary.present + summary.late) / summary.total) * 100, 1) : 0
        };
    }

    function summarizeGradesByCategory(grades) {
        const grouped = {};

        grades.forEach(function (grade) {
            const category = grade.category || "General";

            if (!grouped[category]) {
                grouped[category] = [];
            }

            grouped[category].push(getGradePercentage(grade));
        });

        return Object.keys(grouped).map(function (category) {
            return {
                category: category,
                average: round(average(grouped[category]), 1)
            };
        }).sort(function (left, right) {
            return right.average - left.average;
        });
    }

    function getStudentRecord(studentId) {
        const student = getStudentById(studentId);

        if (!student) {
            return null;
        }

        const grades = getStudentGrades(student.id);
        const attendance = getStudentAttendance(student.id);
        const section = getSectionById(student.sectionId) || createFallbackSection(student.sectionId);

        return {
            student: student,
            section: section,
            grades: grades,
            attendance: attendance,
            latestGrade: grades[0] || null,
            gradeAverage: grades.length ? round(average(grades.map(getGradePercentage)), 1) : 0,
            categoryAverages: summarizeGradesByCategory(grades),
            attendanceSummary: summarizeAttendance(attendance)
        };
    }

    function getSectionRoster(sectionId) {
        return getStudents()
            .filter(function (student) {
                return !sectionId || student.sectionId === sectionId;
            })
            .map(function (student) {
                return getStudentRecord(student.id);
            })
            .sort(function (left, right) {
                return left.student.name.localeCompare(right.student.name);
            });
    }

    function getSectionSummaries(sectionIds) {
        const sections = getSections();
        const requestedIds = Array.isArray(sectionIds)
            ? sectionIds.filter(Boolean)
            : [];
        const visibleIds = requestedIds.length
            ? requestedIds
            : sections.map(function (section) { return section.id; });
        const visibleSections = sections.filter(function (section) {
            return visibleIds.indexOf(section.id) !== -1;
        });
        const scopedSections = visibleSections.length ? visibleSections : sections;

        return scopedSections.map(function (section) {
            const roster = getSectionRoster(section.id);
            const gradeEntries = getGrades().filter(function (grade) {
                return grade.sectionId === section.id;
            }).length;

            return Object.assign({}, section, {
                roster: roster,
                studentCount: roster.length,
                averageGrade: roster.length ? round(average(roster.map(function (record) { return record.gradeAverage; })), 1) : 0,
                attendanceRate: roster.length ? round(average(roster.map(function (record) { return record.attendanceSummary.rate; })), 1) : 0,
                pendingGrades: Math.max(0, (roster.length * Number(app.referenceData && app.referenceData.seededAssessmentCount || 4)) - gradeEntries)
            });
        });
    }

    function decorateGradeEntry(entry) {
        const student = getStudentById(entry.studentId) || {};
        const section = getSectionById(entry.sectionId) || {};

        return Object.assign({}, entry, {
            studentName: student.name || "Unknown learner",
            studentNumber: student.studentNumber || "N/A",
            sectionName: section.name || "Unknown section",
            percentage: getGradePercentage(entry)
        });
    }

    function decorateAttendanceEntry(entry) {
        const student = getStudentById(entry.studentId) || {};
        const section = getSectionById(entry.sectionId) || {};

        return Object.assign({}, entry, {
            studentName: student.name || "Unknown learner",
            studentNumber: student.studentNumber || "N/A",
            sectionName: section.name || "Unknown section",
            statusLabel: STATUS_LABELS[entry.status] || "Present"
        });
    }

    function getTeacherSnapshot(user) {
        const teacher = user || getCurrentUser();
        const sectionIds = teacher && teacher.advisorySections && teacher.advisorySections.length
            ? teacher.advisorySections
            : getSections().map(function (section) { return section.id; });
        const sections = getSectionSummaries(sectionIds);
        const studentIds = sections.reduce(function (ids, section) {
            section.roster.forEach(function (record) {
                ids.push(record.student.id);
            });
            return ids;
        }, []);
        const recentGrades = getGrades()
            .filter(function (grade) { return studentIds.indexOf(grade.studentId) !== -1; })
            .slice(0, 8)
            .map(decorateGradeEntry);
        const recentAttendance = getAttendance()
            .filter(function (record) { return studentIds.indexOf(record.studentId) !== -1; })
            .slice(0, 8)
            .map(decorateAttendanceEntry);

        return {
            sections: sections,
            totalStudents: studentIds.length,
            averageGrade: sections.length ? round(average(sections.map(function (section) { return section.averageGrade; })), 1) : 0,
            averageAttendanceRate: sections.length ? round(average(sections.map(function (section) { return section.attendanceRate; })), 1) : 0,
            pendingGrades: sections.reduce(function (sum, section) { return sum + section.pendingGrades; }, 0),
            recentGrades: recentGrades,
            recentAttendance: recentAttendance
        };
    }

    function getStudentSnapshot(user) {
        const linkedStudent = getLinkedStudent(user);
        const record = linkedStudent ? getStudentRecord(linkedStudent.id) : null;

        if (!record) {
            return null;
        }

        return Object.assign({}, record, {
            classmates: getSectionRoster(record.student.sectionId).filter(function (item) {
                return item.student.id !== record.student.id;
            }),
            completedAssessments: record.grades.length
        });
    }

    function buildStudentNumber(sectionId) {
        const section = getSectionById(sectionId);
        const count = getStudents().filter(function (student) {
            return student.sectionId === sectionId;
        }).length + 1;
        const sectionCode = section && section.name ? section.name.split(" ").pop() : "X";

        return "2026-" + sectionCode + "-" + String(count).padStart(3, "0");
    }

    function createStudentProfile(payload) {
        const data = getSchoolData();
        const student = {
            id: createId("student"),
            userId: payload.userId || "",
            studentNumber: String(payload.studentNumber || buildStudentNumber(payload.sectionId)).trim(),
            name: String(payload.name || "").trim(),
            email: String(payload.email || "").trim(),
            sectionId: payload.sectionId,
            guardian: String(payload.guardian || "Pending guardian details").trim(),
            contact: String(payload.contact || "Not provided").trim() || "Not provided",
            address: String(payload.address || "Address not yet provided").trim(),
            focus: String(payload.focus || "General Studies").trim(),
            status: String(payload.status || "Regular").trim()
        };

        data.students.push(student);
        saveSchoolData(data);
        return student;
    }

    function deleteStudentProfile(studentId) {
        const data = getSchoolData();
        const student = data.students.find(function (item) {
            return item.id === studentId;
        });

        if (!student) {
            return {
                success: false,
                message: "Student record not found."
            };
        }

        data.students = data.students.filter(function (item) {
            return item.id !== studentId;
        });
        data.grades = data.grades.filter(function (grade) {
            return grade.studentId !== studentId;
        });
        data.attendance = data.attendance.filter(function (record) {
            return record.studentId !== studentId;
        });
        saveSchoolData(data);

        if (student.userId) {
            const users = getUsers().filter(function (user) {
                return user.id !== student.userId;
            });
            saveUsers(users);

            if (getCurrentUserId() === student.userId) {
                setCurrentUserId("");
            }
        }

        return {
            success: true
        };
    }

    function registerUser(payload) {
        ensureSeededData();

        const users = getUsers();
        const role = payload.role === "teacher" ? "teacher" : "student";
        const name = String(payload.name || "").trim();
        const email = normalizeEmail(payload.email);
        const password = String(payload.password || "");

        if (!name || !email || !password) {
            return {
                success: false,
                message: "Please complete all required registration fields."
            };
        }

        if (users.some(function (user) { return normalizeEmail(user.email) === email; })) {
            return {
                success: false,
                message: "That email is already registered. Please sign in instead."
            };
        }

        if (role === "student" && !getSectionById(payload.sectionId)) {
            return {
                success: false,
                message: "Please choose a valid section before creating a student account."
            };
        }

        const user = {
            id: createId("user"),
            name: name,
            email: email,
            password: password,
            role: role,
            joinedAt: new Date().toISOString(),
            phone: String(payload.phone || "").trim()
        };

        if (role === "teacher") {
            user.department = String(payload.department || "BSIT Program").trim() || "BSIT Program";
            user.title = "Subject Teacher";
            user.advisorySections = [];
        } else {
            const student = createStudentProfile({
                userId: user.id,
                studentNumber: buildStudentNumber(payload.sectionId),
                name: name,
                email: email,
                sectionId: payload.sectionId,
                guardian: payload.guardian,
                contact: payload.phone,
                address: payload.address,
                focus: "General Studies",
                status: "Regular"
            });
            user.linkedStudentId = student.id;
        }

        users.push(user);
        saveUsers(users);
        setCurrentUserId(user.id);

        return {
            success: true,
            user: user,
            message: "Registration successful. Your e-class record dashboard is ready."
        };
    }

    function loginUser(email, password) {
        ensureSeededData();

        const matchingUser = getUsers().find(function (user) {
            return normalizeEmail(user.email) === normalizeEmail(email);
        });

        if (!matchingUser) {
            return {
                success: false,
                message: "No account was found for that email. Please register first."
            };
        }

        if (matchingUser.password !== String(password || "")) {
            return {
                success: false,
                message: "Incorrect password. Please try again."
            };
        }

        setCurrentUserId(matchingUser.id);

        return {
            success: true,
            user: matchingUser,
            message: "Login successful. Redirecting to the dashboard."
        };
    }

    function logoutUser() {
        setCurrentUserId("");
    }

    function saveGradeEntry(payload) {
        const data = getSchoolData();
        const entry = {
            id: createId("grade"),
            studentId: payload.studentId,
            sectionId: payload.sectionId,
            category: payload.category,
            title: payload.title,
            score: Number(payload.score),
            maxScore: Number(payload.maxScore),
            remarks: String(payload.remarks || "").trim(),
            recordedBy: payload.recordedBy,
            recordedAt: payload.recordedAt || new Date().toISOString()
        };

        data.grades.push(entry);
        saveSchoolData(data);
        return decorateGradeEntry(entry);
    }

    function updateGradeEntry(gradeId, changes) {
        const data = getSchoolData();
        const index = data.grades.findIndex(function (grade) {
            return grade.id === gradeId;
        });

        if (index === -1) {
            return null;
        }

        data.grades[index] = Object.assign({}, data.grades[index], typeof changes === "function" ? changes(clone(data.grades[index])) : changes);
        saveSchoolData(data);
        return decorateGradeEntry(data.grades[index]);
    }

    function deleteGradeEntry(gradeId) {
        const data = getSchoolData();
        data.grades = data.grades.filter(function (grade) {
            return grade.id !== gradeId;
        });
        saveSchoolData(data);
    }

    function saveAttendanceRecord(payload) {
        const data = getSchoolData();
        const existingIndex = data.attendance.findIndex(function (record) {
            return record.studentId === payload.studentId && record.date === payload.date;
        });
        const entry = {
            id: existingIndex >= 0 ? data.attendance[existingIndex].id : createId("attendance"),
            studentId: payload.studentId,
            sectionId: payload.sectionId,
            date: payload.date,
            topic: payload.topic,
            status: payload.status,
            remarks: String(payload.remarks || "").trim(),
            markedBy: payload.markedBy,
            updatedAt: new Date().toISOString()
        };

        if (existingIndex >= 0) {
            data.attendance[existingIndex] = entry;
        } else {
            data.attendance.push(entry);
        }

        saveSchoolData(data);
        return decorateAttendanceEntry(entry);
    }

    function updateAttendanceRecord(attendanceId, changes) {
        const data = getSchoolData();
        const index = data.attendance.findIndex(function (record) {
            return record.id === attendanceId;
        });

        if (index === -1) {
            return null;
        }

        data.attendance[index] = Object.assign({}, data.attendance[index], typeof changes === "function" ? changes(clone(data.attendance[index])) : changes, {
            updatedAt: new Date().toISOString()
        });
        saveSchoolData(data);
        return decorateAttendanceEntry(data.attendance[index]);
    }

    function deleteAttendanceRecord(attendanceId) {
        const data = getSchoolData();
        data.attendance = data.attendance.filter(function (record) {
            return record.id !== attendanceId;
        });
        saveSchoolData(data);
    }

    function deleteUserAccount(userId) {
        const user = getUserById(userId);

        if (!user) {
            return {
                success: false,
                message: "User account not found."
            };
        }

        if (user.role === "student" && user.linkedStudentId) {
            const result = deleteStudentProfile(user.linkedStudentId);

            if (result.success) {
                return {
                    success: true
                };
            }
        }

        const users = getUsers().filter(function (item) {
            return item.id !== userId;
        });

        saveUsers(users);

        if (getCurrentUserId() === userId) {
            setCurrentUserId("");
        }

        return {
            success: true
        };
    }

    function performanceLabel(percentage) {
        const value = Number(percentage || 0);

        if (value >= 90) {
            return "Excellent";
        }

        if (value >= 85) {
            return "Very Good";
        }

        if (value >= 80) {
            return "Good";
        }

        return "Needs Support";
    }

    function performanceTone(percentage) {
        const label = performanceLabel(percentage);
        return label === "Excellent" ? "excellent" : label === "Very Good" ? "good" : label === "Good" ? "fair" : "needs-work";
    }

    function attendanceTone(status) {
        const normalized = String(status || "present").toLowerCase();
        return normalized === "present" ? "excellent" : normalized === "late" ? "fair" : "needs-work";
    }

    Object.assign(app, {
        STORAGE_KEYS: STORAGE_KEYS,
        average: average,
        round: round,
        createId: createId,
        formatDate: formatDate,
        formatDateTime: formatDateTime,
        getInitials: getInitials,
        getRoleLabel: getRoleLabel,
        getUsers: getUsers,
        getCurrentUser: getCurrentUser,
        getUserById: getUserById,
        getCurrentUserId: getCurrentUserId,
        setCurrentUserId: setCurrentUserId,
        getSections: getSections,
        getStudents: getStudents,
        getGrades: getGrades,
        getAttendance: getAttendance,
        getSectionById: getSectionById,
        getStudentById: getStudentById,
        getLinkedStudent: getLinkedStudent,
        getStudentGrades: getStudentGrades,
        getStudentAttendance: getStudentAttendance,
        getGradePercentage: getGradePercentage,
        getStudentRecord: getStudentRecord,
        getSectionRoster: getSectionRoster,
        getSectionSummaries: getSectionSummaries,
        getTeacherSnapshot: getTeacherSnapshot,
        getStudentSnapshot: getStudentSnapshot,
        registerUser: registerUser,
        loginUser: loginUser,
        logoutUser: logoutUser,
        updateUser: updateUser,
        updateStudent: updateStudent,
        createSection: createSection,
        updateSection: updateSection,
        deleteSection: deleteSection,
        createStudentProfile: createStudentProfile,
        deleteStudentProfile: deleteStudentProfile,
        saveGradeEntry: saveGradeEntry,
        updateGradeEntry: updateGradeEntry,
        deleteGradeEntry: deleteGradeEntry,
        saveAttendanceRecord: saveAttendanceRecord,
        updateAttendanceRecord: updateAttendanceRecord,
        deleteAttendanceRecord: deleteAttendanceRecord,
        deleteUserAccount: deleteUserAccount,
        ensureSeededData: ensureSeededData,
        performanceLabel: performanceLabel,
        performanceTone: performanceTone,
        attendanceTone: attendanceTone,
        attendanceStatusLabel: function (status) {
            return STATUS_LABELS[status] || "Present";
        },
        decorateGradeEntry: decorateGradeEntry,
        decorateAttendanceEntry: decorateAttendanceEntry,
        summarizeAttendance: summarizeAttendance,
        summarizeGradesByCategory: summarizeGradesByCategory
    });

    window.EClassRecordApp = app;
}(window));
