@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Task Queue</span>
        <span style="color: #666; font-size: 0.8em;">{{ rand(5, 15) }} active tasks</span>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Task ID</th>
                    <th>Type</th>
                    <th>Target</th>
                    <th>Created</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < 12; $i++)
                <tr>
                    <td style="font-family: monospace; color: #00ccff;">{{ substr(md5(rand()), 0, 8) }}</td>
                    <td style="color: #666;">{{ ['beacon', 'exec', 'exfil', 'recon', 'persist'][array_rand(['beacon', 'exec', 'exfil', 'recon', 'persist'])] }}</td>
                    <td style="color: #666;">{{ ['DESKTOP-', 'LAPTOP-', 'SERVER-', 'WS-'][array_rand(['DESKTOP-', 'LAPTOP-', 'SERVER-', 'WS-'])] }}{{ strtoupper(substr(md5(rand()), 0, 6)) }}</td>
                    <td style="color: #666;">{{ now()->subMinutes(rand(1, 120))->diffForHumans() }}</td>
                    <td>
                        @php $status = ['queued', 'running', 'complete', 'failed'][array_rand(['queued', 'running', 'complete', 'failed'])]; @endphp
                        <span class="badge badge-{{ $status == 'complete' ? 'active' : ($status == 'running' ? 'dormant' : ($status == 'failed' ? 'offline' : 'dormant')) }}">{{ $status }}</span>
                    </td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
@endsection
