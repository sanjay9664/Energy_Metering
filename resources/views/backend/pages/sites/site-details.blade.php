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
</head>

<style>
    .status-box {
  background: #f9f9f9;
  border-radius: 10px;
  padding: 15px;
  box-shadow: 0px 2px 5px rgba(0,0,0,0.1);
}

</style>

<body>
    <div class="header-container">
        <a class="navbar-brand" href="#">
            <img src="https://sochiot.com/wp-content/uploads/2022/04/sochiotlogo-re-e1688377669450.png" alt="sochiot_Logo" class="logo-img" />
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
                                <th class="asset">
                                    Custom Name : {{ $sitejsonData->asset_name }}
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
                                    <strong>Grid:</strong> {{ $sitejsonData->brand }}
                                </td>
                                <td data-label="Capacity">
                                    <strong>Capacity:</strong> {{ $sitejsonData->capacity }}
                                    <strong>DG:</strong> {{ $sitejsonData->capacity }}
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
                <span class="status-box">waiting ...</span>
               
            </div>
        </td>

        <!-- Avg. Voltage / battery_voltage -->
        <td style="width:16%;">
            @php
                $param = $siteData['parameters']['battery_voltage'] ?? null;
                $value = isset($param['md']) ? floatval($param['md']) : null;
                $low   = isset($param['low']) ? floatval($param['low']) : null;
                $high  = isset($param['high']) ? floatval($param['high']) : null;

                if (!is_null($value) && !is_null($low) && !is_null($high)) {
                    $status = ($value >= $low && $value <= $high) ? 'normal' : 'abnormal';
                    $bgColor = $status === 'normal' ? 'green' : 'red';
                } else {
                    $status = 'abnormal';
                    $bgColor = 'red';
                }
            @endphp
            <div class="status-box" style="padding:10px; font-size:14px;">
                <p><strong>Grid_Unit</strong></p>
                <span class="status-box">waiting ...</span>
            </div>
        </td>

        <!-- Current L1 / oil_pressure -->
        <td style="width:16%;">
            @php
                $param = $siteData['parameters']['oil_pressure'] ?? null;
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
                <p><strong>DG_Unit</strong></p>
                <span class="status-box">waiting ...</span>
            </div>
        </td>

        <!-- Current L2 / oil_temperature -->
        <td style="width:16%;">
            @php
                $param = $siteData['parameters']['oil_temperature'] ?? null;
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
                <p><strong>Connection_Status</strong></p>
                <span class="status-box">waiting ...</span>
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
                                                <span class="parameter-value">{{ $addValue }} °C</span>
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
                                                <span class="parameter-value">{{ $addValue }} °C</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="parameter-box">
                                                <span class="parameter-label">Avg Current</span>
                                                <?php
                                                    $key = $sitejsonData->parameters->oil_pressure->add;
                                                    $addValue = '_';
                                                    foreach ($eventData as $event) {
                                                        $eventArray = $event->getArrayCopy();
                                                        if ($eventArray['module_id'] == $sitejsonData->parameters->oil_pressure->md) {
                                                            if (array_key_exists($key, $eventArray)) {
                                                                $addValue = number_format($eventArray[$key], 2);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                <span class="parameter-value">{{ $addValue }} psi</span>
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
                                                <span class="parameter-value">{{ $addValue }}</span>
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
                                                <span class="parameter-value">{{ $addValue }} V</span>
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
                                <th class="asset">
                                    Custom Name: {{ $sitejsonData->asset_name }}
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
                                    <strong>Grid:</strong> {{ $sitejsonData->brand }}
                                </td>
                                <td data-label="Capacity">
                                    <strong>DG:</strong> {{ $sitejsonData->capacity }}
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
                <span class="status-box">waiting ...</span>
            </div>
        </td>

        <!-- Avg. Voltage / battery_voltage -->
        <td style="width:16%;">
            @php
                $param = $siteData['parameters']['battery_voltage'] ?? null;
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
                <p><strong>Grid_Unit</strong></p>
                <span class="status-box">waiting ...</span>
            </div>
        </td>

        <!-- Current L1 / oil_pressure -->
        <td style="width:16%;">
            @php
                $param = $siteData['parameters']['oil_pressure'] ?? null;
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
                <p><strong>DG_Unit</strong></p>
                <span class="status-box">waiting ...</span>
            </div>
        </td>

        <!-- Current L2 / oil_temperature -->
        <td style="width:16%;">
            @php
                $param = $siteData['parameters']['oil_temperature'] ?? null;
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
                <p><strong>Connection_Status</strong></p>
                <span class="status-box">waiting ...</span>
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
                                                    <span class="parameter-value">{{ $addValue }} °C</span>
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
                                                    <span class="parameter-value">{{ $addValue }} °C</span>
                                                </div>
                                                                    </td>
                                                                    <td>
                                                                    <div class="parameter-box">
                                                    <span class="parameter-label">Avg Current</span>
                                                    <?php
                                                        $key = $sitejsonData->parameters->oil_pressure->add;
                                                        $addValue = '_';
                                                        foreach ($eventData as $event) {
                                                            $eventArray = $event->getArrayCopy();
                                                            if ($eventArray['module_id'] == $sitejsonData->parameters->oil_pressure->md) {
                                                                if (array_key_exists($key, $eventArray)) {
                                                                    $addValue = number_format($eventArray[$key], 2);
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                    <span class="parameter-value">{{ $addValue }} psi</span>
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
                                                    <span class="parameter-value">{{ $addValue }}</span>
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
                                                    <span class="parameter-value">{{ $addValue }} V</span>
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

<!-- old one  -->
<!-- <script>
    $(document).on('click', '.start-btn, .stop-btn, .auto-btn, .manual-btn', function(e) {
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
        }
  $('#current-mode').text(actionType.toUpperCase());

   
        let argValue = form.find('input[name="argValue"]').val();
        let moduleId = form.find('input[name="moduleId"]').val();
        let cmdField = form.find('input[name="cmdField"]').val();
        let cmdArg = form.find('input[name="cmdArg"]').val();

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
                        title: `${actionType.charAt(0).toUpperCase() + actionType.slice(1)}ed!`,
                        text: response.message
                    });
                    console.log('External Response:', response.external_response);
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

        // Show confirmation only for 'start' button
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
            // Directly call AJAX for other actions
            ajaxCall();
        }
    });
</script> -->

<!-- FINAL THIS IS WORKING NOW  -->
<!-- <script>
    $(document).on('click', '.start-btn, .stop-btn, .auto-btn, .manual-btn', function(e) {
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
        }

        // ✅ Only update #current-mode for auto or manual
        if (actionType === 'auto' || actionType === 'manual') {
            $('#current-mode').text(actionType.toUpperCase());
        }

        let argValue = form.find('input[name="argValue"]').val();
        let moduleId = form.find('input[name="moduleId"]').val();
        let cmdField = form.find('input[name="cmdField"]').val();
        let cmdArg = form.find('input[name="cmdArg"]').val();

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
                        title: `${actionType.charAt(0).toUpperCase() + actionType.slice(1)}ed!`,
                        text: response.message
                    });
                    console.log('External Response:', response.external_response);
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
</script> -->

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

</body>

</html>