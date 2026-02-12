<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'SHADOW-NET') | Command Interface</title>
    <style>
        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #111111;
            --bg-tertiary: #1a1a1a;
            --text-primary: #00ff88;
            --text-secondary: #00ccff;
            --text-muted: #666666;
            --text-warning: #ffcc00;
            --text-danger: #ff4444;
            --border-color: #00ff8833;
            --border-glow: #00ff8866;
            --accent-cyan: #00ccff;
            --accent-green: #00ff88;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', 'Lucida Console', Monaco, monospace;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            border-bottom: 1px solid var(--border-color);
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--accent-cyan);
            text-shadow: 0 0 10px var(--accent-cyan);
            letter-spacing: 3px;
        }

        .logo-sub {
            font-size: 0.6em;
            color: var(--text-muted);
            letter-spacing: 1px;
        }

        .nav {
            display: flex;
            gap: 30px;
        }

        .nav a {
            color: var(--text-primary);
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid transparent;
            transition: all 0.2s;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 1px;
        }

        .nav a:hover {
            border-color: var(--border-color);
            text-shadow: 0 0 8px var(--accent-green);
        }

        .nav a.active {
            border-color: var(--accent-green);
            background: var(--bg-tertiary);
        }

        /* Status bar */
        .status-bar {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            font-size: 0.75em;
            color: var(--text-muted);
            padding: 8px 0;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent-green);
            box-shadow: 0 0 6px var(--accent-green);
            animation: pulse 2s infinite;
        }

        .status-dot.warning {
            background: var(--text-warning);
            box-shadow: 0 0 6px var(--text-warning);
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Panels */
        .panel {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .panel-header {
            background: var(--bg-tertiary);
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-title {
            color: var(--accent-cyan);
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .panel-body {
            padding: 16px;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85em;
        }

        .data-table th,
        .data-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid var(--bg-tertiary);
        }

        .data-table th {
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.8em;
            letter-spacing: 1px;
            font-weight: normal;
        }

        .data-table tr:hover {
            background: var(--bg-tertiary);
        }

        .data-table a {
            color: var(--accent-cyan);
            text-decoration: none;
        }

        .data-table a:hover {
            text-decoration: underline;
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 0.75em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .badge-active {
            color: var(--accent-green);
            border: 1px solid var(--accent-green);
        }

        .badge-dormant {
            color: var(--text-warning);
            border: 1px solid var(--text-warning);
        }

        .badge-offline {
            color: var(--text-danger);
            border: 1px solid var(--text-danger);
        }

        /* Grid layout */
        .grid {
            display: grid;
            gap: 20px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        @media (max-width: 1024px) {
            .grid-3, .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .grid-2, .grid-3, .grid-4 {
                grid-template-columns: 1fr;
            }
            .nav {
                gap: 15px;
            }
        }

        /* Stats */
        .stat-value {
            font-size: 2em;
            color: var(--accent-cyan);
            text-shadow: 0 0 15px var(--accent-cyan);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.75em;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        /* Log entries */
        .log-entry {
            padding: 8px 0;
            border-bottom: 1px solid var(--bg-tertiary);
            font-size: 0.8em;
        }

        .log-entry:last-child {
            border-bottom: none;
        }

        .log-timestamp {
            color: var(--text-muted);
            margin-right: 10px;
        }

        .log-event {
            color: var(--text-warning);
            margin-right: 10px;
        }

        .log-source {
            color: var(--accent-cyan);
            margin-right: 10px;
        }

        /* Footer */
        .footer {
            border-top: 1px solid var(--border-color);
            padding: 15px 0;
            margin-top: 40px;
            text-align: center;
            font-size: 0.7em;
            color: var(--text-muted);
        }

        /* Terminal output style */
        .terminal-output {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            padding: 16px;
            font-size: 0.85em;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .prompt {
            color: var(--accent-green);
        }

        /* Glow effect on hover for interactive elements */
        .glow-hover:hover {
            box-shadow: 0 0 15px var(--border-glow);
        }

        /* Scanlines effect (subtle) */
        .scanlines::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            background: repeating-linear-gradient(
                0deg,
                rgba(0, 0, 0, 0.15),
                rgba(0, 0, 0, 0.15) 1px,
                transparent 1px,
                transparent 2px
            );
            z-index: 9999;
        }
    </style>
    @yield('styles')
</head>
<body class="scanlines">
    <div class="container">
        <header class="header">
            <div class="header-content">
                <div>
                    <div class="logo">SHADOW-NET<span class="logo-sub"> v3.2.1</span></div>
                </div>
                <nav class="nav">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('network') }}" class="{{ request()->routeIs('network') ? 'active' : '' }}">Network</a>
                    <a href="{{ route('modules') }}" class="{{ request()->routeIs('modules') ? 'active' : '' }}">Modules</a>
                    <a href="{{ route('tasks') }}" class="{{ request()->routeIs('tasks') ? 'active' : '' }}">Tasks</a>
                    <a href="{{ route('diagnostics') }}" class="{{ request()->routeIs('diagnostics') ? 'active' : '' }}">Diagnostics</a>
                    <a href="{{ route('reports') }}" class="{{ request()->routeIs('reports') ? 'active' : '' }}">Reports</a>
                    <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'active' : '' }}">Settings</a>
                </nav>
            </div>
            <div class="status-bar">
                <span class="status-item">
                    <span class="status-dot"></span>
                    C2 LINK ACTIVE
                </span>
                <span class="status-item">
                    <span class="status-dot"></span>
                    ENCRYPTION: AES-256
                </span>
                <span class="status-item">
                    UTC {{ now()->format('Y-m-d H:i:s') }}
                </span>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="footer">
            SHADOW-NET COMMAND INFRASTRUCTURE // AUTHORIZED ACCESS ONLY // SESSION {{ strtoupper(substr(md5(session()->getId()), 0, 8)) }}
        </footer>
    </div>

    @yield('scripts')
</body>
</html>
