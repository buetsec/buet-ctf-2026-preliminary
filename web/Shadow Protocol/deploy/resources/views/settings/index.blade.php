@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="grid grid-2">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">General Settings</span>
        </div>
        <div class="panel-body">
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Beacon Interval (seconds)</label>
                <input type="text" value="60" disabled style="background: #1a1a2e; border: 1px solid #333; color: #666; padding: 8px; width: 100%;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Jitter (%)</label>
                <input type="text" value="15" disabled style="background: #1a1a2e; border: 1px solid #333; color: #666; padding: 8px; width: 100%;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Max Retries</label>
                <input type="text" value="3" disabled style="background: #1a1a2e; border: 1px solid #333; color: #666; padding: 8px; width: 100%;">
            </div>
            <div style="color: #ff4444; font-size: 0.8em; margin-top: 15px;">
                Settings modification requires admin authentication
            </div>
        </div>
    </div>
    
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Encryption Settings</span>
        </div>
        <div class="panel-body">
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Cipher Suite</label>
                <input type="text" value="AES-256-GCM" disabled style="background: #1a1a2e; border: 1px solid #333; color: #00ff88; padding: 8px; width: 100%;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Key Derivation</label>
                <input type="text" value="HKDF-SHA256" disabled style="background: #1a1a2e; border: 1px solid #333; color: #00ff88; padding: 8px; width: 100%;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Key Rotation (hours)</label>
                <input type="text" value="72" disabled style="background: #1a1a2e; border: 1px solid #333; color: #666; padding: 8px; width: 100%;">
            </div>
        </div>
    </div>
    
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Network Settings</span>
        </div>
        <div class="panel-body">
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Primary C2</label>
                <input type="text" value="c2-primary.internal" disabled style="background: #1a1a2e; border: 1px solid #333; color: #666; padding: 8px; width: 100%;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Fallback C2</label>
                <input type="text" value="c2-backup.internal" disabled style="background: #1a1a2e; border: 1px solid #333; color: #666; padding: 8px; width: 100%;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Protocol</label>
                <input type="text" value="HTTPS/TLS 1.3" disabled style="background: #1a1a2e; border: 1px solid #333; color: #00ff88; padding: 8px; width: 100%;">
            </div>
        </div>
    </div>
    
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Logging Settings</span>
        </div>
        <div class="panel-body">
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Log Level</label>
                <input type="text" value="INFO" disabled style="background: #1a1a2e; border: 1px solid #333; color: #666; padding: 8px; width: 100%;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Retention (days)</label>
                <input type="text" value="30" disabled style="background: #1a1a2e; border: 1px solid #333; color: #666; padding: 8px; width: 100%;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Remote Syslog</label>
                <input type="text" value="Disabled" disabled style="background: #1a1a2e; border: 1px solid #333; color: #ff4444; padding: 8px; width: 100%;">
            </div>
        </div>
    </div>
</div>
@endsection
