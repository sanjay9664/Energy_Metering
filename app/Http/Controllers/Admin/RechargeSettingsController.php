<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RechargeSetting;

class RechargeSettingsController extends Controller
{
    public function index($site_id = null)
    {
        $setting = RechargeSetting::where('site_id', $site_id)->first();

        return view('admin.recharge_settings', compact('setting', 'site_id'));
    }

    // Save or update
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'nullable|integer',
            'recharge_amount' => 'nullable|numeric',
            'mains_fixed_charge' => 'nullable|numeric',
            'mains_unit_charge' => 'nullable|numeric',
            'mains_sanction_load' => 'nullable|numeric',
            'dg_fixed_charge' => 'nullable|numeric',
            'dg_unit_charge' => 'nullable|numeric',
            'dg_sanction_load' => 'nullable|numeric',
        ]);

        RechargeSetting::updateOrCreate(
            ['site_id' => $request->site_id],
            $validated
        );

        return back()->with('success', 'Recharge settings saved successfully!');
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:auto,manual',
        ]);

        $setting = RechargeSetting::first();
        $setting->status = $request->status;
        $setting->save();

        return response()->json(['success' => true, 'status' => $setting->status]);
    }
}
