@extends('layouts.app')

@section('title', 'System Error')

@section('content')
<div class="panel" style="max-width: 500px; margin: 100px auto;">
    <div class="panel-header">
        <span class="panel-title">System Error</span>
        <span style="color: #ff4444; font-size: 0.75em;">● ERROR 500</span>
    </div>
    <div class="panel-body">
        <div class="terminal-output" style="text-align: center; padding: 40px;">
            <div style="color: #ff4444; font-size: 1.5em; margin-bottom: 20px;">
                [INTERNAL ERROR]
            </div>
            <div style="color: #666; font-size: 0.9em;">
                An unexpected error occurred.
            </div>
            <div style="color: #333; font-size: 0.75em; margin-top: 30px;">
                Error logged for review.
            </div>
        </div>
    </div>
</div>
@endsection
