<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Error</title>
    <style>
        :root {
            --bg: #1a1a2e;
            --surface: #16213e;
            --primary: #0f3460;
            --accent: #e94560;
            --text: #eee;
            --muted: #888;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', monospace;
            background: var(--bg);
            color: var(--text);
            padding: 20px;
            line-height: 1.6;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            background: var(--accent);
            padding: 20px;
            margin-bottom: 20px;
        }
        .header h1 { font-size: 1.5em; }
        .section {
            background: var(--surface);
            border: 1px solid var(--primary);
            margin-bottom: 20px;
            padding: 15px;
        }
        .section-title {
            color: var(--accent);
            border-bottom: 1px solid var(--primary);
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .exception {
            background: var(--primary);
            padding: 15px;
            overflow-x: auto;
        }
        .trace {
            font-size: 0.85em;
            color: var(--muted);
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            text-align: left;
            padding: 8px 12px;
            border-bottom: 1px solid var(--primary);
        }
        th { color: var(--accent); width: 30%; }
        td { word-break: break-all; }
        .env-value { color: #4ecca3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠ Application Exception</h1>
        </div>

        <div class="section">
            <div class="section-title">Exception Details</div>
            <div class="exception">
                <strong>{{ get_class($exception) }}</strong><br>
                {{ $exception->getMessage() }}
            </div>
        </div>

        <div class="section">
            <div class="section-title">Stack Trace</div>
            <div class="trace">{{ $exception->getTraceAsString() }}</div>
        </div>

        <div class="section">
            <div class="section-title">Environment Variables</div>
            <table>
                @foreach(['APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION', 'CACHE_DRIVER', 'SESSION_DRIVER'] as $key)
                <tr>
                    <th>{{ $key }}</th>
                    <td class="env-value">{{ env($key, '(not set)') }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="section">
            <div class="section-title">Request Information</div>
            <table>
                <tr><th>URL</th><td>{{ request()->fullUrl() }}</td></tr>
                <tr><th>Method</th><td>{{ request()->method() }}</td></tr>
                <tr><th>IP</th><td>{{ request()->ip() }}</td></tr>
                <tr><th>User Agent</th><td>{{ request()->userAgent() }}</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Server Information</div>
            <table>
                <tr><th>PHP Version</th><td>{{ PHP_VERSION }}</td></tr>
                <tr><th>Laravel Version</th><td>{{ app()->version() }}</td></tr>
                <tr><th>Server Time</th><td>{{ now()->toIso8601String() }}</td></tr>
            </table>
        </div>
    </div>
</body>
</html>
