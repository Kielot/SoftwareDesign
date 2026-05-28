<?php
/**
 * nav_auth.php  —  Dynamic Navigation Auth Switcher
 * BICpES Learning Hub
 */

function nav_right_html(): string
{
    if (!is_logged_in()) {
        return '<a href="#login">Login</a>';
    }

    $name      = htmlspecialchars($_SESSION['full_name'],      ENT_QUOTES, 'UTF-8');
    $role      = htmlspecialchars($_SESSION['role'],           ENT_QUOTES, 'UTF-8');
    $stud_num  = htmlspecialchars($_SESSION['student_number'], ENT_QUOTES, 'UTF-8');
    $first     = htmlspecialchars($_SESSION['first_name'],     ENT_QUOTES, 'UTF-8');
    $last      = htmlspecialchars($_SESSION['last_name'],      ENT_QUOTES, 'UTF-8');
    $is_admin  = ($_SESSION['role'] === 'admin');

    // Panel ID line: admins show "Administrator", students show their number
    $panel_id_label = $is_admin
        ? 'Administrator'
        : 'Student No. ' . $stud_num;

    $admin_badge = $is_admin
        ? '<span style="font-size:10px;background:rgba(155,70,212,.25);border:1px solid rgba(155,70,212,.5);color:#cc88f5;padding:2px 10px;border-radius:50px;letter-spacing:1.5px;font-weight:700;text-transform:uppercase;display:inline-block;margin-top:4px;">Admin</span>'
        : '';

    $admin_menu = $is_admin
        ? '<a href="admin_dashboard.php" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:12px;cursor:pointer;color:rgba(245,245,245,.70);font-size:14px;font-weight:500;text-decoration:none;transition:background .2s;font-family:\'Manrope\',sans-serif;" onmouseover="this.style.background=\'rgba(155,70,212,.18)\'" onmouseout="this.style.background=\'transparent\'">
                <div class="item-text">
                    <span class="item-label">Admin Dashboard</span>
                    <span class="item-sub">Manage content</span>
                </div>
                <span class="panel-chevron">›</span>
               </a>
               <div class="panel-divider"></div>'
        : '';

    // For the edit modal: fetch DOB from DB so we can pre-fill it
    $dob_value = '';
    try {
        $db   = get_db();
        $stmt = $db->prepare('SELECT date_of_birth FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            $dob_value = htmlspecialchars($row['date_of_birth'], ENT_QUOTES, 'UTF-8');
        }
    } catch (\Exception $e) {
        // non-fatal
    }

    // Student-number field: admins get readonly "N/A", students get their number
    $stud_field_val   = $is_admin ? 'N/A (Admin Account)' : $stud_num;
    $stud_field_style = 'opacity:.5;cursor:not-allowed;';   // always readonly

    return <<<HTML
    <!-- ── User Panel root ── -->
    <div id="user-panel-root">

        <!-- Trigger button -->
        <button class="user_button" id="userBtn" title="Account">
            <svg viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z"/>
                <path d="M20 21C20 16.5817 16.4183 13 12 13C7.58172 13 4 16.5817 4 21" stroke="white" stroke-width="2" fill="none"/>
            </svg>
        </button>

        <!-- Overlay -->
        <div class="user-panel-overlay" id="userPanelOverlay">
            <div class="panel-backdrop" id="panelBackdrop"></div>
            <div class="user-panel">

                <div class="panel-header">
                    <div class="panel-avatar">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z"/>
                            <path d="M20 21C20 16.5817 16.4183 13 12 13C7.58172 13 4 16.5817 4 21" stroke="white" stroke-width="2" fill="none"/>
                        </svg>
                    </div>
                    <div>
                        <div class="panel-name">{$name}</div>
                        {$admin_badge}
                        <div class="panel-id">{$panel_id_label}</div>
                    </div>
                </div>

                <div class="panel-menu">
                    {$admin_menu}

                    <button class="panel-item" id="panelBtnEdit">
                        <div class="item-text">
                            <span class="item-label">Edit Information</span>
                            <span class="item-sub">Update your profile details</span>
                        </div>
                        <span class="panel-chevron">›</span>
                    </button>

                    <button class="panel-item" id="panelBtnPass">
                        <div class="item-text">
                            <span class="item-label">Change Password</span>
                            <span class="item-sub">Update your credentials</span>
                        </div>
                        <span class="panel-chevron">›</span>
                    </button>

                    <div class="panel-divider"></div>

                    <button class="panel-item logout" id="panelBtnLogout">
                        <div class="item-text">
                            <span class="item-label">Logout</span>
                            <span class="item-sub">Sign out of your account</span>
                        </div>
                    </button>
                </div>

            </div>
        </div>

        <!-- MODAL: Edit Information (pre-filled with real session data) -->
        <div class="modal-overlay" id="modalEditOverlay">
            <div class="modal-backdrop" id="modalEditBackdrop"></div>
            <div class="modal-box">
                <div class="modal-header">
                    <div class="modal-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="modal-header-text">
                        <h2 class="modal-title">Edit Information</h2>
                        <p class="modal-subtitle">Update your profile details below</p>
                    </div>
                    <button class="modal-close" id="modalEditClose">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="modal-field-group">
                        <label class="modal-label">Student Number</label>
                        <input class="modal-input" type="text" id="editStudNum" value="{$stud_field_val}" readonly style="{$stud_field_style}">
                    </div>
                    <div class="modal-row">
                        <div class="modal-field-group">
                            <label class="modal-label">Last Name</label>
                            <input class="modal-input" type="text" id="editLastName" placeholder="Last Name" value="{$last}">
                        </div>
                        <div class="modal-field-group">
                            <label class="modal-label">First Name</label>
                            <input class="modal-input" type="text" id="editFirstName" placeholder="First Name" value="{$first}">
                        </div>
                    </div>
                    <div class="modal-field-group">
                        <label class="modal-label">Date of Birth</label>
                        <input class="modal-input" type="date" id="editDob" value="{$dob_value}">
                    </div>
                    <div id="editMsg" style="font-size:12px;min-height:16px;color:#f87171;font-family:'Manrope',sans-serif;"></div>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn secondary" id="modalEditCancel">Cancel</button>
                    <button class="modal-btn primary" id="modalEditSave">Save Changes</button>
                </div>
            </div>
        </div>

        <!-- MODAL: Change Password -->
        <div class="modal-overlay" id="modalPassOverlay">
            <div class="modal-backdrop" id="modalPassBackdrop"></div>
            <div class="modal-box">
                <div class="modal-header">
                    <div class="modal-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="white" stroke-width="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="modal-header-text">
                        <h2 class="modal-title">Change Password</h2>
                        <p class="modal-subtitle">Keep your account secure</p>
                    </div>
                    <button class="modal-close" id="modalPassClose">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="modal-field-group">
                        <label class="modal-label">Current Password</label>
                        <div class="modal-input-wrap">
                            <input class="modal-input" type="password" id="currentPass" placeholder="Enter current password">
                            <button class="toggle-eye" data-target="currentPass" type="button">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="modal-field-group">
                        <label class="modal-label">New Password</label>
                        <div class="modal-input-wrap">
                            <input class="modal-input" type="password" id="newPass" placeholder="Enter new password">
                            <button class="toggle-eye" data-target="newPass" type="button">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                            </button>
                        </div>
                        <div class="pass-strength-bar"><div class="pass-strength-fill" id="passStrengthFill"></div></div>
                        <span class="pass-strength-label" id="passStrengthLabel"></span>
                    </div>
                    <div class="modal-field-group">
                        <label class="modal-label">Confirm New Password</label>
                        <div class="modal-input-wrap">
                            <input class="modal-input" type="password" id="confirmPass" placeholder="Confirm new password">
                            <button class="toggle-eye" data-target="confirmPass" type="button">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                            </button>
                        </div>
                        <span class="pass-match-label" id="passMatchLabel"></span>
                    </div>
                    <div id="passMsg" style="font-size:12px;min-height:16px;color:#f87171;font-family:'Manrope',sans-serif;"></div>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn secondary" id="modalPassCancel">Cancel</button>
                    <button class="modal-btn primary" id="modalPassSave">Update Password</button>
                </div>
            </div>
        </div>

    </div><!-- /#user-panel-root -->

    <!-- PHP-side session state exposed to JS -->
    <script>
    window.BICPES_SESSION = {
        loggedIn:      true,
        role:          "{$role}",
        fullName:      "{$name}",
        studentNumber: "{$stud_num}",
        firstName:     "{$first}",
        lastName:      "{$last}"
    };
    </script>
HTML;
}

/**
 * Returns the inline JS block for auth-aware behaviour.
 * Paste just before </body> on index.php.
 */
function nav_scripts_html(): string
{
    return <<<'SCRIPT'
<script>
/* ── AUTH-AWARE NAV SCRIPT ──────────────────────────────────────────────── */
(function () {
    'use strict';

    const $ = id => document.getElementById(id);

    function openModal(id)  { const o = $(id); if (o) { o.classList.add('open');    document.body.style.overflow = 'hidden'; } }
    function closeModal(id) { const o = $(id); if (o) { o.classList.remove('open'); document.body.style.overflow = '';       } }

    /* ── Login / Sign-up form (index.php only) ─────────────────────────── */
    const loginSection = $('login');
    if (loginSection) {
        const signUpBtn = $('signUp');
        const signInBtn = $('signIn');
        const closeBtn  = document.querySelector('.close_btn');

        if (signUpBtn) signUpBtn.addEventListener('click', () => {
            loginSection.classList.add('right-panel-active');
            if (closeBtn) closeBtn.style.color = '#9b46d4';
        });
        if (signInBtn) signInBtn.addEventListener('click', () => {
            loginSection.classList.remove('right-panel-active');
            if (closeBtn) closeBtn.style.color = '#efefef';
        });

        /* Sign-up form submit */
        const signupForm = document.querySelector('.new_acc form');
        if (signupForm) {
            signupForm.addEventListener('submit', async e => {
                e.preventDefault();
                const inputs  = signupForm.querySelectorAll('input');
                const payload = {
                    action:         'register',
                    student_number: inputs[0].value,
                    last_name:      inputs[1].value,
                    first_name:     inputs[2].value,
                    date_of_birth:  inputs[3].value,
                    password:       inputs[4].value,
                };
                if (inputs[4].value !== inputs[5].value) {
                    return showFormMsg(signupForm, 'Passwords do not match.', 'error');
                }
                const res = await postAuth(payload);
                showFormMsg(signupForm, res.message, res.success ? 'ok' : 'error');
                if (res.success) signupForm.reset();
            });
        }

        /* Login form submit */
        const loginForm = document.querySelector('.old_acc form');
        if (loginForm) {
            loginForm.addEventListener('submit', async e => {
                e.preventDefault();
                const inputs  = loginForm.querySelectorAll('input');
                const payload = {
                    action:         'login',
                    student_number: inputs[0].value,
                    password:       inputs[1].value,
                };
                const res = await postAuth(payload);
                if (res.success) {
                    // Force a full page reload so PHP re-renders with the new session
                    window.location.replace('index.php');
                } else {
                    showFormMsg(loginForm, res.message, 'error');
                }
            });
        }

        // Auto-open login modal when redirected with ?require_login=1
        if (new URLSearchParams(window.location.search).get('require_login') === '1') {
            loginSection.scrollIntoView({ behavior: 'smooth' });
            // tiny delay so the scroll settles before hash-based show kicks in
            setTimeout(() => { window.location.hash = 'login'; }, 100);
        }
    }

    /* ── User panel (injected by PHP on all logged-in pages) ─────────── */
    const userBtn       = $('userBtn');
    const panelOverlay  = $('userPanelOverlay');
    const panelBackdrop = $('panelBackdrop');

    if (userBtn && panelOverlay) {
        const close = () => panelOverlay.classList.remove('open');
        const open  = () => panelOverlay.classList.add('open');

        userBtn.addEventListener('click', e => {
            e.stopPropagation();
            panelOverlay.classList.contains('open') ? close() : open();
        });
        if (panelBackdrop) panelBackdrop.addEventListener('click', close);
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                close();
                closeModal('modalEditOverlay');
                closeModal('modalPassOverlay');
            }
        });

        const panelBtnEdit   = $('panelBtnEdit');
        const panelBtnPass   = $('panelBtnPass');
        const panelBtnLogout = $('panelBtnLogout');

        if (panelBtnEdit)   panelBtnEdit.addEventListener('click',   () => { close(); openModal('modalEditOverlay'); });
        if (panelBtnPass)   panelBtnPass.addEventListener('click',   () => { close(); openModal('modalPassOverlay'); });
        if (panelBtnLogout) panelBtnLogout.addEventListener('click', () => {
            postAuth({ action: 'logout' }).finally(() => { location.href = 'index.php'; });
        });
    }

    /* ── Modal close buttons ─────────────────────────────────────────── */
    document.addEventListener('click', e => {
        if (e.target.id === 'modalEditClose'  || e.target.id === 'modalEditCancel')  closeModal('modalEditOverlay');
        if (e.target.id === 'modalPassClose'  || e.target.id === 'modalPassCancel')  closeModal('modalPassOverlay');
        if (e.target.id === 'modalEditBackdrop')                                       closeModal('modalEditOverlay');
        if (e.target.id === 'modalPassBackdrop')                                       closeModal('modalPassOverlay');
    });

    /* ── Save — Edit Info ────────────────────────────────────────────── */
    const editSave = $('modalEditSave');
    if (editSave) {
        editSave.addEventListener('click', async () => {
            const payload = {
                action:        'update_profile',
                last_name:     $('editLastName')?.value  ?? '',
                first_name:    $('editFirstName')?.value ?? '',
                date_of_birth: $('editDob')?.value       ?? '',
            };
            const res = await postAuth(payload);
            const msg = $('editMsg');
            if (msg) { msg.textContent = res.message; msg.style.color = res.success ? '#4ade80' : '#f87171'; }
            if (res.success) {
                editSave.textContent = '✓ Saved!';
                editSave.classList.add('success');
                setTimeout(() => {
                    editSave.textContent = 'Save Changes';
                    editSave.classList.remove('success');
                    closeModal('modalEditOverlay');
                    location.reload(); // refresh so nav name updates
                }, 1400);
            }
        });
    }

    /* ── Save — Change Password ──────────────────────────────────────── */
    const passSave = $('modalPassSave');
    if (passSave) {
        passSave.addEventListener('click', async () => {
            const np  = $('newPass')?.value     ?? '';
            const cp  = $('confirmPass')?.value ?? '';
            const msg = $('passMsg');
            if (np !== cp)     { if (msg) msg.textContent = '⚠ Passwords do not match.';      return; }
            if (np.length < 8) { if (msg) msg.textContent = '⚠ Min. 8 characters required.';  return; }
            const payload = { action: 'change_password', current_password: $('currentPass')?.value ?? '', new_password: np };
            const res = await postAuth(payload);
            if (msg) { msg.textContent = res.message; msg.style.color = res.success ? '#4ade80' : '#f87171'; }
            if (res.success) {
                passSave.textContent = '✓ Updated!';
                passSave.classList.add('success');
                setTimeout(() => {
                    passSave.textContent = 'Update Password';
                    passSave.classList.remove('success');
                    closeModal('modalPassOverlay');
                }, 1400);
            }
        });
    }

    /* ── Toggle password visibility ──────────────────────────────────── */
    document.addEventListener('click', e => {
        const btn = e.target.closest('.toggle-eye');
        if (!btn) return;
        const input = $(btn.dataset.target);
        if (!input) return;
        input.type      = input.type === 'password' ? 'text' : 'password';
        btn.style.color = input.type === 'text' ? 'rgba(155,70,212,.80)' : 'rgba(245,245,245,.30)';
    });

    /* ── Password strength meter ─────────────────────────────────────── */
    document.addEventListener('input', e => {
        if (e.target.id !== 'newPass') return;
        const val   = e.target.value;
        const fill  = $('passStrengthFill');
        const label = $('passStrengthLabel');
        if (!fill || !label) return;
        let s = 0;
        if (val.length >= 6)  s++;
        if (val.length >= 10) s++;
        if (/[A-Z]/.test(val) && /[0-9]/.test(val)) s++;
        if (/[^A-Za-z0-9]/.test(val)) s++;
        const lvls = [
            { w: '0%',   c: 'transparent', t: '' },
            { w: '33%',  c: '#f87171',     t: 'Weak' },
            { w: '66%',  c: '#fb923c',     t: 'Fair' },
            { w: '88%',  c: '#facc15',     t: 'Good' },
            { w: '100%', c: '#4ade80',     t: 'Strong' },
        ];
        const lvl = val.length === 0 ? lvls[0] : lvls[Math.min(s, 4)];
        fill.style.width = lvl.w; fill.style.background = lvl.c;
        label.textContent = lvl.t; label.style.color = lvl.c;
    });

    /* ── Password match feedback ─────────────────────────────────────── */
    document.addEventListener('input', e => {
        if (e.target.id !== 'confirmPass') return;
        const np    = $('newPass');
        const cp    = e.target;
        const label = $('passMatchLabel');
        if (!np || !label) return;
        if (!cp.value) { label.textContent = ''; cp.style.borderColor = ''; return; }
        const match = cp.value === np.value;
        label.textContent    = match ? '✓ Passwords match' : '✗ Passwords do not match';
        label.style.color    = match ? '#4ade80' : '#f87171';
        cp.style.borderColor = match ? 'rgba(74,222,128,.50)' : 'rgba(248,113,113,.50)';
    });

    /* ── Shared fetch helper ─────────────────────────────────────────── */
    async function postAuth(payload) {
        try {
            const r = await fetch('auth.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload),
            });
            return await r.json();
        } catch { return { success: false, message: 'Network error. Please try again.' }; }
    }

    /* ── Form message helper ─────────────────────────────────────────── */
    function showFormMsg(form, msg, type) {
        let el = form.querySelector('.form-msg');
        if (!el) {
            el = document.createElement('p');
            el.className = 'form-msg';
            el.style.cssText = 'font-size:12px;margin-top:8px;font-family:"Manrope",sans-serif;text-align:center;';
            form.appendChild(el);
        }
        el.textContent = msg;
        el.style.color = type === 'ok' ? '#4ade80' : '#f87171';
    }

})();
</script>
SCRIPT;
}