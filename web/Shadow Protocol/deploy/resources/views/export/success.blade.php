@extends('layouts.app')

@section('title', 'System Export')

@section('content')
<div class="panel" style="max-width: 600px; margin: 100px auto;">
    <div class="panel-header">
        <span class="panel-title">Intelligence Export</span>
        <span style="color: #00ff88; font-size: 0.75em;">● AUTHORIZED</span>
    </div>
    <div class="panel-body">
        <div class="terminal-output" style="padding: 30px;">
            <div style="color: #00ff88; font-size: 1.2em; margin-bottom: 20px; text-align: center;">
                [EXPORT COMPLETE]
            </div>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="color: #888; padding: 8px 0;">System Status:</td>
                    <td style="color: #00ff88;">{{ $data['system_status'] }}</td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 8px 0;">Active Nodes:</td>
                    <td style="color: #00ccff;">{{ $data['nodes_active'] }}</td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 8px 0;">Last Sync:</td>
                    <td style="color: #fff;">{{ $data['last_sync'] }}</td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 8px 0;">Export ID:</td>
                    <td style="color: #ff6600; font-family: monospace;">{{ $data['export_id'] }}</td>
                </tr>
            </table>
            
            <div style="margin-top: 30px; padding: 15px; border: 1px solid #333; background: rgba(0,0,0,0.3);">
                <div style="color: #00ccff; margin-bottom: 10px;">{{ $data['message'] }}</div>
                <div style="color: #666; font-size: 0.85em; font-family: monospace;">
                    Checksum: {{ $data['checksum'] }}
                </div>
            </div>
            
            <div style="color: #444; font-size: 0.75em; margin-top: 20px; text-align: center;">
                Timestamp: {{ $timestamp }}
            </div>
        </div>
    </div>
</div>
@endsection
