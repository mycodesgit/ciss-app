{{-- Sidebar for the Enrollment module (master_empenrollment) --}}
<ul class="nav flex-column">
    <li class="px-4 py-1"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.enrollment.dashboard') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.dashboard')])>
            <i class="ti ti-home"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>

    {{-- Pages not built yet link to # --}}
    <li>
        <a href="#" class="nav-link">
            <i class="ti ti-user-plus"></i><span class="nav-text">Add Student</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.enrollment.enroll.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.enroll.*')])>
            <i class="ti ti-device-laptop"></i><span class="nav-text">Enroll Student</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.enrollment.edit.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.edit.*')])>
            <i class="ti ti-edit"></i><span class="nav-text">Edit Enrollment</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.enrollment.dup-fees.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.dup-fees.*')])>
            <i class="ti ti-receipt"></i><span class="nav-text">Edit Dup. Fees</span>
        </a>
    </li>
    <li>
        <a href="#" class="nav-link">
            <i class="ti ti-scoreboard"></i><span class="nav-text">Evaluated Student</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.enrollment.history.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.history.*')])>
            <i class="ti ti-history"></i><span class="nav-text">Enrollment History</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.enrollment.gradesheet.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.gradesheet.*')])>
            <i class="ti ti-file-spreadsheet"></i><span class="nav-text">Gradesheet</span>
        </a>
    </li>
    {{-- Registrar and Administrators only: Correction of Grades, Transferred Student, Subjects (the routes check it too: 'registrar' middleware) --}}
    @if (Auth::guard('web')->user()->canCorrectGrades())
        <li>
            <a href="{{ route('employee.enrollment.corrections.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.corrections.*')])>
                <i class="ti ti-file-pencil"></i><span class="nav-text">Correction of Grades</span>
            </a>
        </li>
        <li>
            <a href="{{ route('employee.enrollment.transfers.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.transfers.*')])>
                <i class="ti ti-transfer"></i><span class="nav-text">Transferred Student</span>
            </a>
        </li>
        <li>
            <a href="{{ route('employee.enrollment.subjects.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.subjects.*')])>
                <i class="ti ti-book"></i><span class="nav-text">Subjects</span>
            </a>
        </li>
    @endif

    {{-- Reports: hidden from Encoders (the routes check it too: 'enrollment-reports' middleware) --}}
    @if (Auth::guard('web')->user()->canViewEnrollmentReports())
        <li class="px-4 py-1 mt-2"><small class="nav-text text-muted">Reports</small></li>
        {{-- Administrators, Registrar and Guidance only ('student-info' middleware) --}}
        @if (Auth::guard('web')->user()->canManageStudentInfo())
            <li>
                <a href="{{ route('employee.enrollment.student-info.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.student-info.*')])>
                    <i class="ti ti-user"></i><span class="nav-text">Student Information</span>
                </a>
            </li>
        @endif
        <li>
            <a href="{{ route('employee.enrollment.print-rf.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.print-rf.*')])>
                <i class="ti ti-printer"></i><span class="nav-text">Print Student RF</span>
            </a>
        </li>
        <li>
            <a href="{{ route('employee.enrollment.per-degree.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.per-degree.*')])>
                <i class="ti ti-file-type-pdf"></i><span class="nav-text">Student Per Degree</span>
            </a>
        </li>
        <li>
            <a href="{{ route('employee.enrollment.attendance-per-degree.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.attendance-per-degree.*')])>
                <i class="ti ti-signature"></i><span class="nav-text">Attendance Per Degree</span>
            </a>
        </li>
        <li>
            <a href="{{ route('employee.enrollment.class-attendance.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.class-attendance.*')])>
                <i class="ti ti-clipboard-check"></i><span class="nav-text">Class Attendance</span>
            </a>
        </li>
        <li>
            <a href="{{ route('employee.enrollment.student-grades.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.student-grades.*')])>
                <i class="ti ti-numbers"></i><span class="nav-text">View Student Grades</span>
            </a>
        </li>
        {{-- Administrator, Registrar, Registrar Staff, MIS and Dean of Instruction (User::REPORT_CARD_ROLES) --}}
        @if (Auth::guard('web')->user()->canPrintReportCards())
            <li>
                <a href="{{ route('employee.enrollment.report-card.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.report-card.*')])>
                    <i class="ti ti-id-badge-2"></i><span class="nav-text">Student Report Card</span>
                </a>
            </li>
            <li>
                <a href="{{ route('employee.enrollment.extended-students.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.extended-students.*')])>
                    <i class="ti ti-calendar-due"></i><span class="nav-text">Extended Students</span>
                </a>
            </li>
            <li>
                <a href="{{ route('employee.enrollment.el-pl.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.el-pl.*')])>
                    <i class="ti ti-file-spreadsheet"></i><span class="nav-text">EL and PL</span>
                </a>
            </li>
            <li>
                <a href="{{ route('employee.enrollment.ranking.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.ranking.*')])>
                    <i class="ti ti-military-rank"></i><span class="nav-text">Ranking</span>
                </a>
            </li>
        @endif
        <li>
            <a href="{{ route('employee.enrollment.number-of-enrollees.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.number-of-enrollees.*')])>
                <i class="ti ti-checklist"></i><span class="nav-text">Number of Enrollees</span>
            </a>
        </li>
        {{-- Users given the NSTP menu in User Access (the route checks it too: 'menu.access:nstp-url') --}}
        @if (Auth::guard('web')->user()->canAccessMenu('nstp-url'))
            <li>
                <a href="{{ route('employee.enrollment.nstp-enrollees.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.nstp-enrollees.*')])>
                    <i class="ti ti-brand-miniprogram"></i><span class="nav-text">CWTS/LTS/ROTC</span>
                </a>
            </li>
        @endif
    @endif

    {{-- Outside the Reports block: Encoders may open it too (User::STUDENT_RECORD_ROLES, 'student-record' middleware) --}}
    @if (Auth::guard('web')->user()->canViewStudentRecords())
        <li>
            <a href="{{ route('employee.enrollment.student-record.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.student-record.*')])>
                <i class="ti ti-book-2"></i><span class="nav-text">Student Record</span>
            </a>
        </li>
    @endif
    {{-- Registrar and Administrators only (User::canCorrectGrades, 'registrar' middleware) --}}
    @if (Auth::guard('web')->user()->canCorrectGrades())
        <li>
            <a href="{{ route('employee.enrollment.students-enrolled.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.students-enrolled.*')])>
                <i class="ti ti-category"></i><span class="nav-text">Students Enrolled</span>
            </a>
        </li>
    @endif
</ul>
