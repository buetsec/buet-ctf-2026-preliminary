@extends('layouts.app')

@section('title', 'Not Found')

@section('content')
<div class="panel" style="max-width: 500px; margin: 100px auto;">
    <div class="panel-header">
        <span class="panel-title">Resource Not Found</span>
        <span style="color: #ffcc00; font-size: 0.75em;">● ERROR 404</span>
    </div>
    <div class="panel-body">
        <div class="terminal-output" style="text-align: center; padding: 40px;">
            <div style="color: #ffcc00; font-size: 1.5em; margin-bottom: 20px;">
                [NOT FOUND]
            </div>
            <div style="color: #666; font-size: 0.9em;">
                The requested resource does not exist.
            </div>
            <div style="margin-top: 30px;">
                <a href="{{ route('dashboard') }}" style="color: #00ccff; text-decoration: none;">
                    &larr; Return to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
