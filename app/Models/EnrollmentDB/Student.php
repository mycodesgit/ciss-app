<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'students';

    protected $fillable = [
        'year',
        'stud_id',
        'app_id',
        'status',
        'en_status',
        'yeargrad',
        'p_status',
        'type',
        'campus',
        'lname',
        'fname',
        'mname',
        'ext',
        'course',
        'gender',
        'civil_status',
        'contact',
        'email',
        'religion',
        'address',
        'bday',
        'pbirth',
        'monthly_income',
        'hnum',
        'brgy',
        'city',
        'province',
        'region',
        'zcode',
        'lstsch_attended',
        'suc_lst_attended',
        'studlegstat_id',
        'stud_father',
        'stud_mother',
        'stud_guardian',
        'guardian_contact',
        'lst_sch_attended_year',
        'date_admission',
        'graduation_date',
        'postedBy',
        'stud_pic',
        'graduation_schlyear',
        'graduation_semester',
        'graduation_course',
        'lst_sch_type',
    ];
}
