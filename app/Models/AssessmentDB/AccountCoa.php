<?php

namespace App\Models\AssessmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCoa extends Model
{
    use HasFactory;

    protected $connection = 'assessment';
    protected $table = 'coa_accounts';

    protected $fillable = [
        'accountcoa_code',
        'accountcoa_name',
        'remember_token', 
    ];
}
