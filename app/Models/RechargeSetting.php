<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'm_site_id',
        'm_recharge_amount', 
        'm_fixed_charge', 
        'm_unit_charge', 
        'm_sanction_load',
        'dg_fixed_charge', 
        'dg_unit_charge', 
        'dg_sanction_load',
    ];
}
class PriviousTotalKwh extends Model
{
    protected $table = 'privious_total_kWh';

    protected $fillable = [
        'm_site_id',
        'm_recharge_amount',
        'm_fixed_charge',
        'm_unit_charge',
        'm_sanction_load',
        'dg_fixed_charge',
        'dg_unit_charge',
        'dg_sanction_load',
    ];
}

