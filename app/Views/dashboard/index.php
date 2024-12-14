<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div>
                <h4 class="header-title mb-3">Welcome !</h4>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-12">
            <div>
                <div class="card-box widget-inline">
                    <div class="row">
                        <div class="col-xl-3 col-sm-6 widget-inline-box">
                            <div class="text-center p-3">
                                <h2 class="mt-2"><i class="text-primary mdi mdi-access-point-network mr-2"></i> <b>125</b></h2>
                                <p class="text-muted mb-0">Khách Hàng</p>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6 widget-inline-box">
                            <div class="text-center p-3">
                                <h2 class="mt-2"><i class="text-teal mdi mdi-airplay mr-2"></i> <b>7000</b></h2>
                                <p class="text-muted mb-0">Đơn Hàng</p>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6 widget-inline-box">
                            <div class="text-center p-3">
                                <h2 class="mt-2"><i class="text-info mdi mdi-black-mesa mr-2"></i> <b>6521</b></h2>
                                <p class="text-muted mb-0">Total users</p>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <div class="text-center p-3">
                                <h2 class="mt-2"><i class="text-danger mdi mdi-cellphone-link mr-2"></i> <b>325</b></h2>
                                <p class="text-muted mb-0">Total visits</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end row -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card-box">
                <h5 class="mt-0 font-14">Tổng tiền</h5>
                <div class="text-center">
                    <ul class="list-inline chart-detail-list">
                        <li class="list-inline-item">
                            <p class="font-weight-semibold"><i class="fa fa-circle mr-2 text-primary"></i>Đã thanh toán</p>
                        </li>
                        <li class="list-inline-item">
                            <p class="font-weight-semibold"><i class="fa fa-circle mr-2 text-muted"></i>Tổng đơn</p>
                        </li>
                    </ul>
                </div>
                <div id="dashboard-bar-stacked" class="morris-chart" dir="ltr" style="height: 300px; position: relative; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"><svg height="300" version="1.1" width="658.688" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="overflow: hidden; position: relative; left: -0.15625px; top: -0.546875px;">
                        <desc style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">Created with Raphaël 2.3.0</desc>
                        <defs style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></defs><text x="31.76397705078125" y="255" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">0</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M44.26397705078125,255H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="31.76397705078125" y="197.5" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">75</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M44.26397705078125,197.5H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="31.76397705078125" y="140" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">150</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M44.26397705078125,140H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="31.76397705078125" y="82.5" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">225</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M44.26397705078125,82.5H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="31.76397705078125" y="24.99999999999997" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="2.9999999999999716" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">300</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M44.26397705078125,24.99999999999997H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="606.8959989568538" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2015</tspan>
                        </text><text x="499.72799478426845" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2013</tspan>
                        </text><text x="392.5599906116832" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2011</tspan>
                        </text><text x="285.391986439098" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2009</tspan>
                        </text><text x="178.2239822665128" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2007</tspan>
                        </text><text x="71.05597809392756" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2005</tspan>
                        </text>
                        <rect x="50.96197731156783" y="220.5" width="40.18800156471946" height="34.5" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="50.96197731156783" y="82.5" width="40.18800156471946" height="138" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="104.54597939786044" y="197.5" width="40.18800156471946" height="57.5" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="104.54597939786044" y="147.66666666666666" width="40.18800156471946" height="49.83333333333334" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="158.12998148415306" y="178.33333333333331" width="40.18800156471946" height="76.66666666666669" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="158.12998148415306" y="109.33333333333331" width="40.18800156471946" height="69" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="211.71398357044566" y="197.5" width="40.18800156471946" height="57.5" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="211.71398357044566" y="147.66666666666666" width="40.18800156471946" height="49.83333333333334" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="265.29798565673826" y="178.33333333333331" width="40.18800156471946" height="76.66666666666669" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="265.29798565673826" y="109.33333333333331" width="40.18800156471946" height="69" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="318.8819877430309" y="197.5" width="40.18800156471946" height="57.5" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="318.8819877430309" y="147.66666666666666" width="40.18800156471946" height="49.83333333333334" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="372.46598982932346" y="216.66666666666666" width="40.18800156471946" height="38.33333333333334" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="372.46598982932346" y="186" width="40.18800156471946" height="30.666666666666657" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="426.0499919156161" y="197.5" width="40.18800156471946" height="57.5" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="426.0499919156161" y="147.66666666666666" width="40.18800156471946" height="49.83333333333334" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="479.6339940019087" y="216.66666666666666" width="40.18800156471946" height="38.33333333333334" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="479.6339940019087" y="186" width="40.18800156471946" height="30.666666666666657" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="533.2179960882013" y="197.5" width="40.18800156471946" height="57.5" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="533.2179960882013" y="147.66666666666666" width="40.18800156471946" height="49.83333333333334" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="586.801998174494" y="178.33333333333331" width="40.18800156471946" height="76.66666666666669" rx="0" ry="0" fill="#458bc4" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                        <rect x="586.801998174494" y="109.33333333333331" width="40.18800156471946" height="69" rx="0" ry="0" fill="#ebeff2" stroke="none" fill-opacity="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); fill-opacity: 1;"></rect>
                    </svg>
                    <div class="morris-hover morris-default-style" style="left: 346.201px; top: 106px; display: none;">
                        <div class="morris-hover-row-label">2011</div>
                        <div class="morris-hover-point" style="color: #458bc4">
                            Series A:
                            50
                        </div>
                        <div class="morris-hover-point" style="color: #ebeff2">
                            Series B:
                            40
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end col -->

        <div class="col-lg-6">
            <div class="card-box">
                <h5 class="mt-0 font-14">Sales Analytics</h5>
                <div class="text-center">
                    <ul class="list-inline chart-detail-list">
                        <li class="list-inline-item">
                            <p class="font-weight-semibold"><i class="fa fa-circle mr-2 text-primary"></i>Mobiles</p>
                        </li>
                        <li class="list-inline-item">
                            <p class="font-weight-semibold"><i class="fa fa-circle mr-2 text-info"></i>Tablets</p>
                        </li>
                    </ul>
                </div>
                <div id="dashboard-line-chart" class="morris-chart" dir="ltr" style="height: 300px; position: relative; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"><svg height="300" version="1.1" width="658.688" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="overflow: hidden; position: relative; top: -0.546875px;">
                        <desc style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">Created with Raphaël 2.3.0</desc>
                        <defs style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></defs><text x="36.46797180175781" y="255" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">$0</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M48.96797180175781,255H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="36.46797180175781" y="197.5" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">$25</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M48.96797180175781,197.5H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="36.46797180175781" y="140" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">$50</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M48.96797180175781,140H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="36.46797180175781" y="82.5" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">$75</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M48.96797180175781,82.5H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="36.46797180175781" y="25.00000000000003" text-anchor="end" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: end; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal">
                            <tspan dy="3.0000000000000284" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">$100</tspan>
                        </text>
                        <path fill="none" stroke="#6c7897" d="M48.96797180175781,25.00000000000003H633.688" stroke-opacity="0.1" stroke-width="0.5" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path><text x="633.688" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2015</tspan>
                        </text><text x="550.2219028970048" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2014</tspan>
                        </text><text x="466.75580579400986" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2013</tspan>
                        </text><text x="383.0610344523764" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2012</tspan>
                        </text><text x="299.5949373493813" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2011</tspan>
                        </text><text x="216.1288402463863" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2010</tspan>
                        </text><text x="132.66274314339122" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2009</tspan>
                        </text><text x="48.96797180175781" y="267.5" text-anchor="middle" font-family="sans-serif" font-size="12px" stroke="none" fill="#888888" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0); text-anchor: middle; font-family: sans-serif; font-size: 12px; font-weight: normal;" font-weight="normal" transform="matrix(1,0,0,1,0,10)">
                            <tspan dy="3" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);">2008</tspan>
                        </text>
                        <path fill="none" stroke="#23b195" d="M48.96797180175781,255C69.89166463716617,226.25,111.73905030798286,163.03146374829,132.66274314339122,140C153.52926741913998,117.03146374829001,195.26231597063753,71,216.1288402463863,71C236.99536452213505,71,278.72841307363257,119.875,299.5949373493813,140C320.4614616251301,160.125,362.19451017662766,229.12893296853625,383.0610344523764,232C403.9847272877848,234.87893296853625,445.8321129586015,174.51573187414502,466.75580579400986,163C487.6223300697586,151.51573187414502,529.3553786212561,148.625,550.2219028970048,140C571.0884271727537,131.375,612.8214757242512,105.5,633.688,94" stroke-width="3px" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path>
                        <path fill="none" stroke="#458bc4" d="M48.96797180175781,140C69.89166463716617,125.625,111.73905030798286,76.74213406292749,132.66274314339122,82.5C153.52926741913998,88.24213406292749,195.26231597063753,178.8125,216.1288402463863,186C236.99536452213505,193.1875,278.72841307363257,152.9375,299.5949373493813,140C320.4614616251301,127.0625,362.19451017662766,82.5,383.0610344523764,82.5C403.9847272877848,82.5,445.8321129586015,140,466.75580579400986,140C487.6223300697586,140,529.3553786212561,96.87499999999999,550.2219028970048,82.5C571.0884271727537,68.12499999999999,612.8214757242512,39.37500000000002,633.688,25.00000000000003" stroke-width="3px" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></path>
                        <circle cx="48.96797180175781" cy="255" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="132.66274314339122" cy="140" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="216.1288402463863" cy="71" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="299.5949373493813" cy="140" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="383.0610344523764" cy="232" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="466.75580579400986" cy="163" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="550.2219028970048" cy="140" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="633.688" cy="94" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="48.96797180175781" cy="140" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="132.66274314339122" cy="82.5" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="216.1288402463863" cy="186" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="299.5949373493813" cy="140" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="383.0610344523764" cy="82.5" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="466.75580579400986" cy="140" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="550.2219028970048" cy="82.5" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                        <circle cx="633.688" cy="25.00000000000003" r="0" fill="#ffffff" stroke="#999999" stroke-width="1" style="-webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></circle>
                    </svg>
                    <div class="morris-hover morris-default-style" style="left: 333.577px; top: 92px; display: none;">
                        <div class="morris-hover-row-label">2012</div>
                        <div class="morris-hover-point" style="color: #458bc4">
                            Mobiles:
                            $75
                        </div>
                        <div class="morris-hover-point" style="color: #23b195">
                            Tablets:
                            $10
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-sm-12">
            <div class="card-box">
                <h5 class="mt-0 font-14 mb-3">Danh sách khách đặt nhiều hàng nhất</h5>
                <div class="table-responsive">
                    <table class="table table-hover mails m-0 table table-actions-bar table-centered">
                        <thead>
                            <tr>
                                <th style="min-width: 95px;">

                                    <div class="checkbox checkbox-single checkbox-primary">
                                        <input type="checkbox" class="custom-control-input" id="action-checkbox">
                                        <label class="custom-control-label" for="action-checkbox">&nbsp;</label>
                                    </div>
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Products</th>
                                <th>Start Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>
                                    <div class="checkbox checkbox-primary mr-2 float-left">
                                        <input id="checkbox2" type="checkbox">
                                        <label for="checkbox2"></label>
                                    </div>

                                    <img src="assets\images\users\avatar-2.jpg" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm">
                                </td>

                                <td>
                                    Tomaslau
                                </td>

                                <td>
                                    <a href="#" class="text-muted">tomaslau@dummy.com</a>
                                </td>

                                <td>
                                    <b><a href="" class="text-dark"><b>356</b></a>
                                    </b>
                                </td>

                                <td>
                                    01/11/2003
                                </td>

                            </tr>

                            <tr>
                                <td>
                                    <div class="checkbox checkbox-primary mr-2 float-left">
                                        <input id="checkbox1" type="checkbox">
                                        <label for="checkbox1"></label>
                                    </div>

                                    <img src="assets\images\users\avatar-1.jpg" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm">
                                </td>

                                <td>
                                    Chadengle
                                </td>

                                <td>
                                    <a href="#" class="text-muted">chadengle@dummy.com</a>
                                </td>

                                <td>
                                    <b><a href="" class="text-dark"><b>568</b></a>
                                    </b>
                                </td>

                                <td>
                                    01/11/2003
                                </td>

                            </tr>

                            <tr>
                                <td>
                                    <div class="checkbox checkbox-primary mr-2 float-left">
                                        <input id="checkbox3" type="checkbox">
                                        <label for="checkbox3"></label>
                                    </div>

                                    <img src="assets\images\users\avatar-3.jpg" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm">
                                </td>

                                <td>
                                    Stillnotdavid
                                </td>

                                <td>
                                    <a href="#" class="text-muted">stillnotdavid@dummy.com</a>
                                </td>
                                <td>
                                    <b><a href="" class="text-dark"><b>201</b></a>
                                    </b>
                                </td>

                                <td>
                                    12/11/2003
                                </td>

                            </tr>

                            <tr>
                                <td>
                                    <div class="checkbox checkbox-primary mr-2 float-left">
                                        <input id="checkbox4" type="checkbox">
                                        <label for="checkbox4"></label>
                                    </div>

                                    <img src="assets\images\users\avatar-4.jpg" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm">
                                </td>

                                <td>
                                    Kurafire
                                </td>

                                <td>
                                    <a href="#" class="text-muted">kurafire@dummy.com</a>
                                </td>

                                <td>
                                    <b><a href="" class="text-dark"><b>56</b></a>
                                    </b>
                                </td>

                                <td>
                                    14/11/2003
                                </td>

                            </tr>

                            <tr>
                                <td>
                                    <div class="checkbox checkbox-primary mr-2 float-left">
                                        <input id="checkbox5" type="checkbox">
                                        <label for="checkbox5"></label>
                                    </div>

                                    <img src="assets\images\users\avatar-5.jpg" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm">
                                </td>

                                <td>
                                    Shahedk
                                </td>

                                <td>
                                    <a href="#" class="text-muted">shahedk@dummy.com</a>
                                </td>

                                <td>
                                    <b><a href="" class="text-dark"><b>356</b></a>
                                    </b>
                                </td>

                                <td>
                                    20/11/2003
                                </td>

                            </tr>

                            <tr>
                                <td>
                                    <div class="checkbox checkbox-primary mr-2 float-left">
                                        <input id="checkbox6" type="checkbox">
                                        <label for="checkbox6"></label>
                                    </div>

                                    <img src="assets\images\users\avatar-6.jpg" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm">
                                </td>

                                <td>
                                    Adhamdannaway
                                </td>

                                <td>
                                    <a href="#" class="text-muted">adhamdannaway@dummy.com</a>
                                </td>

                                <td>
                                    <b><a href="" class="text-dark"><b>956</b></a>
                                    </b>
                                </td>

                                <td>
                                    24/11/2003
                                </td>

                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>
<?= $this->endSection() ?>