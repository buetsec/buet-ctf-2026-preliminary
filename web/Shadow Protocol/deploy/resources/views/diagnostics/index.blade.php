@extends('layouts.app')

@section('title', 'System Diagnostics')

@section('styles')
<style>
    .node-row {
        cursor: pointer;
        transition: background 0.2s;
    }
    .node-row:hover {
        background: var(--bg-tertiary);
    }
    .inspector-panel {
        display: none;
    }
    .inspector-panel.active {
        display: block;
    }
    .metric-bar {
        height: 4px;
        background: var(--bg-tertiary);
        margin-top: 4px;
    }
    .metric-bar-fill {
        height: 100%;
        background: var(--accent-cyan);
        transition: width 0.3s;
    }
</style>
@endsection

@section('content')
<div class="grid grid-3" style="margin-bottom: 30px;">
    <div class="panel">
        <div class="panel-body" style="text-align: center;">
            <div class="stat-value">{{ $systemStatus['cpu_usage'] }}</div>
            <div class="stat-label">CPU Load</div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body" style="text-align: center;">
            <div class="stat-value">{{ $systemStatus['memory_usage'] }}</div>
            <div class="stat-label">Memory</div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body" style="text-align: center;">
            <div class="stat-value">{{ $systemStatus['network_latency'] }}</div>
            <div class="stat-label">Latency</div>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">System Status</span>
                <span style="color: #00ff88; font-size: 0.75em;">● OPERATIONAL</span>
            </div>
            <div class="panel-body">
                <table class="data-table">
                    <tr>
                        <td style="color: #666;">Build Hash</td>
                        <td style="font-family: monospace;">{{ $systemStatus['build_hash'] }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Version</td>
                        <td>{{ $systemStatus['version'] }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Active Connections</td>
                        <td>{{ $systemStatus['active_connections'] }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Queued Commands</td>
                        <td>{{ $systemStatus['queued_commands'] }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Last Sync</td>
                        <td style="color: #888;">{{ \Carbon\Carbon::parse($systemStatus['last_sync'])->diffForHumans() }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Disk Usage</td>
                        <td>{{ $systemStatus['disk_usage'] }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Registered Nodes</span>
            </div>
            <div class="panel-body" style="padding: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Alias</th>
                            <th>Region</th>
                            <th>Load</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nodeList as $node)
                        <tr class="node-row" data-node-id="{{ $node['id'] }}" data-node-alias="{{ $node['alias'] }}">
                            <td style="color: #00ccff;">{{ $node['alias'] }}</td>
                            <td style="color: #666;">{{ $node['region'] }}</td>
                            <td>
                                <span>{{ $node['load'] }}</span>
                                <div class="metric-bar">
                                    <div class="metric-bar-fill" style="width: {{ $node['load'] }};"></div>
                                </div>
                            </td>
                            <td>
                                @if($node['status'] === 'operational')
                                    <span style="color: #00ff88;">●</span>
                                @elseif($node['status'] === 'maintenance')
                                    <span style="color: #ffcc00;">●</span>
                                @else
                                    <span style="color: #ff4444;">●</span>
                                @endif
                                {{ $node['status'] }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Node Inspector</span>
                <span id="inspector-status" style="color: #666; font-size: 0.75em;">Select a node</span>
            </div>
            <div class="panel-body">
                <div id="inspector-placeholder" style="text-align: center; padding: 40px; color: #333;">
                    <div style="font-size: 2em; margin-bottom: 10px;">⬡</div>
                    <div style="color: #666; font-size: 0.85em;">Click on a node to inspect</div>
                </div>
                <div id="inspector-content" class="inspector-panel">
                    <div class="terminal-output" id="inspector-output" style="min-height: 200px;">
<span class="prompt">shadownet@diag:~$</span> Initializing...
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Network Topology</span>
            </div>
            <div class="panel-body" style="height: 180px; display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center; color: #333;">
                    <pre style="color: #00ff88; font-size: 0.7em; line-height: 1.4;">
     [C2-MASTER]
         │
    ┌────┼────┐
    │    │    │
  [α]  [β]  [γ]
    │    │    │
   ...  ...  ...
                    </pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nodeRows = document.querySelectorAll('.node-row');
    const inspectorPlaceholder = document.getElementById('inspector-placeholder');
    const inspectorContent = document.getElementById('inspector-content');
    const inspectorOutput = document.getElementById('inspector-output');
    const inspectorStatus = document.getElementById('inspector-status');

    nodeRows.forEach(row => {
        row.addEventListener('click', function() {
            const nodeId = this.dataset.nodeId;
            const nodeAlias = this.dataset.nodeAlias;
            
            inspectorPlaceholder.style.display = 'none';
            inspectorContent.classList.add('active');
            inspectorStatus.textContent = nodeAlias;
            inspectorStatus.style.color = '#00ccff';

            inspectorOutput.innerHTML = `<span class="prompt">shadownet@diag:~$</span> node-inspect --target ${nodeAlias}
[INFO] Connecting to node...
[INFO] Fetching status...`;

            // Fetch node status from API
            fetch('/api/node/status?node_id=' + encodeURIComponent(nodeId), {
                headers: {
                    'Accept': 'application/json',
                    'X-Node-Ref': nodeId
                }
            })
            .then(response => response.json())
            .then(data => {
                inspectorOutput.innerHTML = `<span class="prompt">shadownet@diag:~$</span> node-inspect --target ${nodeAlias}
[OK] Connection established
[OK] Node status: ${data.status || 'unknown'}

Node ID: ${data.node?.id || 'N/A'}
Region:  ${data.node?.region || 'N/A'}
Status:  ${data.node?.status || 'N/A'}

<span class="prompt">shadownet@diag:~$</span> _`;
            })
            .catch(error => {
                inspectorOutput.innerHTML = `<span class="prompt">shadownet@diag:~$</span> node-inspect --target ${nodeAlias}
[ERROR] Failed to fetch node status
[ERROR] ${error.message}

<span class="prompt">shadownet@diag:~$</span> _`;
            });
        });
    });
});
</script>
@endsection
