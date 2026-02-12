@extends('layouts.app')

@section('title', 'Authentication Required')

@section('content')
<div class="panel" style="max-width: 400px; margin: 100px auto;">
    <div class="panel-header">
        <span class="panel-title">SHADOW-NET Authentication</span>
    </div>
    <div class="panel-body">
        <form method="POST" action="/login">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Operator ID</label>
                <input type="text" name="username" style="background: #1a1a2e; border: 1px solid #333; color: #fff; padding: 10px; width: 100%; box-sizing: border-box;" placeholder="Enter operator ID">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">Access Key</label>
                <input type="password" name="password" style="background: #1a1a2e; border: 1px solid #333; color: #fff; padding: 10px; width: 100%; box-sizing: border-box;" placeholder="Enter access key">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="color: #666; font-size: 0.85em; display: block; margin-bottom: 5px;">MFA Token</label>
                <input type="text" name="mfa" style="background: #1a1a2e; border: 1px solid #333; color: #fff; padding: 10px; width: 100%; box-sizing: border-box;" placeholder="6-digit code">
            </div>
            <button type="submit" style="background: #00ccff; border: none; color: #000; padding: 12px; width: 100%; cursor: pointer; font-weight: bold;">
                AUTHENTICATE
            </button>
            
            @if(session('error'))
            <div style="margin-top: 15px; padding: 10px; background: rgba(255,68,68,0.2); border: 1px solid #ff4444; color: #ff4444; font-size: 0.85em;">
                {{ session('error') }}
            </div>
            @endif
        </form>
        
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #333;">
            <div style="color: #444; font-size: 0.75em; text-align: center;">
                Unauthorized access is prohibited and monitored.
            </div>
        </div>
    </div>
</div>
@endsection
