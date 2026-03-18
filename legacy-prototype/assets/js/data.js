(function (window) {
    "use strict";

    const app = window.EClassRecordApp || {};

    const sections = [
        {
            id: "section-a",
            name: "Section A",
            strand: "BSIT 3A",
            room: "ICT Lab 201",
            schedule: "Mon / Wed / Fri - 8:00 AM to 10:00 AM",
            adviser: "Prof. Lucia Mendoza",
            description: "Application development, database laboratory, and formative quiz tracking."
        },
        {
            id: "section-b",
            name: "Section B",
            strand: "BSIT 3B",
            room: "ICT Lab 305",
            schedule: "Tue / Thu - 1:00 PM to 3:00 PM",
            adviser: "Prof. Lucia Mendoza",
            description: "Systems integration, networking activities, and project-based assessment tracking."
        }
    ];

    const users = [
        {
            id: "user-teacher-demo",
            name: "Prof. Lucia Mendoza",
            email: "teacher@eclass.local",
            password: "teacher123",
            role: "teacher",
            joinedAt: "2026-01-08T08:00:00.000Z",
            department: "BSIT Program",
            advisorySections: ["section-a", "section-b"],
            phone: "+63 917 555 2010",
            title: "Class Adviser"
        },
        {
            id: "user-student-demo",
            name: "Aira Mae Santos",
            email: "student@eclass.local",
            password: "student123",
            role: "student",
            joinedAt: "2026-01-09T08:30:00.000Z",
            linkedStudentId: "student-001",
            phone: "+63 917 555 1020"
        }
    ];

    const students = [
        {
            id: "student-001",
            userId: "user-student-demo",
            studentNumber: "2026-A-001",
            name: "Aira Mae Santos",
            email: "student@eclass.local",
            sectionId: "section-a",
            guardian: "Mila Santos",
            contact: "+63 917 555 1020",
            address: "Poblacion East, San Jose",
            focus: "Frontend Development",
            status: "Regular"
        },
        {
            id: "student-002",
            userId: "",
            studentNumber: "2026-A-002",
            name: "John Paul Rivera",
            email: "john.rivera@eclass.local",
            sectionId: "section-a",
            guardian: "Ramon Rivera",
            contact: "+63 917 555 1021",
            address: "Luna Street, San Jose",
            focus: "Database Design",
            status: "Regular"
        },
        {
            id: "student-003",
            userId: "",
            studentNumber: "2026-A-003",
            name: "Camille Dela Cruz",
            email: "camille.delacruz@eclass.local",
            sectionId: "section-a",
            guardian: "Anna Dela Cruz",
            contact: "+63 917 555 1022",
            address: "Rizal Avenue, San Jose",
            focus: "UI Design",
            status: "Regular"
        },
        {
            id: "student-004",
            userId: "",
            studentNumber: "2026-A-004",
            name: "Neil Adrian Gomez",
            email: "neil.gomez@eclass.local",
            sectionId: "section-a",
            guardian: "Lorna Gomez",
            contact: "+63 917 555 1023",
            address: "Mabini District, San Jose",
            focus: "Software Testing",
            status: "Regular"
        },
        {
            id: "student-005",
            userId: "",
            studentNumber: "2026-B-001",
            name: "Sophia Anne Reyes",
            email: "sophia.reyes@eclass.local",
            sectionId: "section-b",
            guardian: "Joel Reyes",
            contact: "+63 917 555 1024",
            address: "Bayanihan Homes, San Jose",
            focus: "Networking",
            status: "Regular"
        },
        {
            id: "student-006",
            userId: "",
            studentNumber: "2026-B-002",
            name: "Mark Joseph Flores",
            email: "mark.flores@eclass.local",
            sectionId: "section-b",
            guardian: "Grace Flores",
            contact: "+63 917 555 1025",
            address: "Pine Road, San Jose",
            focus: "System Analysis",
            status: "Regular"
        },
        {
            id: "student-007",
            userId: "",
            studentNumber: "2026-B-003",
            name: "Trisha Mae Castillo",
            email: "trisha.castillo@eclass.local",
            sectionId: "section-b",
            guardian: "Nina Castillo",
            contact: "+63 917 555 1026",
            address: "Central Park Subdivision, San Jose",
            focus: "Information Security",
            status: "Regular"
        },
        {
            id: "student-008",
            userId: "",
            studentNumber: "2026-B-004",
            name: "Kevin Lorenz Tan",
            email: "kevin.tan@eclass.local",
            sectionId: "section-b",
            guardian: "Victor Tan",
            contact: "+63 917 555 1027",
            address: "Magsaysay Extension, San Jose",
            focus: "Cloud Computing",
            status: "Regular"
        }
    ];

    const attendanceDates = [
        { date: "2026-03-03", topic: "Orientation and syllabus review" },
        { date: "2026-03-05", topic: "Database laboratory" },
        { date: "2026-03-10", topic: "Midterm review" },
        { date: "2026-03-12", topic: "Hands-on coding exercise" },
        { date: "2026-03-17", topic: "Performance task check-in" }
    ];

    const attendanceMap = {
        "student-001": ["present", "present", "late", "present", "present"],
        "student-002": ["present", "present", "present", "late", "present"],
        "student-003": ["late", "present", "present", "present", "present"],
        "student-004": ["present", "absent", "present", "present", "late"],
        "student-005": ["present", "present", "present", "present", "present"],
        "student-006": ["absent", "present", "late", "present", "present"],
        "student-007": ["present", "late", "present", "present", "present"],
        "student-008": ["present", "present", "absent", "present", "present"]
    };

    const gradeTemplates = [
        { id: "quiz-1", category: "Quiz", title: "Quiz 1 - Web Concepts", maxScore: 20, recordedAt: "2026-03-04T09:10:00.000Z" },
        { id: "quiz-2", category: "Quiz", title: "Quiz 2 - Data Modeling", maxScore: 20, recordedAt: "2026-03-08T09:10:00.000Z" },
        { id: "exam-1", category: "Exam", title: "Midterm Exam", maxScore: 50, recordedAt: "2026-03-13T10:00:00.000Z" },
        { id: "project-1", category: "Project", title: "Interface Prototype", maxScore: 100, recordedAt: "2026-03-17T15:00:00.000Z" }
    ];

    const gradeMap = {
        "student-001": [18, 19, 45, 93],
        "student-002": [17, 18, 42, 89],
        "student-003": [19, 18, 46, 95],
        "student-004": [15, 17, 39, 84],
        "student-005": [20, 19, 47, 97],
        "student-006": [16, 17, 41, 86],
        "student-007": [18, 18, 44, 91],
        "student-008": [17, 16, 40, 88]
    };

    function clone(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function buildAttendance() {
        const remarksByStatus = {
            present: "Present and engaged during class activities.",
            late: "Late arrival noted by the adviser.",
            absent: "Absent during the recorded meeting."
        };

        return students.reduce(function (records, student) {
            const statuses = attendanceMap[student.id] || [];

            attendanceDates.forEach(function (meeting, index) {
                const status = statuses[index] || "present";
                records.push({
                    id: "attendance-" + student.id + "-" + (index + 1),
                    studentId: student.id,
                    sectionId: student.sectionId,
                    date: meeting.date,
                    topic: meeting.topic,
                    status: status,
                    remarks: remarksByStatus[status],
                    markedBy: "user-teacher-demo",
                    updatedAt: meeting.date + "T08:00:00.000Z"
                });
            });

            return records;
        }, []);
    }

    function buildGrades() {
        return students.reduce(function (entries, student) {
            const scores = gradeMap[student.id] || [];

            gradeTemplates.forEach(function (template, index) {
                const score = scores[index] || 0;
                entries.push({
                    id: "grade-" + student.id + "-" + template.id,
                    studentId: student.id,
                    sectionId: student.sectionId,
                    category: template.category,
                    title: template.title,
                    score: score,
                    maxScore: template.maxScore,
                    remarks: score >= template.maxScore * 0.9
                        ? "Excellent work and consistent output."
                        : "Recorded and ready for follow-up feedback.",
                    recordedBy: "user-teacher-demo",
                    recordedAt: template.recordedAt
                });
            });

            return entries;
        }, []);
    }

    app.seed = {
        users: clone(users),
        buildSchoolData: function () {
            return {
                sections: clone(sections),
                students: clone(students),
                attendance: buildAttendance(),
                grades: buildGrades()
            };
        }
    };

    app.referenceData = {
        roles: [
            { value: "teacher", label: "Teacher" },
            { value: "student", label: "Student" }
        ],
        attendanceStatuses: [
            { value: "present", label: "Present" },
            { value: "late", label: "Late" },
            { value: "absent", label: "Absent" }
        ],
        gradeCategories: [
            { value: "Quiz", label: "Quiz" },
            { value: "Exam", label: "Exam" },
            { value: "Project", label: "Project" },
            { value: "Performance Task", label: "Performance Task" }
        ],
        seededAssessmentCount: gradeTemplates.length
    };

    window.EClassRecordApp = app;
}(window));
