<?php

namespace App\Models\AssessmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountAppraisal extends Model
{
    use HasFactory;

    protected $connection = 'assessment';
    protected $table = 'accounts';

    protected $fillable = [
        'fund_id',
        'coa_id',
        'account_name',
        'remember_token', 
    ];
}
