// ========================================
// NEON QUEUE - Frontend Application
// ========================================

const API_BASE = '/api';

// State
let authToken = localStorage.getItem('neonqueue_token');
let currentUser = null;
let pendingUserId = null;
let taskPollInterval = null;
let uptimeInterval = null;
let startTime = Date.now();

// ========== DOM ELEMENTS ==========
const elements = {
    // Auth
    authPanel: document.getElementById('auth-panel'),
    dashboardPanel: document.getElementById('dashboard-panel'),
    loginForm: document.getElementById('login-form'),
    registerForm: document.getElementById('register-form'),
    otpForm: document.getElementById('otp-form'),
    authMessage: document.getElementById('auth-message'),
    tabBtns: document.querySelectorAll('.tab-btn'),
    logoutBtn: document.getElementById('logout-btn'),
    
    // Header
    connectionStatus: document.getElementById('connection-status'),
    userDisplay: document.getElementById('user-display'),
    statusIndicator: document.querySelector('.status-indicator'),
    
    // Tasks
    taskForm: document.getElementById('task-form'),
    taskType: document.getElementById('task-type'),
    taskParams: document.getElementById('task-params'),
    emailParams: document.getElementById('email-params'),
    taskMessage: document.getElementById('task-message'),
    taskList: document.getElementById('task-list'),
    refreshIndicator: document.getElementById('refresh-indicator'),
    
    // Footer
    uptime: document.getElementById('uptime')
};

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', () => {
    initEventListeners();
    startUptimeCounter();
    
    if (authToken) {
        validateToken();
    }
});

function initEventListeners() {
    // Auth tabs
    elements.tabBtns.forEach(btn => {
        btn.addEventListener('click', () => switchAuthTab(btn.dataset.tab));
    });
    
    // Forms
    elements.loginForm.addEventListener('submit', handleLogin);
    elements.registerForm.addEventListener('submit', handleRegister);
    elements.otpForm.addEventListener('submit', handleOtpVerify);
    elements.taskForm.addEventListener('submit', handleTaskSubmit);
    elements.logoutBtn.addEventListener('click', handleLogout);
    
    // Task type change
    elements.taskType.addEventListener('change', handleTaskTypeChange);
}

// ========== AUTH FUNCTIONS ==========
function switchAuthTab(tab) {
    elements.tabBtns.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    
    elements.loginForm.classList.toggle('hidden', tab !== 'login');
    elements.registerForm.classList.toggle('hidden', tab !== 'register');
    elements.otpForm.classList.add('hidden');
    hideMessage(elements.authMessage);
}

async function handleLogin(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    setLoading(btn, true);
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch(`${API_BASE}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            if (data.requires_otp) {
                pendingUserId = data.user_id;
                showOtpForm();
                showMessage(elements.authMessage, 'OTP verification required', 'info');
            } else {
                completeLogin(data);
            }
        } else {
            showMessage(elements.authMessage, data.detail || 'Login failed', 'error');
        }
    } catch (error) {
        showMessage(elements.authMessage, 'Connection error', 'error');
    } finally {
        setLoading(btn, false);
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    setLoading(btn, true);
    
    const formData = new FormData(e.target);
    const payload = {
        username: formData.get('username'),
        email: formData.get('email'),
        password: formData.get('password')
    };
    
    try {
        const response = await fetch(`${API_BASE}/auth/register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            pendingUserId = data.user_id;
            showOtpForm();
            showMessage(elements.authMessage, 'Registration successful! Verify your account.', 'success');
        } else {
            showMessage(elements.authMessage, data.detail || 'Registration failed', 'error');
        }
    } catch (error) {
        showMessage(elements.authMessage, 'Connection error', 'error');
    } finally {
        setLoading(btn, false);
    }
}

async function handleOtpVerify(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    setLoading(btn, true);
    
    const otp = document.getElementById('otp-code').value;
    
    try {
        const response = await fetch(`${API_BASE}/auth/verify-otp`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: pendingUserId, otp: otp })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            completeLogin(data);
        } else {
            showMessage(elements.authMessage, data.detail || 'Verification failed', 'error');
        }
    } catch (error) {
        showMessage(elements.authMessage, 'Connection error', 'error');
    } finally {
        setLoading(btn, false);
    }
}

function showOtpForm() {
    elements.loginForm.classList.add('hidden');
    elements.registerForm.classList.add('hidden');
    elements.otpForm.classList.remove('hidden');
}

function completeLogin(data) {
    authToken = data.access_token;
    currentUser = data.username;
    localStorage.setItem('neonqueue_token', authToken);
    
    showDashboard();
}

async function validateToken() {
    try {
        const response = await fetch(`${API_BASE}/auth/me`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
        });
        
        if (response.ok) {
            const data = await response.json();
            currentUser = data.username;
            showDashboard();
        } else {
            handleLogout();
        }
    } catch (error) {
        handleLogout();
    }
}

function handleLogout() {
    authToken = null;
    currentUser = null;
    localStorage.removeItem('neonqueue_token');
    
    if (taskPollInterval) {
        clearInterval(taskPollInterval);
        taskPollInterval = null;
    }
    
    showAuthPanel();
}

function showDashboard() {
    elements.authPanel.classList.add('hidden');
    elements.dashboardPanel.classList.remove('hidden');
    elements.logoutBtn.classList.remove('hidden');
    
    elements.userDisplay.textContent = currentUser.toUpperCase();
    elements.connectionStatus.textContent = 'CONNECTED';
    elements.statusIndicator.classList.add('connected');
    
    // Start polling for tasks
    loadTasks();
    taskPollInterval = setInterval(loadTasks, 3000);
}

function showAuthPanel() {
    elements.dashboardPanel.classList.add('hidden');
    elements.authPanel.classList.remove('hidden');
    elements.logoutBtn.classList.add('hidden');
    
    elements.userDisplay.textContent = 'GUEST';
    elements.connectionStatus.textContent = 'DISCONNECTED';
    elements.statusIndicator.classList.remove('connected');
    
    // Reset forms
    elements.loginForm.reset();
    elements.registerForm.reset();
    elements.otpForm.reset();
    switchAuthTab('login');
}

// ========== TASK FUNCTIONS ==========
function handleTaskTypeChange() {
    const taskType = elements.taskType.value;
    
    // Hide all param groups
    elements.emailParams.classList.add('hidden');
    
    if (taskType === 'send_email') {
        elements.taskParams.classList.remove('hidden');
        elements.emailParams.classList.remove('hidden');
    } else {
        elements.taskParams.classList.add('hidden');
    }
}

async function handleTaskSubmit(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    setLoading(btn, true);
    
    const taskType = elements.taskType.value;
    let payload = { task_type: taskType };
    
    if (taskType === 'send_email') {
        payload.params = {
            to: document.getElementById('email-to').value,
            subject: document.getElementById('email-subject').value,
            body: document.getElementById('email-body').value
        };
    }
    
    try {
        const response = await fetch(`${API_BASE}/tasks`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showMessage(elements.taskMessage, `Task queued: ${data.task_id}`, 'success');
            elements.taskForm.reset();
            elements.taskParams.classList.add('hidden');
            loadTasks();
        } else {
            showMessage(elements.taskMessage, data.detail || 'Task submission failed', 'error');
        }
    } catch (error) {
        showMessage(elements.taskMessage, 'Connection error', 'error');
    } finally {
        setLoading(btn, false);
    }
}

async function loadTasks() {
    elements.refreshIndicator.classList.add('active');
    
    try {
        const response = await fetch(`${API_BASE}/tasks`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
        });
        
        if (response.ok) {
            const tasks = await response.json();
            renderTasks(tasks);
        }
    } catch (error) {
        console.error('Failed to load tasks:', error);
    } finally {
        elements.refreshIndicator.classList.remove('active');
    }
}

function renderTasks(tasks) {
    if (!tasks || tasks.length === 0) {
        elements.taskList.innerHTML = '<div class="task-empty">NO TASKS FOUND</div>';
        return;
    }
    
    elements.taskList.innerHTML = tasks.map(task => `
        <div class="task-item">
            <div class="task-header">
                <span class="task-id">${task.id.substring(0, 8)}...</span>
                <span class="task-status ${task.status}">${task.status.toUpperCase()}</span>
            </div>
            <div class="task-type">${formatTaskType(task.task_type)}</div>
            <div class="task-time">${formatTime(task.created_at)}</div>
            ${task.output ? `<div class="task-output">${escapeHtml(task.output)}</div>` : ''}
        </div>
    `).join('');
}

function formatTaskType(type) {
    const types = {
        'send_email': '📧 SEND EMAIL',
        'get_news': '📰 GET RECENT NEWS',
        'system_status': '💻 SYSTEM STATUS'
    };
    return types[type] || type.toUpperCase();
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

// ========== UTILITY FUNCTIONS ==========
function setLoading(btn, loading) {
    const text = btn.querySelector('.btn-text');
    const loader = btn.querySelector('.btn-loader');
    
    if (loading) {
        text.classList.add('hidden');
        loader.classList.remove('hidden');
        btn.disabled = true;
    } else {
        text.classList.remove('hidden');
        loader.classList.add('hidden');
        btn.disabled = false;
    }
}

function showMessage(element, text, type) {
    element.textContent = text;
    element.className = `message ${type}`;
    element.classList.remove('hidden');
    
    setTimeout(() => hideMessage(element), 5000);
}

function hideMessage(element) {
    element.classList.add('hidden');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function startUptimeCounter() {
    uptimeInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const hours = String(Math.floor(elapsed / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
        const seconds = String(elapsed % 60).padStart(2, '0');
        elements.uptime.textContent = `${hours}:${minutes}:${seconds}`;
    }, 1000);
}
