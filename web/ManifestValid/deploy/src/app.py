import socket
from urllib.parse import urlparse, urljoin
import requests
from flask import Flask, render_template, request, flash, redirect, url_for

app = Flask(__name__)
app.secret_key = 'sfdaskjdhflkjasdhfasdhfkjasdlhf'

# Configuration
TIMEOUT_SECONDS = 3
MAX_FILE_SIZE = 1024 * 50  # 50KB limit for manifest files

def is_safe_url(url):
    try:
        parsed = urlparse(url)
        hostname = parsed.hostname
        
        if not hostname:
            return False
        ip_address = socket.gethostbyname(hostname)
        ip_parts = list(map(int, ip_address.split('.')))
        
        if ip_address.startswith("127."): return False
        if ip_address.startswith("10."): return False
        if ip_address.startswith("169.254"): return False
        if ip_parts[0] == 172 and 16 <= ip_parts[1] <= 31: return False
        if ip_parts[0] == 192 and ip_parts[1] == 168: return False
        
        return True
    except Exception as e:
        return False

def check_segment_health(base_url, segment_uri):
    target_url = urljoin(base_url, segment_uri)
    if target_url.startswith("file://") or target_url.startswith("/"):
        return {"status": "BLOCKED", "code": 403, "size": 0, "url": target_url}

    try:
        r = requests.get(target_url, timeout=TIMEOUT_SECONDS, allow_redirects=False)
        
        return {
            "status": "OK" if r.status_code == 200 else "ERROR",
            "code": r.status_code,
            "size": len(r.content),
            "content_preview": r.text[:100] if r.headers.get('content-type', '').startswith('text') else "[Binary Data]",
            "url": target_url
        }
    except requests.exceptions.RequestException as e:
        return {"status": "UNREACHABLE", "code": 0, "size": 0, "url": target_url}

@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == 'POST':
        manifest_url = request.form.get('url')
        
        if not manifest_url:
            flash("Please provide a URL.", "danger")
            return redirect(url_for('index'))
        if not is_safe_url(manifest_url):
            flash("Security Alert: The provided URL resolves to a restricted or private network address.", "danger")
            return redirect(url_for('index'))

        try:
            response = requests.get(manifest_url, timeout=TIMEOUT_SECONDS)
            if response.status_code != 200:
                flash(f"Could not fetch manifest. Status Code: {response.status_code}", "warning")
                return redirect(url_for('index'))
            
            content = response.text
            
            lines = content.splitlines()
            segments = []
            metadata = {
                "duration": 0,
                "version": "Unknown",
                "media_sequence": 0
            }

            for line in lines:
                line = line.strip()
                if not line: continue
                
                if line.startswith("#EXT-X-VERSION:"):
                    metadata['version'] = line.split(":")[1]
                elif line.startswith("#EXT-X-TARGETDURATION:"):
                    metadata['duration'] = line.split(":")[1]
                elif line.startswith("#EXT-X-MEDIA-SEQUENCE:"):
                    metadata['media_sequence'] = line.split(":")[1]
                elif not line.startswith("#"):
                    if len(segments) < 3:
                        health_data = check_segment_health(manifest_url, line)
                        segments.append(health_data)

            return render_template('report.html', url=manifest_url, meta=metadata, segments=segments)

        except Exception as e:
            flash(f"Processing Error: {str(e)}", "danger")
            return redirect(url_for('index'))

    return render_template('index.html')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)