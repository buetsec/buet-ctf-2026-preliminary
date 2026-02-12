@extends('layouts.app')

@section('title', 'Modules')

@section('content')
<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Payload Modules</span>
    </div>
    <div class="panel-body">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
            @foreach([
                ['name' => 'keylogger.dll', 'version' => '2.1.0', 'status' => 'loaded', 'desc' => 'Keyboard input capture'],
                ['name' => 'screenshot.so', 'version' => '1.8.3', 'status' => 'loaded', 'desc' => 'Screen capture module'],
                ['name' => 'persistence.exe', 'version' => '3.0.1', 'status' => 'standby', 'desc' => 'Persistence mechanism'],
                ['name' => 'exfil_ftp.bin', 'version' => '2.4.0', 'status' => 'loaded', 'desc' => 'FTP exfiltration'],
                ['name' => 'lateral_smb.dll', 'version' => '1.2.0', 'status' => 'disabled', 'desc' => 'SMB lateral movement'],
                ['name' => 'cred_dump.exe', 'version' => '2.0.0', 'status' => 'loaded', 'desc' => 'Credential extraction'],
                ['name' => 'port_scan.so', 'version' => '1.5.2', 'status' => 'standby', 'desc' => 'Network port scanner'],
                ['name' => 'dns_tunnel.bin', 'version' => '1.3.0', 'status' => 'disabled', 'desc' => 'DNS tunneling'],
                ['name' => 'clipboard.dll', 'version' => '1.1.0', 'status' => 'loaded', 'desc' => 'Clipboard monitor'],
                ['name' => 'browser_grab.exe', 'version' => '2.2.1', 'status' => 'standby', 'desc' => 'Browser data extraction'],
            ] as $module)
            <div class="panel" style="margin: 0;">
                <div class="panel-body">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="color: #00ccff; font-weight: bold;">{{ $module['name'] }}</div>
                            <div style="color: #666; font-size: 0.8em;">{{ $module['desc'] }}</div>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge badge-{{ $module['status'] == 'loaded' ? 'active' : ($module['status'] == 'standby' ? 'dormant' : 'offline') }}">{{ $module['status'] }}</span>
                            <div style="color: #444; font-size: 0.7em; margin-top: 4px;">v{{ $module['version'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
