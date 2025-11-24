<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DG SET MONITORING SYSTEM</title>
    <link rel="stylesheet" href="{{url('backend/assets/css/admin.css')}}">
    <link rel="stylesheet" href="{{url('backend/assets/css/admin-all-min.css')}}">
    <link rel="stylesheet" href="{{url('backend/assets/css/admin-bootstrap-min.css')}}">
    <link rel="stylesheet" href="{{url('/css/admin-sites.css')}}" class="rel">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="{{url('backend/assets/js/admin-bootstrap.bundle.js')}}"></script>
    <script src="{{url('backend/assets/js/adminCDN.js')}}"></script>
    <!-- jQuery (if not already loaded) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- PDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<style>
.pdf-wrapper {
    width: 1400px;
    /* Increased width for more content */
    padding: 30px;
    background: #fff;
    color: #000;
    font-size: 14px;
    font-family: 'Arial', sans-serif;
    overflow-x: auto;
    word-break: break-word;
}

.pdf-wrapper h3 {
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
    white-space: pre-line;
    /* Supports \n in innerText */
}

.pdf-wrapper table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    page-break-inside: auto;
    margin-top: 20px;
}

.pdf-wrapper thead {
    background: #f0f0f0;
}

.pdf-wrapper th,
.pdf-wrapper td {
    border: 1px solid #333;
    padding: 6px 8px;
    font-size: 12px;
    text-align: left;
    word-wrap: break-word;
}

.pdf-wrapper tr {
    page-break-inside: avoid;
    page-break-after: auto;
}

.toggle-btn-container {
    display: flex;
    width: 200px;
    height: 40px;
    border-radius: 20px;
    background-color: #e9ecef;
    position: relative;
    cursor: pointer;
    overflow: hidden;
}

.toggle-option {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    z-index: 2;
    transition: color 0.3s;
    color: #6c757d;
}

.toggle-option.active {
    color: white;
}

.toggle-slider {
    position: absolute;
    width: 50%;
    height: 100%;
    background-color: #198754;
    border-radius: 20px;
    transition: transform 0.3s ease;
}

/* position the slider for auto or manual */
.toggle-slider.auto {
    transform: translateX(0);
}

.toggle-slider.manual {
    transform: translateX(100%);
}
</style>



<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container-fluid">
            <!-- Logo on the left -->
            <a class="navbar-brand" href="#">
                <img src="https://sochiot.com/wp-content/uploads/2022/04/sochiotlogo-re-e1688377669450.png" alt="Logo"
                    width="120" height="40" class="logo-white">
            </a>

            <!-- Centered title -->
            <div class="navbar-brand mx-auto">
                <span class="d-none d-md-inline">Energy Monitoring System</span>
                <span class="d-md-none">DGMS</span>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <div class="nav-buttons">
                            <div class="refresh-time-box text-light">
                                <i class="fas fa-clock me-1"></i>
                                <span id="lastRefreshTime">
                                    {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                </span>
                            </div>

                            <form id="admin-logout-form" action="{{ route('admin.logout.submit') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>

                            <button class="btn btn-outline-light btn-logout"
                                onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </button>

                            <button class="btn btn-outline-light btn-refresh" onclick="handleRefresh()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>

                            <button class="btn btn-outline-light btn-download" id="downloadPdf">
                                <i class="fas fa-download me-1"></i> PDF
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Loader (Initially Hidden) -->
    <div id="loader" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
    z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="container">
        <!-- Main Dashboard -->
        <div class="dashboard-container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="text-primary">Site Overview</h3>
            </div>

            <!-- Filter Controls -->
            <div class="filter-controls">
                <div class="filter-group">
                    <select class="form-select form-select-sm filter-select" id="bankSelect">
                        <option value="" selected>Select Client_Name</option>
                        @php
                        $uniqueGenerators = collect($siteData)
                        ->map(fn($site) => json_decode($site->data)->generator)
                        ->unique();
                        @endphp

                        @foreach($uniqueGenerators as $generator)
                        <option value="{{ $generator }}">{{ $generator }}</option>
                        @endforeach
                    </select>

                    <select class="form-select form-select-sm filter-select" id="locationSelect">
                        <option value="" selected>Select Location</option>
                        @php
                        $uniqueGroups = collect($siteData)
                        ->map(fn($site) => json_decode($site->data)->group)
                        ->unique();
                        @endphp
                        @foreach($uniqueGroups as $group)
                        <option value="{{$group}}">{{$group}}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-primary btn-sm" id="resetFilters">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Sites Table -->
            <div class="table-responsive" id="siteTable">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>S.No</th>
                            <th>Site Name</th>
                            <th>Supply Status</th>
                            <th>Connection Status </th>
                            <th>Client Name</th>
                            <th>Location</th>
                            <th>Meter Number</th>
                            <th>KWH</th>
                            <th>Balance Grid</th>
                            <th>RMS Status</th>
                            <th>Meter Status</th>
                            <th>Setting</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $i=1; @endphp
                        @foreach ($siteData as $site)
                        @php
                        $sitejsonData = json_decode($site->data, true);
                        $updatedAt = null;
                        $isRecent = false;
                        $formattedUpdatedAt = 'N/A';

                        if (!empty($site->updatedAt) && $site->updatedAt !== 'N/A') {
                        try {
                        $updatedAt = Carbon\Carbon::parse($site->updatedAt)->timezone('Asia/Kolkata');
                        $now = Carbon\Carbon::now('Asia/Kolkata');
                        $isRecent = $updatedAt->diffInHours($now) < 24; $formattedUpdatedAt=$updatedAt->format("d M Y
                            h:i A");
                            } catch (\Exception $e) {
                            \Log::error('Date Parsing Error: ' . $e->getMessage());
                            }
                            }

                            $gatewayStatus = $isRecent ? 'online' : 'offline';
                            $controllerStatus = $isRecent ? 'online' : 'offline';
                            @endphp

                            <tr class="site-row" data-site-id="{{ $site->id }}"
                                data-bank="{{ $sitejsonData['generator'] ?? '' }}"
                                data-location="{{ $sitejsonData['group'] ?? '' }}">
                                <td>{{ $i }}</td>


                                <td class="site-name-cell">
                                    <a href="{{ url('admin/sites/'.$site->slug . '?role=admin') }}"
                                        class="site-name-link" style="text-decoration: none; font-weight: bold;"
                                        target="_blank">
                                        {{ $site->site_name }}
                                    </a>
                                </td>

                                <?php
                                    $readOn = isset($sitejsonData['parameters']) ? floatval($sitejsonData['parameters']) : 0;
                                    $fuelMd = $sitejsonData['parameters']['number_of_starts']['md'] ?? null;
                                    $fuelKey = $sitejsonData['parameters']['number_of_starts']['add'] ?? null;
                                    $addValue = 0;

                                    foreach ($eventData as $event) {
                                        $eventArray = $event instanceof \ArrayObject ? $event->getArrayCopy() : (array) $event;

                                        if ($fuelMd && isset($eventArray['module_id']) && $eventArray['module_id'] == $fuelMd) {
                                            if ($fuelKey && array_key_exists($fuelKey, $eventArray)) {
                                                $addsupplyValue = $eventArray[$fuelKey];
                                            }
                                            break;
                                        }
                                    }
                                    // dd($addValue);
                                ?>
                                <td>
                                    <span class="controller-status-text {{ $addsupplyValue }}"><strong>{{ $addsupplyValue }}
                                        </strong></span>
                                </td>

                                <td class="status-cell">
                                    <?php
        $readOn = isset($sitejsonData['readOn']) ? floatval($sitejsonData['readOn']) : 0;
        $fuelMd = $sitejsonData['readOn']['md'] ?? null;
        $fuelKey = $sitejsonData['readOn']['add'] ?? null;
        $addValue = '_';

        foreach ($eventData as $event) {
            $eventArray = $event instanceof \ArrayObject ? $event->getArrayCopy() : (array) $event;

            if ($fuelMd && isset($eventArray['module_id']) && $eventArray['module_id'] == $fuelMd) {
                if ($fuelKey && array_key_exists($fuelKey, $eventArray)) {
                    $addValue = $eventArray[$fuelKey];
                }
                break;
            }
        }

        // CONDITION BASED COLOR + TEXT
        $showText = "Unknown";
        $color = "gray";

        if (strtolower($addValue) === "high") {
            $showText = "Connected";
            $color = "green";
        } elseif (strtolower($addValue) === "low") {
            $showText = "Disconnected";
            $color = "red";
        }
    ?>

                                    <div class="fuel-container">
                                        <div class="fuel-indicator">
                                            <div class="fuel-level"></div>

                                            <!-- Status Text -->
                                            <span class="fuel-indicator" style="padding:6px 10px; border-radius:5px;
                         background:<?= $color ?>; color:white; font-weight:bold;">
                                                <?= $showText ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>


                                <td>{{ $sitejsonData['generator'] ?? 'N/A' }}</td>
                                <td>{{ $sitejsonData['group'] ?? 'N/A' }}</td>
                                <td>{{ $sitejsonData['serial_number'] ?? 'N/A' }}</td>

                                <!-- <td>
                                    @php
                                    $increased_running_hours = DB::table('running_hours')
                                        ->where('site_id', $site->id)
                                        ->first();

                                    $increaseRunningHours = (float) ($increased_running_hours->increase_running_hours ?? 0);

                                    $addValue = 0;
                                    $key = $sitejsonData['running_hours']['add'] ?? null;
                                    $md = $sitejsonData['running_hours']['md'] ?? null;

                                    if ($key && $md) {
                                        foreach ($eventData as $event) {
                                            // $event is already an array
                                            if (isset($event['module_id']) && $event['module_id'] == $md) {
                                                if (array_key_exists($key, $event)) {
                                                    $addValue = (float) $event[$key];
                                                }
                                                break;
                                            }
                                        }
                                    }

                                    $increaseMinutes = $sitejsonData['running_hours']['increase_minutes'] ?? 1;
                                    $inc_addValue = $increaseMinutes > 0 ? $addValue / $increaseMinutes : $addValue;

                                    // Total running hours including DB value
                                    $inc_addValueFormatted = $inc_addValue + $increaseRunningHours;

                                    // Prevent negative value
                                    if ($inc_addValueFormatted < 0) { $inc_addValueFormatted=0; } 
                                    $totalMinutes=round($inc_addValueFormatted * 60);
                                        $hours=floor($totalMinutes / 60); $minutes=$totalMinutes % 60; @endphp {{ $hours }} hrs
                                        {{ $minutes }} mins </td> -->


                                <?php
                                    $setting = $rechargeSetting->where('m_site_id', $site->id)->first();
                                ?>

                                <td>{{ $setting->kwh ?? '-' }}</td>

                                <td class="rechargeStatus" data-site-id="{{ $site->id }}"
                                    data-amount="{{ $rechargeSetting[$site->id]->m_recharge_amount ?? 0 }}">
                                    {{ $rechargeSetting[$site->id]->m_recharge_amount ?? 0 }}
                                </td>

                                <td class="status-cell">
                                    <div class="status-dot controller-dot"></div>
                                </td>

                                <td class="status-cell">
                                    <div class="status-dot gateway-dot"></div>
                                </td>

                                <td class="setting-col text-center" style="cursor:pointer;">
                                    @php
                                    $setting = $rechargeSetting[$site->id] ?? null;
                                    @endphp
                                    <!-- Modal trigger ko alag span mein rakho -->
                                    <span data-bs-toggle="modal" data-bs-target="#settingsModal{{ $site->id }}">
                                        <i class="fa-solid fa-gear me-2 text-primary"></i>
                                        Recharge
                                    </span>

                                    <!-- Modal with normal backdrop -->
                                    <div class="modal fade" id="settingsModal{{ $site->id }}" tabindex="-1"
                                        aria-labelledby="settingsModalLabel{{ $site->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-md modal-dialog-centered">
                                            <div class="modal-content rounded-4 shadow-lg border-0 overflow-hidden">

                                                <!-- Modal Header -->
                                                <div class="modal-header bg-gradient bg-primary text-white py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fa-solid fa-bolt me-2 fs-5"></i>
                                                        <h5 class="modal-title mb-0 fw-bold"
                                                            id="settingsModalLabel{{ $site->id }}">
                                                            Recharge & Load Settings</h5>
                                                    </div>
                                                    <button type="button" class="btn-close btn-close-white shadow-none"
                                                        data-bs-dismiss="modal"></button>
                                                </div>

                                                <!-- Modal Body -->
                                                <div class="modal-body p-4">
                                                    <form id="rechargeForm" method="POST"
                                                        action="{{ route('admin.recharge.store') }}" class="px-2">
                                                        @csrf

                                                        <!-- Hidden Site ID -->
                                                        <input type="hidden" name="m_site_id" id="m_site_id"
                                                            value="{{ $site->id ?? '' }}">

                                                        <!-- Recharge Section -->
                                                        <div class="card border-0 bg-light mb-4">
                                                            <div class="card-header bg-transparent border-0 py-2">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fa-solid fa-wallet text-primary me-2"></i>
                                                                    <h6 class="mb-0 fw-bold text-primary">Recharge
                                                                        Details</h6>
                                                                </div>
                                                            </div>
                                                            <div class="card-body pt-0">
                                                                <div class="row mb-3 align-items-center">
                                                                    <label
                                                                        class="col-sm-5 col-form-label fw-medium">Recharge
                                                                        Amount:</label>
                                                                    <div class="col-sm-7">
                                                                        <div class="input-group input-group-sm">
                                                                            <span
                                                                                class="input-group-text bg-primary text-white border-primary">
                                                                                <i
                                                                                    class="fa-solid fa-indian-rupee-sign"></i>
                                                                            </span>
                                                                            <!-- <input type="text" name="m_recharge_amount" id="m_recharge_amount_{{ $site->id }}"
                                                                            class="form-control border-primary"
                                                                            value="{{ $rechargeSetting[$site->id]->m_recharge_amount ?? '' }}"
                                                                            placeholder="Enter amount"> -->
                                                                            <!-- <input type="text" 
                                                                                name="m_recharge_amount" 
                                                                                id="m_recharge_amount_{{ $site->id }}" 
                                                                                class="form-control border-primary" 
                                                                                value="{{ $rechargeSetting[$site->id]->m_recharge_amount ?? '' }}" 
                                                                                placeholder="Enter amount"
                                                                                data-site-id="{{ $site->id }}"> -->
                                                                            <input type="text" name="m_recharge_amount"
                                                                                id="m_recharge_amount_{{ $site->id }}"
                                                                                class="form-control border-primary"
                                                                                value="" {{-- ALWAYS BLANK --}}
                                                                                placeholder="Enter amount"
                                                                                data-site-id="{{ $site->id }}">



                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Mains Details -->
                                                        <div class="card border-0 bg-light mb-4">
                                                            <div class="card-header bg-transparent border-0 py-2">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fa-solid fa-plug text-success me-2"></i>
                                                                    <h6 class="mb-0 fw-bold text-success">Mains Details
                                                                    </h6>
                                                                </div>
                                                            </div>
                                                            <div class="card-body pt-0">
                                                                <div class="row mb-3 align-items-center">
                                                                    <label
                                                                        class="col-sm-5 col-form-label fw-medium">Fixed
                                                                        Charge:</label>
                                                                    <div class="col-sm-7">
                                                                        <div class="input-group input-group-sm">
                                                                            <span
                                                                                class="input-group-text bg-success text-white border-success">
                                                                                <i
                                                                                    class="fa-solid fa-indian-rupee-sign"></i>
                                                                            </span>
                                                                            <input type="text" name="m_fixed_charge"
                                                                                class="form-control border-success"
                                                                                placeholder="Fixed Charge"
                                                                                value="{{ $rechargeSetting[$site->id]->m_fixed_charge ?? '' }}">
                                                                            <span
                                                                                class="input-group-text bg-success text-white border-success">Per
                                                                                Month/kW</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-3 align-items-center">
                                                                    <label
                                                                        class="col-sm-5 col-form-label fw-medium">Unit
                                                                        Charge:</label>
                                                                    <div class="col-sm-7">
                                                                        <div class="input-group input-group-sm">
                                                                            <span
                                                                                class="input-group-text bg-success text-white border-success">
                                                                                <i
                                                                                    class="fa-solid fa-indian-rupee-sign"></i>
                                                                            </span>
                                                                            <input type="text" name="m_unit_charge"
                                                                                class="form-control border-success"
                                                                                placeholder="Unit Charge"
                                                                                value="{{ $rechargeSetting[$site->id]->m_unit_charge ?? '' }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-2 align-items-center">
                                                                    <label
                                                                        class="col-sm-5 col-form-label fw-medium">Sanction
                                                                        Load:</label>
                                                                    <div class="col-sm-7">
                                                                        <div class="input-group input-group-sm">
                                                                            <span
                                                                                class="input-group-text bg-success text-white border-success">
                                                                                <i class="fa-solid fa-bolt"></i>
                                                                            </span>
                                                                            <input type="text" name="m_sanction_load"
                                                                                class="form-control border-success"
                                                                                placeholder="Sanction Load"
                                                                                value="{{ $rechargeSetting[$site->id]->m_sanction_load ?? '' }}">
                                                                            <span
                                                                                class="input-group-text bg-success text-white border-success">kW</span>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- DG Details -->
                                                        <div class="card border-0 bg-light mb-3">
                                                            <div class="card-header bg-transparent border-0 py-2">
                                                                <div class="d-flex align-items-center">
                                                                    <i
                                                                        class="fa-solid fa-gas-pump text-danger me-2"></i>
                                                                    <h6 class="mb-0 fw-bold text-danger">DG Details</h6>
                                                                </div>
                                                            </div>
                                                            <div class="card-body pt-0">
                                                                <div class="row mb-3 align-items-center">
                                                                    <label
                                                                        class="col-sm-5 col-form-label fw-medium">Fixed
                                                                        Charge:</label>
                                                                    <div class="col-sm-7">
                                                                        <div class="input-group input-group-sm">
                                                                            <span
                                                                                class="input-group-text bg-danger text-white border-danger">
                                                                                <i
                                                                                    class="fa-solid fa-indian-rupee-sign"></i>
                                                                            </span>
                                                                            <input type="text" name="dg_fixed_charge"
                                                                                class="form-control border-danger"
                                                                                placeholder="Fixed Charge"
                                                                                value="{{ $rechargeSetting[$site->id]->dg_fixed_charge ?? '' }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-3 align-items-center">
                                                                    <label
                                                                        class="col-sm-5 col-form-label fw-medium">Unit
                                                                        Charge:</label>
                                                                    <div class="col-sm-7">
                                                                        <div class="input-group input-group-sm">
                                                                            <span
                                                                                class="input-group-text bg-danger text-white border-danger">
                                                                                <i
                                                                                    class="fa-solid fa-indian-rupee-sign"></i>
                                                                            </span>
                                                                            <input type="text" name="dg_unit_charge"
                                                                                class="form-control border-danger"
                                                                                placeholder="Unit Charge"
                                                                                value="{{ $rechargeSetting[$site->id]->dg_unit_charge ?? '' }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-2 align-items-center">
                                                                    <label
                                                                        class="col-sm-5 col-form-label fw-medium">Sanction
                                                                        Load:</label>
                                                                    <div class="col-sm-7">
                                                                        <div class="input-group input-group-sm">
                                                                            <span
                                                                                class="input-group-text bg-danger text-white border-danger">
                                                                                <i class="fa-solid fa-bolt"></i>
                                                                            </span>
                                                                            <input type="text" name="dg_sanction_load"
                                                                                class="form-control border-danger"
                                                                                placeholder="Sanction Load"
                                                                                value="{{ $rechargeSetting[$site->id]->dg_sanction_load ?? '' }}">
                                                                            <span
                                                                                class="input-group-text bg-danger text-white border-danger">kW</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <?php
                                                            $readOn = isset($sitejsonData['connect']) ? floatval($sitejsonData['connect']) : 0;
                                                            $connectMd = $sitejsonData['connect']['md'] ?? null;
                                                            $connectAdd = $sitejsonData['connect']['add'] ?? null;
                                                            $disconnectBtnValue = 0;
                                                     
                                                            foreach ($eventData as $event) {
                                                                $eventArray = $event instanceof \ArrayObject ? $event->getArrayCopy() : (array) $event;

                                                                if ($connectMd && isset($eventArray['module_id']) && $eventArray['module_id'] == $connectMd) {
                                                                    if ($connectAdd && array_key_exists($connectAdd, $eventArray)) {
                                                                        $disconnectBtnValue = $eventArray[$connectAdd];
                                                                    }
                                                                    break;
                                                                }
                                                            }
                                                        ?>

                                                        <?php
                                                            $setting = $rechargeSetting->where('m_site_id', $site->id)->first();
                                                        ?>

                                                        <!-- toggle -->
                                                        <div class="container mt-5">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <!-- Toggle -->
                                                                <div class="toggle-btn-container">
                                                                    <div
                                                                        class="toggle-slider {{ $rechargeSetting[$site->id]->status ?? 'auto' }}">
                                                                    </div>
                                                                    <div class="toggle-option {{ ($rechargeSetting[$site->id]->status ?? 'auto') === 'auto' ? 'active' : '' }}"
                                                                        data-value="auto">
                                                                        Auto
                                                                        <input type="hidden" name="status" value="auto">
                                                                    </div>
                                                                    <div class="toggle-option {{ ($rechargeSetting[$site->id]->status ?? 'auto') === 'manual' ? 'active' : '' }}"
                                                                        data-value="manual">
                                                                        Manual
                                                                        <input type="hidden" name="status"
                                                                            value="manual">
                                                                    </div>
                                                                </div>

                                                                <!-- Connect Button -->
                                                                <button type="button"
                                                                    class="btn btn-success btn-sm connectBtn"
                                                                    data-site-id="1">
                                                                    <input type="hidden" name="moduleId" value="<?= htmlspecialchars($connectMd) ?>">
                                                                    <input type="hidden" name="cmdField"
                                                                        value="<?= htmlspecialchars($connectAdd) ?>">
                                                                    <input type="hidden" name="cmdArg" value="0">
                                                                    <input type="hidden" name="argValue" value="1">
                                                                    Connect
                                                                </button>

                                                                <!-- Disconnect Button -->
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm disconnectBtn"
                                                                    data-site-id="1">
                                                                    <input type="hidden" name="moduleId" value="<?= htmlspecialchars($connectMd) ?>">
                                                                    <input type="hidden" name="cmdField"
                                                                        value="<?= htmlspecialchars($connectAdd) ?>">
                                                                    <input type="hidden" name="cmdArg" value="1">
                                                                    <input type="hidden" name="argValue" value="1">
                                                                    Disconnect
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Modal Footer -->
                                                        <div class="modal-footer bg-light py-3">
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm px-3"
                                                                data-bs-dismiss="modal">
                                                                <i class="fa-solid fa-xmark me-1"></i> Cancel
                                                            </button>
                                                            <div>

                                                                <div class="text-end">
                                                                    <button type="submit"
                                                                        class="btn btn-primary btn-sm">
                                                                        <i class="fa-solid fa-save me-1"></i> Submit
                                                                    </button>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </form>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @php $i++;
                            @endphp
                            @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    <script src="{{url('backend/assets/js/admin-jquery-3.6.0.min.js')}}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const storageKey = "recharge_settings";
        const fields = [
            "recharge_amount",
            "mains_fixed", "mains_unit", "mains_load",
            "dg_fixed", "dg_unit", "dg_load"
        ];

        // Load old data if exists
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            const data = JSON.parse(saved);
            fields.forEach(f => {
                if (data[f]) document.getElementById(f).value = data[f];
            });
        }

        // ✅ Save data as JSON and then clear inputs
        document.getElementById("connectBtn").addEventListener("click", function() {
            const obj = {};
            fields.forEach(f => obj[f] = document.getElementById(f).value.trim());

            // Save to localStorage
            localStorage.setItem(storageKey, JSON.stringify(obj, null, 2));
            console.log("Saved JSON:", JSON.stringify(obj, null, 2));

            // ✅ Clear all input fields after saving
            fields.forEach(f => document.getElementById(f).value = "");

            // Optional: success feedback
            alert("Recharge settings saved successfully!");
        });

        // ✅ Clear all data on Disconnect
        document.getElementById("disconnectBtn").addEventListener("click", function() {
            localStorage.removeItem(storageKey);
            fields.forEach(f => document.getElementById(f).value = "");
            console.log("LocalStorage cleared for", storageKey);
        });
    });
    </script>
    <script>
    function updateRefreshTime() {
        const now = new Date();
        const options = {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        const formattedTime = now.toLocaleString('en-IN', options);
        document.getElementById('lastRefreshTime').textContent = `Last refreshed: ${formattedTime}`;
    }

    function handleRefresh() {
        $('#loader').show();
        updateRefreshTime();
        setTimeout(() => {
            location.reload();
        }, 500);
    }

    setInterval(handleRefresh, 5 * 60 * 1000);

    document.addEventListener('DOMContentLoaded', updateRefreshTime);

    document.getElementById('downloadPdf').addEventListener('click', function() {
        document.getElementById('loader').style.display = 'block';

        const originalTable = document.getElementById('siteTable');
        const clonedTable = originalTable.cloneNode(true);

        // Remove hidden rows
        $(clonedTable).find('tr').each(function() {
            if ($(this).css('display') === 'none') {
                $(this).remove();
            }
        });

        // Build wrapper
        const wrapper = document.createElement('div');
        wrapper.classList.add('pdf-wrapper');

        const now = new Date();
        const formattedDateTime = now.toLocaleString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        const heading = document.createElement('h3');
        heading.innerText = `DGMS Site Overview Report\nGenerated on: ${formattedDateTime}`;
        wrapper.appendChild(heading);
        wrapper.appendChild(clonedTable);

        document.body.appendChild(wrapper); // attach to DOM for rendering

        const opt = {
            margin: [10, 10, 10, 10],
            filename: 'DGMS_Site_Overview_' + now.toISOString().slice(0, 10) + '.pdf',
            image: {
                type: 'jpeg',
                quality: 1
            }, // highest quality
            html2canvas: {
                scale: 4, // very high resolution
                useCORS: true,
                scrollX: 0,
                scrollY: 0
            },
            jsPDF: {
                unit: 'px',
                format: [1400, 1000], // wider than A3
                orientation: 'landscape'
            }
        };

        // Delay helps rendering accuracy
        setTimeout(() => {
            html2pdf()
                .set(opt)
                .from(wrapper)
                .save()
                .then(() => {
                    document.getElementById('loader').style.display = 'none';
                    document.body.removeChild(wrapper);
                })
                .catch((error) => {
                    document.getElementById('loader').style.display = 'none';
                    alert('PDF generation failed: ' + error.message);
                    document.body.removeChild(wrapper);
                });
        }, 300);
    });


    // Reset filters
    document.getElementById('resetFilters').addEventListener('click', function() {
        document.getElementById('bankSelect').value = '';
        document.getElementById('locationSelect').value = '';
        filterSites();
    });

    $(document).ready(function() {
        $('#bankSelect, #locationSelect').change(filterSites);

        // Initialize status dots
        updateStatusDots();
    });

    function filterSites() {
        const bankValue = $('#bankSelect').val().toLowerCase();
        const locationValue = $('#locationSelect').val().toLowerCase();

        $('.site-row').each(function() {
            const rowBank = $(this).data('bank').toString().toLowerCase();
            const rowLocation = $(this).data('location').toString().toLowerCase();

            const bankMatch = bankValue === '' || rowBank.includes(bankValue);
            const locationMatch = locationValue === '' || rowLocation.includes(locationValue);

            if (bankMatch && locationMatch) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function updateStatusDots() {
        let siteIds = [];

        $('.site-row').each(function() {
            siteIds.push($(this).data('site-id'));

            $(this).find('.gateway-dot, .controller-dot')
                .removeClass('ONLINE OFFLINE')
                .addClass('loading');

            // Don't change the controller status text
            // Don't show "Loading..."

            $(this).find('.site-name-link')
                .css('color', '#6c757d');
        });

        if (siteIds.length > 0) {
            $.ajax({
                url: '{{ route("admin.site.statuses") }}',
                method: 'POST',
                data: {
                    site_ids: siteIds,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $.each(data, function(siteId, statuses) {
                        let row = $('.site-row[data-site-id="' + siteId + '"]');

                        let dgStatus = (statuses.dg_status || '').replace(/['"]+/g, '');
                        let ctrlStatus = (statuses.controller_status || '').replace(/['"]+/g, '');

                        // Update dot status
                        row.find('.gateway-dot')
                            .removeClass('loading ONLINE OFFLINE')
                            .addClass(dgStatus);

                        row.find('.controller-dot')
                            .removeClass('loading ONLINE OFFLINE')
                            .addClass(ctrlStatus);

                        // Update site name color
                        let color = (ctrlStatus === 'ONLINE') ? 'green' : 'red';
                        row.find('.site-name-link').css('color', color);

                        // Show "—" only if DG is OFFLINE
                        if (dgStatus === 'OFFLINE') {
                            row.find('.controller-status-text').html(
                                // '<strong style="font-size: 1.3rem; font-weight: bold;">—</strong>'
                            );
                        }
                        // Else: do nothing, leave the ON/OFF text from Blade
                    });
                },
                error: function() {
                    $('.gateway-dot, .controller-dot')
                        .removeClass('loading ONLINE')
                        .addClass('OFFLINE');

                    $('.controller-status-text').html(
                        // '<strong style="font-size: 1.3rem; font-weight: bold;">—</strong>'
                    );

                    $('.site-name-link').css('color', 'red');
                }
            });
        }
    }

    $('#rechargeForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Recharge settings saved successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });

                $('#settingsModal1').modal('hide');
            },
            error: function(xhr) {
                let message = 'Error saving data!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: message,
                    confirmButtonText: 'OK'
                });
            }
        });
    });
    </script>

    <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {

        /* ---------------------------------------------------------
           ✅ MANUAL CONNECT / DISCONNECT BUTTON — NO CHANGE
        --------------------------------------------------------- */
        $(document).on('click', '.connectBtn, .disconnectBtn', function(e) {
            e.preventDefault();

            let $btn = $(this);
            let isConnect = $btn.hasClass('connectBtn');
            let actionType = isConnect ? 'connect' : 'disconnect';

            let siteId = $btn.data('site-id');
            let argValue = $btn.find('input[name="argValue"]').val();
            let moduleId = $btn.find('input[name="moduleId"]').val();
            let cmdArg = $btn.find('input[name="cmdArg"]').val();
            let cmdField = $btn.find('input[name="cmdField"]').val();

            Swal.fire({
                title: `Are you sure you want to ${actionType.toUpperCase()}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/start-process',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            site_id: siteId,
                            argValue,
                            moduleId,
                            cmdArg,
                            cmdField,
                            actionType
                        },
                        beforeSend: () => $btn.prop('disabled', true),
                        success: (response) => {
                            Swal.fire({
                                icon: 'success',
                                title: response.message
                            });
                        },
                        error: (xhr) => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong.'
                            });
                        },
                        complete: () => $btn.prop('disabled', false)
                    });
                }
            });
        });




        /* ---------------------------------------------------------
           ❗❗ REMOVE INPUT-BASED CONNECT/DISCONNECT
           Because you don't want input trigger!
        --------------------------------------------------------- */
        // ❌ Deleted old input event listener




        /* ---------------------------------------------------------
           ✅ NEW LOGIC:
           Recharge amount JO TABLE ME SHOW HO RAHA HAI
           US VALUE ke hisab se connect/disconnect chalega.
        --------------------------------------------------------- */

        document.querySelectorAll(".rechargeStatus").forEach(function(cell) {

            let siteId = cell.dataset.siteId;
            let value = parseFloat(cell.dataset.amount) || 0;

            // Positive → connect, Negative → disconnect
            let status = value > 0 ? 0 : 1;
            let actionType = status === 0 ? "connect" : "disconnect";

            // Correct button
            let btnSelector = status === 0 ?
                `.connectBtn[data-site-id="${siteId}"]` :
                `.disconnectBtn[data-site-id="${siteId}"]`;

            let $btn = $(btnSelector);

            if ($btn.length === 0) {
                console.error("Button not found for site:", siteId);
                return;
            }

            let moduleId = $btn.find('input[name="moduleId"]').val();
            let cmdField = $btn.find('input[name="cmdField"]').val();
            let cmdArg = $btn.find('input[name="cmdArg"]').val();

            console.log(`AUTO ${actionType.toUpperCase()} for Site: ${siteId}, Amount: ${value}`);

            fetch('{{ route('admin.trigger.connection.api') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status,
                            site_id: siteId,
                            moduleId,
                            cmdField,
                            cmdArg
                        })
                    })
                .then(res => res.json())
                .then(data => console.log("API Response:", data))
                .catch(err => console.error("API Error:", err));
        });

    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleSlider = document.querySelector('.toggle-slider');
        const toggleOptions = document.querySelectorAll('.toggle-option');

        toggleOptions.forEach(option => {
            option.addEventListener('click', function() {
                const selectedValue = option.getAttribute('data-value');

                toggleOptions.forEach(opt => opt.classList.remove('active'));

                option.classList.add('active');

                toggleSlider.className = 'toggle-slider ' + selectedValue;

                toggleOptions.forEach(opt => {
                    const input = opt.querySelector('input[type="hidden"]');
                    input.value = opt.classList.contains('active') ? selectedValue : '';
                });

                console.log('Mode changed to:', selectedValue);

                fetch('/admin/recharge-settings/update-status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            status: selectedValue
                        })
                    })
                    .then(res => res.json())
                    .then(data => console.log('Server response:', data))
                    .catch(err => console.error('Error:', err));
            });
        });
    });
    </script>

</body>

</html>