<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RechargeSetting;
use App\Models\Site;
use Illuminate\Support\Facades\Log;

class DailyFixedChargeDeduction extends Command
{
    protected $signature = 'fixedcharge:deduct-daily';
    protected $description = 'Deduct daily fixed charge from all sites';

    public function handle()
    {
        $this->info('Starting daily fixed charge deduction...');
        
        // Get all sites with recharge settings
        $rechargeSettings = RechargeSetting::whereNotNull('m_fixed_charge')
            ->with('site')
            ->get();
            
        foreach ($rechargeSettings as $setting) {
            try {
                $this->deductDailyFixedCharge($setting);
            } catch (\Exception $e) {
                Log::error("Failed to deduct fixed charge for site {$setting->m_site_id}: " . $e->getMessage());
            }
        }
        
        $this->info('Daily fixed charge deduction completed.');
        return 0;
    }
    
    private function deductDailyFixedCharge($rechargeSetting)
    {
        $fixedCharge = $rechargeSetting->m_fixed_charge ?? 0;
        $sanctionLoadR = $rechargeSetting->m_sanction_load_r ?? 0;
        $sanctionLoadY = $rechargeSetting->m_sanction_load_y ?? 0;
        $sanctionLoadB = $rechargeSetting->m_sanction_load_b ?? 0;
        
        // Get current month's days
        $daysInMonth = date('t');
        
        // Calculate total sanction load
        $totalSanctionLoad = $sanctionLoadR + $sanctionLoadY + $sanctionLoadB;
        
        if ($totalSanctionLoad > 0 && $daysInMonth > 0) {
            // Calculate daily deduction
            $dailyDeduction = ($fixedCharge * $totalSanctionLoad) / $daysInMonth;
            
            // Deduct from m_recharge_amount
            $currentBalance = $rechargeSetting->m_recharge_amount ?? 0;
            $newBalance = $currentBalance - $dailyDeduction;
            
            if ($newBalance < 0) {
                Log::warning("Site {$rechargeSetting->m_site_id} has insufficient balance for daily deduction.");
                // Optional: Send low balance alert
                $this->sendLowBalanceAlert($rechargeSetting);
            } else {
                // Update balance
                $rechargeSetting->update([
                    'm_recharge_amount' => $newBalance
                ]);
                
                // Log the deduction
                Log::info("Daily fixed charge deducted for site {$rechargeSetting->m_site_id}:", [
                    'date' => now()->format('Y-m-d'),
                    'daily_deduction' => $dailyDeduction,
                    'previous_balance' => $currentBalance,
                    'new_balance' => $newBalance,
                    'total_sanction_load' => $totalSanctionLoad
                ]);
                
                // Send update to meter if needed
                $this->updateMeterBalance($rechargeSetting, $newBalance);
            }
        }
    }
    
    private function sendLowBalanceAlert($rechargeSetting)
    {
        // Implement your alert logic here
        // Email, SMS, or notification
    }
    
    private function updateMeterBalance($rechargeSetting, $newBalance)
    {
        // Send new balance to meter if real-time update is required
        // Use your existing API call logic
    }
}