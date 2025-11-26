<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/react-circular-progressbar/2.0.3/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="{{url('backend/assets/css/site-details.css')}}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</head>
<style>
    :root {
        --primary-color: #002E6E;
        --secondary-color: #28a745;
        --light-bg: #f8f9fa;
        --card-bg: #ffffff;
    }

    body {
        background-color: #f5f7fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        width: 100%;
        overflow-x: hidden;
    }

    .status-box {
        background: #f9f9f9;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);

    }

    .container-fluid {
        padding: 20px;
        max-width: 100%;
    }

    .consumption-section {
        background-color: var(--light-bg);
        border-radius: 15px;
        padding: 25px;
        margin-top: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        width: 100%;
    }

    .graph-container {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        height: 400px;
        position: relative;
    }

    .stats-box {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 15px;
        height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stats-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .stats-label {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
    }

    .filter-section {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }

    .filter-btn {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .filter-btn:hover {
        background-color: #001f4d;
        transform: translateY(-2px);
    }

    .download-btn {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .download-btn:hover {
        background-color: #218838;
        transform: translateY(-2px);
    }

    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
    }

    .nav-tabs .nav-link {
        color: var(--primary-color);
        font-weight: 600;
        border: none;
        padding: 12px 25px;
        border-radius: 8px 8px 0 0;
    }

    .nav-tabs .nav-link.active {
        background-color: var(--primary-color);
        color: white;
    }

    .loading-spinner {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        background: rgba(255, 255, 255, 0.9);
        padding: 20px;
        border-radius: 10px;
    }

    .download-option {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .download-option:hover {
        border-color: var(--primary-color);
        background-color: rgba(0, 46, 110, 0.05);
    }

    .download-option.active {
        border-color: var(--primary-color);
        background-color: rgba(0, 46, 110, 0.1);
    }

    .download-icon {
        font-size: 24px;
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .success-alert {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideInRight 0.5s ease;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .chart-title {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 15px;
    }

    .consumption-badge {
        background-color: var(--primary-color);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .card-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .metric-highlight {
        display: flex;
        justify-content: space-between;
        background: rgba(0, 46, 110, 0.05);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .metric-title {
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .metric-value-large {
        font-size: 32px;
        font-weight: 700;
        color: var(--primary-color);
    }

    .metric-change {
        font-size: 14px;
        font-weight: 600;
    }

    .up {
        color: #28a745;
    }

    .down {
        color: #e74c3c;
    }

    .kwh-display-container {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    .kwh-box {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        width: 48%;
        height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .kwh-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .kwh-label {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 10px;
        }

        .consumption-section {
            padding: 15px;
        }

        .graph-container {
            height: 300px;
            padding: 15px;
        }

        .stats-box,
        .kwh-box {
            height: 100px;
            padding: 15px;
        }

        .stats-value,
        .kwh-value {
            font-size: 24px;
        }

        .metric-value-large {
            font-size: 24px;
        }

        .filter-section {
            padding: 15px;
        }

        .nav-tabs .nav-link {
            padding: 10px 15px;
            font-size: 14px;
        }
    }

    @media (max-width: 576px) {
        .graph-container {
            height: 250px;
        }

        .stats-box,
        .kwh-box {
            height: 90px;
            padding: 10px;
        }

        .stats-value,
        .kwh-value {
            font-size: 20px;
        }

        .metric-value-large {
            font-size: 20px;
        }

        .metric-highlight {
            flex-direction: column;
            gap: 15px;
        }

        .metric-highlight .text-end {
            text-align: left !important;
        }
    }
</style>


<body>
    <div class="header-container">
        <a class="navbar-brand" href="#">
            <img src="https://sochiot.com/wp-content/uploads/2022/04/sochiotlogo-re-e1688377669450.png"
                alt="sochiot_Logo" class="logo-img" />
        </a>
        <h5 class="header-title"> Energy Monitoring System</h5>
    </div>

    <div class="container-fluid">
        <div class="row mt-3" id="event-data">
            <!-- First Table for Asset Information -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-center text-white fw-bold fs-4 p-1" style="background: #002E6E;">
                        ASSET INFORMATION
                    </div>
                    <table class="table table-bordered table-striped table-hover">
                        <tbody>
                            <tr>
                                <th class="asset" style="background: #002E6E;">
                                    <strong>Custom Name</strong> : {{ $sitejsonData->asset_name }}
                                </th>
                                <td data-label="site_name">
                                    <strong>Site_Name:</strong> {{ $sitejsonData->site_name }}
                                </td>
                                <td data-label="Group">
                                    <strong>Location:</strong> {{ $sitejsonData->group }}
                                </td>
                                <td data-label="Generator">
                                    <strong>Meter Name:</strong> {{ $sitejsonData->generator }}
                                </td>
                                <td data-label="S/N">
                                    <strong>Meter Number</st||rong> {{ $sitejsonData->serial_number }}
                                </td>
                                <td data-label="Model">
                                    <strong>Controller:</strong> {{ $sitejsonData->asset_name }}
                                </td>
                                <td data-label="Brand">
                                    <!-- <strong>Grid:</strong> {{ $sitejsonData->brand }} -->
                                    <strong>Grid:</strong> {{ $rechargeSetting->m_sanction_load}}kW
                                </td>
                                <td data-label="Capacity">
                                    <strong>DG:</strong> {{ $rechargeSetting->dg_sanction_load}}kW
                                </td>
                            </tr>

                            <tr>
                                <?php
                                    // Get increased running hours from DB
                                    $increased_running_hours = DB::table('running_hours')->where('site_id', $siteData->id)->first();
                                    $increaseRunningHours = (float) ($increased_running_hours->increase_running_hours ?? 0);

                                    $addValue = 0;
                                    $key = $sitejsonData->running_hours->add ?? null;

                                    // Extract addValue from eventData
                                    foreach ($eventData as $event) {
                                        $eventArray = $event->getArrayCopy();
                                        if (
                                            isset($eventArray['module_id']) &&
                                            $eventArray['module_id'] == ($sitejsonData->running_hours->md ?? null)
                                        ) {
                                            if ($key && array_key_exists($key, $eventArray)) {
                                                $rawValue = $eventArray[$key];
                                                if (is_numeric($rawValue)) {
                                                    $addValue = (float) $rawValue;
                                                }
                                            }
                                            break;
                                        }
                                    }

                                    // Calculate increased value per minute
                                    $increaseMinutes = $sitejsonData->running_hours->increase_minutes ?? null;
                                    $inc_addValue = $addValue;

                                    if (is_numeric($increaseMinutes) && (float)$increaseMinutes > 0) {
                                        $inc_addValue /= (float)$increaseMinutes;
                                    }

                                    // Final total running hours
                                    $inc_addValueFormatted = $inc_addValue + $increaseRunningHours;

                                    // Convert to hours and minutes
                                    $hours = floor($inc_addValueFormatted);
                                    $minutes = round(($inc_addValueFormatted - $hours) * 60);
                                ?>

                                <?php
                                    $keya = $sitejsonData->run_status->add ?? null;
                                    $addValuerunstatus = 0.0;

                                    foreach ($eventData as $event) {
                                        $eventArraya = $event->getArrayCopy();
                                        if (
                                            isset($eventArraya['module_id']) &&
                                            $eventArraya['module_id'] == ($sitejsonData->run_status->md ?? null)
                                        ) {
                                            if ($keya && array_key_exists($keya, $eventArraya)) {
                                                $value = $eventArraya[$keya];
                                                if (is_numeric($value)) {
                                                    $addValuerunstatus = (float) $value;
                                                }
                                            }
                                            break;
                                        }
                                    }
                                ?>

                                <td colspan="7">

                                    <!-- sanjay -->
                                    <table
                                        style="width:100%; text-align:center; border-collapse:separate; border-spacing:10px;">
                                        <tr>
                                            <!-- Supply / RPM -->
                                            <td style="width:16%;">
                                                @php
                                                $param = $siteData['parameters']['rpm'] ?? null;
                                                $value = isset($param['md']) ? floatval($param['md']) : null;
                                                $low = isset($param['low']) ? floatval($param['low']) : null;
                                                $high = isset($param['high']) ? floatval($param['high']) : null;

                                                if (!is_null($value) && !is_null($low) && !is_null($high)) {
                                                $status = ($value >= $low && $value <= $high) ? 'normal' : 'abnormal' ;
                                                    $bgColor=$status==='normal' ? 'green' : 'red' ; } else {
                                                    $status='abnormal' ; $bgColor='red' ; } @endphp <div
                                                    class="status-box" style="padding:10px; font-size:14px;">
                                                    <p><strong>Grid_Balance</strong></p>
                                                    <span
                                                        class="status-box">{{ $rechargeSetting->m_recharge_amount }}</span>

                </div>
                </td>

                <!-- Avg. Voltage / battery_voltage -->
                <td style="width:16%;">
                    <?php
                        $key = $sitejsonData->parameters->oil_temperature->add;
                        $Grid_Unit = '_';
                        foreach ($eventData as $event) {
                            $eventArray = $event->getArrayCopy();
                            if ($eventArray['module_id'] == $sitejsonData->parameters->oil_temperature->md) {
                                if (array_key_exists($key, $eventArray)) {
                                    $Grid_Unit = number_format($eventArray[$key], 2);
                                }
                                break;
                            }
                        }
                    ?>
                    <div class="status-box" style="padding:10px; font-size:14px;">
                        <p><strong>Grid_Unit</strong></p>
                        <span class="status-box">{{ $Grid_Unit }}</span>
                    </div>
                </td>

                <!-- Current L1 / oil_pressure -->
                <td style="width:16%;">
                    <?php
                    $key = $sitejsonData->parameters->dg_unit->add;
                    $Dg_Unit = '_';
                    foreach ($eventData as $event) {
                        $eventArray = $event->getArrayCopy();
                        if ($eventArray['module_id'] == $sitejsonData->parameters->dg_unit->md) {
                            if (array_key_exists($key, $eventArray)) {
                                $Dg_Unit = number_format($eventArray[$key], 2);
                            }
                            break;
                        }
                    }
                ?>
                    <div class="status-box" style="padding:10px; font-size:14px;">
                        <p><strong>DG_Unit</strong></p>
                        <span class="status-box">{{ $Dg_Unit }}</span>
                    </div>
                </td>

                <!-- Current L2 / oil_temperature -->
                <td style="width:16%;">
                    <?php
                        $key = $sitejsonData->readOn->add;
                        $Connection_status = '_';
                        foreach ($eventData as $event) {
                            $eventArray = $event->getArrayCopy();
                            if ($eventArray['module_id'] == $sitejsonData->readOn->md) {
                                if (array_key_exists($key, $eventArray)) {
                                    $Connection_status = $eventArray[$key];
                                }
                                break;
                            }
                        }

        // APPLY CONDITION FOR COLOR & TEXT
        $statusText = "Unknown";
        $statusColor = "gray";

        if (strtolower($Connection_status) === "high") {
            $statusText = "Connected";
            $statusColor = "green";
        } elseif (strtolower($Connection_status) === "low") {
            $statusText = "Disconnected";
            $statusColor = "red";
        }
    ?>

                    <div class="status-box" style="padding:10px; font-size:14px;">
                        <p><strong>Connection_Status</strong></p>

                        <span class="status-box" style="padding:6px 10px; border-radius:5px; 
                     background:<?= $statusColor ?>; 
                     color:white; font-weight:bold;">
                            <?= $statusText ?>
                        </span>
                    </div>
                </td>


                <!-- Current L3 / number_of_starts -->
                <td style="width:16%;">
                    <?php
            $key = $sitejsonData->parameters->number_of_starts->add;
            $addValue = '_';
                foreach ($eventData as $event) {
                    $eventArray = $event->getArrayCopy();
                    if ($eventArray['module_id'] == $sitejsonData->parameters->number_of_starts->md) {
                        if (array_key_exists($key, $eventArray)) {
                    $addValue = number_format($eventArray[$key], 2);
                            }
                            break;
                            }
                        }
                ?>
                    <div class="status-box" style="padding:10px; font-size:14px;">
                        <p><strong>Supply_Status</strong></p>
                        <!-- <span class="status-box">waiting ...</span> -->
                        <span class="status-box">{{ $addValue }}</span>
                    </div>
                </td>

                <!-- Updated At -->
                <td style="width:20%;">
                    <div class="status-box" style="padding:10px; font-size:14px;">
                        <i class="fas fa-clock text-info" style="font-size:18px;"></i>
                        <p><strong>Updated At:</strong></p>
                        <h6 class="text-muted">{{ $latestCreatedAt }}</h6>
                    </div>
                </td>
                </tr>
                </table>
                </td>
                </tr>
                </tbody>
                </table>
            </div>
        </div>

        <!-- Second Table for Electrical Parameters -->
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header text-center text-white fw-bold fs-5 p-3" style="background:#002E6E;">
                    Electrical Parameters
                </div>
                <div class=" card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover m-0">
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="parameter-box">
                                            <span class="parameter-label">Avg Voltage</span>
                                            <?php
                                                    $key = $sitejsonData->parameters->coolant_temperature->add;
                                                    $addValue = '_';
                                                    foreach ($eventData as $event) {
                                                        $eventArray = $event->getArrayCopy();
                                                        if ($eventArray['module_id'] == $sitejsonData->parameters->coolant_temperature->md) {
                                                            if (array_key_exists($key, $eventArray)) {
                                                                $addValue = number_format($eventArray[$key], 2);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                ?>
                                            <span class="parameter-value">{{ $addValue }} V </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="parameter-box">
                                            <span class="parameter-label">Avg kVA</span>
                                            <?php
                                                    $key = $sitejsonData->active_power_kva->add;
                                                    $addValue = '_';
                                                    foreach ($eventData as $event) {
                                                        $eventArray = $event->getArrayCopy();
                                                        if ($eventArray['module_id'] == $sitejsonData->active_power_kva->md) {
                                                            if (array_key_exists($key, $eventArray)) {
                                                                $addValue = number_format($eventArray[$key], 2);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                ?>
                                            <span class="parameter-value">{{ $addValue }} </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="parameter-box">
                                            <span class="parameter-label">Avg Current</span>
                                            <?php
                                                    $key = $sitejsonData->parameters->oil_temperature->add;
                                                    $addValue = '_';
                                                    foreach ($eventData as $event) {
                                                        $eventArray = $event->getArrayCopy();
                                                        if ($eventArray['module_id'] == $sitejsonData->parameters->oil_temperature->md) {
                                                            if (array_key_exists($key, $eventArray)) {
                                                                $addValue = number_format($eventArray[$key], 2);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                ?>
                                            <span class="parameter-value">{{ $addValue }}A </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="parameter-box">
                                            <span class="parameter-label">Frequency</span>
                                            <?php
                                                    $key = $sitejsonData->electric_parameters->frequency->add;
                                                    $addValue = '_';
                                                    foreach ($eventData as $event) {
                                                        $eventArray = $event->getArrayCopy();
                                                        if ($eventArray['module_id'] == $sitejsonData->electric_parameters->frequency->md) {
                                                            if (array_key_exists($key, $eventArray)) {
                                                                $addValue = number_format($eventArray[$key], 2);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                ?>
                                            <span class="parameter-value">{{ $addValue }}HZ</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="parameter-box">
                                            <span class="parameter-label">Avg kVAR</span>
                                            <span class="parameter-value">-</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="parameter-box">

                                            <span class="parameter-label">KWH</span>
                                            <?php
                                                    $key = $sitejsonData->total_kwh->add;
                                                    $addValue = '_';
                                                    foreach ($eventData as $event) {
                                                        $eventArray = $event->getArrayCopy();
                                                        if ($eventArray['module_id'] == $sitejsonData->total_kwh->md) {
                                                            if (array_key_exists($key, $eventArray)) {
                                                                $addValue = number_format((float)$eventArray[$key], 2);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                ?>
                                            <span class="parameter-value">{{ $addValue }} </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <div class="parameter-box">
                                            <span class="parameter-label">Voltage (L-L)</span>
                                            <div class="phase-values">
                                                <?php
                                                        $keys = [
                                                            'a' => $sitejsonData->electric_parameters->voltage_l_l->a->add,
                                                            'b' => $sitejsonData->electric_parameters->voltage_l_l->b->add,
                                                            'c' => $sitejsonData->electric_parameters->voltage_l_l->c->add
                                                        ];
                                                        $values = ['R' => '_', 'Y' => '_', 'B' => '_'];
                                                        
                                                        foreach ($eventData as $event) {
                                                            $eventArray = $event->getArrayCopy();
                                                            if ($eventArray['module_id'] == $sitejsonData->electric_parameters->voltage_l_l->a->md) {
                                                                foreach (['a' => 'R', 'b' => 'Y', 'c' => 'B'] as $phase => $label) {
                                                                    if (array_key_exists($keys[$phase], $eventArray)) {
                                                                        $values[$label] = number_format((float)$eventArray[$keys[$phase]], 2);
                                                                    }
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                <div class="phase-value">
                                                    <span class="phase-label">R-Y</span>
                                                    <span class="phase-number">{{ $values['R'] }} V</span>
                                                </div>
                                                <div class="phase-value">
                                                    <span class="phase-label">Y-B</span>
                                                    <span class="phase-number">{{ $values['Y'] }} V</span>
                                                </div>
                                                <div class="phase-value">
                                                    <span class="phase-label">B-R</span>
                                                    <span class="phase-number">{{ $values['B'] }} V</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td colspan="3">
                                        <div class="parameter-box">
                                            <i class="fas fa-bolt parameter-icon text-success"></i>
                                            <span class="parameter-label">Current</span>
                                            <div class="phase-values">
                                                <?php
                                                        $keys = [
                                                            'a' => $sitejsonData->electric_parameters->current->a->add,
                                                            'b' => $sitejsonData->electric_parameters->current->b->add,
                                                            'c' => $sitejsonData->electric_parameters->current->c->add
                                                        ];
                                                        $values = ['R' => '_', 'Y' => '_', 'B' => '_'];
                                                        
                                                        foreach ($eventData as $event) {
                                                            $eventArray = $event->getArrayCopy();
                                                            if ($eventArray['module_id'] == $sitejsonData->electric_parameters->current->a->md) {
                                                                foreach (['a' => 'R', 'b' => 'Y', 'c' => 'B'] as $phase => $label) {
                                                                    if (array_key_exists($keys[$phase], $eventArray)) {
                                                                        $values[$label] = number_format((float)$eventArray[$keys[$phase]], 2);
                                                                    }
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                <div class="phase-value">
                                                    <span class="phase-label">Phase R</span>
                                                    <span class="phase-number">{{ $values['R'] }} A</span>
                                                </div>
                                                <div class="phase-value">
                                                    <span class="phase-label">Phase Y</span>
                                                    <span class="phase-number">{{ $values['Y'] }} A</span>
                                                </div>
                                                <div class="phase-value">
                                                    <span class="phase-label">Phase B</span>
                                                    <span class="phase-number">{{ $values['B'] }} A</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- *************************************************************start line ghraph ******************************************************************    -->
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold" style="color: var(--primary-color);">
                    <i class="fas fa-bolt me-2"></i>Energy Monitoring System
                </h2>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn download-btn" data-bs-toggle="modal" data-bs-target="#downloadModal">
                    <i class="fas fa-download me-1"></i> Download Report
                </button>
            </div>
        </div>

        <!-- Consumption Dashboard -->
        <div class="consumption-section">
            <h4 class="mb-4">
                <i class="fas fa-chart-line me-2"></i>Consumption Dashboard
            </h4>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="consumptionTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#daily">Daily</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#monthly">Monthly</button>
                </li>
            </ul>

            <div class="tab-content mt-4">
                <!-- Daily Tab -->
                <div class="tab-pane fade show active" id="daily">
                    <div class="row">
                        <!-- Unit Consumption -->
                        <div class="col-md-6">
                            <div class="graph-container mb-4">
                                <div class="card-header-custom">
                                    <h5 class="chart-title">
                                        Unit Consumption
                                    </h5>
                                    <span class="consumption-badge" id="dailyUnitMonth">Aug 2025</span>
                                </div>

                                <!-- Unit Filter Section -->
                                <div class="filter-section">
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Select Month</label>
                                            <select class="form-select" id="unitMonthSelect">
                                                <option value="0" selected>January</option>
                                                <option value="1">February</option>
                                                <option value="2">March</option>
                                                <option value="3">April</option>
                                                <option value="4">May</option>
                                                <option value="5">June</option>
                                                <option value="6">July</option>
                                                <option value="7" >August</option>
                                                <option value="8">September</option>
                                                <option value="9">October</option>
                                                <option value="10">November</option>
                                                <option value="11">December</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Select Year</label>
                                            <select class="form-select" id="unitYearSelect">
                                                <option>2023</option>
                                                <option>2024</option>
                                                <option selected>2025</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn filter-btn w-100" id="applyUnitFilter">
                                                <i class="fas fa-filter me-1"></i> Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="metric-highlight">
                                    <div>
                                        <div class="metric-title">Avg kWh</div>
                                        <div class="metric-value-large" id="dailyUnitCurrent"></div>
                                        <div class="metric-change down">
                                            <i class="fas fa-arrow-down me-1"></i> <span
                                                id="unitChangePercent"></span> from last month
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="metric-title">Max kWh</div>
                                        <div class="metric-value-large" id="dailyUnitMax"></div>
                                        <div class="metric-change up">
                                            <i class="fas fa-arrow-up me-1"></i> <span
                                                id="unitMaxChangePercent"></span> from last month
                                        </div>
                                    </div>
                                </div>

                                <div class="loading-spinner" id="unitChartLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Loading unit consumption data...</p>
                                </div>
                                <canvas id="dailyUnitChart"></canvas>
                            </div>

                            <div class="kwh-display-container">
                                <div class="kwh-box">
                                    <div class="kwh-value" id=""></div>
                                    
                                </div>
                                <div class="kwh-box">
                                    <div class="kwh-value" id=""></div>
                                   
                                </div>
                            </div>
                        </div>

                        <!-- Amount Consumption -->
                        <div class="col-md-6">
                            <div class="graph-container mb-4">
                                <div class="card-header-custom">
                                    <h5 class="chart-title">
                                        Amount Consumption
                                    </h5>
                                    <span class="consumption-badge" id="dailyAmountMonth">Aug 2025</span>
                                </div>

                                <!-- Amount Filter Section -->
                                <div class="filter-section">
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Select Month</label>
                                            <select class="form-select" id="amountMonthSelect">
                                                <option value="0" selected>January</option>
                                                <option value="1">February</option>
                                                <option value="2">March</option>
                                                <option value="3">April</option>
                                                <option value="4">May</option>
                                                <option value="5">June</option>
                                                <option value="6">July</option>
                                                <option value="7" >August</option>
                                                <option value="8">September</option>
                                                <option value="9">October</option>
                                                <option value="10" >November</option>
                                                <option value="11">December</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Select Year</label>
                                            <select class="form-select" id="amountYearSelect">
                                                <option>2023</option>
                                                <option>2024</option>
                                                <option selected>2025</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn filter-btn w-100" id="applyAmountFilter">
                                                <i class="fas fa-filter me-1"></i> Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="metric-highlight">
                                    <div>
                                        <div class="metric-title">Avg Rs.</div>
                                        <div class="metric-value-large" id="dailyAmountCurrent"></div>
                                        <div class="metric-change down">
                                            <i class="fas fa-arrow-down me-1"></i> <span
                                                id="amountChangePercent"></span> from last month
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="metric-title">Max  Rs.</div>
                                        <div class="metric-value-large" id="dailyAmountMax"></div>
                                        <div class="metric-change up">
                                            <i class="fas fa-arrow-up me-1"></i> <span
                                                id="amountMaxChangePercent"></span> from last month
                                        </div>
                                    </div>
                                </div>

                                <div class="loading-spinner" id="amountChartLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Loading amount consumption data...</p>
                                </div>
                                <canvas id="dailyAmountChart"></canvas>
                            </div>

                            <div class="kwh-display-container">
                                <div class="kwh-box">
                                    <div class="kwh-value" id=""></div>
                                    <!-- <div class="kwh-label">Average Rs.</div> -->
                                </div>
                                <div class="kwh-box">
                                    <div class="kwh-value" id=""></div>
                                    <!-- <div class="kwh-label">Maximum Rs.</div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Tab -->
                <div class="tab-pane fade" id="monthly">
                    <div class="row">
                        <!-- Monthly Unit Chart -->
                        <div class="col-md-6">
                            <div class="graph-container mb-4">
                                <div class="card-header-custom">
                                    <h5 class="chart-title">
                                        Unit Consumption
                                    </h5>
                                    <span class="consumption-badge" id="monthlyUnitYear">2025</span>
                                </div>

                                <!-- Monthly Unit Filter Section -->
                                <div class="filter-section">
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Select Year</label>
                                            <select class="form-select" id="monthlyUnitYearSelect">
                                                <option>2023</option>
                                                <option>2024</option>
                                                <option selected>2025</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Select Range</label>
                                            <select class="form-select" id="monthlyUnitRangeSelect">
                                                <option value="all">All Months</option>
                                                <option value="q1">Jan - Mar (Q1)</option>
                                                <option value="q2">Apr - Jun (Q2)</option>
                                                <option value="q3">Jul - Sep (Q3)</option>
                                                <option value="q4">Oct - Dec (Q4)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn filter-btn w-100" id="applyMonthlyUnitFilter">
                                                <i class="fas fa-filter me-1"></i> Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="metric-highlight">
                                    <div>
                                        <div class="metric-title">Avg kWh</div>
                                        <div class="metric-value-large" id="monthlyUnitCurrent"></div>
                                        <div class="metric-change down">
                                            <i class="fas fa-arrow-down me-1"></i> <span
                                                id="monthlyUnitChangePercent">4.08%</span> from last month
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="metric-title">Max kWh</div>
                                        <div class="metric-value-large" id="monthlyUnitMax"></div>
                                        <div class="metric-change up">
                                            <i class="fas fa-arrow-up me-1"></i> <span
                                                id="monthlyUnitMaxChangePercent"></span> from last month
                                        </div>
                                    </div>
                                </div>

                                <div class="loading-spinner" id="monthlyUnitLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Loading monthly unit data...</p>
                                </div>
                                <canvas id="monthlyUnitChart"></canvas>
                            </div>

                            <!-- Average and Max kWh Display -->
                            <div class="kwh-display-container">
                                <div class="kwh-box">
                                    <div class="kwh-value" id=""></div>
                                   
                                </div>
                                <div class="kwh-box">
                                    <div class="kwh-value" id=""></div>
                                    
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Amount Chart -->
                        <div class="col-md-6">
                            <div class="graph-container">
                                <div class="card-header-custom">
                                    <h5 class="chart-title">
                                        Amount Consumption
                                    </h5>
                                    <span class="consumption-badge" id="monthlyAmountYear">2025</span>
                                </div>

                                <!-- Monthly Amount Filter Section -->
                                <div class="filter-section">
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Select Year</label>
                                            <select class="form-select" id="monthlyAmountYearSelect">
                                                <option>2023</option>
                                                <option>2024</option>
                                                <option selected>2025</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Select Range</label>
                                            <select class="form-select" id="monthlyAmountRangeSelect">
                                                <option value="all">All Months</option>
                                                <option value="q1">Jan - Mar (Q1)</option>
                                                <option value="q2">Apr - Jun (Q2)</option>
                                                <option value="q3">Jul - Sep (Q3)</option>
                                                <option value="q4">Oct - Dec (Q4)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn filter-btn w-100" id="applyMonthlyAmountFilter">
                                                <i class="fas fa-filter me-1"></i> Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="metric-highlight">
                                    <div>
                                        <div class="metric-title">Avg Rs.</div>
                                        <div class="metric-value-large" id="monthlyAmountCurrent"></div>
                                        <div class="metric-change down">
                                            <i class="fas fa-arrow-down me-1"></i> <span
                                                id="monthlyAmountChangePercent"></span> from last month
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="metric-title">Max  Rs.</div>
                                        <div class="metric-value-large" id="monthlyAmountMax"></div>
                                        <div class="metric-change up">
                                            <i class="fas fa-arrow-up me-1"></i> <span
                                                id="monthlyAmountMaxChangePercent"></span> from last month
                                        </div>
                                    </div>
                                </div>

                                <div class="loading-spinner" id="monthlyAmountLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Loading monthly amount data...</p>
                                </div>
                                <canvas id="monthlyAmountChart"></canvas>
                            </div>

                            <!-- Average and Max  Rs. Display -->
                            <div class="kwh-display-container">
                                <div class="kwh-box">
                                    <div class="kwh-value" id=""></div>
                                    <!-- <div class="kwh-label">Average Rs.</div> -->
                                </div>
                                <div class="kwh-box">
                                    <div class="kwh-value" id=""></div>
                                    <!-- <div class="kwh-label">Maximum Rs.</div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Modal -->
    <div class="modal fade" id="downloadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-download me-2"></i>Download Consumption Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="download-option active" data-type="daily">
                                <div class="download-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <h6 class="fw-bold">Daily Report</h6>
                                <p class="mb-2 text-muted">Download detailed daily consumption data</p>
                                <small class="text-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Includes date-wise units and amount
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="download-option" data-type="monthly">
                                <div class="download-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h6 class="fw-bold">Monthly Report</h6>
                                <p class="mb-2 text-muted">Download monthly summary report</p>
                                <small class="text-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Includes monthly totals and averages
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Select Format</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="downloadFormat" id="formatExcel"
                                        value="excel" checked>
                                    <label class="form-check-label fw-bold" for="formatExcel">
                                        <i class="fas fa-file-excel text-success me-2"></i>Excel Format
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="downloadFormat" id="formatCSV"
                                        value="csv">
                                    <label class="form-check-label fw-bold" for="formatCSV">
                                        <i class="fas fa-file-csv text-primary me-2"></i>CSV Format
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="downloadFormat" id="formatPDF"
                                        value="pdf">
                                    <label class="form-check-label fw-bold" for="formatPDF">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>PDF Format
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Tip:</strong> The report will include current filter settings and download timestamp.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn download-btn" id="confirmDownload">
                        <i class="fas fa-download me-1"></i> Generate & Download
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- *****************************************************End********************************************** -->
    </div>

    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function fetchSiteData() {
        const slug = "{{ $siteData->slug }}";
        const url = `/admin/site-data/${slug}`;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.eventData) {
                    let eventList = '';

                    if (response.eventData) {
                        console.log(response.eventData);
                        const event = response.eventData;
                        eventList = `
                                <div class="container-fluid">
                                    <div class="row mt-3" id="event-data">
                                        <!-- First Table for Asset Information -->
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header text-center text-white fw-bold fs-4 p-1" style="background: #002E6E;">
                                                    ASSET INFORMATION
                                                </div>
                                                <table class="table table-bordered table-striped table-hover">
                                                    <tbody>
                                                        <tr>
                                <th class="asset" style="background: #002E6E;">
                                    <strong>Custom Name</strong>: {{ $sitejsonData->asset_name }}
                                </th>
                                <td data-label="site_name">
                                    <strong>Site_Name:</strong> {{ $sitejsonData->site_name }}
                                </td>
                                <td data-label="Group">
                                    <strong>Location:</strong> {{ $sitejsonData->group }}
                                </td>
                                <td data-label="Generator">
                                    <strong>Meter Name:</strong> {{ $sitejsonData->generator }}
                                </td>
                                <td data-label="S/N">
                                    <strong>Meter Number</strong> {{ $sitejsonData->serial_number }}
                                </td>
                                <td data-label="Model">
                                    <strong>Controller:</strong> {{ $sitejsonData->asset_name }}
                                </td>
                                <td data-label="Brand">
                                    <strong>Grid:</strong> {{ $rechargeSetting->m_sanction_load}}kW
                                </td>
                                <td data-label="Capacity">
                                    <strong>DG:</strong> {{ $rechargeSetting->dg_sanction_load}}kW
                                </td>
                            </tr>
                                                        <tr>
                                                        <td colspan="7">
                                                                    </div>
                                                                    
      <?php
    $keyaa = $sitejsonData->mode_md->add ?? null;
    $addValueModestatus = null;

    foreach ($eventData as $event) {
        $eventArraya = $event->getArrayCopy();
        if (
            isset($eventArraya['module_id']) &&
            $eventArraya['module_id'] == ($sitejsonData->mode_md->md ?? null)
        ) {
            if ($keyaa && array_key_exists($keyaa, $eventArraya)) {
                $value = $eventArraya[$keyaa];
                if (is_numeric($value)) {
                    $addValueModestatus = (float) $value;
                }
            }
            break;
        }
    }
?>
 

<table style="width:100%; text-align:center; border-collapse:separate; border-spacing:10px;">
    <tr>
        <!-- Supply / RPM -->
        <td style="width:16%;">
            @php
                $param = $siteData['parameters']['rpm'] ?? null;
                $value = isset($param['md']) ? floatval($param['md']) : null;
                $low = isset($param['low']) ? floatval($param['low']) : null;
                $high = isset($param['high']) ? floatval($param['high']) : null;

                if (!is_null($value) && !is_null($low) && !is_null($high)) {
                    $status = ($value >= $low && $value <= $high) ? 'normal' : 'abnormal';
                    $bgColor = $status === 'normal' ? 'green' : 'red';
                } else {
                    $status = 'abnormal';
                    $bgColor = 'red';
                }
            @endphp
            <div class="status-box" style="padding:10px; font-size:14px;">
                <p><strong>Grid_Balance</strong></p>
                <span class="status-box">{{ $rechargeSetting->m_recharge_amount }}</span>
            </div>
        </td>

        <!-- Avg. Voltage / battery_voltage -->
        <td style="width:16%;">
            <?php
                $key = $sitejsonData->parameters->oil_temperature->add;
                $Grid_Unit = '_';
                foreach ($eventData as $event) {
                    $eventArray = $event->getArrayCopy();
                    if ($eventArray['module_id'] == $sitejsonData->parameters->oil_temperature->md) {
                        if (array_key_exists($key, $eventArray)) {
                            $Grid_Unit = number_format($eventArray[$key], 2);
                        }
                        break;
                    }
                }
            ?>
            <div class="status-box" style="padding:10px; font-size:14px;">
                <p><strong>Grid_Unit</strong></p>
                <span class="status-box">{{ $Grid_Unit }}</span>
            </div>
        </td>

        <!-- Current L1 / oil_pressure -->
        <td style="width:16%;">
            <?php
                $key = $sitejsonData->parameters->dg_unit->add;
                $Dg_Unit = '_';
                foreach ($eventData as $event) {
                    $eventArray = $event->getArrayCopy();
                    if ($eventArray['module_id'] == $sitejsonData->parameters->dg_unit->md) {
                        if (array_key_exists($key, $eventArray)) {
                            $Dg_Unit = number_format($eventArray[$key], 2);
                        }
                        break;
                    }
                }
            ?>
            <div class="status-box" style="padding:10px; font-size:14px;">
                <p><strong>DG_Unit</strong></p>
                <span class="status-box">{{ $Dg_Unit }}</span>
            </div>
        </td>

        <!-- Current L2 / oil_temperature -->
        <td style="width:16%;">
                    <?php
                        $key = $sitejsonData->readOn->add;
                        $Connection_status = '_';
                        foreach ($eventData as $event) {
                            $eventArray = $event->getArrayCopy();
                            if ($eventArray['module_id'] == $sitejsonData->readOn->md) {
                                if (array_key_exists($key, $eventArray)) {
                                    $Connection_status = $eventArray[$key];
                                }
                                break;
                            }
                        }

        // APPLY CONDITION FOR COLOR & TEXT
        $statusText = "Unknown";
        $statusColor = "gray";

        if (strtolower($Connection_status) === "high") {
            $statusText = "Connected";
            $statusColor = "green";
        } elseif (strtolower($Connection_status) === "low") {
            $statusText = "Disconnected";
            $statusColor = "red";
        }
    ?>

    <div class="status-box" style="padding:10px; font-size:14px;">
        <p><strong>Connection_Status</strong></p>

        <span class="status-box"
              style="padding:6px 10px; border-radius:5px; 
                     background:<?= $statusColor ?>; 
                     color:white; font-weight:bold;">
            <?= $statusText ?>
        </span>
    </div>
</td>

        <!-- Current L3 / number_of_starts -->
            <td style="width:16%;">
                <?php
                    $key = $sitejsonData->parameters->number_of_starts->add;
                    $addValue = '_';
                    foreach ($eventData as $event) {
                        $eventArray = $event->getArrayCopy();
                        if ($eventArray['module_id'] == $sitejsonData->parameters->number_of_starts->md) {
                            if (array_key_exists($key, $eventArray)) {
                        $addValue = number_format($eventArray[$key], 2);
                                }
                            break;
                            }
                        }
                    ?>
                <div class="status-box" style="padding:10px; font-size:14px;">
                    <p><strong>Supply_Status</strong></p>
                    <!-- <span class="status-box">waiting ...</span> -->
                    <span class="status-box">{{ $addValue }}</span>
                </div>
            </td>

        <!-- Updated At -->
        <td style="width:20%;">
            <div class="status-box" style="padding:10px; font-size:14px;">
                <i class="fas fa-clock text-info" style="font-size:18px;"></i>
                <p><strong>Updated At:</strong></p>
                <h6 class="text-muted">{{ $latestCreatedAt }}</h6>
            </div>
        </td>
    </tr>
</table>





                                                            </td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Second Table for Electrical Parameters -->
                                        <div class="col-md-12 mt-4">
                                            <div class="card">
                                                <div class="card-header text-center text-white fw-bold fs-5 p-3" style="background:#002E6E;">
                                                    Electrical Parameters
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped table-hover m-0">
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <div class="parameter-box">
                                                    <span class="parameter-label">Avg Voltage</span>
                                                    <?php
                                                        $key = $sitejsonData->parameters->coolant_temperature->add;
                                                        $addValue = '_';
                                                        foreach ($eventData as $event) {
                                                            $eventArray = $event->getArrayCopy();
                                                            if ($eventArray['module_id'] == $sitejsonData->parameters->coolant_temperature->md) {
                                                                if (array_key_exists($key, $eventArray)) {
                                                                    $addValue = number_format($eventArray[$key], 2);
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                    <span class="parameter-value">{{ $addValue }} V</span>
                                                </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="parameter-box">
                                                    <span class="parameter-label">Avg kVA</span>
                                                    <?php
                                                    $key = $sitejsonData->active_power_kva->add;
                                                    $addValue = '_';
                                                    foreach ($eventData as $event) {
                                                        $eventArray = $event->getArrayCopy();
                                                        if ($eventArray['module_id'] == $sitejsonData->active_power_kva->md) {
                                                            if (array_key_exists($key, $eventArray)) {
                                                                $addValue = number_format($eventArray[$key], 2);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                    <span class="parameter-value">{{ $addValue }}</span>
                                                </div>
                                                                    </td>
                                                                    <td>
                                                                    <div class="parameter-box">
                                                    <span class="parameter-label">Avg Current</span>
                                                    <?php
                                                        $key = $sitejsonData->parameters->oil_temperature->add;
                                                        $addValue = '_';
                                                        foreach ($eventData as $event) {
                                                            $eventArray = $event->getArrayCopy();
                                                            if ($eventArray['module_id'] == $sitejsonData->parameters->oil_temperature->md) {
                                                                if (array_key_exists($key, $eventArray)) {
                                                                    $addValue = number_format($eventArray[$key], 2);
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                    <span class="parameter-value">{{ $addValue }} A</span>
                                                </div>
                                                                    </td>
                                                                    <td>
                                                                    <div class="parameter-box">
                                                    <span class="parameter-label">Frequency</span>
                                                    <?php
                                                    $key = $sitejsonData->electric_parameters->frequency->add;
                                                    $addValue = '_';
                                                    foreach ($eventData as $event) {
                                                        $eventArray = $event->getArrayCopy();
                                                        if ($eventArray['module_id'] == $sitejsonData->electric_parameters->frequency->md) {
                                                            if (array_key_exists($key, $eventArray)) {
                                                                $addValue = number_format($eventArray[$key], 2);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                    <span class="parameter-value">{{ $addValue }} HZ </span>
                                                </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="parameter-box">
                                                                            <span class="parameter-label">Avg kVAR</span>
                                                                            <span class="parameter-value">-</span>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                    <div class="parameter-box">
                                                    <span class="parameter-label">KWH</span>
                                                     <?php
                                                    $key = $sitejsonData->total_kwh->add;
                                                    $addValue = '_';
                                                    foreach ($eventData as $event) {
                                                        $eventArray = $event->getArrayCopy();
                                                        if ($eventArray['module_id'] == $sitejsonData->total_kwh->md) {
                                                            if (array_key_exists($key, $eventArray)) {
                                                                $addValue = number_format((float)$eventArray[$key], 2);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                    <span class="parameter-value">{{ $addValue }} </span>
                                                </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                <td colspan="3">
                                                <div class="parameter-box">
                                                    <span class="parameter-label">Voltage (L-L)</span>
                                                    <div class="phase-values">
                                                        <?php
                                                            $keys = [
                                                                'a' => $sitejsonData->electric_parameters->voltage_l_l->a->add,
                                                                'b' => $sitejsonData->electric_parameters->voltage_l_l->b->add,
                                                                'c' => $sitejsonData->electric_parameters->voltage_l_l->c->add
                                                            ];
                                                            $values = ['R' => '_', 'Y' => '_', 'B' => '_'];
                                                            
                                                            foreach ($eventData as $event) {
                                                                $eventArray = $event->getArrayCopy();
                                                                if ($eventArray['module_id'] == $sitejsonData->electric_parameters->voltage_l_l->a->md) {
                                                                    foreach (['a' => 'R', 'b' => 'Y', 'c' => 'B'] as $phase => $label) {
                                                                        if (array_key_exists($keys[$phase], $eventArray)) {
                                                                            $values[$label] = number_format((float)$eventArray[$keys[$phase]], 2);
                                                                        }
                                                                    }
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                        <div class="phase-value">
                                                            <span class="phase-label">R-Y</span>
                                                            <span class="phase-number">{{ $values['R'] }} V</span>
                                                        </div>
                                                        <div class="phase-value">
                                                            <span class="phase-label">Y-B</span>
                                                            <span class="phase-number">{{ $values['Y'] }} V</span>
                                                        </div>
                                                        <div class="phase-value">
                                                            <span class="phase-label">B-R</span>
                                                            <span class="phase-number">{{ $values['B'] }} V</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                                                    <td colspan="3">
                                                                        <div class="parameter-box">
                                                    <i class="fas fa-bolt parameter-icon text-success"></i>
                                                    <span class="parameter-label">Current</span>
                                                    <div class="phase-values">
                                                        <?php
                                                            $keys = [
                                                                'a' => $sitejsonData->electric_parameters->current->a->add,
                                                                'b' => $sitejsonData->electric_parameters->current->b->add,
                                                                'c' => $sitejsonData->electric_parameters->current->c->add
                                                            ];
                                                            $values = ['R' => '_', 'Y' => '_', 'B' => '_'];
                                                            
                                                            foreach ($eventData as $event) {
                                                                $eventArray = $event->getArrayCopy();
                                                                if ($eventArray['module_id'] == $sitejsonData->electric_parameters->current->a->md) {
                                                                    foreach (['a' => 'R', 'b' => 'Y', 'c' => 'B'] as $phase => $label) {
                                                                        if (array_key_exists($keys[$phase], $eventArray)) {
                                                                            $values[$label] = number_format((float)$eventArray[$keys[$phase]], 2);
                                                                        }
                                                                    }
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                        <div class="phase-value">
                                                            <span class="phase-label">Phase R</span>
                                                            <span class="phase-number">{{ $values['R'] }} A</span>
                                                        </div>
                                                        <div class="phase-value">
                                                            <span class="phase-label">Phase Y</span>
                                                            <span class="phase-number">{{ $values['Y'] }} A</span>
                                                        </div>
                                                        <div class="phase-value">
                                                            <span class="phase-label">Phase B</span>
                                                            <span class="phase-number">{{ $values['B'] }} A</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                    }

                    $('#event-data').html(eventList);
                }
            },
            error: function(xhr) {
                console.error('Error fetching site data:', xhr.responseText);
            }
        });
    }

    // Fetch data immediately when the page loads
    fetchSiteData();
    // setInterval(fetchSiteData, 10000);
    setInterval(fetchSiteData, 10000000);
    </script>



    <script>
    $(document).on('click', '.start-btn, .stop-btn, .auto-btn, .manual-btn, .reading-on-btn, .reading-off-btn',
        function(e) {
            e.preventDefault();

            let form = $(this).closest('form');
            let actionType = '';

            if ($(this).hasClass('start-btn')) {
                actionType = 'start';
            } else if ($(this).hasClass('stop-btn')) {
                actionType = 'stop';
            } else if ($(this).hasClass('auto-btn')) {
                actionType = 'auto';
            } else if ($(this).hasClass('manual-btn')) {
                actionType = 'manual';
            } else if ($(this).hasClass('reading-on-btn')) {
                actionType = 'reading_on';
            } else if ($(this).hasClass('reading-off-btn')) {
                actionType = 'reading_off';
            }

            let argValue = form.find('input[name="argValue"]').val();
            let moduleId = form.find('input[name="moduleId"]').val();
            let cmdField = form.find('input[name="cmdField"]').val();
            let cmdArg = form.find('input[name="cmdArg"]').val();

            console.log(moduleId);

            if (!argValue || !moduleId || !cmdField || !cmdArg) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Service Not Active',
                    text: 'Service is not active for this site, kindly contact the team!',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const ajaxCall = () => {
                $.ajax({
                    url: '/admin/start-process',
                    method: 'POST',
                    data: {
                        argValue,
                        moduleId,
                        cmdField,
                        cmdArg,
                        actionType,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: `${actionType.charAt(0).toUpperCase() + actionType.slice(1)}!`,
                            text: response.message
                        });

                        console.log('External Response:', response.external_response);

                        // ✅ Backend confirmed mode change — only update UI now
                        if (actionType === 'auto' || actionType === 'manual') {
                            if (response.mode_status === 0) {
                                $('#current-mode').text('AUTO');
                            } else if (response.mode_status === 1) {
                                $('#current-mode').text('MANUAL');
                            }
                        }

                        // Update reading status if applicable
                        if (actionType === 'reading_on' || actionType === 'reading_off') {
                            // Update reading status display based on your logic
                            // This is a placeholder - replace with your actual logic
                            if (actionType === 'reading_on') {
                                $('#reading-status').html(
                                    '<span class="status-increasing">Increasing</span>');
                            } else {
                                $('#reading-status').html(
                                    '<span class="status-normal">Normal</span>');
                            }
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again.'
                        });
                        console.error(xhr.responseText);
                    }
                });
            };

            if (actionType === 'start') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Are you sure you want to START this genset?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Start',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        ajaxCall();
                    }
                });
            } else {
                ajaxCall();
            }
        });
    </script>





<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- <script>
    // Global variables
    let dailyUnitChart, dailyAmountChart, monthlyUnitChart, monthlyAmountChart;
    let selectedDownloadType = 'daily';
    let reportData = [];
    
    // Get current balance from the displayed value
    function getCurrentBalance() {
        const balanceElement = document.querySelector('.status-box .status-box');
        if (balanceElement) {
            // Extract numeric value from the displayed text
            const balanceText = balanceElement.textContent.trim();
            const balanceValue = parseFloat(balanceText.replace(/[^\d.]/g, ''));
            return isNaN(balanceValue) ? {{ $rechargeSetting->m_recharge_amount }} : balanceValue;
        }
        return {{ $rechargeSetting->m_recharge_amount }};
    }
    
    let rechargeSettings = {
        m_recharge_amount: {{ $rechargeSetting->m_recharge_amount }}, // Initial balance
        m_sanction_load: {{ $rechargeSetting->m_sanction_load }},   // Recharge amount
        unit_rate: 10 // Rs. 10 per unit
    };

    // CSRF Token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Initializing Energy Dashboard...');
        console.log('💰 Current Balance:', getCurrentBalance());
        
        initializeCharts();
        setupEventListeners();
        fetchRealData();
    });

    function initializeCharts() {
        console.log('📈 Initializing charts...');

        // Daily Unit Chart
        const dailyUnitCtx = document.getElementById('dailyUnitChart').getContext('2d');
        dailyUnitChart = new Chart(dailyUnitCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Unit Consumption (kWh)',
                    data: [],
                    borderColor: '#002E6E',
                    backgroundColor: 'rgba(0, 46, 110, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#002E6E',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        // Daily Amount Chart
        const dailyAmountCtx = document.getElementById('dailyAmountChart').getContext('2d');
        dailyAmountChart = new Chart(dailyAmountCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Amount (Rs.)',
                    data: [],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        // Monthly Unit Chart
        const monthlyUnitCtx = document.getElementById('monthlyUnitChart').getContext('2d');
        monthlyUnitChart = new Chart(monthlyUnitCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Units (kWh)',
                    data: [],
                    backgroundColor: 'rgba(0, 46, 110, 0.7)',
                    borderColor: '#002E6E',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        // Monthly Amount Chart
        const monthlyAmountCtx = document.getElementById('monthlyAmountChart').getContext('2d');
        monthlyAmountChart = new Chart(monthlyAmountCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Amount (Rs.)',
                    data: [],
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: '#28a745',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        console.log('✅ Charts initialized');
    }

    function setupEventListeners() {
        console.log('🔗 Setting up event listeners...');

        // Daily filter buttons
        document.getElementById('applyUnitFilter').addEventListener('click', updateDailyUnitChart);
        document.getElementById('applyAmountFilter').addEventListener('click', updateDailyAmountChart);

        // Monthly filter buttons
        document.getElementById('applyMonthlyUnitFilter').addEventListener('click', updateMonthlyUnitChart);
        document.getElementById('applyMonthlyAmountFilter').addEventListener('click', updateMonthlyAmountChart);

        // Download modal options
        document.querySelectorAll('.download-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.download-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                this.classList.add('active');
                selectedDownloadType = this.dataset.type;
            });
        });

        // Download button
        document.getElementById('confirmDownload').addEventListener('click', downloadReport);

        console.log('✅ Event listeners setup completed');
    }

    // Real data fetch function
    async function fetchRealData() {
        console.log('📊 Fetching real data from server...');

        try {
            // Stats fetch karein
            const statsResponse = await fetch('/admin/energy-stats');
            const statsData = await statsResponse.json();

            if (statsData.success) {
                updateStatsDisplay(statsData);
            }

            // Automatically load ALL charts with current data
            await loadInitialCharts();

        } catch (error) {
            console.error('Error fetching data:', error);
            // Even if API fails, try to load initial charts
            await loadInitialCharts();
        }
    }

    // Load initial ALL charts with current data
    async function loadInitialCharts() {
        console.log('🔄 Loading initial ALL charts...');
        
        // Set dropdowns to current values
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();
        
        // Set daily filter dropdowns
        const unitMonthSelect = document.getElementById('unitMonthSelect');
        const unitYearSelect = document.getElementById('unitYearSelect');
        if (unitMonthSelect && unitYearSelect) {
            unitMonthSelect.value = currentMonth;
            unitYearSelect.value = currentYear;
        }
        
        const amountMonthSelect = document.getElementById('amountMonthSelect');
        const amountYearSelect = document.getElementById('amountYearSelect');
        if (amountMonthSelect && amountYearSelect) {
            amountMonthSelect.value = currentMonth;
            amountYearSelect.value = currentYear;
        }
        
        // Set monthly filter dropdowns
        const monthlyUnitYearSelect = document.getElementById('monthlyUnitYearSelect');
        const monthlyAmountYearSelect = document.getElementById('monthlyAmountYearSelect');
        if (monthlyUnitYearSelect && monthlyAmountYearSelect) {
            monthlyUnitYearSelect.value = currentYear;
            monthlyAmountYearSelect.value = currentYear;
        }
        
        // Load ALL charts with current data
        await updateDailyUnitChart();
        await updateDailyAmountChart();
        await updateMonthlyUnitChart();
        await updateMonthlyAmountChart();
        
        console.log('✅ All charts loaded successfully');
    }

    function updateStatsDisplay(statsData) {
        const current = statsData.current_month;
        const last = statsData.last_month;

        if (current) {
            // Daily unit stats
            document.getElementById('dailyUnitCurrent').textContent = (current.avg_units || 0).toFixed(2);
            document.getElementById('dailyUnitMax').textContent = (current.max_units || 0).toFixed(2);
            document.getElementById('dailyUnitAvg').textContent = (current.avg_units || 0).toFixed(2);
            document.getElementById('dailyUnitMaxKwh').textContent = (current.max_units || 0).toFixed(2);

            // Daily amount stats
            document.getElementById('dailyAmountCurrent').textContent = (current.avg_amount || 0).toFixed(2);
            document.getElementById('dailyAmountMax').textContent = (current.max_amount || 0).toFixed(2);
            document.getElementById('dailyAmountAvg').textContent = (current.avg_amount || 0).toFixed(2);
            document.getElementById('dailyAmountMaxRs').textContent = (current.max_amount || 0).toFixed(2);

            // Monthly unit stats
            document.getElementById('monthlyUnitCurrent').textContent = (current.total_units || 0).toFixed(2);
            document.getElementById('monthlyUnitMax').textContent = (current.max_monthly_units || 0).toFixed(2);
            document.getElementById('monthlyUnitAvg').textContent = (current.avg_monthly_units || 0).toFixed(2);
            document.getElementById('monthlyUnitMaxKwh').textContent = (current.max_monthly_units || 0).toFixed(2);

            // Monthly amount stats
            document.getElementById('monthlyAmountCurrent').textContent = (current.total_amount || 0).toFixed(2);
            document.getElementById('monthlyAmountMax').textContent = (current.max_monthly_amount || 0).toFixed(2);
            document.getElementById('monthlyAmountAvg').textContent = (current.avg_monthly_amount || 0).toFixed(2);
            document.getElementById('monthlyAmountMaxRs').textContent = (current.max_monthly_amount || 0).toFixed(2);

            // Percentage changes calculate karein
            if (last && last.avg_units > 0) {
                const unitChange = ((current.avg_units - last.avg_units) / last.avg_units * 100).toFixed(2);
                document.getElementById('unitChangePercent').textContent = `${Math.abs(unitChange)}%`;
                const unitChangeElement = document.querySelector('#unitChangePercent').closest('.metric-change');
                unitChangeElement.className = `metric-change ${unitChange >= 0 ? 'up' : 'down'}`;
                unitChangeElement.querySelector('i').className = unitChange >= 0 ? 'fas fa-arrow-up me-1' :
                    'fas fa-arrow-down me-1';
            }

            if (last && last.avg_amount > 0) {
                const amountChange = ((current.avg_amount - last.avg_amount) / last.avg_amount * 100).toFixed(2);
                document.getElementById('amountChangePercent').textContent = `${Math.abs(amountChange)}%`;
                const amountChangeElement = document.querySelector('#amountChangePercent').closest('.metric-change');
                amountChangeElement.className = `metric-change ${amountChange >= 0 ? 'up' : 'down'}`;
                amountChangeElement.querySelector('i').className = amountChange >= 0 ? 'fas fa-arrow-up me-1' :
                    'fas fa-arrow-down me-1';
            }
        }
    }

    async function updateDailyUnitChart() {
        console.log('🔄 Updating daily unit chart with real data...');

        const month = parseInt(document.getElementById('unitMonthSelect').value);
        const year = parseInt(document.getElementById('unitYearSelect').value);
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Update display
        document.getElementById('dailyUnitMonth').textContent = `${monthNames[month]} ${year}`;

        // Show loading
        document.getElementById('unitChartLoading').style.display = 'block';

        try {
            // Server se data fetch karein
            const response = await fetch(`/admin/energy-data?type=daily&month=${month + 1}&year=${year}`);
            const result = await response.json();

            if (result.success) {
                const apiData = result.data;

                // Chart data prepare karein
                const labels = [];
                const data = [];

                // Puri month ke liye data prepare karein
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                for (let day = 1; day <= daysInMonth; day++) {
                    labels.push(`Day ${day}`);

                    // Corresponding data find karein
                    const dayData = apiData.find(item => item.day === day);
                    data.push(dayData ? dayData.total_units : 0);
                }

                // Update chart
                dailyUnitChart.data.labels = labels;
                dailyUnitChart.data.datasets[0].data = data;
                dailyUnitChart.update();

                // Stats update karein
                updateUnitStats(data);
            }
        } catch (error) {
            console.error('Error fetching daily unit data:', error);
            
        } finally {
            // Hide loading
            document.getElementById('unitChartLoading').style.display = 'none';
        }
    }

    function updateUnitStats(data) {
        const nonZeroData = data.filter(val => val > 0);
        if (nonZeroData.length > 0) {
            const currentDay = Math.min(new Date().getDate(), nonZeroData.length) - 1;
            const unitCurrent = nonZeroData[currentDay] || 0;
            const unitAvg = (nonZeroData.reduce((a, b) => a + b, 0) / nonZeroData.length).toFixed(2);
            const unitMax = Math.max(...nonZeroData).toFixed(2);

            document.getElementById('dailyUnitCurrent').textContent = unitCurrent.toFixed(2);
            document.getElementById('dailyUnitMax').textContent = unitMax;
            document.getElementById('dailyUnitAvg').textContent = unitAvg;
            document.getElementById('dailyUnitMaxKwh').textContent = unitMax;
        }
    }

    async function updateDailyAmountChart() {
        console.log('🔄 Updating daily amount chart with real data...');

        const month = parseInt(document.getElementById('amountMonthSelect').value);
        const year = parseInt(document.getElementById('amountYearSelect').value);
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Update display
        document.getElementById('dailyAmountMonth').textContent = `${monthNames[month]} ${year}`;

        // Show loading
        document.getElementById('amountChartLoading').style.display = 'block';

        try {
            // Server se data fetch karein
            const response = await fetch(`/admin/energy-data?type=daily&month=${month + 1}&year=${year}`);
            const result = await response.json();

            if (result.success) {
                const apiData = result.data;

                // Chart data prepare karein
                const labels = [];
                const data = [];

                // Puri month ke liye data prepare karein
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                for (let day = 1; day <= daysInMonth; day++) {
                    labels.push(`Day ${day}`);

                    // Corresponding data find karein
                    const dayData = apiData.find(item => item.day === day);
                    data.push(dayData ? dayData.total_amount : 0);
                }

                // Update chart
                dailyAmountChart.data.labels = labels;
                dailyAmountChart.data.datasets[0].data = data;
                dailyAmountChart.update();

                // Stats update karein
                updateAmountStats(data);
            }
        } catch (error) {
            console.error('Error fetching daily amount data:', error);
            
        } finally {
            // Hide loading
            document.getElementById('amountChartLoading').style.display = 'none';
        }
    }

    function updateAmountStats(data) {
        const nonZeroData = data.filter(val => val > 0);
        if (nonZeroData.length > 0) {
            const currentDay = Math.min(new Date().getDate(), nonZeroData.length) - 1;
            const amountCurrent = nonZeroData[currentDay] || 0;
            const amountAvg = (nonZeroData.reduce((a, b) => a + b, 0) / nonZeroData.length).toFixed(2);
            const amountMax = Math.max(...nonZeroData).toFixed(2);

            document.getElementById('dailyAmountCurrent').textContent = amountCurrent.toFixed(2);
            document.getElementById('dailyAmountMax').textContent = amountMax;
            document.getElementById('dailyAmountAvg').textContent = amountAvg;
            document.getElementById('dailyAmountMaxRs').textContent = amountMax;
        }
    }

    async function updateMonthlyUnitChart() {
        console.log('🔄 Updating monthly unit chart with real data...');

        const year = parseInt(document.getElementById('monthlyUnitYearSelect').value);

        // Update display
        document.getElementById('monthlyUnitYear').textContent = year;

        // Show loading
        document.getElementById('monthlyUnitLoading').style.display = 'block';

        try {
            // Server se data fetch karein
            const response = await fetch(`/admin/energy-data?type=monthly&year=${year}`);
            const result = await response.json();

            if (result.success) {
                const apiData = result.data;

                // Chart data prepare karein
                const data = Array(12).fill(0);

                apiData.forEach(item => {
                    const monthIndex = item.month - 1;
                    data[monthIndex] = item.total_units;
                });

                // Update chart
                monthlyUnitChart.data.datasets[0].data = data;
                monthlyUnitChart.update();

                // Stats update karein
                updateMonthlyUnitStats(data);
            }
        } catch (error) {
            console.error('Error fetching monthly unit data:', error);
            
        } finally {
            // Hide loading
            document.getElementById('monthlyUnitLoading').style.display = 'none';
        }
    }

    function updateMonthlyUnitStats(data) {
        const nonZeroData = data.filter(val => val > 0);
        if (nonZeroData.length > 0) {
            const currentMonth = new Date().getMonth();
            const unitCurrent = data[currentMonth] || 0;
            const unitAvg = (nonZeroData.reduce((a, b) => a + b, 0) / nonZeroData.length).toFixed(2);
            const unitMax = Math.max(...nonZeroData).toFixed(2);

            document.getElementById('monthlyUnitCurrent').textContent = unitCurrent.toFixed(2);
            document.getElementById('monthlyUnitMax').textContent = unitMax;
            document.getElementById('monthlyUnitAvg').textContent = unitAvg;
            document.getElementById('monthlyUnitMaxKwh').textContent = unitMax;
        }
    }

    async function updateMonthlyAmountChart() {
        console.log('🔄 Updating monthly amount chart with real data...');

        const year = parseInt(document.getElementById('monthlyAmountYearSelect').value);

        // Update display
        document.getElementById('monthlyAmountYear').textContent = year;

        // Show loading
        document.getElementById('monthlyAmountLoading').style.display = 'block';

        try {
            // Server se data fetch karein
            const response = await fetch(`/admin/energy-data?type=monthly&year=${year}`);
            const result = await response.json();

            if (result.success) {
                const apiData = result.data;

                // Chart data prepare karein
                const data = Array(12).fill(0);

                apiData.forEach(item => {
                    const monthIndex = item.month - 1;
                    data[monthIndex] = item.total_amount;
                });

                // Update chart
                monthlyAmountChart.data.datasets[0].data = data;
                monthlyAmountChart.update();

                // Stats update karein
                updateMonthlyAmountStats(data);
            }
        } catch (error) {
            console.error('Error fetching monthly amount data:', error);
            
        } finally {
            // Hide loading
            document.getElementById('monthlyAmountLoading').style.display = 'none';
        }
    }

    function updateMonthlyAmountStats(data) {
        const nonZeroData = data.filter(val => val > 0);
        if (nonZeroData.length > 0) {
            const currentMonth = new Date().getMonth();
            const amountCurrent = data[currentMonth] || 0;
            const amountAvg = (nonZeroData.reduce((a, b) => a + b, 0) / nonZeroData.length).toFixed(2);
            const amountMax = Math.max(...nonZeroData).toFixed(2);

            document.getElementById('monthlyAmountCurrent').textContent = amountCurrent.toFixed(2);
            document.getElementById('monthlyAmountMax').textContent = amountMax;
            document.getElementById('monthlyAmountAvg').textContent = amountAvg;
            document.getElementById('monthlyAmountMaxRs').textContent = amountMax;
        }
    }

    // Download Report Function - UPDATED with CURRENT BALANCE
    async function downloadReport() {
        console.log('💾 Downloading report...');

        const format = document.querySelector('input[name="downloadFormat"]:checked').value;
        const downloadTime = new Date().toLocaleString();
        
        // Get current balance from display
        const currentBalance = getCurrentBalance();
        console.log('💰 Using current balance for report:', currentBalance);

        try {
            // Show loading state
            const downloadBtn = document.getElementById('confirmDownload');
            const originalText = downloadBtn.innerHTML;
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
            downloadBtn.disabled = true;

            // Get report data based on selected type
            await getReportData();

            // ALWAYS generate report, even if no real data - use sample data as fallback
            if (!reportData || reportData.length === 0) {
                console.log('No real data available, using sample data');
                reportData = generateSampleData();
            }

            console.log('Report data ready:', reportData);

            // Generate report based on format
            switch (format) {
                case 'excel':
                    await downloadExcelReport(currentBalance);
                    break;
                case 'csv':
                    await downloadCSVReport(currentBalance);
                    break;
                case 'pdf':
                    await downloadPDFReport(currentBalance);
                    break;
                default:
                    throw new Error('Unknown format selected');
            }

            showSuccessMessage(`Report downloaded successfully as ${format.toUpperCase()}!`);
            
        } catch (error) {
            console.error('Error downloading report:', error);
            showErrorMessage('Error generating report: ' + error.message);
        } finally {
            // Restore button state
            const downloadBtn = document.getElementById('confirmDownload');
            if (downloadBtn) {
                downloadBtn.innerHTML = '<i class="fas fa-download me-1"></i> Generate & Download';
                downloadBtn.disabled = false;
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('downloadModal'));
            if (modal) modal.hide();
        }
    }

    // Get report data based on selected type - ALWAYS returns data
    async function getReportData() {
        console.log('📊 Getting report data for type:', selectedDownloadType);
        
        try {
            let data = [];
            
            switch (selectedDownloadType) {
                case 'daily':
                    data = await getDailyReportData();
                    break;
                case 'monthly':
                    data = await getMonthlyReportData();
                    break;
                case 'complete':
                    data = await getCompleteReportData();
                    break;
            }
            
            // If no data from API, use sample data
            if (!data || data.length === 0) {
                console.log('No data from API, using sample data');
                data = generateSampleData();
            }
            
            reportData = data;
            console.log('Data retrieved:', reportData);
            return data;
            
        } catch (error) {
            console.warn('Error getting data, using sample data:', error);
            // ALWAYS return sample data as fallback
            reportData = generateSampleData();
            return reportData;
        }
    }

    // Get daily report data - with fallback
    async function getDailyReportData() {
        console.log('📅 Getting daily report data...');
        
        try {
            // Use current filter values or default values
            const month = document.getElementById('unitMonthSelect') ? 
                parseInt(document.getElementById('unitMonthSelect').value) : new Date().getMonth();
            const year = document.getElementById('unitYearSelect') ? 
                parseInt(document.getElementById('unitYearSelect').value) : new Date().getFullYear();
            
            const response = await fetch(`/admin/energy-data?type=daily&month=${month + 1}&year=${year}`);
            
            if (response.ok) {
                const result = await response.json();

                if (result.success && result.data && result.data.length > 0) {
                    return processDailyData(result.data, month, year);
                }
            }
        } catch (error) {
            console.error('Error getting daily data:', error);
        }
        
        // Return empty array - will be handled by fallback
        return [];
    }

    // Process daily data with proper balance calculation - USING CURRENT BALANCE
    function processDailyData(apiData, month, year) {
        const currentBalance = getCurrentBalance();
        let runningBalance = currentBalance; // Start with CURRENT balance
        const processedData = [];
        
        apiData.forEach((item, index) => {
            const unitConsumed = item.total_units || 0;
            const amount = unitConsumed * rechargeSettings.unit_rate;
            
            // Calculate running balance
            runningBalance = runningBalance - amount;
            
            // Check if recharge happened (when balance goes below threshold)
            const recharge = runningBalance < (rechargeSettings.m_recharge_amount * 0.2) ? 
                rechargeSettings.m_sanction_load : 0;
                
            // If recharge happened, add to balance
            if (recharge > 0) {
                runningBalance += recharge;
            }
            
            processedData.push({
                srNo: index + 1,
                date: `${year}-${(month + 1).toString().padStart(2, '0')}-${item.day.toString().padStart(2, '0')}`,
                unitConsumed: unitConsumed,
                amount: amount,
                balance: Math.max(0, runningBalance), // Balance can't be negative
                recharge: recharge
            });
        });
        
        return processedData.filter(item => item.unitConsumed > 0);
    }

    // Get monthly report data - with fallback
    async function getMonthlyReportData() {
        console.log('📊 Getting monthly report data...');
        
        try {
            const year = document.getElementById('monthlyUnitYearSelect') ? 
                parseInt(document.getElementById('monthlyUnitYearSelect').value) : new Date().getFullYear();
            
            const response = await fetch(`/admin/energy-data?type=monthly&year=${year}`);
            
            if (response.ok) {
                const result = await response.json();

                if (result.success && result.data && result.data.length > 0) {
                    return processMonthlyData(result.data, year);
                }
            }
        } catch (error) {
            console.error('Error getting monthly data:', error);
        }
        
        // Return empty array - will be handled by fallback
        return [];
    }

    // Process monthly data with proper balance calculation - USING CURRENT BALANCE
    function processMonthlyData(apiData, year) {
        const currentBalance = getCurrentBalance();
        let runningBalance = currentBalance; // Start with CURRENT balance
        const processedData = [];
        
        apiData.forEach((item, index) => {
            const unitConsumed = item.total_units || 0;
            const amount = unitConsumed * rechargeSettings.unit_rate;
            
            // Calculate running balance
            runningBalance = runningBalance - amount;
            
            // Check if recharge happened (when balance goes below threshold)
            const recharge = runningBalance < (rechargeSettings.m_recharge_amount * 0.2) ? 
                rechargeSettings.m_sanction_load : 0;
                
            // If recharge happened, add to balance
            if (recharge > 0) {
                runningBalance += recharge;
            }
            
            processedData.push({
                srNo: index + 1,
                date: `${year}-${item.month.toString().padStart(2, '0')}-01`,
                unitConsumed: unitConsumed,
                amount: amount,
                balance: Math.max(0, runningBalance), // Balance can't be negative
                recharge: recharge
            });
        });
        
        return processedData.filter(item => item.unitConsumed > 0);
    }

    // Get complete report data
    async function getCompleteReportData() {
        console.log('📋 Getting complete report data...');
        
        const dailyData = await getDailyReportData();
        const monthlyData = await getMonthlyReportData();
        
        const combinedData = [...dailyData, ...monthlyData];
        
        // If no data from APIs, use sample data
        if (combinedData.length === 0) {
            return generateSampleData();
        }
        
        return combinedData;
    }

    // Generate comprehensive sample data - UPDATED with CURRENT BALANCE
    function generateSampleData() {
        console.log('🎯 Generating comprehensive sample data...');
        
        const sampleData = [];
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth();
        
        const currentBalance = getCurrentBalance();
        let runningBalance = currentBalance; // Start with CURRENT balance
        
        // Generate daily data for current month
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        
        for (let i = 0; i < daysInMonth; i++) {
            const unitConsumed = Math.floor(Math.random() * 15) + 5; // 5-20 units
            const amount = unitConsumed * rechargeSettings.unit_rate;
            const day = i + 1;
            
            // Calculate running balance
            runningBalance = runningBalance - amount;
            
            // Check if recharge happened (when balance goes below 20% of recharge amount)
            const recharge = runningBalance < (rechargeSettings.m_recharge_amount * 0.2) ? 
                rechargeSettings.m_sanction_load : 0;
                
            // If recharge happened, add to balance
            if (recharge > 0) {
                runningBalance += recharge;
            }
            
            sampleData.push({
                srNo: i + 1,
                date: `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`,
                unitConsumed: unitConsumed,
                amount: amount,
                balance: Math.max(0, runningBalance), // Balance can't be negative
                recharge: recharge
            });
        }
        
        console.log('Sample data generated with', sampleData.length, 'records');
        console.log('Final balance:', runningBalance);
        return sampleData;
    }

    // Excel download function - UPDATED with CURRENT BALANCE
    async function downloadExcelReport(currentBalance) {
        console.log('📊 Generating Excel report...');
        
        try {
            // Prepare data for Excel
            const excelData = reportData.map(item => ({
                'Sr.No': item.srNo,
                'Date': formatDate(item.date),
                'Unit Consumed (kWh)': item.unitConsumed,
                'Amount (Rs.)': item.amount,
                'Balance (Rs.)': item.balance,
                'Recharge (Rs.)': item.recharge
            }));
            
            // Add current balance row
            excelData.push({
                'Sr.No': '',
                'Date': 'Current Balance',
                'Unit Consumed (kWh)': '',
                'Amount (Rs.)': '',
                'Balance (Rs.)': currentBalance,
                'Recharge (Rs.)': ''
            });
            
            // Create workbook and worksheet
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            
            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Energy Report');
            
            // Generate file name
            const fileName = `Energy_Report_${selectedDownloadType}_${new Date().toISOString().split('T')[0]}.xlsx`;
            
            // Download file
            XLSX.writeFile(wb, fileName);
            console.log('✅ Excel file downloaded successfully');
            
        } catch (error) {
            console.error('Error generating Excel:', error);
            throw new Error('Failed to generate Excel file: ' + error.message);
        }
    }

    // CSV download function - UPDATED with CURRENT BALANCE
    async function downloadCSVReport(currentBalance) {
        console.log('📊 Generating CSV report...');
        
        try {
            // Prepare CSV headers and data
            const headers = ['Sr.No', 'Date', 'Unit Consumed (kWh)', 'Amount (Rs.)', 'Balance (Rs.)', 'Recharge (Rs.)'];
            const csvRows = [
                headers.join(','),
                ...reportData.map(item => [
                    item.srNo,
                    `"${formatDate(item.date)}"`,
                    item.unitConsumed,
                    item.amount,
                    item.balance,
                    item.recharge
                ].join(','))
            ];
            
            // Add current balance row
            csvRows.push(`"","Current Balance","","","${currentBalance}",""`);
            
            const csvContent = csvRows.join('\n');
            
            // Create and download file
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            
            const fileName = `Energy_Report_${selectedDownloadType}_${new Date().toISOString().split('T')[0]}.csv`;
            link.setAttribute('href', url);
            link.setAttribute('download', fileName);
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up
            setTimeout(() => URL.revokeObjectURL(url), 100);
            console.log('✅ CSV file downloaded successfully');
            
        } catch (error) {
            console.error('Error generating CSV:', error);
            throw new Error('Failed to generate CSV file: ' + error.message);
        }
    }

    // PDF download function - UPDATED with CURRENT BALANCE
    async function downloadPDFReport(currentBalance) {
        console.log('📊 Generating PDF report...');
        
        try {
            // Check if jsPDF is available with proper error handling
            if (typeof jspdf === 'undefined' && typeof window.jspdf === 'undefined') {
                console.error('PDF library not loaded');
                // Fallback to CSV if PDF library not available
                await downloadCSVReport(currentBalance);
                return;
            }
            
            // Use the correct jsPDF reference
            const jsPDF = window.jspdf.jsPDF;
            const doc = new jsPDF();
            
            // Add title
            doc.setFontSize(16);
            doc.setTextColor(0, 46, 110);
            doc.text(`Energy Consumption Report`, 105, 15, { align: 'center' });
            
            // Add report type and date
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(`${selectedDownloadType.charAt(0).toUpperCase() + selectedDownloadType.slice(1)} Report - Generated on: ${new Date().toLocaleString()}`, 105, 22, { align: 'center' });
            
            // Add CURRENT balance info
            doc.text(`Current Balance: Rs. ${currentBalance} | Recharge Amount: Rs. ${rechargeSettings.m_sanction_load} | Unit Rate: Rs. ${rechargeSettings.unit_rate}/kWh`, 105, 28, { align: 'center' });
            
            // Prepare table data
            const tableData = reportData.map(item => [
                item.srNo.toString(),
                formatDate(item.date),
                item.unitConsumed.toString() + ' kWh',
                'Rs. ' + item.amount.toString(),
                'Rs. ' + item.balance.toString(),
                'Rs. ' + item.recharge.toString()
            ]);
            
            // Add table using autoTable - with proper error handling
            if (typeof doc.autoTable !== 'undefined') {
                doc.autoTable({
                    startY: 35,
                    head: [['Sr.No', 'Date', 'Units Consumed', 'Amount', 'Balance', 'Recharge']],
                    body: tableData,
                    headStyles: {
                        fillColor: [0, 46, 110],
                        textColor: 255,
                        fontStyle: 'bold'
                    },
                    styles: {
                        fontSize: 8,
                        cellPadding: 3
                    },
                    margin: { top: 35 }
                });
                
                // Add current balance summary
                const finalY = doc.lastAutoTable.finalY + 10;
                doc.setFontSize(10);
                doc.setTextColor(0, 46, 110);
                doc.text(`Current Available Balance: Rs. ${currentBalance}`, 105, finalY, { align: 'center' });
            } else {
                // Simple table if autoTable not available
                let yPosition = 35;
                tableData.forEach(row => {
                    if (yPosition < 280) {
                        doc.text(row.join(' | '), 10, yPosition);
                        yPosition += 10;
                    }
                });
            }
            
            // Generate file name and save
            const fileName = `Energy_Report_${selectedDownloadType}_${new Date().toISOString().split('T')[0]}.pdf`;
            doc.save(fileName);
            console.log('✅ PDF file downloaded successfully');
            
        } catch (error) {
            console.error('Error generating PDF:', error);
            // Try CSV as fallback
            try {
                await downloadCSVReport(currentBalance);
                console.log('PDF failed, CSV download successful as fallback');
            } catch (fallbackError) {
                throw new Error('Failed to generate PDF and CSV fallback: ' + error.message);
            }
        }
    }

    // Utility function to format dates
    function formatDate(dateString) {
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        } catch (error) {
            return dateString;
        }
    }

    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success success-alert';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2 fs-5"></i>
                <div>
                    <strong>Success!</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.appendChild(alertDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger success-alert';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2 fs-5"></i>
                <div>
                    <strong>Error!</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.appendChild(alertDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
</script> -->


<script>
    // Global variables
    let dailyUnitChart, dailyAmountChart, monthlyUnitChart, monthlyAmountChart;
    let selectedDownloadType = 'daily';
    let reportData = [];
    
    // Balance tracking system
    let balanceHistory = [];
    let currentBalance = 0;
    
    // Initialize balance from display
    function initializeBalance() {
        const balanceElement = document.querySelector('.status-box .status-box');
        if (balanceElement) {
            const balanceText = balanceElement.textContent.trim();
            currentBalance = parseFloat(balanceText.replace(/[^\d.]/g, '')) || {{ $rechargeSetting->m_recharge_amount }};
            
            // Store initial balance in history
            balanceHistory = [{
                date: new Date().toISOString().split('T')[0],
                type: 'initial',
                amount: currentBalance,
                balance: currentBalance,
                description: 'Initial Balance'
            }];
            
            console.log('💰 Initial Balance:', currentBalance);
        }
        return currentBalance;
    }
    
    let rechargeSettings = {
        m_recharge_amount: {{ $rechargeSetting->m_recharge_amount }},
        m_sanction_load: {{ $rechargeSetting->m_sanction_load }},
        unit_rate: 10
    };

    // CSRF Token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Initializing Energy Dashboard with Balance Tracking...');
        
        // Initialize balance system first
        currentBalance = initializeBalance();
        
        initializeCharts();
        setupEventListeners();
        fetchRealData();
    });

    // Balance Management Functions
    function updateBalanceDisplay(newBalance) {
        const balanceElement = document.querySelector('.status-box .status-box');
        if (balanceElement) {
            balanceElement.textContent = newBalance.toFixed(2);
            currentBalance = newBalance;
        }
    }

    function addBalanceTransaction(date, type, amount, description = '') {
        const transaction = {
            date: date,
            type: type, // 'consumption', 'recharge', 'initial'
            amount: amount,
            balance: currentBalance,
            description: description
        };
        
        balanceHistory.push(transaction);
        console.log('💳 Balance Transaction:', transaction);
    }

    function calculateConsumption(unitConsumed) {
        return unitConsumed * rechargeSettings.unit_rate;
    }

    function processConsumption(date, unitConsumed) {
        const consumptionAmount = calculateConsumption(unitConsumed);
        
        if (currentBalance >= consumptionAmount) {
            currentBalance -= consumptionAmount;
            
            // Add to transaction history
            addBalanceTransaction(
                date, 
                'consumption', 
                -consumptionAmount, 
                `Energy Consumption: ${unitConsumed} kWh`
            );
            
            updateBalanceDisplay(currentBalance);
            return true;
        } else {
            console.warn('❌ Insufficient balance for consumption');
            return false;
        }
    }

    function processRecharge(date, rechargeAmount) {
        currentBalance += rechargeAmount;
        
        // Add to transaction history
        addBalanceTransaction(
            date,
            'recharge',
            rechargeAmount,
            `Balance Recharge`
        );
        
        updateBalanceDisplay(currentBalance);
        return true;
    }

    // Auto-recharge logic
    function checkAutoRecharge(date) {
        const rechargeThreshold = rechargeSettings.m_recharge_amount * 0.2; // 20% threshold
        
        if (currentBalance <= rechargeThreshold) {
            console.log('🔄 Auto-recharge triggered');
            processRecharge(date, rechargeSettings.m_sanction_load);
            return rechargeSettings.m_sanction_load;
        }
        return 0;
    }

    function initializeCharts() {
        console.log('📈 Initializing charts...');

        // Daily Unit Chart
        const dailyUnitCtx = document.getElementById('dailyUnitChart').getContext('2d');
        dailyUnitChart = new Chart(dailyUnitCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Unit Consumption (kWh)',
                    data: [],
                    borderColor: '#002E6E',
                    backgroundColor: 'rgba(0, 46, 110, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#002E6E',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        // Daily Amount Chart
        const dailyAmountCtx = document.getElementById('dailyAmountChart').getContext('2d');
        dailyAmountChart = new Chart(dailyAmountCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Amount (Rs.)',
                    data: [],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        // Monthly Unit Chart
        const monthlyUnitCtx = document.getElementById('monthlyUnitChart').getContext('2d');
        monthlyUnitChart = new Chart(monthlyUnitCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Units (kWh)',
                    data: [],
                    backgroundColor: 'rgba(0, 46, 110, 0.7)',
                    borderColor: '#002E6E',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        // Monthly Amount Chart
        const monthlyAmountCtx = document.getElementById('monthlyAmountChart').getContext('2d');
        monthlyAmountChart = new Chart(monthlyAmountCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Amount (Rs.)',
                    data: [],
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: '#28a745',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        console.log('✅ Charts initialized');
    }

    function setupEventListeners() {
        console.log('🔗 Setting up event listeners...');

        // Daily filter buttons
        document.getElementById('applyUnitFilter').addEventListener('click', updateDailyUnitChart);
        document.getElementById('applyAmountFilter').addEventListener('click', updateDailyAmountChart);

        // Monthly filter buttons
        document.getElementById('applyMonthlyUnitFilter').addEventListener('click', updateMonthlyUnitChart);
        document.getElementById('applyMonthlyAmountFilter').addEventListener('click', updateMonthlyAmountChart);

        // Download modal options
        document.querySelectorAll('.download-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.download-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                this.classList.add('active');
                selectedDownloadType = this.dataset.type;
            });
        });

        // Download button
        document.getElementById('confirmDownload').addEventListener('click', downloadReport);

        console.log('✅ Event listeners setup completed');
    }

    // Real data fetch function
    async function fetchRealData() {
        console.log('📊 Fetching real data from server...');

        try {
            // Stats fetch karein
            const statsResponse = await fetch('/admin/energy-stats');
            const statsData = await statsResponse.json();

            if (statsData.success) {
                updateStatsDisplay(statsData);
            }

            // Automatically load ALL charts with current data
            await loadInitialCharts();

        } catch (error) {
            console.error('Error fetching data:', error);
            // Even if API fails, try to load initial charts
            await loadInitialCharts();
        }
    }

    // Load initial ALL charts with current data
    async function loadInitialCharts() {
        console.log('🔄 Loading initial ALL charts...');
        
        // Set dropdowns to current values
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();
        
        // Set daily filter dropdowns
        const unitMonthSelect = document.getElementById('unitMonthSelect');
        const unitYearSelect = document.getElementById('unitYearSelect');
        if (unitMonthSelect && unitYearSelect) {
            unitMonthSelect.value = currentMonth;
            unitYearSelect.value = currentYear;
        }
        
        const amountMonthSelect = document.getElementById('amountMonthSelect');
        const amountYearSelect = document.getElementById('amountYearSelect');
        if (amountMonthSelect && amountYearSelect) {
            amountMonthSelect.value = currentMonth;
            amountYearSelect.value = currentYear;
        }
        
        // Set monthly filter dropdowns
        const monthlyUnitYearSelect = document.getElementById('monthlyUnitYearSelect');
        const monthlyAmountYearSelect = document.getElementById('monthlyAmountYearSelect');
        if (monthlyUnitYearSelect && monthlyAmountYearSelect) {
            monthlyUnitYearSelect.value = currentYear;
            monthlyAmountYearSelect.value = currentYear;
        }
        
        // Load ALL charts with current data
        await updateDailyUnitChart();
        await updateDailyAmountChart();
        await updateMonthlyUnitChart();
        await updateMonthlyAmountChart();
        
        console.log('✅ All charts loaded successfully');
    }

    function updateStatsDisplay(statsData) {
        const current = statsData.current_month;
        const last = statsData.last_month;

        if (current) {
            // Daily unit stats
            document.getElementById('dailyUnitCurrent').textContent = (current.avg_units || 0).toFixed(2);
            document.getElementById('dailyUnitMax').textContent = (current.max_units || 0).toFixed(2);
            document.getElementById('dailyUnitAvg').textContent = (current.avg_units || 0).toFixed(2);
            document.getElementById('dailyUnitMaxKwh').textContent = (current.max_units || 0).toFixed(2);

            // Daily amount stats
            document.getElementById('dailyAmountCurrent').textContent = (current.avg_amount || 0).toFixed(2);
            document.getElementById('dailyAmountMax').textContent = (current.max_amount || 0).toFixed(2);
            document.getElementById('dailyAmountAvg').textContent = (current.avg_amount || 0).toFixed(2);
            document.getElementById('dailyAmountMaxRs').textContent = (current.max_amount || 0).toFixed(2);

            // Monthly unit stats
            document.getElementById('monthlyUnitCurrent').textContent = (current.total_units || 0).toFixed(2);
            document.getElementById('monthlyUnitMax').textContent = (current.max_monthly_units || 0).toFixed(2);
            document.getElementById('monthlyUnitAvg').textContent = (current.avg_monthly_units || 0).toFixed(2);
            document.getElementById('monthlyUnitMaxKwh').textContent = (current.max_monthly_units || 0).toFixed(2);

            // Monthly amount stats
            document.getElementById('monthlyAmountCurrent').textContent = (current.total_amount || 0).toFixed(2);
            document.getElementById('monthlyAmountMax').textContent = (current.max_monthly_amount || 0).toFixed(2);
            document.getElementById('monthlyAmountAvg').textContent = (current.avg_monthly_amount || 0).toFixed(2);
            document.getElementById('monthlyAmountMaxRs').textContent = (current.max_monthly_amount || 0).toFixed(2);

            // Percentage changes calculate karein
            if (last && last.avg_units > 0) {
                const unitChange = ((current.avg_units - last.avg_units) / last.avg_units * 100).toFixed(2);
                document.getElementById('unitChangePercent').textContent = `${Math.abs(unitChange)}%`;
                const unitChangeElement = document.querySelector('#unitChangePercent').closest('.metric-change');
                unitChangeElement.className = `metric-change ${unitChange >= 0 ? 'up' : 'down'}`;
                unitChangeElement.querySelector('i').className = unitChange >= 0 ? 'fas fa-arrow-up me-1' :
                    'fas fa-arrow-down me-1';
            }

            if (last && last.avg_amount > 0) {
                const amountChange = ((current.avg_amount - last.avg_amount) / last.avg_amount * 100).toFixed(2);
                document.getElementById('amountChangePercent').textContent = `${Math.abs(amountChange)}%`;
                const amountChangeElement = document.querySelector('#amountChangePercent').closest('.metric-change');
                amountChangeElement.className = `metric-change ${amountChange >= 0 ? 'up' : 'down'}`;
                amountChangeElement.querySelector('i').className = amountChange >= 0 ? 'fas fa-arrow-up me-1' :
                    'fas fa-arrow-down me-1';
            }
        }
    }

    async function updateDailyUnitChart() {
        console.log('🔄 Updating daily unit chart with real data...');

        const month = parseInt(document.getElementById('unitMonthSelect').value);
        const year = parseInt(document.getElementById('unitYearSelect').value);
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Update display
        document.getElementById('dailyUnitMonth').textContent = `${monthNames[month]} ${year}`;

        // Show loading
        document.getElementById('unitChartLoading').style.display = 'block';

        try {
            // Server se data fetch karein
            const response = await fetch(`/admin/energy-data?type=daily&month=${month + 1}&year=${year}`);
            const result = await response.json();

            if (result.success) {
                const apiData = result.data;

                // Chart data prepare karein
                const labels = [];
                const data = [];

                // Puri month ke liye data prepare karein
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                for (let day = 1; day <= daysInMonth; day++) {
                    labels.push(`Day ${day}`);

                    // Corresponding data find karein
                    const dayData = apiData.find(item => item.day === day);
                    data.push(dayData ? dayData.total_units : 0);
                }

                // Update chart
                dailyUnitChart.data.labels = labels;
                dailyUnitChart.data.datasets[0].data = data;
                dailyUnitChart.update();

                // Stats update karein
                updateUnitStats(data);
            }
        } catch (error) {
            console.error('Error fetching daily unit data:', error);
            
        } finally {
            // Hide loading
            document.getElementById('unitChartLoading').style.display = 'none';
        }
    }

    function updateUnitStats(data) {
        const nonZeroData = data.filter(val => val > 0);
        if (nonZeroData.length > 0) {
            const currentDay = Math.min(new Date().getDate(), nonZeroData.length) - 1;
            const unitCurrent = nonZeroData[currentDay] || 0;
            const unitAvg = (nonZeroData.reduce((a, b) => a + b, 0) / nonZeroData.length).toFixed(2);
            const unitMax = Math.max(...nonZeroData).toFixed(2);

            document.getElementById('dailyUnitCurrent').textContent = unitCurrent.toFixed(2);
            document.getElementById('dailyUnitMax').textContent = unitMax;
            document.getElementById('dailyUnitAvg').textContent = unitAvg;
            document.getElementById('dailyUnitMaxKwh').textContent = unitMax;
        }
    }

    async function updateDailyAmountChart() {
        console.log('🔄 Updating daily amount chart with real data...');

        const month = parseInt(document.getElementById('amountMonthSelect').value);
        const year = parseInt(document.getElementById('amountYearSelect').value);
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Update display
        document.getElementById('dailyAmountMonth').textContent = `${monthNames[month]} ${year}`;

        // Show loading
        document.getElementById('amountChartLoading').style.display = 'block';

        try {
            // Server se data fetch karein
            const response = await fetch(`/admin/energy-data?type=daily&month=${month + 1}&year=${year}`);
            const result = await response.json();

            if (result.success) {
                const apiData = result.data;

                // Chart data prepare karein
                const labels = [];
                const data = [];

                // Puri month ke liye data prepare karein
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                for (let day = 1; day <= daysInMonth; day++) {
                    labels.push(`Day ${day}`);

                    // Corresponding data find karein
                    const dayData = apiData.find(item => item.day === day);
                    data.push(dayData ? dayData.total_amount : 0);
                }

                // Update chart
                dailyAmountChart.data.labels = labels;
                dailyAmountChart.data.datasets[0].data = data;
                dailyAmountChart.update();

                // Stats update karein
                updateAmountStats(data);
            }
        } catch (error) {
            console.error('Error fetching daily amount data:', error);
            
        } finally {
            // Hide loading
            document.getElementById('amountChartLoading').style.display = 'none';
        }
    }

    function updateAmountStats(data) {
        const nonZeroData = data.filter(val => val > 0);
        if (nonZeroData.length > 0) {
            const currentDay = Math.min(new Date().getDate(), nonZeroData.length) - 1;
            const amountCurrent = nonZeroData[currentDay] || 0;
            const amountAvg = (nonZeroData.reduce((a, b) => a + b, 0) / nonZeroData.length).toFixed(2);
            const amountMax = Math.max(...nonZeroData).toFixed(2);

            document.getElementById('dailyAmountCurrent').textContent = amountCurrent.toFixed(2);
            document.getElementById('dailyAmountMax').textContent = amountMax;
            document.getElementById('dailyAmountAvg').textContent = amountAvg;
            document.getElementById('dailyAmountMaxRs').textContent = amountMax;
        }
    }

    async function updateMonthlyUnitChart() {
        console.log('🔄 Updating monthly unit chart with real data...');

        const year = parseInt(document.getElementById('monthlyUnitYearSelect').value);

        // Update display
        document.getElementById('monthlyUnitYear').textContent = year;

        // Show loading
        document.getElementById('monthlyUnitLoading').style.display = 'block';

        try {
            // Server se data fetch karein
            const response = await fetch(`/admin/energy-data?type=monthly&year=${year}`);
            const result = await response.json();

            if (result.success) {
                const apiData = result.data;

                // Chart data prepare karein
                const data = Array(12).fill(0);

                apiData.forEach(item => {
                    const monthIndex = item.month - 1;
                    data[monthIndex] = item.total_units;
                });

                // Update chart
                monthlyUnitChart.data.datasets[0].data = data;
                monthlyUnitChart.update();

                // Stats update karein
                updateMonthlyUnitStats(data);
            }
        } catch (error) {
            console.error('Error fetching monthly unit data:', error);
            
        } finally {
            // Hide loading
            document.getElementById('monthlyUnitLoading').style.display = 'none';
        }
    }

    function updateMonthlyUnitStats(data) {
        const nonZeroData = data.filter(val => val > 0);
        if (nonZeroData.length > 0) {
            const currentMonth = new Date().getMonth();
            const unitCurrent = data[currentMonth] || 0;
            const unitAvg = (nonZeroData.reduce((a, b) => a + b, 0) / nonZeroData.length).toFixed(2);
            const unitMax = Math.max(...nonZeroData).toFixed(2);

            document.getElementById('monthlyUnitCurrent').textContent = unitCurrent.toFixed(2);
            document.getElementById('monthlyUnitMax').textContent = unitMax;
            document.getElementById('monthlyUnitAvg').textContent = unitAvg;
            document.getElementById('monthlyUnitMaxKwh').textContent = unitMax;
        }
    }

    async function updateMonthlyAmountChart() {
        console.log('🔄 Updating monthly amount chart with real data...');

        const year = parseInt(document.getElementById('monthlyAmountYearSelect').value);

        // Update display
        document.getElementById('monthlyAmountYear').textContent = year;

        // Show loading
        document.getElementById('monthlyAmountLoading').style.display = 'block';

        try {
            // Server se data fetch karein
            const response = await fetch(`/admin/energy-data?type=monthly&year=${year}`);
            const result = await response.json();

            if (result.success) {
                const apiData = result.data;

                // Chart data prepare karein
                const data = Array(12).fill(0);

                apiData.forEach(item => {
                    const monthIndex = item.month - 1;
                    data[monthIndex] = item.total_amount;
                });

                // Update chart
                monthlyAmountChart.data.datasets[0].data = data;
                monthlyAmountChart.update();

                // Stats update karein
                updateMonthlyAmountStats(data);
            }
        } catch (error) {
            console.error('Error fetching monthly amount data:', error);
            
        } finally {
            // Hide loading
            document.getElementById('monthlyAmountLoading').style.display = 'none';
        }
    }

    function updateMonthlyAmountStats(data) {
        const nonZeroData = data.filter(val => val > 0);
        if (nonZeroData.length > 0) {
            const currentMonth = new Date().getMonth();
            const amountCurrent = data[currentMonth] || 0;
            const amountAvg = (nonZeroData.reduce((a, b) => a + b, 0) / nonZeroData.length).toFixed(2);
            const amountMax = Math.max(...nonZeroData).toFixed(2);

            document.getElementById('monthlyAmountCurrent').textContent = amountCurrent.toFixed(2);
            document.getElementById('monthlyAmountMax').textContent = amountMax;
            document.getElementById('monthlyAmountAvg').textContent = amountAvg;
            document.getElementById('monthlyAmountMaxRs').textContent = amountMax;
        }
    }

    // Updated data processing with balance tracking
    function processDailyDataWithBalance(apiData, month, year) {
        const processedData = [];
        let tempBalance = currentBalance; // Start with current balance
        
        apiData.forEach((item, index) => {
            const unitConsumed = item.total_units || 0;
            const amount = calculateConsumption(unitConsumed);
            const date = `${year}-${(month + 1).toString().padStart(2, '0')}-${item.day.toString().padStart(2, '0')}`;
            
            let rechargeAmount = 0;
            
            // Process consumption
            if (unitConsumed > 0) {
                if (tempBalance >= amount) {
                    tempBalance -= amount;
                } else {
                    // Insufficient balance - still deduct but show negative
                    tempBalance -= amount;
                }
                
                // Check for auto-recharge
                rechargeAmount = checkAutoRecharge(date);
                if (rechargeAmount > 0) {
                    tempBalance += rechargeAmount;
                }
            }
            
            processedData.push({
                srNo: index + 1,
                date: date,
                unitConsumed: unitConsumed,
                amount: amount,
                balance: Math.max(0, tempBalance), // Prevent negative in display
                recharge: rechargeAmount,
                actualBalance: tempBalance // Store actual balance (can be negative)
            });
        });
        
        return processedData.filter(item => item.unitConsumed > 0);
    }

    // Download Report Function - UPDATED with BALANCE TRACKING
    async function downloadReport() {
        console.log('💾 Downloading report with balance tracking...');

        const format = document.querySelector('input[name="downloadFormat"]:checked').value;
        
        try {
            // Show loading state
            const downloadBtn = document.getElementById('confirmDownload');
            const originalText = downloadBtn.innerHTML;
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
            downloadBtn.disabled = true;

            // Get report data
            await getReportData();

            if (!reportData || reportData.length === 0) {
                console.log('No real data available, using sample data');
                reportData = generateSampleDataWithBalance();
            }

            console.log('Report data ready:', reportData);

            // Generate report based on format
            switch (format) {
                case 'excel':
                    await downloadExcelReportWithBalance();
                    break;
                case 'csv':
                    await downloadCSVReportWithBalance();
                    break;
                case 'pdf':
                    await downloadPDFReportWithBalance();
                    break;
                default:
                    throw new Error('Unknown format selected');
            }

            showSuccessMessage(`Report downloaded successfully as ${format.toUpperCase()}!`);
            
        } catch (error) {
            console.error('Error downloading report:', error);
            showErrorMessage('Error generating report: ' + error.message);
        } finally {
            // Restore button state
            const downloadBtn = document.getElementById('confirmDownload');
            if (downloadBtn) {
                downloadBtn.innerHTML = '<i class="fas fa-download me-1"></i> Generate & Download';
                downloadBtn.disabled = false;
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('downloadModal'));
            if (modal) modal.hide();
        }
    }

    // Get report data based on selected type - ALWAYS returns data
    async function getReportData() {
        console.log('📊 Getting report data for type:', selectedDownloadType);
        
        try {
            let data = [];
            
            switch (selectedDownloadType) {
                case 'daily':
                    data = await getDailyReportData();
                    break;
                case 'monthly':
                    data = await getMonthlyReportData();
                    break;
                case 'complete':
                    data = await getCompleteReportData();
                    break;
            }
            
            // If no data from API, use sample data
            if (!data || data.length === 0) {
                console.log('No data from API, using sample data');
                data = generateSampleDataWithBalance();
            }
            
            reportData = data;
            console.log('Data retrieved:', reportData);
            return data;
            
        } catch (error) {
            console.warn('Error getting data, using sample data:', error);
            // ALWAYS return sample data as fallback
            reportData = generateSampleDataWithBalance();
            return reportData;
        }
    }

    // Get daily report data - with fallback
    async function getDailyReportData() {
        console.log('📅 Getting daily report data...');
        
        try {
            // Use current filter values or default values
            const month = document.getElementById('unitMonthSelect') ? 
                parseInt(document.getElementById('unitMonthSelect').value) : new Date().getMonth();
            const year = document.getElementById('unitYearSelect') ? 
                parseInt(document.getElementById('unitYearSelect').value) : new Date().getFullYear();
            
            const response = await fetch(`/admin/energy-data?type=daily&month=${month + 1}&year=${year}`);
            
            if (response.ok) {
                const result = await response.json();

                if (result.success && result.data && result.data.length > 0) {
                    return processDailyDataWithBalance(result.data, month, year);
                }
            }
        } catch (error) {
            console.error('Error getting daily data:', error);
        }
        
        // Return empty array - will be handled by fallback
        return [];
    }

    // Get monthly report data - with fallback
    async function getMonthlyReportData() {
        console.log('📊 Getting monthly report data...');
        
        try {
            const year = document.getElementById('monthlyUnitYearSelect') ? 
                parseInt(document.getElementById('monthlyUnitYearSelect').value) : new Date().getFullYear();
            
            const response = await fetch(`/admin/energy-data?type=monthly&year=${year}`);
            
            if (response.ok) {
                const result = await response.json();

                if (result.success && result.data && result.data.length > 0) {
                    return processMonthlyDataWithBalance(result.data, year);
                }
            }
        } catch (error) {
            console.error('Error getting monthly data:', error);
        }
        
        // Return empty array - will be handled by fallback
        return [];
    }

    // Process monthly data with proper balance calculation
    function processMonthlyDataWithBalance(apiData, year) {
        const processedData = [];
        let tempBalance = currentBalance; // Start with current balance
        
        apiData.forEach((item, index) => {
            const unitConsumed = item.total_units || 0;
            const amount = calculateConsumption(unitConsumed);
            const date = `${year}-${item.month.toString().padStart(2, '0')}-01`;
            
            let rechargeAmount = 0;
            
            // Process consumption
            if (unitConsumed > 0) {
                if (tempBalance >= amount) {
                    tempBalance -= amount;
                } else {
                    tempBalance -= amount;
                }
                
                // Check for auto-recharge
                rechargeAmount = checkAutoRecharge(date);
                if (rechargeAmount > 0) {
                    tempBalance += rechargeAmount;
                }
            }
            
            processedData.push({
                srNo: index + 1,
                date: date,
                unitConsumed: unitConsumed,
                amount: amount,
                balance: Math.max(0, tempBalance),
                recharge: rechargeAmount,
                actualBalance: tempBalance
            });
        });
        
        return processedData.filter(item => item.unitConsumed > 0);
    }

    // Get complete report data
    async function getCompleteReportData() {
        console.log('📋 Getting complete report data...');
        
        const dailyData = await getDailyReportData();
        const monthlyData = await getMonthlyReportData();
        
        const combinedData = [...dailyData, ...monthlyData];
        
        // If no data from APIs, use sample data
        if (combinedData.length === 0) {
            return generateSampleDataWithBalance();
        }
        
        return combinedData;
    }

    // Generate comprehensive sample data with balance tracking
    function generateSampleDataWithBalance() {
        console.log('🎯 Generating sample data with balance tracking...');
        
        const sampleData = [];
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth();
        
        let runningBalance = currentBalance; // Start with current balance
        
        // Generate daily data for current month
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        
        for (let i = 0; i < daysInMonth; i++) {
            const unitConsumed = Math.floor(Math.random() * 15) + 5; // 5-20 units
            const amount = calculateConsumption(unitConsumed);
            const day = i + 1;
            const date = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            
            let rechargeAmount = 0;
            
            // Process consumption
            if (unitConsumed > 0) {
                runningBalance -= amount;
                
                // Check for auto-recharge
                if (runningBalance <= (rechargeSettings.m_recharge_amount * 0.2)) {
                    rechargeAmount = rechargeSettings.m_sanction_load;
                    runningBalance += rechargeAmount;
                }
            }
            
            sampleData.push({
                srNo: i + 1,
                date: date,
                unitConsumed: unitConsumed,
                amount: amount,
                balance: Math.max(0, runningBalance),
                recharge: rechargeAmount,
                actualBalance: runningBalance
            });
        }
        
        console.log('Sample data generated with', sampleData.length, 'records');
        console.log('Final running balance:', runningBalance);
        return sampleData;
    }

    // Updated Excel download with balance tracking
    async function downloadExcelReportWithBalance() {
        console.log('📊 Generating Excel report with balance tracking...');

        try {
            // Prepare data for Excel
            const excelData = reportData.map((item, index) => {

                const currentBalanceToday = currentBalance; // from your summary
                const lastReportedBalance = reportData[reportData.length - 1].balance;

                let newRecharge = 0;

                // Apply recharge ONLY on last row (today)
                if (index === reportData.length - 1) {
                    newRecharge = currentBalanceToday - lastReportedBalance;
                }

                console.log(
                    `Row ${item.srNo}: Today = ${currentBalanceToday}, Last Balance = ${lastReportedBalance}, Recharge = ${newRecharge}`
                );

                return {
                    'Sr.No': item.srNo,
                    'Date': formatDate(item.date),
                    'Unit Consumed (kWh)': item.unitConsumed.toFixed(2),
                    'Amount (Rs.)': item.amount.toFixed(2),
                    'Balance (Rs.)': item.balance.toFixed(2),
                    'Recharge (Rs.)': newRecharge.toFixed(2),
                    'Remarks': newRecharge > 0 ? 'Auto Recharge' : 'Consumption'
                };
            });

            // Add summary section
            excelData.push(
                { 'Sr.No': '', 'Date': '', 'Unit Consumed (kWh)': '', 'Amount (Rs.)': '', 'Balance (Rs.)': '', 'Recharge (Rs.)': '', 'Remarks': '' },
                { 'Sr.No': '', 'Date': 'SUMMARY', 'Unit Consumed (kWh)': '', 'Amount (Rs.)': '', 'Balance (Rs.)': '', 'Recharge (Rs.)': '', 'Remarks': '' },
                { 'Sr.No': '', 'Date': 'Current Balance', 'Unit Consumed (kWh)': '', 'Amount (Rs.)': '', 'Balance (Rs.)': currentBalance.toFixed(2), 'Recharge (Rs.)': '', 'Remarks': '' },
                { 'Sr.No': '', 'Date': 'Total Consumption', 'Unit Consumed (kWh)': reportData.reduce((sum, item) => sum + item.unitConsumed, 0).toFixed(2), 'Amount (Rs.)': reportData.reduce((sum, item) => sum + item.amount, 0).toFixed(2), 'Balance (Rs.)': '', 'Recharge (Rs.)': '', 'Remarks': '' },
                { 'Sr.No': '', 'Date': 'Total Recharge', 'Unit Consumed (kWh)': '', 'Amount (Rs.)': '', 'Balance (Rs.)': '', 'Recharge (Rs.)': reportData.reduce((sum, item) => sum + item.recharge, 0).toFixed(2), 'Remarks': '' }
            );

            // Create workbook
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);

            XLSX.utils.book_append_sheet(wb, ws, 'Energy Report');

            const fileName = `Energy_Report_${selectedDownloadType}_${new Date().toISOString().split('T')[0]}.xlsx`;

            XLSX.writeFile(wb, fileName);

            console.log('✅ Excel file downloaded successfully');

        } catch (error) {
            console.error('Error generating Excel:', error);
            throw new Error('Failed to generate Excel file: ' + error.message);
        }
    }

    // Updated CSV download with balance tracking + recharge difference logic
    async function downloadCSVReportWithBalance() {
        console.log('📊 Generating CSV with correct recharge logic...');

        try {
            let previousBalance = null;

            // STEP 1 — Create processed report with NEW recharge logic
            const processedReport = reportData.map((item, index) => {
                let recharge = 0;

                if (previousBalance !== null) {
                    // Recharge happens ONLY when balance increases
                    if (item.balance > previousBalance) {
                        recharge = item.balance - previousBalance;
                    }
                }

                previousBalance = item.balance;

                return {
                    ...item,
                    recharge: recharge,
                    remarks: recharge > 0 ? "Recharge" : "Consumption"
                };
            });

            // STEP 2 — CSV Headers
            const headers = [
                'Sr.No',
                'Date',
                'Unit Consumed (kWh)',
                'Amount (Rs.)',
                'Balance (Rs.)',
                'Recharge (Rs.)',
                'Remarks'
            ];

            // STEP 3 — Build CSV rows
            const csvRows = [
                headers.join(','),
                ...processedReport.map(row => [
                    row.srNo,
                    `"${formatDate(row.date)}"`,
                    row.unitConsumed.toFixed(2),
                    row.amount.toFixed(2),
                    row.balance.toFixed(2),
                    row.recharge.toFixed(2),
                    `"${row.remarks}"`
                ].join(','))
            ];

            // STEP 4 — Summary Row
            csvRows.push(
                ',,,,,,',
                `"SUMMARY",,,,,,`,
                `"Current Balance",,,,${currentBalance.toFixed(2)},`,
                `"Total Consumption",${processedReport.reduce((s,i)=>s+i.unitConsumed,0).toFixed(2)},${processedReport.reduce((s,i)=>s+i.amount,0).toFixed(2)},,,`,
                `"Total Recharge",,,,,${processedReport.reduce((s,i)=>s+i.recharge,0).toFixed(2)},`
            );

            // STEP 5 — Convert to Blob
            const csvContent = csvRows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `Energy_Report_${selectedDownloadType}_${new Date().toISOString().split('T')[0]}.csv`;

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            console.log("✅ CSV downloaded with correct recharge values");

        } catch (error) {
            console.error("CSV Generation Error:", error);
        }
    }

    // PDF download function - UPDATED with correct RECHARGE LOGIC
    async function downloadPDFReportWithBalance() {
        console.log('📄 Generating PDF report with balance tracking...');

        try {
            if (typeof jspdf === 'undefined' && typeof window.jspdf === 'undefined') {
                console.error('PDF library not loaded');
                await downloadCSVReportWithBalance();
                return;
            }

            const jsPDF = window.jspdf.jsPDF;
            const doc = new jsPDF();

            // Title
            doc.setFontSize(16);
            doc.setTextColor(0, 46, 110);
            doc.text(`Energy Consumption Report`, 105, 15, { align: 'center' });

            // Subtitle
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(
                `${selectedDownloadType.charAt(0).toUpperCase() + selectedDownloadType.slice(1)} Report - Generated on: ${new Date().toLocaleString()}`,
                105, 22, { align: 'center' }
            );

            // Current balance info
            doc.text(
                `Current Balance: Rs. ${currentBalance.toFixed(2)} | Recharge Amount: Rs. ${rechargeSettings?.m_sanction_load || 0} | Unit Rate: Rs. ${rechargeSettings?.unit_rate}/kWh`,
                105, 28, { align: 'center' }
            );

            // --------------------------
            // 🔥 Recharge Calculation Logic
            // --------------------------
            const currentBalanceToday = currentBalance;
            const lastReportedBalance = reportData[reportData.length - 1].balance;

            // Prepare PDF table data with correct recharge logic
            const tableData = reportData.map((item, index) => {

                let newRecharge = 0;

                // Apply recharge ONLY on last report row (today)
                if (index === reportData.length - 1) {
                    newRecharge = currentBalanceToday - lastReportedBalance;
                }

                return [
                    item.srNo.toString(),
                    formatDate(item.date),
                    item.unitConsumed.toFixed(2) + ' kWh',
                    'Rs. ' + item.amount.toFixed(2),
                    'Rs. ' + item.balance.toFixed(2),
                    'Rs. ' + newRecharge.toFixed(2),
                    newRecharge > 0 ? 'Auto Recharge' : 'Consumption'
                ];
            });

            // --------------------------
            // PDF Table Rendering
            // --------------------------
            if (typeof doc.autoTable !== 'undefined') {
                doc.autoTable({
                    startY: 35,
                    head: [['Sr.No', 'Date', 'Units Consumed', 'Amount', 'Balance', 'Recharge', 'Remarks']],
                    body: tableData,
                    headStyles: {
                        fillColor: [0, 46, 110],
                        textColor: 255,
                        fontStyle: 'bold'
                    },
                    styles: {
                        fontSize: 8,
                        cellPadding: 3
                    },
                    margin: { top: 35 }
                });

                // Summary
                const finalY = doc.lastAutoTable.finalY + 10;
                doc.setFontSize(10);
                doc.setTextColor(0, 46, 110);
                doc.text(`Current Available Balance: Rs. ${currentBalance.toFixed(2)}`, 105, finalY, { align: 'center' });

                doc.text(
                    `Total Consumption: ${reportData.reduce((sum, item) => sum + item.unitConsumed, 0).toFixed(2)} kWh | Total Amount: Rs. ${reportData.reduce((sum, item) => sum + item.amount, 0).toFixed(2)}`,
                    105, finalY + 6, { align: 'center' }
                );
            }

            // Save PDF
            const fileName = `Energy_Report_${selectedDownloadType}_${new Date().toISOString().split('T')[0]}.pdf`;
            doc.save(fileName);

            console.log('✅ PDF downloaded successfully');

        } catch (error) {
            console.error('Error generating PDF:', error);
            try {
                await downloadCSVReportWithBalance();
            } catch (fallbackError) {
                throw new Error('Failed to generate PDF and CSV fallback: ' + error.message);
            }
        }
    }

    // Manual recharge function (for testing)
    function manualRecharge(amount) {
        processRecharge(new Date().toISOString().split('T')[0], amount);
        showSuccessMessage(`Manual recharge of Rs. ${amount} successful!`);
    }

    // Export functions for manual testing
    window.manualRecharge = manualRecharge;
    window.getCurrentBalance = () => currentBalance;
    window.getBalanceHistory = () => balanceHistory;

    // Utility function to format dates
    function formatDate(dateString) {
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        } catch (error) {
            return dateString;
        }
    }

    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success success-alert';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2 fs-5"></i>
                <div>
                    <strong>Success!</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger success-alert';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2 fs-5"></i>
                <div>
                    <strong>Error!</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
</script>




</body>

</html>
