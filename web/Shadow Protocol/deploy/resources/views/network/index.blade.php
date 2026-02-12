@extends('layouts.app')

@section('title', 'Network Topology')

@section('content')
<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Network Infrastructure</span>
    </div>
    <div class="panel-body">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <div class="panel" style="margin: 0;">
                <div class="panel-body" style="text-align: center;">
                    <div style="color: #00ff88; font-size: 2em;">4</div>
                    <div style="color: #666;">Proxies Online</div>
                </div>
            </div>
            <div class="panel" style="margin: 0;">
                <div class="panel-body" style="text-align: center;">
                    <div style="color: #00ccff; font-size: 2em;">12</div>
                    <div style="color: #666;">Active Relays</div>
                </div>
            </div>
            <div class="panel" style="margin: 0;">
                <div class="panel-body" style="text-align: center;">
                    <div style="color: #ffcc00; font-size: 2em;">2</div>
                    <div style="color: #666;">Degraded Nodes</div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 30px;">
            <pre style="color: #00ff88; font-size: 0.8em; line-height: 1.4;">
┌─────────────────────────────────────────────────────────────────┐
│                        NETWORK TOPOLOGY                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│                         [C2-PRIMARY]                            │
│                              │                                   │
│              ┌───────────────┼───────────────┐                  │
│              │               │               │                   │
│         [PROXY-EU]     [PROXY-NA]      [PROXY-AP]              │
│              │               │               │                   │
│      ┌───────┴───┐    ┌─────┴─────┐   ┌────┴────┐             │
│      │           │    │           │   │         │              │
│   [RELAY]    [RELAY] [RELAY]  [RELAY] [RELAY] [RELAY]         │
│      │           │    │           │   │         │              │
│   [IMPLANTS]  [IMPLANTS] [IMPLANTS] [IMPLANTS] [IMPLANTS]     │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
            </pre>
        </div>

        <table class="data-table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Node</th>
                    <th>Type</th>
                    <th>Region</th>
                    <th>Latency</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>c2-primary</td>
                    <td style="color: #666;">C2 Server</td>
                    <td style="color: #666;">us-east-1</td>
                    <td style="color: #666;">—</td>
                    <td><span class="badge badge-active">online</span></td>
                </tr>
                <tr>
                    <td>proxy-eu-1</td>
                    <td style="color: #666;">Proxy</td>
                    <td style="color: #666;">eu-west-1</td>
                    <td style="color: #666;">45ms</td>
                    <td><span class="badge badge-active">online</span></td>
                </tr>
                <tr>
                    <td>proxy-na-1</td>
                    <td style="color: #666;">Proxy</td>
                    <td style="color: #666;">us-west-2</td>
                    <td style="color: #666;">12ms</td>
                    <td><span class="badge badge-active">online</span></td>
                </tr>
                <tr>
                    <td>proxy-ap-1</td>
                    <td style="color: #666;">Proxy</td>
                    <td style="color: #666;">ap-southeast-1</td>
                    <td style="color: #666;">180ms</td>
                    <td><span class="badge badge-dormant">degraded</span></td>
                </tr>
                <tr>
                    <td>relay-01</td>
                    <td style="color: #666;">Relay</td>
                    <td style="color: #666;">eu-central-1</td>
                    <td style="color: #666;">52ms</td>
                    <td><span class="badge badge-active">online</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
