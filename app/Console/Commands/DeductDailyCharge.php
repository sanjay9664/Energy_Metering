<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RechargeSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class DeductDailyCharge extends Command
{
    protected $signature = 'charge:deduct-daily';
    protected $description = 'Deduct daily fixed charges from each site recharge amount';

    public function handle()
    {
        $now = \Carbon\Carbon::now();
        $hour = $now->format('H'); 
        $daysInMonth = $now->daysInMonth;

        $sites = \App\Models\Site::all();

        foreach ($sites as $site) {

            $totalKwhValue = $this->getTotalKwhValue($site);
            $this->info("🔹 Site ID {$site->id}: total_kwh = {$totalKwhValue}");

            $recharge = RechargeSetting::where('m_site_id', $site->id)->first();
            if (!$recharge) continue;

            // -----------------------------------
            // 1️⃣ INCREMENTAL KWH DEDUCTION
            // -----------------------------------

            $previousKwh = floatval($recharge->kwh ?? 0);
            // $totalKwhValue = 4.49;

            $increment = round($totalKwhValue - $previousKwh, 3);

            if ($increment < 0) {
                $increment = 0;
            }

            if ($increment > 0) {
                $energyCharge = $increment * floatval($recharge->m_unit_charge);

                $this->deductAmount($recharge, $energyCharge, "Incremental Energy ({$increment} units)");

                \App\Models\DeductionHistory::create([
                    'site_id'     => $site->id,
                    'recharge_id' => $recharge->id,
                    'type'        => 'Incremental Energy',
                    'units'       => $increment,
                    'amount'      => $energyCharge,
                ]);
            }

            // Update DB with test reading
            $recharge->kwh = $totalKwhValue;
            $recharge->last_kwh_time = now();
            $recharge->save();

            // -----------------------------------
            // 2️⃣ DAILY FIXED CHARGE AT MIDNIGHT
            // -----------------------------------
            if ($hour == 0) {

                $monthlyFixedCharge = floatval($recharge->m_fixed_charge) * floatval($recharge->m_sanction_load);
                $dailyFixedCharge = $monthlyFixedCharge / $daysInMonth;

                if ($dailyFixedCharge > 0) {
                    $this->deductAmount($recharge, $dailyFixedCharge, 'Daily Fixed');

                    \App\Models\DeductionHistory::create([
                        'site_id'     => $site->id,
                        'recharge_id' => $recharge->id,
                        'type'        => 'Daily Fixed',
                        'units'       => 0,
                        'amount'      => $dailyFixedCharge,
                    ]);
                }
            }

            // -----------------------------------
            // 3️⃣ REMOTE API (unchanged)
            // -----------------------------------
            $cmdArg = ($recharge->m_recharge_amount <= 0) ? 1 : 0;
            $this->triggerRemoteApi($site, $cmdArg);
        }

        $this->info('✅ Incremental + daily fixed deduction completed.');
    }

    /**
     * Helper to perform safe deductions with log output
     */
    private function deductAmount($recharge, $deduction, $type)
    {
        if ($deduction <= 0) {
            $this->warn("⚠️ {$type}: No charge to deduct.");
            return;
        }

        $oldAmount = $recharge->m_recharge_amount;

        $recharge->m_recharge_amount -= $deduction;
        $recharge->save();

        $this->info("✅ {$type}: Deducted ₹" . number_format($deduction, 2) .
            " from ₹" . number_format($oldAmount, 2) .
            " → Remaining ₹" . number_format($recharge->m_recharge_amount, 2));

        if ($recharge->m_recharge_amount <= 0) {
            $this->warn("⚠️ {$type}: Insufficient balance! Current balance is ₹" . number_format($recharge->m_recharge_amount, 2));
        }
    }

    protected function getTotalKwhValue($site)
    {
        $this->info("🔍 Debugging Site ID {$site->id}...");
        $data = $site->data;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $decoded;
            } else {
                $this->warn("⚠️ Site ID {$site->id}: data field is not valid JSON.");
                return '_';
            }
        }
        if (!isset($data['total_kwh'])) {
            $this->warn("⚠️ Site ID {$site->id}: total_kwh not found in data field.");
            return '_';
        }
        $totalKwh = $data['total_kwh'];

        if (is_string($totalKwh)) {
            $totalKwh = json_decode($totalKwh, true);
        } elseif (is_object($totalKwh)) {
            $totalKwh = (array) $totalKwh;
        }

        $this->line("➡️ Raw total_kwh: " . print_r($totalKwh, true));

        if (!isset($totalKwh['md']) || !isset($totalKwh['add'])) {
            $this->warn("⚠️ Site ID {$site->id}: Missing md/add in total_kwh.");
            return '_';
        }

        $moduleId = $totalKwh['md'];
        $key = $totalKwh['add'];

        $this->line("📦 module_id = {$moduleId}, key = {$key}");

        $mongoData = \App\Models\MongodbFrontend::where('data->module_id', (int) $moduleId)
            ->latest()
            ->first();

        if (!$mongoData) {
            $this->warn("⚠️ Site ID {$site->id}: No MongoDB record found for module_id {$moduleId}");
            return '_';
        }

        $data = is_string($mongoData->data)
            ? json_decode($mongoData->data, true)
            : (array) $mongoData->data;

        $this->line("📄 MongoDB keys: " . implode(', ', array_keys($data)));

        if (array_key_exists($key, $data)) {
            return number_format((float) $data[$key], 2);
        }

        $this->warn("⚠️ Key '{$key}' not found in MongoDB record for module_id {$moduleId}");
        return '_';
    }

    /**
     * Trigger remote API command
     */
    protected function triggerRemoteApi($site, $cmdArg)
    {
        try {
            $totalKwh = is_string($site->connect) ? json_decode($site->connect, true) : (array)$site->connect;
            $data = $site->data;
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $decoded;

                $moduleId = $data['connect']['md'] ?? null;
                $key = $data['connect']['add'] ?? null;
            }
          
            $payload = [
                'argValue'  => '1',
                'moduleId'  => $moduleId,
                'cmdField'  => $key,
                'cmdArg'    => $cmdArg,
            ];
            // dd($payload);
            $response = Http::post('http://app.sochiot.com:8082/api/config-engine/device/command/push/remote', $payload);

            if ($response->successful()) {
                $this->info("🚀 API triggered successfully for Site ID {$site->id} (cmdArg={$cmdArg})");
            } else {
                $this->warn("⚠️ API failed for Site ID {$site->id}: " . $response->body());
            }
        } catch (\Exception $e) {
            $this->error("❌ API error for Site ID {$site->id}: " . $e->getMessage());
        }
    }
}
