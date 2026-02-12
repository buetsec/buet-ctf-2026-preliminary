@extends('layouts.app')

@section('title', 'Help')

@section('content')
<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Documentation</span>
    </div>
    <div class="panel-body">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <div class="panel" style="margin: 0;">
                <div class="panel-body">
                    <div style="color: #00ccff; font-weight: bold; margin-bottom: 10px;">Getting Started</div>
                    <ul style="color: #666; font-size: 0.85em; padding-left: 20px;">
                        <li>Dashboard Overview</li>
                        <li>Implant Management</li>
                        <li>Task Scheduling</li>
                        <li>Report Generation</li>
                    </ul>
                </div>
            </div>
            <div class="panel" style="margin: 0;">
                <div class="panel-body">
                    <div style="color: #00ccff; font-weight: bold; margin-bottom: 10px;">Modules</div>
                    <ul style="color: #666; font-size: 0.85em; padding-left: 20px;">
                        <li>Keylogger Configuration</li>
                        <li>Screenshot Capture</li>
                        <li>Credential Extraction</li>
                        <li>Persistence Methods</li>
                    </ul>
                </div>
            </div>
            <div class="panel" style="margin: 0;">
                <div class="panel-body">
                    <div style="color: #00ccff; font-weight: bold; margin-bottom: 10px;">Network</div>
                    <ul style="color: #666; font-size: 0.85em; padding-left: 20px;">
                        <li>Proxy Configuration</li>
                        <li>Relay Setup</li>
                        <li>DNS Tunneling</li>
                        <li>Failover Routing</li>
                    </ul>
                </div>
            </div>
            <div class="panel" style="margin: 0;">
                <div class="panel-body">
                    <div style="color: #00ccff; font-weight: bold; margin-bottom: 10px;">Security</div>
                    <ul style="color: #666; font-size: 0.85em; padding-left: 20px;">
                        <li>Encryption Standards</li>
                        <li>Key Management</li>
                        <li>Authentication</li>
                        <li>Audit Logging</li>
                    </ul>
                </div>
            </div>
            <div class="panel" style="margin: 0;">
                <div class="panel-body">
                    <div style="color: #00ccff; font-weight: bold; margin-bottom: 10px;">API Reference</div>
                    <ul style="color: #666; font-size: 0.85em; padding-left: 20px;">
                        <li>REST Endpoints</li>
                        <li>Authentication</li>
                        <li>Rate Limiting</li>
                        <li>Error Codes</li>
                    </ul>
                </div>
            </div>
            <div class="panel" style="margin: 0;">
                <div class="panel-body">
                    <div style="color: #00ccff; font-weight: bold; margin-bottom: 10px;">Troubleshooting</div>
                    <ul style="color: #666; font-size: 0.85em; padding-left: 20px;">
                        <li>Connection Issues</li>
                        <li>Beacon Problems</li>
                        <li>Module Errors</li>
                        <li>Performance Tuning</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; border: 1px solid #333; background: rgba(0,0,0,0.3);">
            <div style="color: #666; font-size: 0.85em;">
                Full documentation available on internal wiki. Contact operator support for access credentials.
            </div>
        </div>
    </div>
</div>
@endsection
