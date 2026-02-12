@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="panel" style="max-width: 600px; margin: 100px auto;">
    <div class="panel-header">
        <span class="panel-title">Access Denied</span>
        <span style="color: #ff4444; font-size: 0.75em;">● ERROR 403</span>
    </div>
    <div class="panel-body">
        <div class="terminal-output" style="text-align: center; padding: 40px;">
            <div style="color: #ff4444; font-size: 1.5em; margin-bottom: 20px;">
                [ACCESS DENIED]
            </div>
            @if(request()->routeIs('system.export'))
                <div style="color: #666; font-size: 0.9em; margin-bottom: 15px;">
                    @if(!request()->has('signature'))
                        Missing required signature parameter.
                    @elseif(!request()->has('expires'))
                        Missing required expires parameter.
                    @else
                        Invalid or expired signature.
                    @endif
                </div>
                <div style="color: #444; font-size: 0.8em; border-top: 1px solid #222; padding-top: 15px; margin-top: 15px; text-align: left;">
                    <span style="color: #666;">Required format:</span><br>
                    <code style="color: #00ccff;">/system/export?expires=&lt;timestamp&gt;&signature=&lt;hmac&gt;</code>
                </div>
            @else
                <div style="color: #666; font-size: 0.9em;">
                    {{ $exception->getMessage() ?: 'Unauthorized access attempt logged.' }}
                </div>
            @endif
            <div style="color: #333; font-size: 0.75em; margin-top: 30px;">
                Incident ID: {{ strtoupper(substr(md5(now()), 0, 12)) }}
            </div>
        </div>
    </div>
</div>
@endsection
