<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // SAMPLE DATA for the dashboard UI. Replace each block with real queries
        // (enrollment, calendar, announcements) when those modules are connected.
        $campusEnrollment = [
            'MC' => 8450, 'VC' => 1240, 'SCC' => 1610, 'MP' => 980, 'HC' => 1120, 'IC' => 1030,
            'CA' => 640, 'CC' => 1350, 'SC' => 870, 'HinC' => 760, 'VE' => 690,
        ];
        $totalEnrolled = array_sum($campusEnrollment);

        return view('pages.employee.home.dashboard', [
            'term' => [
                'academic_year' => '2025-2026',
                'semester' => '2nd Semester',
                'status' => 'Classes Ongoing',
            ],

            'stats' => [
                ['label' => 'Total Enrolled (All Campuses)', 'value' => $totalEnrolled, 'icon' => 'ti ti-users'],
                ['label' => 'New Students', 'value' => 3215, 'icon' => 'ti ti-user-plus'],
                ['label' => 'Pending Pre-Enrollment', 'value' => 412, 'icon' => 'ti ti-hourglass'],
                ['label' => 'Programs Offered', 'value' => 64, 'icon' => 'ti ti-books'],
            ],

            'campusEnrollment' => $campusEnrollment,
            'totalEnrolled' => $totalEnrolled,

            // status: done | current | upcoming
            'calendar' => [
                ['title' => 'Enrollment Period', 'date' => 'Jan 5 – 16, 2026', 'status' => 'done'],
                ['title' => 'Start of Classes', 'date' => 'Jan 19, 2026', 'status' => 'done'],
                ['title' => 'Midterm Examination', 'date' => 'Mar 9 – 13, 2026', 'status' => 'current'],
                ['title' => 'Final Examination', 'date' => 'May 11 – 15, 2026', 'status' => 'upcoming'],
                ['title' => 'Submission of Grades', 'date' => 'May 22, 2026', 'status' => 'upcoming'],
            ],

            'announcements' => [
                ['title' => 'Midterm grade encoding is now open', 'source' => 'Registrar', 'date' => 'Mar 2, 2026'],
                ['title' => 'Updated class schedules posted for all campuses', 'source' => 'Academic Affairs', 'date' => 'Feb 24, 2026'],
                ['title' => 'Scholarship renewal requirements due on March 20', 'source' => 'Scholarship Office', 'date' => 'Feb 18, 2026'],
                ['title' => 'CISS scheduled maintenance this Saturday, 8:00 – 10:00 PM', 'source' => 'MISO', 'date' => 'Feb 12, 2026'],
            ],
        ]);
    }
}
