function setStatus(text) {
  const el = document.getElementById('statusLine');
  if (el) el.textContent = text;
}

let toastTimer;
function showToast(message) {
  const el = document.getElementById('toast');
  if (!el) return;

  el.textContent = message;
  el.classList.add('toast--show');

  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    el.classList.remove('toast--show');
  }, 2200);
}

function fmtTime(iso) {
  try {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  } catch {
    return '';
  }
}

function escapeHtml(str) {
  return String(str || '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

const localActivity = [];

function guessKind(command) {
  const c = (command || '').toUpperCase();
  if (c.includes('LIGHT')) return 'lights';
  if (c.includes('LOCK') || c.includes('GARAGE')) return 'lock';
  if (c.includes('THERMO')) return 'climate';
  if (c.includes('CAMERA') || c.includes('ALARM')) return 'security';
  return 'system';
}

function mergeActivity(serverItems) {
  const now = Date.now();

  for (let i = localActivity.length - 1; i >= 0; i--) {
    const ts = new Date(localActivity[i].ts).getTime();
    if (Number.isFinite(ts) && now - ts > 15000) localActivity.splice(i, 1);
  }

  const merged = [...localActivity, ...(serverItems || [])];

  merged.sort((a, b) => {
    const ta = new Date(a.ts || 0).getTime() || 0;
    const tb = new Date(b.ts || 0).getTime() || 0;
    return tb - ta;
  });

  return merged;
}

function renderActivityInto(listId, items, opts = {}) {
  const list = document.getElementById(listId);
  if (!list) return;

  const rawLimit = list.getAttribute('data-limit');
  let limit = parseInt(rawLimit || String(opts.defaultLimit || '12'), 10);
  if (!Number.isFinite(limit) || limit <= 0) limit = opts.defaultLimit || 12;
  if (limit > 200) limit = 200;

  list.innerHTML = '';

  if (!items || items.length === 0) {
    const li = document.createElement('li');
    li.className = 'activityItem';
    li.innerHTML = `
      <div class="activityLeft">
        <span class="activityDot" aria-hidden="true"></span>
        <div class="activityText">
          <div class="activityMsg">${escapeHtml(opts.emptyTitle || 'No recent activity')}</div>
          <div class="activitySub">${escapeHtml(opts.emptySub || 'Actions will appear here')}</div>
        </div>
      </div>
      <div class="activityTime"></div>
    `;
    list.appendChild(li);
    return;
  }

  items.slice(0, limit).forEach((it) => {
    const li = document.createElement('li');
    li.className = 'activityItem';

    const time = it.ts ? fmtTime(it.ts) : '';
    const message = (it.message || '').toString();
    const kind = (it.kind || 'system').toString();

    const sub = kind === 'lights' ? 'Lighting'
      : kind === 'lock' ? 'Access'
      : kind === 'climate' ? 'Climate'
      : kind === 'security' ? 'Security'
      : 'System';

    li.innerHTML = `
      <div class="activityLeft">
        <span class="activityDot activityDot--${escapeHtml(kind)}" aria-hidden="true"></span>
        <div class="activityText">
          <div class="activityMsg" title="${escapeHtml(message)}">${escapeHtml(message)}</div>
          <div class="activitySub">${escapeHtml(sub)}</div>
        </div>
      </div>
      <div class="activityTime">${escapeHtml(time)}</div>
    `;

    list.appendChild(li);
  });
}

let lastActivityFetch = 0;
async function fetchActivity() {
  const res = await fetch('/api/activity', { headers: { 'Accept': 'application/json' } });
  const data = await res.json();
  return Array.isArray(data.items) ? data.items : [];
}

async function refreshDashboardActivity(force = false) {
  const list = document.getElementById('activityList');
  if (!list) return;

  const now = Date.now();
  if (!force && now - lastActivityFetch < 1500) return;
  lastActivityFetch = now;

  const meta = document.getElementById('activityMeta');
  const lastSync = document.getElementById('lastSyncPill');

  try {
    const serverItems = await fetchActivity();
    renderActivityInto('activityList', mergeActivity(serverItems), { defaultLimit: 12 });

    const t = new Date();
    const pretty = t.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    if (meta) meta.textContent = `Updated ${pretty}`;
    if (lastSync) lastSync.textContent = pretty;
  } catch {
    if (meta) meta.textContent = 'Activity temporarily unavailable';
  }
}

async function refreshHomeActivity() {
  const list = document.getElementById('homeActivityList');
  if (!list) return;

  const meta = document.getElementById('homeActivityMeta');
  const last = document.getElementById('homeLast');

  try {
    const items = await fetchActivity();
    renderActivityInto('homeActivityList', items.slice(-20).reverse(), {
      defaultLimit: 6,
      emptyTitle: 'No activity yet',
      emptySub: 'Device events will appear here'
    });

    if (items && items.length && items[items.length - 1] && items[items.length - 1].ts) {
      const pretty = fmtTime(items[items.length - 1].ts);
      if (last) last.textContent = pretty || '—';
    }

    if (meta) {
      const t = new Date();
      meta.textContent = `Updated ${t.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    }
  } catch {
    if (meta) meta.textContent = 'Unavailable';
  }
}

function setDeviceState(key, value) {
  if (!key) return;
  const card = document.querySelector(`.devicecard[data-state-key="${CSS.escape(key)}"]`);
  if (!card) return;
  const pill = card.querySelector('.statePill');
  if (!pill) return;
  pill.textContent = value;
  pill.setAttribute('data-state', value);
}

function nextThermoValue() {
  const base = 21 + Math.floor(Math.random() * 4);
  return `${base}°C`;
}

const ACTIONS = {
  lights_on: {
    command: 'LIGHTS ON',
    label: 'Living room lights turned on',
    kind: 'lights',
    state: { key: 'lights', value: 'On' }
  },
  lights_off: {
    command: 'LIGHTS OFF',
    label: 'Living room lights turned off',
    kind: 'lights',
    state: { key: 'lights', value: 'Off' }
  },
  garage_open: {
    command: 'GARAGE OPEN',
    label: 'Garage door opened',
    kind: 'lock',
    state: { key: 'garage', value: 'Open' }
  },
  garage_close: {
    command: 'GARAGE CLOSE',
    label: 'Garage door closed',
    kind: 'lock',
    state: { key: 'garage', value: 'Closed' }
  },
  lock_status: {
    command: 'LOCK STATUS',
    label: 'Front door lock status checked',
    kind: 'lock'
  },
  camera_snap: {
    command: 'CAMERA SNAP',
    label: 'Security camera snapshot captured',
    kind: 'security'
  },
  alarm_arm: {
    command: 'ALARM ARM',
    label: 'Alarm armed',
    kind: 'security',
    state: { key: 'alarm', value: 'Armed' }
  },
  alarm_disarm: {
    command: 'ALARM DISARM',
    label: 'Alarm disarmed',
    kind: 'security',
    state: { key: 'alarm', value: 'Disarmed' }
  },
  thermostat_get: {
    command: 'THERMOSTAT GET',
    label: 'Thermostat reading refreshed',
    kind: 'climate',
    state: { key: 'thermostat', value: nextThermoValue }
  }
};

async function sendAction(actionId) {
  const action = ACTIONS[actionId];
  if (!action) return;

  const command = action.command;
  const label = action.label || 'Update applied';
  const kind = action.kind || guessKind(command);

  setStatus(`${label} • syncing…`);
  showToast(label);

  localActivity.unshift({
    ts: new Date().toISOString(),
    kind,
    message: label
  });
  renderActivityInto('activityList', mergeActivity([]), { defaultLimit: 12 });

  if (action.state && action.state.key) {
    const v = typeof action.state.value === 'function' ? action.state.value() : action.state.value;
    if (v) setDeviceState(action.state.key, v);
  }

  const payload = {
    jsonrpc: '2.0',
    id: Date.now(),
    method: 'cmd',
    params: { command }
  };

  try {
    const res = await fetch('/api/rpc', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    let reply;
    try { reply = await res.json(); } catch { reply = null; }
    const msg = reply && reply.result && reply.result.message ? reply.result.message : label;

    if (res.ok) setStatus(`${msg} • applied`);
    else setStatus(`${label} • request queued`);

    refreshDashboardActivity(true);
  } catch {
    setStatus('Hub unreachable • try again');
  }
}

function setupFilters() {
  const grid = document.getElementById('devicesGrid');
  if (!grid) return;

  const meta = document.getElementById('devicesMeta');
  const input = document.getElementById('deviceSearch');
  const chips = Array.from(document.querySelectorAll('.chip[data-room]'));

  let selectedRoom = 'all';

  function apply() {
    const q = (input && input.value ? input.value : '').trim().toLowerCase();
    let visible = 0;

    grid.querySelectorAll('.devicecard').forEach((card) => {
      const room = (card.getAttribute('data-room') || '').trim();
      const name = (card.getAttribute('data-name') || '').toLowerCase();

      const roomOk = selectedRoom === 'all' || room === selectedRoom;
      const qOk = !q || name.includes(q);

      const ok = roomOk && qOk;
      card.style.display = ok ? '' : 'none';
      if (ok) visible += 1;
    });

    if (meta) meta.textContent = `${visible} online`;
  }

  function setRoom(room) {
    selectedRoom = room || 'all';
    chips.forEach((c) => {
      const isActive = (c.getAttribute('data-room') || 'all') === selectedRoom;
      c.classList.toggle('is-active', isActive);
      c.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });
    apply();
  }

  chips.forEach((btn) => {
    btn.addEventListener('click', () => {
      setRoom(btn.getAttribute('data-room') || 'all');
    });
  });

  if (input) {
    let t;
    input.addEventListener('input', () => {
      if (t) clearTimeout(t);
      t = setTimeout(apply, 60);
    });
  }

  try {
    const params = new URLSearchParams(window.location.search);
    const initial = params.get('room');
    if (initial) setRoom(initial);
    else setRoom('all');
  } catch {
    setRoom('all');
  }
}

function initDashboard() {
  document.querySelectorAll('button[data-action]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const a = btn.getAttribute('data-action');
      if (a) sendAction(a);
    });
  });

  const refreshBtn = document.getElementById('refreshBtn');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
      showToast('Refreshing…');
      refreshDashboardActivity(true);
    });
  }

  setupFilters();
  refreshDashboardActivity(true);
  setInterval(() => refreshDashboardActivity(false), 4000);
}

function initHome() {
  refreshHomeActivity();
  setInterval(() => refreshHomeActivity(), 10000);
}

window.addEventListener('DOMContentLoaded', () => {
  const page = (document.body && document.body.dataset && document.body.dataset.page) ? document.body.dataset.page : '';
  if (page === 'dashboard') initDashboard();
  else initHome();
});
