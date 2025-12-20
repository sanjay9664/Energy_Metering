<?php
declare(strict_types=1);
namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SiteRequest;
use App\Models\Site;
use App\User;
use App\Models\Admin;
use App\Models\MongodbData;
use App\Models\MongodbFrontend;
use App\Models\RechargeSetting;
use App\Models\DeductionHistory;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use MongoDB\Client as MongoClient;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Pool;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Facades\Cache;
use DateTimeZone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Auth, DB, Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
use App\Models\Recharge;
use App\Exports\EnergyReportExport;
use Maatwebsite\Excel\Facades\Excel;

class SiteController extends Controller
{
    public function index(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['site.view']);

        $user = Auth::guard('admin')->user();

        if ($user->hasRole('superadmin')) {
            return view('backend.pages.sites.index', [
                'sites' => Site::all(),
            ]);
        } else {
            return view('backend.pages.sites.index', [
                'sites' => Site::where('email', $user->email)->get(),
            ]);
        }
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['site.create']);

        $user_emails = Admin::select('email', 'username')->where('username', '!=', 'superadmin')->get();
        return view('backend.pages.sites.create', compact('user_emails'));
    }

    protected function generateUniqueSlug($siteName)
    {
        $slug = Str::slug($siteName);
        $originalSlug = $slug;

        $count = 1;
        while (Site::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function store(SiteRequest $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['site.create']);

        $site = new Site();
        $site->site_name = $request->site_name;
        $site->slug = $this->generateUniqueSlug($request->site_name);
        $site->email = $request->email;
        $site->device_id = $request->device_id;
        $site->alternate_device_id = $request->alternate_device_id;
        $site->user_email = $request->user_email;
        $site->user_password = Hash::make($request->user_password);
        $site->clusterID = $request->clusterID;
        $site->increase_running_hours_status = $request->has('increase_running_hours_status') ? 1 : 0;

        $additionalData = [
            'site_name' => $request->input('site_name'),
            'meter_type' => $request->input('meter_type'),
            'asset_name' => $request->input('asset_name'),
            'group' => $request->input('group'),
            'generator' => $request->input('generator'),
            'serial_number' => $request->input('serial_number'),
            'model' => $request->input('model'),
            'brand' => $request->input('brand'),
            'capacity' => $request->input('capacity'),
            'run_status' => [
                'md' => $request->input('run_status_md'),
                'add' => $request->input('run_status_add'),
            ],
            'active_power_kw' => [
                'md' => $request->input('active_power_kw_md'),
                'add' => $request->input('active_power_kw_add'),
            ],
            'active_power_kva' => [
                'md' => $request->input('active_power_kva_md'),
                'add' => $request->input('active_power_kva_add'),
            ],
            'power_factor' => [
                'md' => $request->input('power_factor_md'),
                'add' => $request->input('power_factor_add'),
            ],
            'meter_number' => [
                'md' => $request->input('meter_number_md'),
                'add' => $request->input('meter_number_add'),
            ],
            'total_kwh' => [
                'md' => $request->input('total_kwh_md'),
                'add' => $request->input('total_kwh_add'),
            ],
            'start_md' => [
                'md' => $request->input('start_md'),
                'add' => $request->input('start_add'),
                'argument' => $request->input('start_arg'),
            ],
            'stop_md' => [
                'md' => $request->input('stop_md'),
                'add' => $request->input('stop_add'),
                'argument' => $request->input('stop_arg'),
            ],
            'auto_md' => [
                'md' => $request->input('auto_md'),
                'add' => $request->input('auto_add'),
                'argument' => $request->input('auto_arg'),
            ],
            'manual_md' => [
                'md' => $request->input('manual_md'),
                'add' => $request->input('manual_add'),
                'argument' => $request->input('manual_arg1'),
            ],
            'mode_md' => [
                'md' => $request->input('mode_md'),
                'add' => $request->input('mode_add'),

            ],
            'parameters' => [
                'coolant_temperature' => [
                    'md' => $request->input('coolant_temperature_md'),
                    'add' => $request->input('coolant_temperature_add'),
                    'low' => $request->input('coolant_temperature_low'),
                    'high' => $request->input('coolant_temperature_high')
                ],
                'grid_balance' => [
                    'md' => $request->input('grid_balance_md'),
                    'add' => $request->input('grid_balance_add'),
                ],
                'dg_unit' => [
                    'md' => $request->input('dg_unit_md'),
                    'add' => $request->input('dg_unit_add'),
                ],
                'oil_temperature' => [
                    'md' => $request->input('oil_temperature_md'),
                    'add' => $request->input('oil_temperature_add'),
                    // 'low' => $request->input('oil_temperature_low'),
                    // 'high' => $request->input('oil_temperature_high')
                ],
                'grid_unit' => [
                    'md' => $request->input('grid_unit_md'),
                    'add' => $request->input('grid_unit_add'),
                ],
                'oil_pressure' => [
                    'md' => $request->input('oil_pressure_md'),
                    'add' => $request->input('oil_pressure_add'),
                    'low' => $request->input('oil_pressure_low'),
                    'high' => $request->input('oil_pressure_high')
                ],
                'rpm' => [
                    'md' => $request->input('rpm_md'),
                    'add' => $request->input('rpm_add'),
                    'low' => $request->input('rpm_low'),
                    'high' => $request->input('rpm_high')
                ],
                'number_of_starts' => [
                    'md' => $request->input('number_of_starts_md'),
                    'add' => $request->input('number_of_starts_add'),
                    'low' => $request->input('number_of_starts_low'),
                    'high' => $request->input('number_of_starts_high')
                ],
                'battery_voltage' => [
                    'md' => $request->input('battery_voltage_md'),
                    'add' => $request->input('battery_voltage_add'),
                ],
                'fuel' => [
                    'md' => $request->input('fuel_md'),
                    'add' => $request->input('fuel_add'),
                ],
            ],
            'running_hours' => [
                'value' => $request->input('running_hours_value'),
                'md' => $request->input('running_hours_md'),
                'add' => $request->input('running_hours_add'),
                'admin_run_hours' => $request->input('admin_run_hours'),
                'increase_minutes' => $request->input('increase_minutes'),
            ],
            'readOn' => [
                    'md' => $request->input('readOn_md'),
                    'add' => $request->input('readOn_add'),
                ],
            'connect' => [
                    'md' => $request->input('connect_md'),
                    'add' => $request->input('connect_add'),
                ],
            'electric_parameters' => [
                'voltage_l_l' => [
                    'a' => [
                        'md' => $request->input('voltage_l_l_a_md'),
                        'add' => $request->input('voltage_l_l_a_add'),
                    ],
                    'b' => [
                        'md' => $request->input('voltage_l_l_b_md'),
                        'add' => $request->input('voltage_l_l_b_add'),
                    ],
                    'c' => [
                        'md' => $request->input('voltage_l_l_c_md'),
                        'add' => $request->input('voltage_l_l_c_add'),
                    ],
                ],
                'voltage_l_n' => [
                    'a' => [
                        'md' => $request->input('voltage_l_n_a_md'),
                        'add' => $request->input('voltage_l_n_a_add'),
                    ],
                    'b' => [
                        'md' => $request->input('voltage_l_n_b_md'),
                        'add' => $request->input('voltage_l_n_b_add'),
                    ],
                    'c' => [
                        'md' => $request->input('voltage_l_n_c_md'),
                        'add' => $request->input('voltage_l_n_c_add'),
                    ],
                ],
                'current' => [
                    'a' => [
                        'md' => $request->input('current_a_md'),
                        'add' => $request->input('current_a_add'),
                    ],
                    'b' => [
                        'md' => $request->input('current_b_md'),
                        'add' => $request->input('current_b_add'),
                    ],
                    'c' => [
                        'md' => $request->input('current_c_md'),
                        'add' => $request->input('current_c_add'),
                    ],
                ],
                'pf_data' => [
                    'md' => $request->input('pf_data_md'),
                    'add' => $request->input('pf_data_add'),
                ],
                'frequency' => [
                    'md' => $request->input('frequency_md'),
                    'add' => $request->input('frequency_add'),
                ],
            ],
            'alarm_status' => [
                'recharge' => [
                    'md' =>  $request->input('recharge_md'),
                    'add' => $request->input('recharge_add'),
                ],
                 'fixed_charge_mains' => [
                    'md' =>  $request->input('fixed_charge_md_mains'),
                    'add' => $request->input('fixed_charge_add_mains'),
                ],
                'unit_charge_mains' => [
                    'md' =>  $request->input('unit_charge_md_mains'),
                    'add' => $request->input('unit_charge_add_mains'),
                ],
                'sanction_load_mains_r' => [
                    'md' => $request->input('sanction_load_mains_r'),
                    'add' => $request->input('sanction_load_mains_r'),
                ],
                'sanction_load_mains_y' => [
                    'md' => $request->input('sanction_load_md_mains_y'),
                    'add' => $request->input('sanction_load_add_mains_y'),
                ],
                'sanction_load_mains_b' => [
                    'md' => $request->input('sanction_load_md_mains_b'),
                    'add' => $request->input('sanction_load_add_mains_b'),
                ],
                'fixed_charge_dg' => [
                    'md' => $request->input('fixed_charge_md_dg'),
                    'add' => $request->input('fixed_charge_add_dg'),
                ],
                'unit_charge_dg' => [
                    'md' => $request->input('unit_charge_md_dg'),
                    'add' => $request->input('unit_charge_add_dg'),
                ],
                'sanction_load_dg_r' => [
                    'md' => $request->input('sanction_load_md_dg_r'),
                    'add' => $request->input('sanction_load_add_dg_r'),
                ],
                'sanction_load_dg_y' => [
                    'md' => $request->input('sanction_load_md_dg_y'),
                    'add' => $request->input('sanction_load_add_dg_y'),
                ],
                'sanction_load_dg_b' => [
                    'md' => $request->input('sanction_load_md_dg_b'),
                    'add' => $request->input('sanction_load_add_dg_b'),
                ],
                'oil_pressure_status' => [
                    'md' => $request->input('oil_pressure_md_status'),
                    'add' => $request->input('oil_pressure_add_status'),
                ],
                'battery_level_status' => [
                    'md' => $request->input('battery_level_md_status'),
                    'add' => $request->input('battery_level_add_status'),
                ],
                'low_oil_pressure_status' => [
                    'md' => $request->input('low_oil_pressure_md_status'),
                    'add' => $request->input('low_oil_pressure_add_status'),
                ],
                'high_oil_temperature_status' => [
                    'md' => $request->input('high_oil_temperature_md_status'),
                    'add' => $request->input('high_oil_temperature_add_status'),
                ],
                'over_speed_status' => [
                    'md' => $request->input('over_speed_md_status'),
                    'add' => $request->input('over_speed_add_status'),
                ],
                'fail_to_come_to_rest_status' => [
                    'md' => $request->input('fail_to_come_to_rest_md_status'),
                    'add' => $request->input('fail_to_come_to_rest_add_status'),
                ],
                'generator_low_voltage_status' => [
                    'md' => $request->input('generator_low_voltage_md_status'),
                    'add' => $request->input('generator_low_voltage_add_status'),
                ]
            ],
        ];

        // dd($additionalData);
        $site->data = json_encode($additionalData);
        $site->save();

        session()->flash('success', __('Site has been created.'));
        return redirect()->route('admin.sites.index');
    }

    public function edit(int $id): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['site.edit']);

        $site = Site::findOrFail($id);
        $siteData = json_decode($site->data, true);
        $user_emails = Admin::select('email', 'username')->where('username', '!=', 'superadmin')->get();
    
        return view('backend.pages.sites.edit', [
            'site' => $site,
            'siteData' => $siteData,
            'roles' => Role::all(),
            'user_emails' => $user_emails,
        ]);
    }

    public function update(SiteRequest $request, int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['site.update']);

        $site = Site::findOrFail($id);
        $site->site_name = $request->site_name;
        $site->slug = $this->generateUniqueSlug($request->site_name);
        $site->email = $request->email;
        $site->device_id = $request->device_id;
        $site->alternate_device_id = $request->alternate_device_id;
        $site->user_email = $request->user_email;
        $site->user_password = Hash::make($request->user_password);
        $site->clusterID = $request->clusterID;
        $site->increase_running_hours_status = $request->input('increase_running_hours_status', 0);

        $additionalData = [
            'site_name' => $request->input('site_name'),
            'meter_type' => $request->input('meter_type'),
            'asset_name' => $request->input('asset_name'),
            'group' => $request->input('group'),
            'generator' => $request->input('generator'),
            'serial_number' => $request->input('serial_number'),
            'model' => $request->input('model'),
            'brand' => $request->input('brand'),
            'capacity' => $request->input('capacity'),
            'run_status' => [
                'md' => $request->input('run_status_md'),
                'add' => $request->input('run_status_add'),
            ],
            'active_power_kw' => [
                'md' => $request->input('active_power_kw_md'),
                'add' => $request->input('active_power_kw_add'),
            ],
            'active_power_kva' => [
                'md' => $request->input('active_power_kva_md'),
                'add' => $request->input('active_power_kva_add'),
            ],
            'power_factor' => [
                'md' => $request->input('power_factor_md'),
                'add' => $request->input('power_factor_add'),
            ],
            'meter_number' => [
                'md' => $request->input('meter_number_md'),
                'add' => $request->input('meter_number_add'),
            ],
            'total_kwh' => [
                'md' => $request->input('total_kwh_md'),
                'add' => $request->input('total_kwh_add'),
            ],
            'start_md' => [
                'md' => $request->input('start_md'),
                'add' => $request->input('start_add'),
                'argument' => $request->input('start_arg'),
            ],
            'stop_md' => [
                'md' => $request->input('stop_md'),
                'add' => $request->input('stop_add'),
                'argument' => $request->input('stop_arg'),
            ],
            'auto_md' => [
                'md' => $request->input('auto_md'),
                'add' => $request->input('auto_add'),
                'argument' => $request->input('auto_arg'),
            ],
            'manual_md' => [
                'md' => $request->input('manual_md'),
                'add' => $request->input('manual_add'),
                'argument' => $request->input('manual_arg1'),
            ],
            'mode_md' => [
                'md' => $request->input('mode_md'),
                'add' => $request->input('mode_add'),

            ],
            'parameters' => [
                'coolant_temperature' => [
                    'md' => $request->input('coolant_temperature_md'),
                    'add' => $request->input('coolant_temperature_add'),
                    'low' => $request->input('coolant_temperature_low'),
                    'high' => $request->input('coolant_temperature_high')
                ],
                'grid_balance' => [
                    'md' => $request->input('grid_balance_md'),
                    'add' => $request->input('grid_balance_add'),
                ],
                'dg_unit' => [
                    'md' => $request->input('dg_unit_md'),
                    'add' => $request->input('dg_unit_add'),
                ],
                'oil_temperature' => [
                    'md' => $request->input('oil_temperature_md'),
                    'add' => $request->input('oil_temperature_add'),
                    // 'low' => $request->input('oil_temperature_low'),
                    // 'high' => $request->input('oil_temperature_high')
                ],
                'grid_unit' => [
                    'md' => $request->input('grid_unit_md'),
                    'add' => $request->input('grid_unit_add'),
                ],
                'oil_pressure' => [
                    'md' => $request->input('oil_pressure_md'),
                    'add' => $request->input('oil_pressure_add'),
                    'low' => $request->input('oil_pressure_low'),
                    'high' => $request->input('oil_pressure_high')
                ],
                'rpm' => [
                    'md' => $request->input('rpm_md'),
                    'add' => $request->input('rpm_add'),
                    'low' => $request->input('rpm_low'),
                    'high' => $request->input('rpm_high')
                ],
                'number_of_starts' => [
                    'md' => $request->input('number_of_starts_md'),
                    'add' => $request->input('number_of_starts_add'),
                    'low' => $request->input('number_of_starts_low'),
                    'high' => $request->input('number_of_starts_high')
                ],
                'battery_voltage' => [
                    'md' => $request->input('battery_voltage_md'),
                    'add' => $request->input('battery_voltage_add'),
                ],
                'fuel' => [
                    'md' => $request->input('fuel_md'),
                    'add' => $request->input('fuel_add'),
                ],
            ],
            'running_hours' => [
                'value' => $request->input('running_hours_value'),
                'md' => $request->input('running_hours_md'),
                'add' => $request->input('running_hours_add'),
                'admin_run_hours' => $request->input('admin_run_hours'),
                'increase_minutes' => $request->input('increase_minutes'),
            ],
            'readOn' => [
                    'md' => $request->input('readOn_md'),
                    'add' => $request->input('readOn_add'),
                ],
            'connect' => [
                    'md' => $request->input('connect_md'),
                    'add' => $request->input('connect_add'),
                ],
            'electric_parameters' => [
                'voltage_l_l' => [
                    'a' => [
                        'md' => $request->input('voltage_l_l_a_md'),
                        'add' => $request->input('voltage_l_l_a_add'),
                    ],
                    'b' => [
                        'md' => $request->input('voltage_l_l_b_md'),
                        'add' => $request->input('voltage_l_l_b_add'),
                    ],
                    'c' => [
                        'md' => $request->input('voltage_l_l_c_md'),
                        'add' => $request->input('voltage_l_l_c_add'),
                    ],
                ],
                'voltage_l_n' => [
                    'a' => [
                        'md' => $request->input('voltage_l_n_a_md'),
                        'add' => $request->input('voltage_l_n_a_add'),
                    ],
                    'b' => [
                        'md' => $request->input('voltage_l_n_b_md'),
                        'add' => $request->input('voltage_l_n_b_add'),
                    ],
                    'c' => [
                        'md' => $request->input('voltage_l_n_c_md'),
                        'add' => $request->input('voltage_l_n_c_add'),
                    ],
                ],
                'current' => [
                    'a' => [
                        'md' => $request->input('current_a_md'),
                        'add' => $request->input('current_a_add'),
                    ],
                    'b' => [
                        'md' => $request->input('current_b_md'),
                        'add' => $request->input('current_b_add'),
                    ],
                    'c' => [
                        'md' => $request->input('current_c_md'),
                        'add' => $request->input('current_c_add'),
                    ],
                ],
                'pf_data' => [
                    'md' => $request->input('pf_data_md'),
                    'add' => $request->input('pf_data_add'),
                ],
                'frequency' => [
                    'md' => $request->input('frequency_md'),
                    'add' => $request->input('frequency_add'),
                ],
            ],
            'alarm_status' => [
                'recharge' => [
                    'md' =>  $request->input('recharge_md'),
                    'add' => $request->input('recharge_add'),
                ],
                'fixed_charge_mains' => [
                    'md' =>  $request->input('fixed_charge_md_mains'),
                    'add' => $request->input('fixed_charge_add_mains'),
                ],
                'unit_charge_mains' => [
                    'md' =>  $request->input('unit_charge_md_mains'),
                    'add' => $request->input('unit_charge_add_mains'),
                ],
                'sanction_load_mains_r' => [
                    'md' => $request->input('sanction_load_md_mains_r'),
                    'add' => $request->input('sanction_load_add_mains_r'),
                ],
                'sanction_load_mains_y' => [
                    'md' => $request->input('sanction_load_md_mains_y'),
                    'add' => $request->input('sanction_load_add_mains_y'),
                ],
                'sanction_load_mains_b' => [
                    'md' => $request->input('sanction_load_md_mains_b'),
                    'add' => $request->input('sanction_load_add_mains_b'),
                ],
                'fixed_charge_dg' => [
                    'md' => $request->input('fixed_charge_md_dg'),
                    'add' => $request->input('fixed_charge_add_dg'),
                ],
                'unit_charge_dg' => [
                    'md' => $request->input('unit_charge_md_dg'),
                    'add' => $request->input('unit_charge_add_dg'),
                ],
                'sanction_load_dg_r' => [
                    'md' => $request->input('sanction_load_md_dg_r'),
                    'add' => $request->input('sanction_load_add_dg_r'),
                ],
                'sanction_load_dg_y' => [
                    'md' => $request->input('sanction_load_md_dg_y'),
                    'add' => $request->input('sanction_load_add_dg_y'),
                ],
                'sanction_load_dg_b' => [
                    'md' => $request->input('sanction_load_md_dg_b'),
                    'add' => $request->input('sanction_load_add_dg_b'),
                ],
                'oil_pressure_status' => [
                    'md' => $request->input('oil_pressure_md_status'),
                    'add' => $request->input('oil_pressure_add_status'),
                ],
                'battery_level_status' => [
                    'md' => $request->input('battery_level_md_status'),
                    'add' => $request->input('battery_level_add_status'),
                ],
                'low_oil_pressure_status' => [
                    'md' => $request->input('low_oil_pressure_md_status'),
                    'add' => $request->input('low_oil_pressure_add_status'),
                ],
                'high_oil_temperature_status' => [
                    'md' => $request->input('high_oil_temperature_md_status'),
                    'add' => $request->input('high_oil_temperature_add_status'),
                ],
                'over_speed_status' => [
                    'md' => $request->input('over_speed_md_status'),
                    'add' => $request->input('over_speed_add_status'),
                ],
                'fail_to_come_to_rest_status' => [
                    'md' => $request->input('fail_to_come_to_rest_md_status'),
                    'add' => $request->input('fail_to_come_to_rest_add_status'),
                ],
                'generator_low_voltage_status' => [
                    'md' => $request->input('generator_low_voltage_md_status'),
                    'add' => $request->input('generator_low_voltage_add_status'),
                ]
            ],
        ];

        // dd($additionalData);
        $site->data = json_encode($additionalData);
        $site->save();

        session()->flash('success', __('Site has been Updated.'));
        return redirect()->route('admin.sites.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['site.delete']);

        $site = Site::findOrFail($id);
        $site->delete();
        session()->flash('success', 'Site has been deleted.');
        return back();
    }

    public function show(Request $request, $slug)
    {
        $siteData = Site::where('slug', $slug)->first();
        if (!$siteData) {
            return redirect()->back()->withErrors('Site not found or module_id is missing.');
        }

        $data = json_decode($siteData->data, true);
        $mdValues = $this->extractMdFields($data);

        $mongoUri = 'mongodb://isaqaadmin:password@44.240.110.54:27017/isa_qa';
        $client = new MongoClient($mongoUri);
        $database = $client->isa_qa;
        $collection = $database->device_events;

        $events = [];

        if (!empty($mdValues)) {
            $uniqueMdValues = array_unique((array) $mdValues);
            $uniqueMdValues = array_filter($uniqueMdValues, function ($value) {
                return !empty($value);
            });
            $uniqueMdValues = array_map('intval', $uniqueMdValues);
            $uniqueMdValues = array_values($uniqueMdValues);

            foreach ($uniqueMdValues as $moduleId) {
                $event = $collection->findOne(
                    ['module_id' => $moduleId],
                    ['sort' => ['createdAt' => -1]]
                );
                if ($event) {
                    $events[] = $event;
                }
            }
        }

        if (empty($events)) {
            return redirect()->back()->withErrors('No data found for the specified module_id values.');
        }

        usort($events, function ($a, $b) {
            $createdAtA = new UTCDateTime((int) round($a['created_at_timestamp'] * 1000));
            $createdAtB = new UTCDateTime((int) round($b['created_at_timestamp'] * 1000));
            return $createdAtB <=> $createdAtA;
        });

        $latestCreatedAt = $events[0]['createdAt'];

        $latestCreatedAtFormatted = $latestCreatedAt->toDateTime()->setTimezone(new DateTimeZone('Asia/Kolkata'))->format('d-m-Y H:i:s');

        foreach ($events as &$event) {
            $event['createdAt'] = $event['createdAt']->toDateTime()->setTimezone(new DateTimeZone('Asia/Kolkata'))->format('d-m-Y H:i:s');

            $event['latestCreatedAt'] = $latestCreatedAtFormatted;
        }

        header('Content-Type: application/json');

        $eventsData = json_encode($events, JSON_PRETTY_PRINT);

        $sitejsonData = json_decode($siteData['data']);

        $user = Auth::guard('admin')->user();

        $role = $request->query('role');

        $rechargeSetting = RechargeSetting::where('m_site_id', $siteData->id)->firstOrFail();
        $deductionHistory = DeductionHistory::get();
       
        if ($role == 'superadmin') {
            return view('backend.pages.sites.superadmin-site-details', [
                'siteData' => $siteData,
                'sitejsonData' => $sitejsonData,
                'eventData' => $events,
                'latestCreatedAt' => $latestCreatedAtFormatted,
            ]);
        }

        if ($role == 'admin') {
            // return $deductionHistory;
            return view('backend.pages.sites.site-details', [
                'siteData' => $siteData,
                'sitejsonData' => $sitejsonData,
                'eventData' => $events,
                'latestCreatedAt' => $latestCreatedAtFormatted,
                'rechargeSetting' => $rechargeSetting,
                'deductionHistory' => $deductionHistory,
            ]);
        }

        if ($user->hasRole('superadmin')) {

            return view(
                'backend.pages.sites.superadmin-site-details',
                [
                    'siteData' => $siteData,
                    'sitejsonData' => $sitejsonData,
                    'eventData' => $events,
                    'latestCreatedAt' => $latestCreatedAtFormatted,
                ]
            );
        } else {
            return view(
                'backend.pages.sites.site-details',
                [
                    'siteData' => $siteData,
                    'sitejsonData' => $sitejsonData,
                    'eventData' => $events,
                    'latestCreatedAt' => $latestCreatedAtFormatted,
                ]
            );
        }
    }

    public function AdminSites(Request $request)
    {
        $role = $request->query('role');
        $bankName = $request->query('bank_name');
        $location = $request->query('location');

        $siteData = collect();
        $eventData = [];
        $latestCreatedAt = null;

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('admin.login')->withErrors('You must be logged in.');
        }

        $userEmail = $user->email;

        if (!empty($location)) {
            $query = DB::table('sites')->where('email', $userEmail);

            if (!empty($bankName) && $bankName !== 'Select Bank') {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.generator')) = ?", [$bankName]);
            }
            if (!empty($location) && $location !== 'Select Location') {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.group')) = ?", [$location]);
            }

            $siteData = $query->get();

            $decodedSiteData = $siteData->map(function ($site) {
                return json_decode($site->data, true);
            });

            $mdValues = $this->extractMdFields($decodedSiteData->toArray());

            $mongoUri = 'mongodb://isaqaadmin:password@44.240.110.54:27017/isa_qa';
            $client = new MongoClient($mongoUri);
            $database = $client->isa_qa;
            $collection = $database->device_events;

            if (!empty($mdValues)) {
                $uniqueMdValues = array_unique(array_filter(array_map('intval', (array) $mdValues)));

                foreach ($uniqueMdValues as $moduleId) {
                    $event = $collection->findOne(
                        ['module_id' => $moduleId],
                        ['sort' => ['createdAt' => -1]]
                    );

                    if ($event) {
                        $eventData[] = $event;
                    }
                }
            }

            usort($eventData, function ($a, $b) {
                return ($b['created_at_timestamp'] ?? 0) <=> ($a['created_at_timestamp'] ?? 0);
            });

            $latestCreatedAt = !empty($eventData) ? $eventData[0]['createdAt']->toDateTime()
                ->setTimezone(new DateTimeZone('Asia/Kolkata'))
                ->format('d-m-Y H:i:s') : 'N/A';

            foreach ($eventData as &$event) {
                $event['createdAt'] = $event['createdAt']->toDateTime()
                    ->setTimezone(new DateTimeZone('Asia/Kolkata'))
                    ->format('d-m-Y H:i:s');
                $event['latestCreatedAt'] = $latestCreatedAt;
            }

            foreach ($siteData as $site) {
                $matchingEvent = collect($eventData)->first(function ($event) use ($site) {
                    return isset($event['device_id'], $site->device_id) &&
                        trim(strtolower($event['device_id'])) === trim(strtolower($site->device_id));
                });

                $site->updatedAt = isset($matchingEvent['updatedAt']) ? $matchingEvent['updatedAt']->toDateTime()
                    ->setTimezone(new DateTimeZone('Asia/Kolkata'))
                    ->format('d-m-Y H:i:s') : 'N/A';
            }

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('backend.pages.sites.partials.site-table', compact('siteData', 'decodedSiteData', 'eventData', 'latestCreatedAt'))->render()
                ]);
            }

            return view('backend.pages.sites.admin-sites', compact('siteData', 'decodedSiteData', 'eventData', 'latestCreatedAt'));
        } else {
            ini_set('max_execution_time', 120);
            $start = microtime(true);

            $user = Auth::guard('admin')->user();
            $userEmail = $user->email;

            $siteData = $user->hasRole('superadmin')
                ? Site::select(['id', 'site_name', 'slug', 'email', 'data', 'device_id', 'clusterID','alternate_device_id','user_email','user_password'])->get()
                : Site::where('email', $userEmail)->select(['id', 'site_name', 'slug', 'email', 'data', 'device_id', 'clusterID','alternate_device_id','user_email','user_password'])->get();

            $mdValues = $this->extractMdFields(
                $siteData->pluck('data')->map(fn($data) => json_decode($data, true))->toArray()
            );

            $eventData = MongodbFrontend::pluck('data')->toArray();

            // return $eventData;

            usort($eventData, fn($a, $b) => ($b['created_at_timestamp'] ?? 0) <=> ($a['created_at_timestamp'] ?? 0));

            if (!empty($eventData)) {
                $timestamp = (int) ($eventData[0]['createdAt']['$date']['$numberLong'] ?? 0);
                $latestCreatedAt = $timestamp
                    ? (new \DateTime('@' . ($timestamp / 1000)))
                    ->setTimezone(new \DateTimeZone('Asia/Kolkata'))
                    ->format('d-m-Y H:i:s')
                    : 'N/A';
            } else {
                $latestCreatedAt = 'N/A';
            }

            foreach ($eventData as &$event) {
                $timestamp = (int) ($event['createdAt']['$date']['$numberLong'] ?? 0);

                $event['createdAt'] = $timestamp
                    ? (new \DateTime('@' . ($timestamp / 1000)))
                    ->setTimezone(new \DateTimeZone('Asia/Kolkata'))
                    ->format('d-m-Y H:i:s')
                    : 'N/A';

                $event['latestCreatedAt'] = $latestCreatedAt;
            }

            $eventMap = collect($eventData)->mapWithKeys(function ($event) {
                $key = strtolower(trim($event['device_id'] ?? ''));
                return [$key => $event];
            });

            foreach ($siteData as $site) {
                $deviceId = strtolower(trim($site->device_id ?? ''));
                $matchingEvent = $eventMap[$deviceId] ?? null;

                $updatedAt = 'N/A';

                if ($matchingEvent && isset($matchingEvent['updatedAt'])) {
                    if ($matchingEvent['updatedAt'] instanceof \MongoDB\BSON\UTCDateTime) {
                        $updatedAt = $matchingEvent['updatedAt']
                            ->toDateTime()
                            ->setTimezone(new \DateTimeZone('Asia/Kolkata'))
                            ->format('d-m-Y H:i:s');
                    } elseif (is_array($matchingEvent['updatedAt']['$date'] ?? null)) {
                        $timestamp = (int) ($matchingEvent['updatedAt']['$date']['$numberLong'] ?? 0);
                        if ($timestamp) {
                            $updatedAt = (new \DateTime('@' . ($timestamp / 1000)))
                                ->setTimezone(new \DateTimeZone('Asia/Kolkata'))
                                ->format('d-m-Y H:i:s');
                        }
                    }
                }

                $site->updatedAt = $updatedAt;
            }

            $rechargeSetting = RechargeSetting::whereIn('m_site_id', $siteData->pluck('id'))->get()->keyBy('m_site_id');

            $sitejsonData = json_decode($siteData->first()->data ?? '{}', true);
            // return $eventData;

            return view('backend.pages.sites.admin-sites', compact('siteData', 'sitejsonData', 'eventData', 'latestCreatedAt', 'rechargeSetting'));
        }
    }

    public function fetchStatuses(Request $request)
    {
        $siteIds = $request->input('site_ids', []);
        $sites = Site::whereIn('id', $siteIds)->select('id', 'device_id', 'clusterID')->get();

        $httpClient = new Client();
        $dgRequests = [];
        $controllerRequests = [];
        $dgResults = [];
        $controllerResults = [];

        foreach ($sites as $site) {
            if ($site->device_id) {
                $dgRequests[$site->id] = new GuzzleRequest('GET', "http://app.sochiot.com/api/config-engine/device/status/uuid/{$site->device_id}");
            }
            if ($site->clusterID) {
                $controllerRequests[$site->id] = new GuzzleRequest('GET', "http://app.sochiot.com/api/config-engine/gateway/status/uuid/{$site->clusterID}");
            }
        }

        // Pool for DG
        $dgPool = new Pool($httpClient, $dgRequests, [
            'concurrency' => 10,
            'fulfilled' => function ($response, $siteId) use (&$dgResults) {
                $dgResults[$siteId] = strtoupper(trim($response->getBody()->getContents()));
            },
            'rejected' => function () {}
        ]);

        // Pool for Controller
        $ctrlPool = new Pool($httpClient, $controllerRequests, [
            'concurrency' => 10,
            'fulfilled' => function ($response, $siteId) use (&$controllerResults) {
                $controllerResults[$siteId] = strtoupper(trim($response->getBody()->getContents()));
            },
            'rejected' => function () {}
        ]);

        $dgPool->promise()->wait();
        $ctrlPool->promise()->wait();

        $statuses = [];
        foreach ($siteIds as $siteId) {
            $statuses[$siteId] = [
                'dg_status' => $dgResults[$siteId] ?? 'OFFLINE',
                'controller_status' => $controllerResults[$siteId] ?? 'OFFLINE',
            ];
        }

        return response()->json($statuses);
    }

    public function fetchLatestData($slug)
    {
        $siteData = Site::where('slug', $slug)->first();

        if (!$siteData) {
            return response()->json(['error' => 'Site not found or module_id is missing.'], 404);
        }

        $data = json_decode($siteData->data, true);
        $mdValues = $this->extractMdFields($data);
        $mongoUri = 'mongodb://isaqaadmin:password@44.240.110.54:27017/isa_qa';
        $client = new MongoClient($mongoUri);
        $database = $client->isa_qa;
        $collection = $database->device_events;

        $events = [];
        if (!empty($mdValues)) {
            $uniqueMdValues = array_unique((array) $mdValues);
            $uniqueMdValues = array_filter($uniqueMdValues, function ($value) {
                return !empty($value);
            });
            $uniqueMdValues = array_map('intval', $uniqueMdValues);
            $uniqueMdValues = array_values($uniqueMdValues);

            foreach ($uniqueMdValues as $moduleId) {
                $event = $collection->findOne(
                    ['module_id' => $moduleId],
                    ['sort' => ['createdAt' => -1]]
                );
                if ($event) {
                    $events[] = $event;
                }
            }
        }

        $eventsData = json_encode($events, JSON_PRETTY_PRINT);

        return response()->json(['eventData' => $events]);
    }

    private function extractMdFields($data)
    {
        $mdFields = [];

        array_walk_recursive($data, function ($value, $key) use (&$mdFields) {
            if ($key === 'md' && !is_null($value)) {
                $mdFields[] = $value;
            }
        });

        return $mdFields;
    }

    // For Api
    public function apiSites()
    {
        $user = Auth::guard('admin_api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->hasRole('superadmin')) {
            $sites = Site::all();
        } else {
            $sites = Site::where('email', $user->email)->get();
        }

        return response()->json([
            'success' => true,
            'data' => $sites,
        ]);
    }

    public function apiStoreDevice(Request $request)
    {
        $emails = is_array($request->userEmail)
            ? $request->userEmail
            : explode(',', $request->userEmail);

        $validator = Validator::make($request->all(), [
            'deviceName'       => 'required|string|max:255',
            'deviceId'         => 'required|string|max:255',
            'moduleId'         => 'required|string|max:255',
            'eventField'       => 'required|string|max:255',
            'siteId'           => 'required|string|max:255',
            'lowerLimit'       => 'nullable|numeric',
            'upperLimit'       => 'nullable|numeric',
            'lowerLimitMsg'    => 'nullable|string|max:255',
            'upperLimitMsg'    => 'nullable|string|max:255',
            'userEmail'        => ['required', function ($attribute, $value, $fail) use ($emails) {
                foreach ($emails as $email) {
                    if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                        $fail('One or more email addresses are invalid.');
                        break;
                    }
                }
            }],
            'userPassword'     => 'required|string|min:8',
            'userPassword'     => 'required|string|min:8',
            'owner_email'      => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for existing device
        $exists = DB::table('device_events')
            ->where('deviceName', $request->deviceName)
            ->where('deviceId', $request->deviceId)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Device with this name and ID already exists.'
            ], 409);
        }

        // Insert device record
        DB::table('device_events')->insert([
            'deviceName'     => $request->deviceName,
            'deviceId'       => $request->deviceId,
            'moduleId'       => $request->moduleId,
            'eventField'     => $request->eventField,
            'siteId'         => $request->siteId,
            'lowerLimit'     => $request->lowerLimit,
            'upperLimit'     => $request->upperLimit,
            'lowerLimitMsg'  => $request->lowerLimitMsg,
            'upperLimitMsg'  => $request->upperLimitMsg,
            'userEmail'      => is_array($request->userEmail)
                ? implode(',', $request->userEmail)
                : $request->userEmail,
            'userPassword'   => Hash::make($request->userPassword),
            'owner_email'    => $request->owner_email,
            'created_at'     => now(),
            'updated_at'     => now()
        ]);

        return response()->json([
            'message' => 'Device event saved successfully'
        ], 201);
    }

    public function apiFetchDevice(Request $request)
    {
        $data = DB::table('device_events')->get();
        return view('backend.pages.notification.dg-list', ['data' => $data]);
    }

    public function NotificationCreate(Request $request)
    {
        $data = DB::table('device_events')->get();
        return view('backend.pages.notification.create-site', ['data' => $data]);
    }

    public function NotificationEdit(Request $request)
    {
        return view('backend.pages.notification.edit-site');
    }

    public function apiUpdateDevice(Request $request)
    {
        $device = DeviceEvent::find($request->id);

        if (!$device) {
            return response()->json(['message' => 'Device not found.'], 404);
        }

        $emails = is_array($request->userEmail)
            ? $request->userEmail
            : explode(',', $request->userEmail);

        // Update fields
        $device->deviceName      = $request->deviceName;
        $device->deviceId        = $request->deviceId;
        $device->moduleId        = $request->moduleId;
        $device->eventField      = $request->eventField;
        $device->siteId          = $request->siteId;
        $device->lowerLimit      = $request->lowerLimit;
        $device->upperLimit      = $request->upperLimit;
        $device->lowerLimitMsg   = $request->lowerLimitMsg;
        $device->upperLimitMsg   = $request->upperLimitMsg;
        $device->userEmail       = json_encode($emails); // ✅ Save as JSON array
        $device->userPassword    = $request->userPassword;
        $device->owner_email     = $request->owner_email;

        $device->save();

        return response()->json(['message' => 'Device updated successfully.']);
    }

    public function showDeviceForm()
    {
        $data = DB::table('device_events')->get();
        return view('device-update', compact('data'));
    }

    public function startProcess(Request $request)
    {
        // dd($request->all());
        $data = $request->only(['argValue', 'moduleId', 'cmdField', 'cmdArg', 'actionType']);
        $action = $data['actionType'] ?? 'unknown';

        try {
            $apiUrl = 'http://app.sochiot.com:8082/api/config-engine/device/command/push/remote';

            $response = Http::post($apiUrl, [
                'argValue' => $data['argValue'],
                'moduleId' => $data['moduleId'],
                'cmdField' => $data['cmdField'],
                'cmdArg' => $data['cmdArg'],
            ]);

            return response()->json([
                'message' => ucfirst($action) . ' process completed successfully.',
                'external_response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => ucfirst($action) . ' process failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function storeRechargeSettings(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'm_site_id' => 'required|integer|exists:sites,id',
    //             'm_recharge_amount' => 'nullable|numeric',
    //             'm_fixed_charge' => 'nullable|numeric',
    //             'm_unit_charge' => 'nullable|numeric',
    //             'm_sanction_load' => 'nullable|numeric',
    //             'dg_fixed_charge' => 'nullable|numeric',
    //             'dg_unit_charge' => 'nullable|numeric',
    //             'dg_sanction_load' => 'nullable|numeric',
    //             'kwh' => 'nullable|numeric', // ⬅️ NEW
    //         ]);


    //         $siteId = $validated['m_site_id'];
    //         $deltaAmount = $validated['m_recharge_amount'] ?? 0; // jo user input kare (add/subtract)

    //         // 🔹 Get existing record (if any)
    //         $existing = RechargeSetting::where('m_site_id', $siteId)->first();

    //         if ($existing) {
    //             $oldAmount = $existing->m_recharge_amount ?? 0;
    //             $updatedAmount = $oldAmount + $deltaAmount; // Allow negative result also

    //             $existing->update([
    //                 'm_recharge_amount' => $updatedAmount,
    //                 'm_fixed_charge' => $validated['m_fixed_charge'] ?? $existing->m_fixed_charge,
    //                 'm_unit_charge' => $validated['m_unit_charge'] ?? $existing->m_unit_charge,
    //                 'm_sanction_load' => $validated['m_sanction_load'] ?? $existing->m_sanction_load,
    //                 'dg_fixed_charge' => $validated['dg_fixed_charge'] ?? $existing->dg_fixed_charge,
    //                 'dg_unit_charge' => $validated['dg_unit_charge'] ?? $existing->dg_unit_charge,
    //                 'dg_sanction_load' => $validated['dg_sanction_load'] ?? $existing->dg_sanction_load,
    //                 'kwh' => $validated['kwh'] ?? $existing->kwh, // ⬅️ NEW
    //             ]);

    //         } 
    //         else {
    //             // No record yet → just create
    //             RechargeSetting::create($validated);
    //         }

    //         return back()->with('success', 'Recharge balance updated successfully!');
    //     } 
    //     catch (\Illuminate\Validation\ValidationException $e) {
    //         return back()
    //             ->withErrors($e->validator)
    //             ->withInput()
    //             ->with('error', 'Please correct the highlighted errors.');
    //     } 
    //     catch (\Exception $e) {
    //         return back()
    //             ->with('error', 'Unexpected error: ' . $e->getMessage())
    //             ->withInput();
    //     }
    // }

    // public function storeRechargeSettings(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'm_site_id' => 'required|integer|exists:sites,id',
    //             'm_recharge_amount' => 'nullable|numeric',
    //             'm_fixed_charge' => 'nullable|numeric',
    //             'm_unit_charge' => 'nullable|numeric',
    //             'm_sanction_load' => 'nullable|numeric',
    //             'dg_fixed_charge' => 'nullable|numeric',
    //             'dg_unit_charge' => 'nullable|numeric',
    //             'dg_sanction_load' => 'nullable|numeric',
    //             'kwh' => 'nullable|numeric',
    //         ]);

    //         $siteId = $validated['m_site_id'];
    //         $deltaAmount = $validated['m_recharge_amount'] ?? 0;

    //         // Fetch existing recharge setting
    //         $existing = RechargeSetting::where('m_site_id', $siteId)->first();

    //         if ($existing) {
    //             $oldAmount = $existing->m_recharge_amount ?? 0;
    //             $updatedAmount = $oldAmount + $deltaAmount;

    //             $existing->update([
    //                 'm_recharge_amount' => $updatedAmount,
    //                 'm_fixed_charge' => $validated['m_fixed_charge'] ?? $existing->m_fixed_charge,
    //                 'm_unit_charge' => $validated['m_unit_charge'] ?? $existing->m_unit_charge,
    //                 'm_sanction_load' => $validated['m_sanction_load'] ?? $existing->m_sanction_load,
    //                 'dg_fixed_charge' => $validated['dg_fixed_charge'] ?? $existing->dg_fixed_charge,
    //                 'dg_unit_charge' => $validated['dg_unit_charge'] ?? $existing->dg_unit_charge,
    //                 'dg_sanction_load' => $validated['dg_sanction_load'] ?? $existing->dg_sanction_load,
    //                 'kwh' => $validated['kwh'] ?? $existing->kwh,
    //             ]);
    //         } else {
    //             // Create new setting
    //             RechargeSetting::create($validated);
    //         }

    //         /**
    //          * ----------------------------------------
    //          * INSERT INTO "recharges" TABLE
    //          * ----------------------------------------
    //          */
    //         Recharge::create([
    //             'site_id' => $siteId,
    //             'recharge_id' => 1,
    //             'recharge_amount' => $deltaAmount,
    //         ]);

    //         return back()->with('success', 'Recharge updated & recharge entry added!');
    //     }

    //     catch (\Illuminate\Validation\ValidationException $e) {
    //         return back()
    //             ->withErrors($e->validator)
    //             ->withInput()
    //             ->with('error', 'Please correct the errors.');
    //     }

    //     catch (\Exception $e) {
    //         return back()
    //             ->with('error', 'Unexpected error: ' . $e->getMessage())
    //             ->withInput();
    //     }
    // }
// public function storeRechargeSettings(Request $request)
// {
//     // Start database transaction
//     DB::beginTransaction();
    
//     try {
//         $validated = $request->validate([
//             'm_site_id' => 'required|integer|exists:sites,id',
//             'm_recharge_amount' => 'nullable|numeric',
//             'm_fixed_charge' => 'nullable|numeric',
//             'm_unit_charge' => 'nullable|numeric',
//             'm_sanction_load' => 'nullable|numeric',
//             'dg_fixed_charge' => 'nullable|numeric',
//             'dg_unit_charge' => 'nullable|numeric',
//             'dg_sanction_load' => 'nullable|numeric',
//             'kwh' => 'nullable|numeric',
//         ]);

//         $siteId = $validated['m_site_id'];
//         $deltaAmount = $validated['m_recharge_amount'] ?? 0;
//         $kwhValue = $validated['kwh'] ?? null;

//         // Debug log
//         \Log::info('Storing Recharge Settings', [
//             'site_id' => $siteId,
//             'kwh_input' => $kwhValue,
//             'delta_amount' => $deltaAmount
//         ]);

//         // Get existing record or create new
//         $rechargeSetting = RechargeSetting::where('m_site_id', $siteId)->first();

//         if ($rechargeSetting) {
//             // Calculate new amount
//             $currentAmount = $rechargeSetting->m_recharge_amount ?? 0;
//             $newAmount = $currentAmount + $deltaAmount;
            
//             // Prepare update data
//             $updateData = [
//                 'm_recharge_amount' => $newAmount,
//                 'm_fixed_charge' => $validated['m_fixed_charge'] ?? $rechargeSetting->m_fixed_charge,
//                 'm_unit_charge' => $validated['m_unit_charge'] ?? $rechargeSetting->m_unit_charge,
//                 'm_sanction_load' => $validated['m_sanction_load'] ?? $rechargeSetting->m_sanction_load,
//                 'dg_fixed_charge' => $validated['dg_fixed_charge'] ?? $rechargeSetting->dg_fixed_charge,
//                 'dg_unit_charge' => $validated['dg_unit_charge'] ?? $rechargeSetting->dg_unit_charge,
//                 'dg_sanction_load' => $validated['dg_sanction_load'] ?? $rechargeSetting->dg_sanction_load,
//             ];
            
//             // Update KWH only if provided
//             if (!is_null($kwhValue)) {
//                 $updateData['kwh'] = $kwhValue;
//             }
            
//             // Update the record
//             $rechargeSetting->update($updateData);
            
//             \Log::info('Updated existing record', [
//                 'old_amount' => $currentAmount,
//                 'new_amount' => $newAmount,
//                 'kwh' => $kwhValue
//             ]);
//         } else {
//             // Create new record
//             $rechargeSetting = RechargeSetting::create([
//                 'm_site_id' => $siteId,
//                 'm_recharge_amount' => $deltaAmount,
//                 'kwh' => $kwhValue,
//                 'm_fixed_charge' => $validated['m_fixed_charge'] ?? null,
//                 'm_unit_charge' => $validated['m_unit_charge'] ?? null,
//                 'm_sanction_load' => $validated['m_sanction_load'] ?? null,
//                 'dg_fixed_charge' => $validated['dg_fixed_charge'] ?? null,
//                 'dg_unit_charge' => $validated['dg_unit_charge'] ?? null,
//                 'dg_sanction_load' => $validated['dg_sanction_load'] ?? null,
//             ]);
            
//             \Log::info('Created new record', [
//                 'amount' => $deltaAmount,
//                 'kwh' => $kwhValue
//             ]);
//         }

//         // Add to recharge history table
//         $maxRechargeId = Recharge::where('site_id', $siteId)->max('recharge_id') ?? 0;
//         $newRechargeId = $maxRechargeId + 1;
        
//         Recharge::create([
//             'site_id' => $siteId,
//             'recharge_id' => $newRechargeId,
//             'recharge_amount' => $deltaAmount,
//             'kwh' => $kwhValue,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);

//         \Log::info('Created recharge history entry', [
//             'recharge_id' => $newRechargeId,
//             'amount' => $deltaAmount
//         ]);

//         // Commit transaction
//         DB::commit();

//         // Clear cache and session to force fresh data
//         \Cache::forget('recharge_settings_' . $siteId);
//         session()->forget('recharge_data_' . $siteId);

//         return redirect()->back()
//             ->with('success', 'Recharge settings saved successfully!')
//             ->with('refresh_page', true); // Add flag for page refresh

//     } catch (\Exception $e) {
//         // Rollback transaction on error
//         DB::rollBack();
        
//         \Log::error('Error in storeRechargeSettings: ' . $e->getMessage());
//         \Log::error('Stack trace: ' . $e->getTraceAsString());
        
//         return redirect()->back()
//             ->with('error', 'Error: ' . $e->getMessage())
//             ->withInput();
//     }
// }

    // public function storeRechargeSettings(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $validated = $request->validate([
    //             'm_site_id' => 'required|integer|exists:sites,id',
    //             'm_recharge_amount' => 'nullable|numeric',
    //             'm_fixed_charge' => 'nullable|numeric',
    //             'm_unit_charge' => 'nullable|numeric',
    //             'm_sanction_load_r' => 'nullable|numeric',
    //             'm_sanction_load_y' => 'nullable|numeric',
    //             'm_sanction_load_b' => 'nullable|numeric',
    //             'dg_fixed_charge' => 'nullable|numeric',
    //             'dg_unit_charge' => 'nullable|numeric',
    //             'dg_sanction_load_r' => 'nullable|numeric',
    //             'dg_sanction_load_y' => 'nullable|numeric',
    //             'dg_sanction_load_b' => 'nullable|numeric',
    //             'kwh' => 'nullable|numeric',
    //         ]);

    //         $siteId = $validated['m_site_id'];
    //         $deltaAmount = $validated['m_recharge_amount'] ?? 0;
    //         $kwhValue = $validated['kwh'] ?? null;

    //         $rechargeSetting = RechargeSetting::where('m_site_id', $siteId)->first();

    //         $updateData = [
    //             'm_recharge_amount' => ($rechargeSetting->m_recharge_amount ?? 0) + $deltaAmount,
    //             'm_fixed_charge' => $validated['m_fixed_charge'] ?? $rechargeSetting->m_fixed_charge ?? null,
    //             'm_unit_charge' => $validated['m_unit_charge'] ?? $rechargeSetting->m_unit_charge ?? null,
    //             'm_sanction_load_r' => $validated['m_sanction_load_r'] ?? $rechargeSetting->m_sanction_load_r ?? null,
    //             'm_sanction_load_y' => $validated['m_sanction_load_y'] ?? $rechargeSetting->m_sanction_load_y ?? null,
    //             'm_sanction_load_b' => $validated['m_sanction_load_b'] ?? $rechargeSetting->m_sanction_load_b ?? null,
    //             'dg_fixed_charge' => $validated['dg_fixed_charge'] ?? $rechargeSetting->dg_fixed_charge ?? null,
    //             'dg_unit_charge' => $validated['dg_unit_charge'] ?? $rechargeSetting->dg_unit_charge ?? null,
    //             'dg_sanction_load_r' => $validated['dg_sanction_load_r'] ?? $rechargeSetting->dg_sanction_load_r ?? null,
    //             'dg_sanction_load_y' => $validated['dg_sanction_load_y'] ?? $rechargeSetting->dg_sanction_load_y ?? null,
    //             'dg_sanction_load_b' => $validated['dg_sanction_load_b'] ?? $rechargeSetting->dg_sanction_load_b ?? null,
    //         ];

    //         if (!is_null($kwhValue)) {
    //             $updateData['kwh'] = $kwhValue;
    //         }

    //         if ($rechargeSetting) {
    //             $rechargeSetting->update($updateData);
    //         } else {
    //             $updateData['m_site_id'] = $siteId;
    //             $rechargeSetting = RechargeSetting::create($updateData);
    //         }

    //         // Recharge history
    //         $maxRechargeId = Recharge::where('site_id', $siteId)->max('recharge_id') ?? 0;
    //         Recharge::create([
    //             'site_id' => $siteId,
    //             'recharge_id' => $maxRechargeId + 1,
    //             'recharge_amount' => $deltaAmount,
    //             'kwh' => $kwhValue,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         DB::commit();

    //         // PUSH DATA TO DEVICE AFTER SUCCESSFUL RECHARGE
    //         $this->pushRechargeToDeviceInternal($siteId, 1);

    //         \Cache::forget('recharge_settings_' . $siteId);
    //         session()->forget('recharge_data_' . $siteId);

    //         return redirect()->back()->with('success', 'Recharge settings saved successfully!')->with('refresh_page', true);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::error('Error in storeRechargeSettings: '.$e->getMessage());
    //         return redirect()->back()->with('error', 'Error: '.$e->getMessage())->withInput();
    //     }
    // }

    // private function pushRechargeToDeviceInternal($siteId, $status = 1)
    // {
    //     $site = Site::select('data')->where('id', $siteId)->first();
    //     if (!$site) return;

    //     $siteJson = json_decode($site->data, true);

    //     $moduleId = $siteJson['alarm_status']['recharge']['md'] ?? null;

    //     if (!$moduleId) {
    //         \Log::error('Module ID missing in site JSON', ['siteId' => $siteId]);
    //         return;
    //     }

    //     $requiredKeys = [
    //         'recharge',
    //         'unit_charge_dg',
    //         'fixed_charge_dg',
    //         'unit_charge_mains',
    //         'fixed_charge_mains',
    //         'sanction_load_dg_b',
    //         'sanction_load_dg_r',
    //         'sanction_load_dg_y',
    //         'sanction_load_mains_b',
    //         'sanction_load_mains_r',
    //         'sanction_load_mains_y',
    //     ];

    //     $deviceData = array_intersect_key(
    //         $siteJson['alarm_status'] ?? [],
    //         array_flip($requiredKeys)
    //     );

    //     $payload = [
    //         'argValue' => 1,
    //         'cmdArg'   => $status,
    //         'moduleId' => (string) $moduleId,
    //         'cmdField' => 'recharge_settings',
    //         'data'     => $deviceData,
    //     ];

    //     try {
    //         $response = Http::timeout(10)->post(
    //             'http://app.sochiot.com:8082/api/config-engine/device/command/push/remote',
    //             $payload
    //         );

    //         \Log::info('Recharge pushed successfully', [
    //             'site_id' => $siteId,
    //             'module_id' => $moduleId,
    //             'payload' => json_encode($payload),
    //             'response' => json_encode($response->json()),
    //             'status' => 'SUCCESS',
    //         ]);

    //     } catch (\Exception $e) {
    //         \Log::error('Recharge Push Error', ['error' => $e->getMessage()]);
    //     }
    // }


    // top both function commnet because check the code 
    
public function storeRechargeSettings(Request $request)
{
    DB::beginTransaction();
    try {
        $validated = $request->validate([
            'm_site_id' => 'required|integer|exists:sites,id',
            'm_recharge_amount' => 'nullable|numeric',
            'm_fixed_charge' => 'nullable|numeric',
            'm_unit_charge' => 'nullable|numeric',
            'm_sanction_load_r' => 'nullable|numeric',
            'm_sanction_load_y' => 'nullable|numeric',
            'm_sanction_load_b' => 'nullable|numeric',
            'dg_fixed_charge' => 'nullable|numeric',
            'dg_unit_charge' => 'nullable|numeric',
            'dg_sanction_load_r' => 'nullable|numeric',
            'dg_sanction_load_y' => 'nullable|numeric',
            'dg_sanction_load_b' => 'nullable|numeric',
            'kwh' => 'nullable|numeric',
        ]);

        $siteId = $validated['m_site_id'];
        $deltaAmount = $validated['m_recharge_amount'] ?? 0;
        $kwhValue = $validated['kwh'] ?? null;

        $rechargeSetting = RechargeSetting::where('m_site_id', $siteId)->first();

        $updateData = [
            'm_recharge_amount' => ($rechargeSetting->m_recharge_amount ?? 0) + $deltaAmount,
            'm_fixed_charge' => $validated['m_fixed_charge'] ?? $rechargeSetting->m_fixed_charge ?? null,
            'm_unit_charge' => $validated['m_unit_charge'] ?? $rechargeSetting->m_unit_charge ?? null,
            'm_sanction_load_r' => $validated['m_sanction_load_r'] ?? $rechargeSetting->m_sanction_load_r ?? null,
            'm_sanction_load_y' => $validated['m_sanction_load_y'] ?? $rechargeSetting->m_sanction_load_y ?? null,
            'm_sanction_load_b' => $validated['m_sanction_load_b'] ?? $rechargeSetting->m_sanction_load_b ?? null,
            'dg_fixed_charge' => $validated['dg_fixed_charge'] ?? $rechargeSetting->dg_fixed_charge ?? null,
            'dg_unit_charge' => $validated['dg_unit_charge'] ?? $rechargeSetting->dg_unit_charge ?? null,
            'dg_sanction_load_r' => $validated['dg_sanction_load_r'] ?? $rechargeSetting->dg_sanction_load_r ?? null,
            'dg_sanction_load_y' => $validated['dg_sanction_load_y'] ?? $rechargeSetting->dg_sanction_load_y ?? null,
            'dg_sanction_load_b' => $validated['dg_sanction_load_b'] ?? $rechargeSetting->dg_sanction_load_b ?? null,
        ];

        if (!is_null($kwhValue)) {
            $updateData['kwh'] = $kwhValue;
        }

        if ($rechargeSetting) {
            $rechargeSetting->update($updateData);
        } else {
            $updateData['m_site_id'] = $siteId;
            $rechargeSetting = RechargeSetting::create($updateData);
        }

        // Recharge history
        $maxRechargeId = Recharge::where('site_id', $siteId)->max('recharge_id') ?? 0;
        Recharge::create([
            'site_id' => $siteId,
            'recharge_id' => $maxRechargeId + 1,
            'recharge_amount' => $deltaAmount,
            'kwh' => $kwhValue,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::commit();

        // PUSH ALL SETTINGS TO DEVICE - WITH CORRECT FORMAT
        $devicePushResults = $this->pushIndividualSettingsToDevice($siteId, $rechargeSetting);

        \Cache::forget('recharge_settings_' . $siteId);
        session()->forget('recharge_data_' . $siteId);

        return redirect()->back()
            ->with('success', 'Recharge settings saved and pushed to device successfully!')
            ->with('device_push_results', $devicePushResults)
            ->with('refresh_page', true);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error in storeRechargeSettings: '.$e->getMessage());
        return redirect()->back()->with('error', 'Error: '.$e->getMessage())->withInput();
    }
}

private function pushIndividualSettingsToDevice($siteId, $rechargeSetting)
{
    try {
        // Get site data from database
        $site = Site::find($siteId);
        if (!$site || !$site->data) {
            \Log::error("Site not found or no data for site ID: {$siteId}");
            return ['success' => false, 'message' => 'Site data not found'];
        }

        $siteData = json_decode($site->data, true);
        
        // DEBUG: Show all alarm_status configurations
        \Log::info("=== DEVICE CONFIGURATIONS FOR SITE {$siteId} ===");
        foreach ($siteData['alarm_status'] ?? [] as $key => $config) {
            \Log::info("{$key}: " . json_encode($config));
        }
        
        $deviceConfigs = $siteData['alarm_status'] ?? [];
        
        // Correct field mapping based on your JSON structure
        $fieldConfigs = [
            // Mains Charges
            'm_fixed_charge' => [
                'device_key' => 'fixed_charge_mains',
                'multiply' => 1, // No multiplication for fixed charge
                'input_field' => 'm_fixed_charge',
                'expected_format' => 'integer' // Rs value
            ],
            'm_unit_charge' => [
                'device_key' => 'unit_charge_mains',
                'multiply' => 100, // Convert Rs to paisa
                'input_field' => 'm_unit_charge',
                'expected_format' => 'integer' // Paisa value
            ],
            
            // DG Charges
            'dg_fixed_charge' => [
                'device_key' => 'fixed_charge_dg',
                'multiply' => 1,
                'input_field' => 'dg_fixed_charge',
                'expected_format' => 'integer'
            ],
            'dg_unit_charge' => [
                'device_key' => 'unit_charge_dg',
                'multiply' => 100, // Convert Rs to paisa
                'input_field' => 'dg_unit_charge',
                'expected_format' => 'integer'
            ],
            
            // Mains Sanction Loads (kW to Watts)
            'm_sanction_load_r' => [
                'device_key' => 'sanction_load_mains_r',
                'multiply' => 100, // kW to Watts
                'input_field' => 'm_sanction_load_r',
                'expected_format' => 'integer'
            ],
            'm_sanction_load_y' => [
                'device_key' => 'sanction_load_mains_y',
                'multiply' => 100, // kW to Watts
                'input_field' => 'm_sanction_load_y',
                'expected_format' => 'integer'
            ],
            'm_sanction_load_b' => [
                'device_key' => 'sanction_load_mains_b',
                'multiply' => 100, // kW to Watts
                'input_field' => 'm_sanction_load_b',
                'expected_format' => 'integer'
            ],
            
            // DG Sanction Loads (kW to Watts)
            'dg_sanction_load_r' => [
                'device_key' => 'sanction_load_dg_r',
                'multiply' => 100, // kW to Watts
                'input_field' => 'dg_sanction_load_r',
                'expected_format' => 'integer'
            ],
            'dg_sanction_load_y' => [
                'device_key' => 'sanction_load_dg_y',
                'multiply' => 100, // kW to Watts
                'input_field' => 'dg_sanction_load_y',
                'expected_format' => 'integer'
            ],
            'dg_sanction_load_b' => [
                'device_key' => 'sanction_load_dg_b',
                'multiply' => 100, // kW to Watts
                'input_field' => 'dg_sanction_load_b',
                'expected_format' => 'integer'
            ],
        ];

        $results = [];
        $successCount = 0;
        $totalCount = 0;

        foreach ($fieldConfigs as $fieldKey => $config) {
            $inputValue = $rechargeSetting->{$config['input_field']};
            
            if (is_null($inputValue) || $inputValue === '') {
                continue;
            }

            $totalCount++;

            if (!isset($deviceConfigs[$config['device_key']])) {
                \Log::warning("Device config not found: {$config['device_key']}");
                continue;
            }

            $deviceSetting = $deviceConfigs[$config['device_key']];
            
            if (empty($deviceSetting['md']) || empty($deviceSetting['add'])) {
                \Log::warning("Missing module/address for: {$config['device_key']}");
                continue;
            }

            // Calculate final value
            $finalValue = (int)($inputValue * $config['multiply']);
            
            // FORMAT THE ADDRESS CORRECTLY
            $cmdField = $this->formatAddressForMeter($deviceSetting['add']);
            
            $apiPayload = [
                'argValue' => 1,
                'cmdArg' => $finalValue,
                'moduleId' => (string) $deviceSetting['md'],
                'cmdField' => $cmdField
            ];

            \Log::info("🔧 SENDING TO METER:", [
                'field' => $fieldKey,
                'input' => $inputValue,
                'multiplied' => $finalValue,
                'multiply_factor' => $config['multiply'],
                'moduleId' => $deviceSetting['md'],
                'original_add' => $deviceSetting['add'],
                'formatted_cmdField' => $cmdField,
                'payload' => $apiPayload
            ]);

            $apiResponse = $this->sendSingleDeviceCommand($apiPayload, $fieldKey, $siteId);
            
            if ($apiResponse['success']) {
                $successCount++;
                \Log::info("✅ SUCCESS: {$fieldKey} = {$finalValue} sent to meter");
            } else {
                \Log::error("❌ FAILED: {$fieldKey} - " . ($apiResponse['error'] ?? 'Unknown error'));
            }

            $results[] = [
                'field' => $fieldKey,
                'input' => $inputValue,
                'sent' => $finalValue,
                'module' => $deviceSetting['md'],
                'address' => $cmdField,
                'success' => $apiResponse['success']
            ];

            sleep(1); // 1 second delay between requests
        }

        return [
            'success' => $successCount > 0,
            'total' => $totalCount,
            'success_count' => $successCount,
            'results' => $results
        ];

    } catch (\Exception $e) {
        \Log::error('Error pushing settings to device: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

private function formatAddressForMeter($address)
{
    // If address is already in correct format, return as is
    if (strpos($address, ',') !== false) {
        return $address; // "6,41396"
    }
    
    // If it's a single number, try to format it
    if (is_numeric($address)) {
        // Try different formats that might work
        $possibleFormats = [
            "6,$address",  // "6,41396"
            "4,$address",  // "4,41396"
            "3,$address",  // "3,41396"
            "$address",    // "41396"
        ];
        
        // You might need to adjust this based on what works for your meter
        return "6,$address"; // Default format
    }
    
    return $address;
}

private function sendSingleDeviceCommand($payload, $fieldName, $siteId)
{
    $apiUrl = 'http://app.sochiot.com:8082/api/config-engine/device/command/push/remote';
    
    try {
        $client = new \GuzzleHttp\Client();
        
        $response = $client->post($apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $payload,
            'timeout' => 15,
            'http_errors' => false
        ]);

        $responseBody = json_decode($response->getBody()->getContents(), true);
        
        return [
            'success' => $response->getStatusCode() === 200,
            'status_code' => $response->getStatusCode(),
            'response' => $responseBody
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
    
    public function triggerConnectionApi(Request $request)
    {
        $request->validate([
            'status' => 'required|in:0,1',
            'site_id' => 'required|integer',
            'moduleId' => 'nullable|string',
            'cmdField' => 'nullable|string',
        ]);

        try {
            $siteId = $request->site_id;
            $status = $request->status;

            // Fetch recharge settings
            $recharge = RechargeSetting::where('m_site_id', $siteId)->first();

            if(!$recharge) {
                return response()->json(['success'=>false,'message'=>'Recharge data not found']);
            }

            // Update recharge amount logic (auto deduct/add)
            if($status == 1) { // Disconnect
                $recharge->m_recharge_amount = $recharge->m_recharge_amount - 0; // or any logic
            }

            $recharge->save();

            // Trigger remote API
            $payload = [
                'argValue' => 1,
                'cmdArg'   => $status,
                'moduleId' => $request->moduleId ?? '',
                'cmdField' => $request->cmdField ?? '',
            ];
            $response = Http::post('http://app.sochiot.com:8082/api/config-engine/device/command/push/remote', $payload);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Command executed' : 'API failed',
                'recharge_amount' => $recharge->m_recharge_amount
            ]);
        } catch(\Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // *********************************************************************************calculate report send data******************
    public function energyConsumptionDashboard()
    {
        $sites = Site::all();
        return view('backend.energy-dashboard', compact('sites'));
    }
    
    public function getEnergyData(Request $request)
    {
        try {
            $siteId = $request->site_id ?? 1;
            $type = $request->type;
            $month = $request->month ?? Carbon::now()->month;
            $year = $request->year ?? Carbon::now()->year;

            $query = DeductionHistory::where('site_id', $siteId);

            if ($type === 'daily') {
                $data = $this->getDailyData($query, $month, $year);
            } else {
                $data = $this->getMonthlyData($query, $year);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'type' => $type
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Daily data fetch
     */
    private function getDailyData($query, $month, $year)
    {
        return $query->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->selectRaw('DAY(created_at) as day, SUM(units) as total_units, SUM(amount) as total_amount')
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get()
                    ->map(function($item) {
                        return [
                            'day' => $item->day,
                            'total_units' => (float) $item->total_units,
                            'total_amount' => (float) $item->total_amount
                        ];
                    });
    }

    /**
     * Monthly data fetch
     */
    private function getMonthlyData($query, $year)
    {
        return $query->whereYear('created_at', $year)
                    ->selectRaw('MONTH(created_at) as month, SUM(units) as total_units, SUM(amount) as total_amount')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->map(function($item) {
                        return [
                            'month' => $item->month,
                            'total_units' => (float) $item->total_units,
                            'total_amount' => (float) $item->total_amount
                        ];
                    });
    }

    /**
     * Energy stats fetch
     */
    public function getEnergyStats(Request $request)
    {
        try {
            $siteId = $request->site_id ?? 1;
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $lastMonth = Carbon::now()->subMonth()->month;
            $lastMonthYear = Carbon::now()->subMonth()->year;

            // Current month stats
            $currentMonthData = DeductionHistory::where('site_id', $siteId)
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->selectRaw('
                    COALESCE(SUM(units), 0) as total_units,
                    COALESCE(SUM(amount), 0) as total_amount,
                    COALESCE(AVG(units), 0) as avg_units,
                    COALESCE(AVG(amount), 0) as avg_amount,
                    COALESCE(MAX(units), 0) as max_units,
                    COALESCE(MAX(amount), 0) as max_amount
                ')
                ->first();

            // Last month stats
            $lastMonthData = DeductionHistory::where('site_id', $siteId)
                ->whereYear('created_at', $lastMonthYear)
                ->whereMonth('created_at', $lastMonth)
                ->selectRaw('
                    COALESCE(SUM(units), 0) as total_units,
                    COALESCE(SUM(amount), 0) as total_amount,
                    COALESCE(AVG(units), 0) as avg_units,
                    COALESCE(AVG(amount), 0) as avg_amount,
                    COALESCE(MAX(units), 0) as max_units,
                    COALESCE(MAX(amount), 0) as max_amount
                ')
                ->first();

            return response()->json([
                'success' => true,
                'current_month' => $currentMonthData,
                'last_month' => $lastMonthData,
                'current_month_name' => Carbon::now()->format('F Y'),
                'last_month_name' => Carbon::now()->subMonth()->format('F Y')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stats: ' . $e->getMessage()
            ], 500);
        }
    }

    // ************************excel **********************

    public function downloadReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:daily,monthly,complete',
            'month' => 'sometimes|integer|min:1|max:12',
            'year' => 'sometimes|integer|min:2020|max:2030'
        ]);

        try {
            $data = $this->getRechargeData($request->type, $request->only(['month','year']));
            // dd($data);

            return $this->downloadExcelReport($request->type, $request->only(['month','year']));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadExcelReport($reportType, $filters)
    {
        $data = $this->getRechargeData($reportType, $filters);

        $previousAmount = null;

        foreach ($data as $item) {

            $todayAmount = floatval($item->m_recharge_amount);

            if ($previousAmount === null) {
                // first row → no cut
                $item->amount_cut = 0;
            } else {
                // diff calculation
                $item->amount_cut = $previousAmount - $todayAmount;
            }

            // assign previous amount for next row
            $previousAmount = $todayAmount;
        }
        $fileName = 'Recharge_Report_' . ucfirst($reportType) . '_' . now()->format('d-m-Y') . '.xlsx';

        return Excel::download(new EnergyReportExport($data), $fileName);
    }

    private function getRechargeData($reportType, $filters)
    {
        $query = Recharge::query()
            ->join('recharge_settings', 'recharges.recharge_id', '=', 'recharge_settings.id')
            ->select(
                'recharges.id',
                'recharges.created_at as setting_created_at',
                'recharge_settings.kwh',
                'recharge_settings.m_recharge_amount',
                'recharge_settings.m_unit_charge',
                'recharges.recharge_amount'
            );

        if ($reportType === 'daily') {
            if (isset($filters['month'], $filters['year'])) {
                $query->whereMonth('recharges.created_at', $filters['month'])
                    ->whereYear('recharges.created_at', $filters['year'])
                    ->whereDate('recharges.created_at', '<=', Carbon::today());
            } else {
                $query->whereDate('recharges.created_at', Carbon::today());
            }
        }

        if ($reportType === 'monthly') {
            if (isset($filters['month'], $filters['year'])) {
                $query->whereMonth('recharges.created_at', $filters['month'])
                    ->whereYear('recharges.created_at', $filters['year']);
            }
        }

        return $query->orderBy('recharges.created_at', 'asc')->get();
    }

    //Api for application
    public function mobileSiteDetails($slug)
    {
        $site = Site::where('slug', $slug)->first();

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found'
            ], 404);
        }

        $siteJson = json_decode($site->data, true);

        // Extract md values (module_id)
        $mdValues = $this->extractMdFields($siteJson);
        $moduleId = collect($mdValues)->filter()->first();

        // MongoDB fields
        $client = new MongoClient('mongodb://isaqaadmin:password@44.240.110.54:27017/isa_qa');
        $collection = $client->isa_qa->device_events;

        $event = null;
        if ($moduleId) {
            $event = $collection->findOne(
                ['module_id' => (int) $moduleId],
                ['sort' => ['createdAt' => -1]]
            );
        }

        // Defaults
        $gridBalance = null;
        $gridUnit = null;
        $dgUnit = null;
        $supplyStatus = null;
        $updatedAt = null;

        if ($event) {
            $gridBalance  = $event['grid_Balance'] ?? null;
            $gridUnit     = $event['grid_unit'] ?? null;
            $dgUnit       = $event['dg_unit'] ?? null;
            $supplyStatus = $event['supply_status'] ?? null;

            if (isset($event['createdAt'])) {
                $updatedAt = $event['createdAt']
                    ->toDateTime()
                    ->setTimezone(new \DateTimeZone('Asia/Kolkata'))
                    ->format('d-m-Y H:i:s');
            }
        }

        return response()->json([
            'success' => true,

            // from local db
            'asset_information' => [
                'custom_name'   => $siteJson['asset_name'] ?? null,
                'site_name'     => $site->site_name,
                'location'      => $siteJson['group'] ?? null,
                'meter_name'    => $siteJson['generator'] ?? null,
                'meter_number'  => $siteJson['serial_number'] ?? null,
                'controller'    => $siteJson['asset_name'] ?? null,
                'grid_kw'       => 5.0,   // added after knowing where it comming from
                'dg_kw'         => 50.0   // will change after the finding where it is comming
            ],

            //live events from mongodb
            'live_data' => [
                'grid_balance'     => $gridBalance,
                'grid_unit'        => $gridUnit,
                'dg_unit'          => $dgUnit,
                'connection_status'=> 'DISCONNECTED', // can be dynamic later
                'supply_status'    => $supplyStatus,
                'updated_at'       => $updatedAt
            ]
        ]);
    }

}
