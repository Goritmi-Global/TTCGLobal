<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralAccount extends Model
{ 
    use HasFactory;
    public $incrementing = false;
    protected $table = 'referral_accounts';
    
}
