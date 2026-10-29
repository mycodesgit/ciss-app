{{-- Generate Pre-Enrollment: iframe src = the PDF route (AdmissionPreEnrollmentController, dompdf); opened by admission/common.js --}}
@include('pages.employee.admission.shared.pdf-modal', ['id' => 'preEnrollmentModal', 'title' => 'Pre-Enrollment Form'])
