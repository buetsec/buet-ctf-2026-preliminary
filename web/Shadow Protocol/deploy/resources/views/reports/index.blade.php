@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Operational Reports</span>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Report</th>
                    <th>Type</th>
                    <th>Generated</th>
                    <th>Size</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>daily_summary_{{ date('Ymd') }}.enc</td>
                    <td style="color: #666;">Daily Summary</td>
                    <td style="color: #666;">{{ now()->subHours(2)->format('Y-m-d H:i') }}</td>
                    <td style="color: #666;">2.4 MB</td>
                    <td><span class="badge badge-active">ready</span></td>
                </tr>
                <tr>
                    <td>exfil_report_{{ date('Ymd') }}.enc</td>
                    <td style="color: #666;">Exfiltration</td>
                    <td style="color: #666;">{{ now()->subHours(4)->format('Y-m-d H:i') }}</td>
                    <td style="color: #666;">1.8 MB</td>
                    <td><span class="badge badge-active">ready</span></td>
                </tr>
                <tr>
                    <td>beacon_stats_{{ date('Ymd') }}.enc</td>
                    <td style="color: #666;">Beacon Stats</td>
                    <td style="color: #666;">{{ now()->subHours(6)->format('Y-m-d H:i') }}</td>
                    <td style="color: #666;">856 KB</td>
                    <td><span class="badge badge-active">ready</span></td>
                </tr>
                <tr>
                    <td>network_audit_{{ date('Ymd') }}.enc</td>
                    <td style="color: #666;">Network Audit</td>
                    <td style="color: #666;">{{ now()->subHours(12)->format('Y-m-d H:i') }}</td>
                    <td style="color: #666;">3.2 MB</td>
                    <td><span class="badge badge-dormant">pending</span></td>
                </tr>
                <tr>
                    <td>weekly_summary_{{ date('YW') }}.enc</td>
                    <td style="color: #666;">Weekly Summary</td>
                    <td style="color: #666;">{{ now()->subDays(2)->format('Y-m-d H:i') }}</td>
                    <td style="color: #666;">12.1 MB</td>
                    <td><span class="badge badge-active">ready</span></td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 20px; padding: 15px; border: 1px solid #333; background: rgba(0,0,0,0.3);">
            <div style="color: #666; font-size: 0.85em;">
                All reports are encrypted with AES-256-GCM. Download requires operator authentication.
            </div>
        </div>
    </div>
</div>
@endsection
