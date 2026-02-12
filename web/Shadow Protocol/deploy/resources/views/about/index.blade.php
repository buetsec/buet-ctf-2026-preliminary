@extends('layouts.app')

@section('title', 'About')

@section('content')
<div class="panel" style="max-width: 600px; margin: 50px auto;">
    <div class="panel-header">
        <span class="panel-title">SHADOW-NET C2</span>
    </div>
    <div class="panel-body" style="text-align: center;">
        <div style="font-size: 3em; color: #00ff88; margin-bottom: 20px;">◈</div>
        <div style="color: #00ccff; font-size: 1.2em; margin-bottom: 10px;">Version 3.2.1</div>
        <div style="color: #666; font-size: 0.9em; margin-bottom: 30px;">Build: stable-{{ rand(1000, 9999) }}</div>
        
        <div style="text-align: left; border-top: 1px solid #333; padding-top: 20px;">
            <table style="width: 100%; font-size: 0.85em;">
                <tr>
                    <td style="color: #666; padding: 8px 0;">Core Framework</td>
                    <td style="color: #fff; text-align: right;">Laravel 8.x</td>
                </tr>
                <tr>
                    <td style="color: #666; padding: 8px 0;">PHP Version</td>
                    <td style="color: #fff; text-align: right;">8.0.x</td>
                </tr>
                <tr>
                    <td style="color: #666; padding: 8px 0;">Encryption</td>
                    <td style="color: #00ff88; text-align: right;">AES-256-GCM</td>
                </tr>
                <tr>
                    <td style="color: #666; padding: 8px 0;">Protocol</td>
                    <td style="color: #00ff88; text-align: right;">TLS 1.3</td>
                </tr>
                <tr>
                    <td style="color: #666; padding: 8px 0;">Database</td>
                    <td style="color: #fff; text-align: right;">SQLite (Encrypted)</td>
                </tr>
                <tr>
                    <td style="color: #666; padding: 8px 0;">Uptime</td>
                    <td style="color: #00ccff; text-align: right;">{{ rand(10, 99) }}d {{ rand(0, 23) }}h {{ rand(0, 59) }}m</td>
                </tr>
            </table>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; border: 1px solid #333; background: rgba(0,0,0,0.3);">
            <div style="color: #444; font-size: 0.75em;">
                CLASSIFIED // INTERNAL USE ONLY
            </div>
        </div>
    </div>
</div>
@endsection
