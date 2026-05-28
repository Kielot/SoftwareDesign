<?php
require_once __DIR__ . '/auth.php';
require_admin();                      // redirects to index.php if not admin
require_once __DIR__ . '/nav_auth.php';

$db = get_db();

/* ══════════════════════════════════════════════════════════════════════════════
   AJAX / POST HANDLER  (all mutations go through here)
══════════════════════════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    // helper
    $ok  = fn(string $msg, array $extra = []) => json_encode(['success' => true,  'message' => $msg] + $extra);
    $err = fn(string $msg)                     => json_encode(['success' => false, 'message' => $msg]);

    switch ($action) {

        /* ── TOPICS ── */
        case 'topic_create':
            $num      = (int)($input['topic_num']   ?? 0);
            $name     = trim($input['name']         ?? '');
            $desc     = trim($input['description']  ?? '');
            $cat      = trim($input['category']     ?? '');
            $overview = trim($input['overview_body'] ?? '');
            $pdf      = trim($input['pdf_filename']  ?? '');
            $acts_raw = $input['activities_json']    ?? '';
            $acts_json= ($acts_raw !== '' && json_decode($acts_raw) !== null) ? $acts_raw : null;
            if (!$num || !$name || !$desc || !$cat) { echo $err('All fields required.'); break; }
            $s = $db->prepare('INSERT INTO topics (topic_num,name,description,category,overview_body,pdf_filename,activities) VALUES (?,?,?,?,?,?,?)');
            $s->bind_param('issssss', $num, $name, $desc, $cat, $overview, $pdf, $acts_json);
            if ($s->execute()) { echo $ok('Topic created.', ['id' => $db->insert_id]); }
            else               { echo $err('Failed: ' . $s->error); }
            $s->close();
            break;

        case 'topic_update':
            $id        = (int)($input['id']           ?? 0);
            $num       = (int)($input['topic_num']    ?? 0);
            $name      = trim($input['name']          ?? '');
            $desc      = trim($input['description']   ?? '');
            $cat       = trim($input['category']      ?? '');
            $overview  = trim($input['overview_body'] ?? '');
            $pdf       = trim($input['pdf_filename']  ?? '');
            $acts_raw  = $input['activities_json']    ?? '';
            $acts_json = ($acts_raw !== '' && json_decode($acts_raw) !== null) ? $acts_raw : null;
            if (!$id || !$num || !$name || !$desc || !$cat) { echo $err('All fields required.'); break; }
            $s = $db->prepare(
                'UPDATE topics SET topic_num=?,name=?,description=?,category=?,overview_body=?,pdf_filename=?,activities=? WHERE id=?'
            );
            $s->bind_param('issssssi', $num, $name, $desc, $cat, $overview, $pdf, $acts_json, $id);
            echo $s->execute() ? $ok('Topic updated.') : $err('Failed: ' . $s->error);
            $s->close();
            break;

        case 'topic_delete':
            $id = (int)($input['id'] ?? 0);
            if (!$id) { echo $err('Invalid ID.'); break; }
            $s = $db->prepare('DELETE FROM topics WHERE id=?');
            $s->bind_param('i', $id);
            echo $s->execute() ? $ok('Topic deleted.') : $err('Failed: ' . $s->error);
            $s->close();
            break;

        /* ── PROJECTS ── */
        case 'project_create':
            $title      = trim($input['title']           ?? '');
            $desc       = trim($input['description']     ?? '');
            $req        = trim($input['requirements']    ?? '');
            $diff       = trim($input['difficulty']      ?? 'Intermediate');
            $cat        = trim($input['category']        ?? 'General');
            $year       = (int)($input['year']           ?? 2026);
            $hero_tag   = trim($input['hero_tag']        ?? '');
            $overview   = trim($input['overview_body']   ?? '');
            $vid_title  = trim($input['video_title']     ?? '');
            $vid_dur    = trim($input['video_duration']  ?? '');
            $vid_url    = trim($input['video_url']       ?? '');
            $vid_type   = trim($input['video_type']      ?? '');
            $comps_raw  = $input['components_json']      ?? '';
            $steps_raw  = $input['procedure_steps']      ?? '';
            $comps_json = ($comps_raw !== '' && json_decode($comps_raw) !== null) ? $comps_raw : null;
            $steps_json = ($steps_raw !== '' && json_decode($steps_raw) !== null) ? $steps_raw : null;
            if (!$title || !$desc || !$req) { echo $err('Title, description and requirements are required.'); break; }
            $s = $db->prepare('INSERT INTO projects (title,description,requirements,difficulty,category,year,hero_tag,overview_body,components_json,procedure_steps,video_title,video_duration,video_url,video_type) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $s->bind_param('sssssissssssss', $title, $desc, $req, $diff, $cat, $year, $hero_tag, $overview, $comps_json, $steps_json, $vid_title, $vid_dur, $vid_url, $vid_type);
            if ($s->execute()) { echo $ok('Project created.', ['id' => $db->insert_id]); }
            else               { echo $err('Failed: ' . $s->error); }
            $s->close();
            break;

        case 'project_update':
            $id           = (int)($input['id']              ?? 0);
            $title        = trim($input['title']            ?? '');
            $desc         = trim($input['description']      ?? '');
            $req          = trim($input['requirements']     ?? '');
            $diff         = trim($input['difficulty']       ?? 'Intermediate');
            $cat          = trim($input['category']         ?? 'General');
            $year         = (int)($input['year']            ?? 2026);
            $hero_tag     = trim($input['hero_tag']         ?? '');
            $overview     = trim($input['overview_body']    ?? '');
            $vid_title    = trim($input['video_title']      ?? '');
            $vid_dur      = trim($input['video_duration']   ?? '');
            $vid_url      = trim($input['video_url']        ?? '');
            $vid_type     = trim($input['video_type']       ?? '');
            $comps_raw    = $input['components_json']       ?? '';
            $steps_raw    = $input['procedure_steps']       ?? '';
            $comps_json   = ($comps_raw !== '' && json_decode($comps_raw) !== null) ? $comps_raw : null;
            $steps_json   = ($steps_raw !== '' && json_decode($steps_raw) !== null) ? $steps_raw : null;
            if (!$id || !$title || !$desc) { echo $err('Title and description are required.'); break; }
            $s = $db->prepare(
                'UPDATE projects SET title=?,description=?,requirements=?,difficulty=?,category=?,year=?,hero_tag=?,overview_body=?,components_json=?,procedure_steps=?,video_title=?,video_duration=?,video_url=?,video_type=? WHERE id=?'
            );
            $s->bind_param('sssssissssssssi', $title, $desc, $req, $diff, $cat, $year, $hero_tag, $overview, $comps_json, $steps_json, $vid_title, $vid_dur, $vid_url, $vid_type, $id);
            echo $s->execute() ? $ok('Project updated.') : $err('Failed: ' . $s->error);
            $s->close();
            break;

        case 'project_delete':
            $id = (int)($input['id'] ?? 0);
            if (!$id) { echo $err('Invalid ID.'); break; }
            $s = $db->prepare('DELETE FROM projects WHERE id=?');
            $s->bind_param('i', $id);
            echo $s->execute() ? $ok('Project deleted.') : $err('Failed: ' . $s->error);
            $s->close();
            break;

        /* ── TOOLS ── */
        case 'tool_create':
            $name = trim($input['tool_name']   ?? '');
            $desc = trim($input['description'] ?? '');
            $url  = trim($input['url_path']    ?? '');
            if (!$name || !$desc || !$url) { echo $err('All fields required.'); break; }
            $s = $db->prepare('INSERT INTO simulation_tools (tool_name,description,url_path) VALUES (?,?,?)');
            $s->bind_param('sss', $name, $desc, $url);
            if ($s->execute()) { echo $ok('Tool created.', ['id' => $db->insert_id]); }
            else               { echo $err('Failed: ' . $s->error); }
            $s->close();
            break;

        case 'tool_update':
            $id   = (int)   ($input['id']          ?? 0);
            $name = trim($input['tool_name']   ?? '');
            $desc = trim($input['description'] ?? '');
            $url  = trim($input['url_path']    ?? '');
            if (!$id || !$name || !$desc || !$url) { echo $err('All fields required.'); break; }
            $s = $db->prepare('UPDATE simulation_tools SET tool_name=?,description=?,url_path=? WHERE id=?');
            $s->bind_param('sssi', $name, $desc, $url, $id);
            echo $s->execute() ? $ok('Tool updated.') : $err('Failed: ' . $s->error);
            $s->close();
            break;

        case 'tool_delete':
            $id = (int)($input['id'] ?? 0);
            if (!$id) { echo $err('Invalid ID.'); break; }
            $s = $db->prepare('DELETE FROM simulation_tools WHERE id=?');
            $s->bind_param('i', $id);
            echo $s->execute() ? $ok('Tool deleted.') : $err('Failed: ' . $s->error);
            $s->close();
            break;

        default:
            http_response_code(400);
            echo $err('Unknown action.');
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════════
   FETCH DATA FOR PAGE RENDER
══════════════════════════════════════════════════════════════════════════════ */
$topics   = $db->query('SELECT * FROM topics            ORDER BY topic_num ASC')->fetch_all(MYSQLI_ASSOC);
$projects = $db->query('SELECT * FROM projects          ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
$tools    = $db->query('SELECT * FROM simulation_tools  ORDER BY id ASC')->fetch_all(MYSQLI_ASSOC);

$topic_count   = count($topics);
$project_count = count($projects);
$tool_count    = count($tools);

// Count students
$user_count = $db->query("SELECT COUNT(*) as c FROM users WHERE role='student'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BICpES — Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="user_design.css">
    <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; text-decoration:none; }

    :root {
        --bg:         #101010;
        --bg-card:    #161616;
        --bg-raised:  #1e1e1e;
        --violet:     #9b46d4;
        --violet-dim: rgba(155,70,212,0.18);
        --violet-gl:  rgba(155,70,212,0.35);
        --white:      #f5f5f5;
        --w70:        rgba(245,245,245,0.70);
        --w40:        rgba(245,245,245,0.40);
        --w15:        rgba(245,245,245,0.15);
        --w08:        rgba(245,245,245,0.08);
        --border:     rgba(245,245,245,0.10);
        --border-v:   rgba(155,70,212,0.30);
        --red:        #f87171;
        --green:      #4ade80;
        --footer:     #160620;
    }

    html { scroll-behavior: smooth; }
    body { background: var(--bg); color: var(--white); font-family: 'Manrope', sans-serif;
           overflow-x: hidden; min-height: 100vh; }

    /* ── NAV ── */
    nav {
        position: fixed; top:0; left:0; right:0;
        display: flex; align-items: center;
        padding: 5px 20px;
        background: var(--bg);
        border-bottom: 1px solid var(--border);
        z-index: 200;
    }
    nav a { color: var(--white); font-family: Arial, sans-serif; font-size: 20px; padding: 10px 20px; }
    .left-side { display:flex; align-items:center; }
    .left-side img { width:75px; height:75px; border-radius:50%; }
    .nav-center { margin-left: auto; margin-right: 50px; }
    .nav-center span { color: var(--violet); }
    .right-side { display:flex; align-items:center; margin-left:auto; }

    /* ── LAYOUT ── */
    .admin-wrap { display: flex; min-height: 100vh; padding-top: 87px; }

    /* ── SIDEBAR ── */
    .sidebar {
        width: 240px; flex-shrink: 0;
        background: var(--bg-card);
        border-right: 1px solid var(--border);
        position: fixed; top: 87px; left: 0; bottom: 0;
        overflow-y: auto; z-index: 100;
        display: flex; flex-direction: column; gap: 4px;
        padding: 24px 12px;
    }
    .sidebar-label { font-size: 10px; letter-spacing: 3px; text-transform: uppercase; color: var(--w40); font-weight: 700; padding: 0 10px 8px; margin-top: 8px; }
    .sidebar-btn {
        display: flex; align-items: center; gap: 12px;
        padding: 11px 14px; border-radius: 12px;
        border: none; background: transparent;
        color: var(--w70); font-size: 13px; font-weight: 500;
        font-family: 'Manrope', sans-serif; cursor: pointer; width: 100%; text-align: left;
        transition: background .2s, color .2s;
    }
    .sidebar-btn:hover  { background: var(--w08); color: var(--white); }
    .sidebar-btn.active { background: var(--violet-dim); color: var(--white); border: 1px solid var(--border-v); }
    .sidebar-btn .icon  { font-size: 17px; width: 22px; text-align:center; }
    .sidebar-divider    { height:1px; background: var(--border); margin: 10px 4px; }
    .stat-chips { display:flex; flex-direction:column; gap:6px; padding: 12px 10px; }
    .stat-chip { display:flex; align-items:center; justify-content:space-between; padding: 8px 12px; border-radius: 10px; background: var(--w08); border: 1px solid var(--border); font-size: 12px; color: var(--w70); }
    .stat-chip span { font-weight:700; color: var(--violet); font-size:14px; }

    /* ── MAIN CONTENT ── */
    .main-content { flex: 1; margin-left: 240px; padding: 40px 48px 80px; min-height: calc(100vh - 87px); }

    /* ── PAGE HEADER ── */
    .page-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 36px; flex-wrap: wrap; gap: 16px; }
    .page-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px, 5vw, 56px); font-weight: 700; line-height: 1.0; color: var(--white); letter-spacing: -1px; }
    .page-title em { font-style:italic; color: #b87ae8; }
    .page-subtitle { font-size: 13px; color: var(--w40); margin-top: 4px; }

    /* ── SECTION ── */
    .section { display: none; animation: fadeUp .4s ease both; }
    .section.active { display: block; }
    .section-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
    .section-title { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 600; color: var(--white); }

    /* ── ADD BUTTON ── */
    .btn-add { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: var(--violet); color: #fff; border: none; border-radius: 12px; font-size: 13px; font-weight: 600; font-family: 'Manrope', sans-serif; cursor: pointer; transition: background .2s, transform .2s, box-shadow .2s; }
    .btn-add:hover { background:#b060e8; transform:translateY(-1px); box-shadow:0 0 18px var(--violet-gl); }

    /* ── TABLE ── */
    .table-wrap { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
    table { width:100%; border-collapse:collapse; }
    thead tr { border-bottom: 1px solid var(--border); }
    th { font-size: 10px; letter-spacing: 2.5px; text-transform: uppercase; color: var(--w40); font-weight:700; padding: 14px 20px; text-align:left; }
    td { padding: 14px 20px; border-bottom: 1px solid var(--w08); font-size: 13px; color: var(--w70); vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    td:first-child { color: var(--white); font-weight:600; }
    .td-desc { max-width: 280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .pill { display:inline-block; padding:3px 12px; border-radius:50px; font-size:11px; font-weight:700; background: var(--violet-dim); border: 1px solid var(--border-v); color: #cc88f5; }
    .pill.beg  { background:rgba(74,222,128,.12); border-color:rgba(74,222,128,.30); color:#4ade80; }
    .pill.int  { background:rgba(251,146,60,.12);  border-color:rgba(251,146,60,.30);  color:#fb923c; }
    .pill.adv  { background:rgba(248,113,113,.12); border-color:rgba(248,113,113,.30); color:#f87171; }
    .td-actions { display:flex; gap:8px; }
    .btn-icon { padding: 6px 14px; border-radius:8px; border:none; font-size:12px; font-weight:600; cursor:pointer; font-family:'Manrope',sans-serif; transition: all .2s; }
    .btn-edit   { background: var(--violet-dim); color:#cc88f5; border:1px solid var(--border-v); }
    .btn-edit:hover   { background:rgba(155,70,212,.30); color:var(--white); }
    .btn-delete { background:rgba(248,113,113,.10); color:var(--red); border:1px solid rgba(248,113,113,.25); }
    .btn-delete:hover { background:rgba(248,113,113,.22); }

    /* ── CRUD MODAL ── */
    .crud-overlay { position: fixed; inset:0; z-index:400; display:flex; align-items:center; justify-content:center; padding:20px; background: rgba(0,0,0,.75); backdrop-filter:blur(6px); opacity:0; pointer-events:none; transition: opacity .3s; }
    .crud-overlay.open { opacity:1; pointer-events:all; }
    .crud-box { width:100%; max-width:560px; background: var(--bg-card); border: 1px solid var(--border-v); border-radius: 20px; overflow:hidden; box-shadow: 0 32px 80px rgba(0,0,0,.7); transform: translateY(20px) scale(.97); opacity:0; transition: transform .35s cubic-bezier(.34,1.56,.64,1), opacity .25s; }
    .crud-overlay.open .crud-box { transform:translateY(0) scale(1); opacity:1; }
    .crud-header { display:flex; align-items:center; gap:14px; padding: 22px 24px 18px; background: linear-gradient(135deg,#2a0a50 0%,#9b46d4 100%); }
    .crud-icon { width:38px; height:38px; border-radius:10px; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .crud-title { font-size:17px; font-weight:700; color:#fff; font-family:'Manrope',sans-serif; }
    .crud-close { margin-left:auto; width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.20); color:rgba(255,255,255,.80); font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition: background .2s, transform .2s; }
    .crud-close:hover { background:rgba(255,255,255,.25); transform:rotate(90deg); }
    .crud-body { padding:24px; display:flex; flex-direction:column; gap:16px; max-height:65vh; overflow-y:auto; }

    /* ── FIELDS ── */
    .field-group { display:flex; flex-direction:column; gap:7px; }
    .field-label { font-size:11px; letter-spacing:1.5px; text-transform:uppercase; font-weight:700; color:rgba(155,70,212,.90); font-family:'Manrope',sans-serif; }
    .field-input, .field-textarea, .field-select { padding:11px 14px; background: var(--bg-raised); border: 1.5px solid var(--border); border-radius:10px; color:var(--white); font-size:13px; font-family:'Manrope',sans-serif; outline:none; transition: border-color .25s, box-shadow .25s; width:100%; }
    .field-input:focus, .field-textarea:focus, .field-select:focus { border-color: rgba(155,70,212,.60); box-shadow: 0 0 0 3px rgba(155,70,212,.12); }
    .field-textarea { resize:vertical; min-height:90px; }
    .field-select option { background:#1e1e1e; }
    .field-row { display:flex; gap:12px; }
    .field-row .field-group { flex:1; }

    /* ── FILE UPLOAD ZONE ── */
    .upload-zone {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 10px; padding: 28px 20px;
        border: 2px dashed var(--border-v);
        border-radius: 12px;
        background: rgba(155,70,212,.06);
        cursor: pointer;
        transition: border-color .25s, background .25s;
        position: relative;
        text-align: center;
    }
    .upload-zone:hover, .upload-zone.drag-over {
        border-color: var(--violet);
        background: var(--violet-dim);
    }
    .upload-zone input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .upload-icon { font-size: 28px; pointer-events: none; }
    .upload-label { font-size: 13px; font-weight: 600; color: var(--w70); pointer-events: none; }
    .upload-sub   { font-size: 11px; color: var(--w40); pointer-events: none; }

    /* file chosen state */
    .upload-zone.has-file { border-style: solid; border-color: rgba(74,222,128,.45); background: rgba(74,222,128,.06); }
    .upload-zone.has-file .upload-label { color: var(--green); }

    /* upload progress */
    .upload-progress { display:none; flex-direction:column; gap:6px; }
    .upload-progress.show { display:flex; }
    .progress-bar-wrap { height:4px; border-radius:2px; background:var(--w08); overflow:hidden; }
    .progress-bar-fill { height:100%; background:var(--violet); border-radius:2px; transition:width .3s ease; }
    .progress-label { font-size:11px; color:var(--w40); }

    /* current file badge */
    .current-file-badge {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: 8px;
        background: var(--w08); border: 1px solid var(--border);
        font-size: 12px; color: var(--w70);
    }
    .current-file-badge .cf-name { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--white); font-weight:600; }
    .cf-clear { cursor:pointer; color:var(--red); font-size:16px; line-height:1; transition:color .2s; background:none; border:none; padding:0 2px; }
    .cf-clear:hover { color:#fca5a5; }

    /* video mode toggle */
    .video-mode-tabs { display:flex; gap:8px; margin-bottom:4px; }
    .vid-tab { padding:7px 18px; border-radius:8px; border:1.5px solid var(--border); background:transparent; color:var(--w40); font-size:12px; font-weight:600; font-family:'Manrope',sans-serif; cursor:pointer; transition:all .2s; }
    .vid-tab.active { background:var(--violet-dim); border-color:var(--border-v); color:#cc88f5; }
    .vid-panel { display:none; }
    .vid-panel.active { display:flex; flex-direction:column; gap:14px; }

    /* ── CRUD FOOTER ── */
    .crud-footer { display:flex; justify-content:flex-end; gap:10px; padding: 18px 24px; border-top: 1px solid var(--border); }
    .btn-cancel { padding:10px 22px; border-radius:10px; border:1px solid var(--border); background: var(--w08); color:var(--w40); font-size:13px; font-weight:600; font-family:'Manrope',sans-serif; cursor:pointer; transition:all .2s; }
    .btn-cancel:hover { background:var(--w15); color:var(--white); }
    .btn-save { padding:10px 22px; border-radius:10px; border:1px solid rgba(155,70,212,.50); background: var(--violet); color:#fff; font-size:13px; font-weight:600; font-family:'Manrope',sans-serif; cursor:pointer; transition:all .2s; }
    .btn-save:hover { background:#b060e8; box-shadow:0 0 16px var(--violet-gl); transform:translateY(-1px); }
    .btn-save.success { background:#2e7d4f; border-color:#2e7d4f; }

    /* ── CONFIRM DELETE ── */
    .confirm-overlay { position:fixed; inset:0; z-index:500; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,.80); backdrop-filter:blur(6px); opacity:0; pointer-events:none; transition:opacity .25s; }
    .confirm-overlay.open { opacity:1; pointer-events:all; }
    .confirm-box { background:var(--bg-card); border:1px solid rgba(248,113,113,.30); border-radius:18px; padding:32px; max-width:380px; width:100%; text-align:center; transform:scale(.95); opacity:0; transition:transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s; }
    .confirm-overlay.open .confirm-box { transform:scale(1); opacity:1; }
    .confirm-icon { font-size:36px; margin-bottom:14px; }
    .confirm-title { font-size:18px; font-weight:700; color:var(--white); margin-bottom:8px; }
    .confirm-sub { font-size:13px; color:var(--w70); line-height:1.6; margin-bottom:24px; }
    .confirm-btns { display:flex; gap:10px; justify-content:center; }
    .btn-confirm-del { padding:10px 24px; border-radius:10px; background:rgba(248,113,113,.15); border:1px solid rgba(248,113,113,.40); color:var(--red); font-size:13px; font-weight:700; font-family:'Manrope',sans-serif; cursor:pointer; transition:all .2s; }
    .btn-confirm-del:hover { background:rgba(248,113,113,.28); }

    /* ── TOAST ── */
    .toast { position:fixed; bottom:28px; right:28px; z-index:9999; padding:14px 20px; border-radius:12px; font-size:13px; font-weight:600; font-family:'Manrope',sans-serif; display:flex; align-items:center; gap:10px; transform:translateY(80px); opacity:0; transition:transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s; pointer-events:none; max-width:340px; }
    .toast.show { transform:translateY(0); opacity:1; }
    .toast.ok  { background:#1a3a28; border:1px solid rgba(74,222,128,.35); color:var(--green); }
    .toast.err { background:#3a1a1a; border:1px solid rgba(248,113,113,.35); color:var(--red); }

    /* ── EMPTY STATE ── */
    .empty { text-align:center; padding:48px 24px; color:var(--w40); font-size:14px; }
    .empty-icon { font-size:36px; margin-bottom:12px; }

    /* ── FOOTER ── */
    footer { margin-left:240px; padding:30px 48px; text-align:center; background:var(--footer); color:rgba(255,255,255,.40); font-size:12px; }

    @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
    </style>
</head>
<body>

<!-- NAV -->
<nav>
    <a href="index.php" class="left-side"><img src="Images/Logo/BICpES Learning Hub Logo.png" alt="Logo"></a>
    <div class="nav-center">
        <a href="index.php"><span><strong>BICpES</strong></span> Learning Hub</a>
    </div>
    <div class="right-side">
        <?php echo nav_right_html(); ?>
    </div>
</nav>

<div class="admin-wrap">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-label">Overview</div>
        <div class="stat-chips">
            <div class="stat-chip">Topics   <span><?= $topic_count ?></span></div>
            <div class="stat-chip">Projects <span><?= $project_count ?></span></div>
            <div class="stat-chip">Tools    <span><?= $tool_count ?></span></div>
            <div class="stat-chip">Students <span><?= $user_count ?></span></div>
        </div>

        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Manage</div>

        <button class="sidebar-btn active" data-tab="topics">
            <span class="icon">📚</span> Topics
        </button>
        <button class="sidebar-btn" data-tab="projects">
            <span class="icon">🔧</span> Projects
        </button>
        <button class="sidebar-btn" data-tab="tools">
            <span class="icon">💻</span> Sim Tools
        </button>

        <div class="sidebar-divider"></div>
        <button class="sidebar-btn" onclick="location.href='index.php'">
            <span class="icon">🏠</span> Back to Site
        </button>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Admin <em>Dashboard</em></div>
                <div class="page-subtitle">Manage platform content — Topics, Projects, and Tools</div>
            </div>
        </div>

        <!-- ═══ TOPICS SECTION ═══ -->
        <div class="section active" id="tab-topics">
            <div class="section-head">
                <div class="section-title">Topics</div>
                <button class="btn-add" onclick="openCrud('topic','create')">＋ Add Topic</button>
            </div>
            <div class="table-wrap">
                <?php if (empty($topics)): ?>
                    <div class="empty"><div class="empty-icon">📭</div>No topics yet. Add one!</div>
                <?php else: ?>
                <table>
                    <thead><tr>
                        <th>#</th><th>Name</th><th>Category</th><th>PDF</th><th>Description</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($topics as $t): ?>
                    <tr>
                        <td><span class="pill"><?= str_pad($t['topic_num'],2,'0',STR_PAD_LEFT) ?></span></td>
                        <td><?= htmlspecialchars($t['name']) ?></td>
                        <td><?= htmlspecialchars($t['category']) ?></td>
                        <td>
                            <?php if (!empty($t['pdf_filename'])): ?>
                                <span style="font-size:11px;color:#4ade80;">✓ <?= htmlspecialchars($t['pdf_filename']) ?></span>
                            <?php else: ?>
                                <span style="font-size:11px;color:rgba(245,245,245,.25);">None</span>
                            <?php endif; ?>
                        </td>
                        <td class="td-desc"><?= htmlspecialchars($t['description']) ?></td>
                        <td><div class="td-actions">
                            <button class="btn-icon btn-edit"
                                onclick='openCrud("topic","edit",<?= json_encode($t, JSON_HEX_APOS|JSON_HEX_TAG) ?>)'>Edit</button>
                            <button class="btn-icon btn-delete"
                                onclick='confirmDelete("topic",<?= $t["id"] ?>,"<?= htmlspecialchars(addslashes($t["name"])) ?>")'>Delete</button>
                        </div></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══ PROJECTS SECTION ═══ -->
        <div class="section" id="tab-projects">
            <div class="section-head">
                <div class="section-title">Projects</div>
                <button class="btn-add" onclick="openCrud('project','create')">＋ Add Project</button>
            </div>
            <div class="table-wrap">
                <?php if (empty($projects)): ?>
                    <div class="empty"><div class="empty-icon">📭</div>No projects yet. Add one!</div>
                <?php else: ?>
                <table>
                    <thead><tr>
                        <th>Title</th><th>Difficulty</th><th>Video</th><th>Description</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($projects as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['title']) ?></td>
                        <td><span class="pill <?= strtolower(substr($p['difficulty'],0,3)) ?>">
                            <?= $p['difficulty'] ?></span></td>
                        <td>
                            <?php if (!empty($p['video_url'])): ?>
                                <?php if (($p['video_type'] ?? '') === 'file'): ?>
                                    <span style="font-size:11px;color:#4ade80;">📁 <?= htmlspecialchars($p['video_url']) ?></span>
                                <?php else: ?>
                                    <span style="font-size:11px;color:#4ade80;">🔗 URL set</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="font-size:11px;color:rgba(245,245,245,.25);">None</span>
                            <?php endif; ?>
                        </td>
                        <td class="td-desc"><?= htmlspecialchars($p['description']) ?></td>
                        <td><div class="td-actions">
                            <button class="btn-icon btn-edit"
                                onclick='openCrud("project","edit",<?= json_encode($p, JSON_HEX_APOS|JSON_HEX_TAG) ?>)'>Edit</button>
                            <button class="btn-icon btn-delete"
                                onclick='confirmDelete("project",<?= $p["id"] ?>,"<?= htmlspecialchars(addslashes($p["title"])) ?>")'>Delete</button>
                        </div></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══ TOOLS SECTION ═══ -->
        <div class="section" id="tab-tools">
            <div class="section-head">
                <div class="section-title">Simulation Tools</div>
                <button class="btn-add" onclick="openCrud('tool','create')">＋ Add Tool</button>
            </div>
            <div class="table-wrap">
                <?php if (empty($tools)): ?>
                    <div class="empty"><div class="empty-icon">📭</div>No tools yet. Add one!</div>
                <?php else: ?>
                <table>
                    <thead><tr>
                        <th>Tool Name</th><th>URL / Path</th><th>Description</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($tools as $tl): ?>
                    <tr>
                        <td><?= htmlspecialchars($tl['tool_name']) ?></td>
                        <td><code style="font-size:11px;color:#cc88f5;"><?= htmlspecialchars($tl['url_path']) ?></code></td>
                        <td class="td-desc"><?= htmlspecialchars($tl['description']) ?></td>
                        <td><div class="td-actions">
                            <button class="btn-icon btn-edit"
                                onclick='openCrud("tool","edit",<?= json_encode($tl, JSON_HEX_APOS|JSON_HEX_TAG) ?>)'>Edit</button>
                            <button class="btn-icon btn-delete"
                                onclick='confirmDelete("tool",<?= $tl["id"] ?>,"<?= htmlspecialchars(addslashes($tl["tool_name"])) ?>")'>Delete</button>
                        </div></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<footer>© 2026 BICpES Learning Hub — Admin Panel</footer>

<!-- ══ CRUD MODAL ══ -->
<div class="crud-overlay" id="crudOverlay">
    <div class="crud-box">
        <div class="crud-header">
            <div class="crud-icon" id="crudIcon">📝</div>
            <div class="crud-title" id="crudTitle">Add Topic</div>
            <button class="crud-close" onclick="closeCrud()">×</button>
        </div>
        <div class="crud-body" id="crudBody"><!-- dynamic --></div>
        <div class="crud-footer">
            <button class="btn-cancel" onclick="closeCrud()">Cancel</button>
            <button class="btn-save" id="btnSave" onclick="saveCrud()">Save</button>
        </div>
    </div>
</div>

<!-- ══ DELETE CONFIRM ══ -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon">🗑️</div>
        <div class="confirm-title">Delete this item?</div>
        <div class="confirm-sub" id="confirmSub">This action cannot be undone.</div>
        <div class="confirm-btns">
            <button class="btn-cancel" onclick="closeConfirm()">Cancel</button>
            <button class="btn-confirm-del" id="btnConfirmDel">Yes, Delete</button>
        </div>
    </div>
</div>

<!-- ══ TOAST ══ -->
<div class="toast" id="toast"></div>

<?php echo nav_scripts_html(); ?>

<script>
/* ── STATE ── */
let crudType = '';
let crudMode = '';
let crudId   = 0;

// Tracks a pending file upload (PDF or video) to upload BEFORE saving the record
let pendingUpload = null;  // { file: File, type: 'pdf'|'video' }

/* ── TAB SWITCHING ── */
document.querySelectorAll('.sidebar-btn[data-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.sidebar-btn[data-tab]').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});

/* ── INJECT SHARED STYLES ── */
(function(){
    const st = document.createElement('style');
    st.textContent = `
        .crud-tabs{display:flex;gap:0;border-bottom:1px solid rgba(245,245,245,.10);margin-bottom:20px;}
        .crud-tab{padding:10px 18px;font-size:12px;font-weight:600;font-family:'Manrope',sans-serif;
                  color:rgba(245,245,245,.45);background:transparent;border:none;
                  border-bottom:2px solid transparent;cursor:pointer;transition:all .2s;margin-bottom:-1px;letter-spacing:.4px;}
        .crud-tab:hover{color:rgba(245,245,245,.75);}
        .crud-tab.active{color:#f5f5f5;border-bottom-color:#9b46d4;}
        .crud-tab-panel{display:none;animation:fadeUp .3s ease both;}
        .crud-tab-panel.active{display:flex;flex-direction:column;gap:16px;}
        .step-card{background:#1e1e1e;border:1px solid rgba(245,245,245,.10);border-radius:10px;
                   padding:14px;display:flex;flex-direction:column;gap:10px;position:relative;}
        .step-card-num{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:#9b46d4;font-weight:700;}
        .btn-remove-step{position:absolute;top:10px;right:10px;background:rgba(248,113,113,.12);
                         border:1px solid rgba(248,113,113,.25);color:#f87171;border-radius:6px;
                         padding:3px 10px;font-size:11px;font-weight:700;cursor:pointer;font-family:'Manrope',sans-serif;}
        .btn-remove-step:hover{background:rgba(248,113,113,.25);}
        .btn-add-row{display:flex;align-items:center;gap:8px;padding:9px 16px;
                     background:rgba(155,70,212,.12);border:1px dashed rgba(155,70,212,.35);
                     border-radius:10px;color:#cc88f5;font-size:12px;font-weight:600;
                     font-family:'Manrope',sans-serif;cursor:pointer;width:100%;transition:all .2s;}
        .btn-add-row:hover{background:rgba(155,70,212,.22);border-color:rgba(155,70,212,.6);}
        .comp-card{background:#1e1e1e;border:1px solid rgba(245,245,245,.10);border-radius:10px;
                   padding:12px;display:flex;gap:10px;align-items:flex-start;position:relative;}
        .comp-card .btn-remove-step{position:static;margin-left:auto;flex-shrink:0;}
        .act-card{background:#1e1e1e;border:1px solid rgba(245,245,245,.10);border-radius:10px;
                  padding:14px;display:flex;flex-direction:column;gap:10px;position:relative;}
    `;
    document.head.appendChild(st);
})();

/* ── ESCAPE HELPERS ── */
function escAttr(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

/* ── TAB SWITCHER (inside modal) ── */
function switchTab(btn) {
    const box = btn.closest('.crud-body');
    box.querySelectorAll('.crud-tab').forEach(t => t.classList.remove('active'));
    box.querySelectorAll('.crud-tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const panel = box.querySelector('#' + btn.dataset.panel);
    if (panel) panel.classList.add('active');
}

/* ═══════════════════════════════════════════════════════════════
   PDF UPLOAD UI HELPERS
═══════════════════════════════════════════════════════════════ */
function initPdfUploadZone(topicId, currentFilename) {
    const zone     = document.getElementById('pdfDropZone');
    const input    = document.getElementById('pdfFileInput');
    const progress = document.getElementById('pdfUploadProgress');
    const fill     = document.getElementById('pdfProgressFill');
    const label    = document.getElementById('pdfProgressLabel');
    const badge    = document.getElementById('pdfCurrentBadge');
    const badgeName= document.getElementById('pdfCurrentName');

    if (!zone || !input) return;

    // Show existing file badge
    if (currentFilename) {
        badgeName.textContent = currentFilename;
        badge.style.display = 'flex';
    }

    // Drag-and-drop
    zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) handlePdfFileChosen(file, zone, input);
    });

    // File input change
    input.addEventListener('change', () => {
        if (input.files[0]) handlePdfFileChosen(input.files[0], zone, input);
    });
}

function handlePdfFileChosen(file, zone, input) {
    if (file.type !== 'application/pdf') {
        toast('err', 'Only PDF files are allowed.');
        return;
    }
    if (file.size > 50 * 1024 * 1024) {
        toast('err', 'File too large. Max 50 MB.');
        return;
    }
    zone.classList.add('has-file');
    zone.querySelector('.upload-label').textContent = '📄 ' + file.name;
    zone.querySelector('.upload-sub').textContent   = (file.size / (1024*1024)).toFixed(2) + ' MB — ready to upload';

    // Store for upload-on-save
    pendingUpload = { file, type: 'pdf' };
    // Also update the hidden text field so it can be read at save time
    const hiddenInput = document.getElementById('f_pdf_filename');
    if (hiddenInput) hiddenInput.value = file.name; // will be overwritten by server response
}

/* ═══════════════════════════════════════════════════════════════
   VIDEO UPLOAD UI HELPERS
═══════════════════════════════════════════════════════════════ */
function initVideoUploadZone(projectId, currentVideoUrl, currentVideoType) {
    const zone     = document.getElementById('videoDropZone');
    const input    = document.getElementById('videoFileInput');

    if (!zone || !input) return;

    zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) handleVideoFileChosen(file, zone);
    });

    input.addEventListener('change', () => {
        if (input.files[0]) handleVideoFileChosen(input.files[0], zone);
    });
}

function handleVideoFileChosen(file, zone) {
    const allowed = ['video/mp4','video/webm','video/ogg','video/quicktime','video/x-msvideo'];
    if (!allowed.includes(file.type) && !file.name.match(/\.(mp4|webm|ogv|ogg|mov|avi)$/i)) {
        toast('err', 'Unsupported format. Use MP4, WebM, MOV, or AVI.');
        return;
    }
    if (file.size > 500 * 1024 * 1024) {
        toast('err', 'File too large. Max 500 MB.');
        return;
    }
    zone.classList.add('has-file');
    zone.querySelector('.upload-label').textContent = '🎬 ' + file.name;
    zone.querySelector('.upload-sub').textContent   = (file.size / (1024*1024)).toFixed(1) + ' MB — ready to upload';
    pendingUpload = { file, type: 'video' };
}

/* Switch URL / Upload tabs in the video panel */
function switchVidTab(el, panelId) {
    document.querySelectorAll('.vid-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.vid-panel').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    const panel = document.getElementById(panelId);
    if (panel) panel.classList.add('active');
    // Clear pending upload when switching back to URL mode
    if (panelId !== 'vid-upload-panel') pendingUpload = null;
}

/* ═══════════════════════════════════════════════════════════════
   FORM DEFINITIONS
═══════════════════════════════════════════════════════════════ */
const forms = {
    topic: {
        icon: '📚', label: 'Topic',
        create: (d={}) => {
            let acts = [];
            try { acts = JSON.parse(d.activities || '[]') || []; } catch(e){}
            if (!Array.isArray(acts)) acts = [];

            const actCards   = acts.map((a,i) => buildActCard(a,i)).join('');
            const overviewVal= (d.overview_body || '').replace(/</g,'&lt;');
            const pdfVal     = escAttr(d.pdf_filename || '');
            const topicId    = d.id || 0;

            return `
            <div class="crud-tabs">
                <button class="crud-tab active" data-panel="t-basic"   onclick="switchTab(this)">Basic Info</button>
                <button class="crud-tab"        data-panel="t-overview" onclick="switchTab(this)">Overview</button>
                <button class="crud-tab"        data-panel="t-pdf"      onclick="switchTab(this)">PDF File</button>
                <button class="crud-tab"        data-panel="t-acts"     onclick="switchTab(this)">Activities</button>
            </div>

            <div class="crud-tab-panel active" id="t-basic">
                <div class="field-row">
                    <div class="field-group" style="max-width:100px">
                        <label class="field-label">Topic #</label>
                        <input class="field-input" id="f_topic_num" type="number" min="1" value="${d.topic_num||''}" placeholder="01">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Name</label>
                        <input class="field-input" id="f_name" type="text" value="${escAttr(d.name||'')}" placeholder="e.g. Basic Circuit Theory">
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Category</label>
                    <select class="field-select" id="f_category">
                        ${['Circuits & Electronics','Digital Systems','Embedded & Programming','Soldering & Fabrication']
                            .map(c=>`<option value="${c}"${(d.category||'')==c?' selected':''}>${c}</option>`).join('')}
                    </select>
                </div>
                <div class="field-group">
                    <label class="field-label">Short Description (shown on listing page)</label>
                    <textarea class="field-textarea" id="f_description" placeholder="Brief one-line description...">${escHtml(d.description||'')}</textarea>
                </div>
            </div>

            <div class="crud-tab-panel" id="t-overview">
                <div class="field-group">
                    <label class="field-label">Content Overview</label>
                    <p style="font-size:11px;color:rgba(245,245,245,.40);margin-bottom:6px;">
                        Separate paragraphs with a pipe character <code style="color:#cc88f5;background:rgba(155,70,212,.15);padding:1px 6px;border-radius:4px;">|</code>
                    </p>
                    <textarea class="field-textarea" id="f_overview_body" style="min-height:180px;" placeholder="First paragraph text|Second paragraph text|...">${overviewVal}</textarea>
                </div>
            </div>

            <div class="crud-tab-panel" id="t-pdf">
                <!-- Hidden field keeps track of the final filename after upload -->
                <input type="hidden" id="f_pdf_filename" value="${pdfVal}">

                ${pdfVal ? `
                <div class="current-file-badge" id="pdfCurrentBadge">
                    <span>📄</span>
                    <span class="cf-name" id="pdfCurrentName">${pdfVal}</span>
                    <button class="cf-clear" title="Remove PDF" onclick="clearPdf()">✕</button>
                </div>` : `<div class="current-file-badge" id="pdfCurrentBadge" style="display:none;">
                    <span>📄</span>
                    <span class="cf-name" id="pdfCurrentName"></span>
                    <button class="cf-clear" title="Remove PDF" onclick="clearPdf()">✕</button>
                </div>`}

                <div class="field-group">
                    <label class="field-label">Upload PDF File</label>
                    <div class="upload-zone" id="pdfDropZone">
                        <input type="file" id="pdfFileInput" accept=".pdf,application/pdf">
                        <span class="upload-icon">📂</span>
                        <span class="upload-label">Click to browse or drag &amp; drop</span>
                        <span class="upload-sub">PDF only · Max 50 MB</span>
                    </div>
                </div>

                <div class="upload-progress" id="pdfUploadProgress">
                    <div class="progress-bar-wrap"><div class="progress-bar-fill" id="pdfProgressFill" style="width:0%"></div></div>
                    <span class="progress-label" id="pdfProgressLabel">Uploading…</span>
                </div>

                <div style="padding:12px 14px;background:rgba(155,70,212,.08);border:1px solid rgba(155,70,212,.20);border-radius:10px;font-size:12px;color:rgba(245,245,245,.55);line-height:1.7;">
                    ℹ The file will be uploaded to <code style="color:#cc88f5;">Materials/</code> on the server when you click <strong>Save</strong>. The PDF viewer on the topic page will load it automatically.
                </div>
            </div>

            <div class="crud-tab-panel" id="t-acts">
                <div id="actList" style="display:flex;flex-direction:column;gap:10px;">
                    ${actCards}
                </div>
                <button class="btn-add-row" onclick="addActCard()">＋ Add Activity / Exercise / Experiment</button>
            </div>`;
        }
    },

    project: {
        icon: '🔧', label: 'Project',
        create: (d={}) => {
            let comps = [], steps = [];
            try { comps = JSON.parse(d.components_json || '[]') || []; } catch(e){}
            try { steps = JSON.parse(d.procedure_steps || '[]') || []; } catch(e){}
            if (!Array.isArray(comps)) comps = [];
            if (!Array.isArray(steps)) steps = [];

            const compCards  = comps.map((c,i) => buildCompCard(c,i)).join('');
            const stepCards  = steps.map((s,i) => buildStepCard(s,i)).join('');
            const overviewVal= (d.overview_body || '').replace(/</g,'&lt;');

            // Video section
            const videoUrl    = escAttr(d.video_url   || '');
            const videoType   = d.video_type || '';
            const videoTitle  = escAttr(d.video_title || '');
            const videoDur    = escAttr(d.video_duration || '');
            const isFile      = videoType === 'file';
            const isUrl       = videoType === 'url' || (!videoType && videoUrl);
            const hasVideo    = !!videoUrl;

            return `
            <div class="crud-tabs">
                <button class="crud-tab active" data-panel="p-basic"   onclick="switchTab(this)">Basic Info</button>
                <button class="crud-tab"        data-panel="p-overview" onclick="switchTab(this)">Overview</button>
                <button class="crud-tab"        data-panel="p-comps"    onclick="switchTab(this)">Components</button>
                <button class="crud-tab"        data-panel="p-steps"    onclick="switchTab(this)">Procedure</button>
                <button class="crud-tab"        data-panel="p-video"    onclick="switchTab(this)">Video</button>
            </div>

            <div class="crud-tab-panel active" id="p-basic">
                <div class="field-group">
                    <label class="field-label">Title</label>
                    <input class="field-input" id="f_title" type="text" value="${escAttr(d.title||'')}" placeholder="e.g. Smart Home Controller">
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Difficulty</label>
                        <select class="field-select" id="f_difficulty">
                            ${['Beginner','Intermediate','Advanced']
                                .map(v=>`<option value="${v}"${(d.difficulty||'Intermediate')==v?' selected':''}>${v}</option>`).join('')}
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Category</label>
                        <select class="field-select" id="f_category">
                            ${['General','Embedded','PCB Design','Circuits','IoT','Robotics']
                                .map(v=>`<option value="${v}"${(d.category||'General')==v?' selected':''}>${v}</option>`).join('')}
                        </select>
                    </div>
                    <div class="field-group" style="max-width:90px;">
                        <label class="field-label">Year</label>
                        <input class="field-input" id="f_year" type="number" min="2020" max="2099" value="${d.year||2026}">
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Hero Tag</label>
                    <input class="field-input" id="f_hero_tag" type="text" value="${escAttr(d.hero_tag||'')}" placeholder="e.g. Embedded Systems · 2026">
                </div>
                <div class="field-group">
                    <label class="field-label">Short Description</label>
                    <textarea class="field-textarea" id="f_description" placeholder="One-line overview...">${escHtml(d.description||'')}</textarea>
                </div>
                <div class="field-group">
                    <label class="field-label">Requirements / Parts</label>
                    <textarea class="field-textarea" id="f_requirements" placeholder="List of parts...">${escHtml(d.requirements||'')}</textarea>
                </div>
            </div>

            <div class="crud-tab-panel" id="p-overview">
                <div class="field-group">
                    <label class="field-label">Content Overview</label>
                    <p style="font-size:11px;color:rgba(245,245,245,.40);margin-bottom:6px;">
                        Separate paragraphs with <code style="color:#cc88f5;background:rgba(155,70,212,.15);padding:1px 6px;border-radius:4px;">|</code>
                    </p>
                    <textarea class="field-textarea" id="f_overview_body" style="min-height:180px;" placeholder="First paragraph|Second paragraph|...">${overviewVal}</textarea>
                </div>
            </div>

            <div class="crud-tab-panel" id="p-comps">
                <p style="font-size:11px;color:rgba(245,245,245,.40);margin-bottom:4px;">Add each component — shown in the Parts &amp; Materials table.</p>
                <div id="compList" style="display:flex;flex-direction:column;gap:8px;">
                    ${compCards}
                </div>
                <button class="btn-add-row" onclick="addCompCard()">＋ Add Component</button>
            </div>

            <div class="crud-tab-panel" id="p-steps">
                <p style="font-size:11px;color:rgba(245,245,245,.40);margin-bottom:4px;">Each step appears in order on the project page.</p>
                <div id="stepList" style="display:flex;flex-direction:column;gap:10px;">
                    ${stepCards}
                </div>
                <button class="btn-add-row" onclick="addStepCard()">＋ Add Step</button>
            </div>

            <!-- ══ VIDEO TAB ══ -->
            <div class="crud-tab-panel" id="p-video">

                <!-- Metadata fields -->
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Video Title</label>
                        <input class="field-input" id="f_video_title" type="text" value="${videoTitle}" placeholder="e.g. Smart Home Controller — Complete Build Guide">
                    </div>
                    <div class="field-group" style="max-width:130px;">
                        <label class="field-label">Duration</label>
                        <input class="field-input" id="f_video_duration" type="text" value="${videoDur}" placeholder="42:17">
                    </div>
                </div>

                <!-- Current video info -->
                ${hasVideo ? `
                <div class="current-file-badge" id="videoCurrentBadge">
                    <span>${isFile ? '📁' : '🔗'}</span>
                    <span class="cf-name" id="videoCurrentName">${videoUrl}</span>
                    <button class="cf-clear" title="Remove video" onclick="clearVideo()">✕</button>
                </div>` : `<div class="current-file-badge" id="videoCurrentBadge" style="display:none;">
                    <span id="videoCurrentIcon">🔗</span>
                    <span class="cf-name" id="videoCurrentName"></span>
                    <button class="cf-clear" title="Remove video" onclick="clearVideo()">✕</button>
                </div>`}

                <!-- Hidden fields -->
                <input type="hidden" id="f_video_url"  value="${videoUrl}">
                <input type="hidden" id="f_video_type" value="${videoType}">

                <!-- Mode tabs -->
                <div class="field-group">
                    <label class="field-label">Video Source</label>
                    <div class="video-mode-tabs">
                        <button class="vid-tab ${!isFile ? 'active' : ''}" onclick="switchVidTab(this,'vid-url-panel')">🔗 URL / Link</button>
                        <button class="vid-tab ${isFile  ? 'active' : ''}" onclick="switchVidTab(this,'vid-upload-panel')">📁 Upload File</button>
                    </div>
                </div>

                <!-- URL panel -->
                <div class="vid-panel ${!isFile ? 'active' : ''}" id="vid-url-panel">
                    <div class="field-group">
                        <label class="field-label">Video URL</label>
                        <input class="field-input" id="f_video_url_input"
                               type="url"
                               value="${isUrl && !isFile ? videoUrl : ''}"
                               placeholder="https://www.youtube.com/watch?v=... or any video URL"
                               oninput="syncVideoUrl(this.value,'url')">
                    </div>
                    <div style="padding:12px 14px;background:rgba(155,70,212,.08);border:1px solid rgba(155,70,212,.20);border-radius:10px;font-size:12px;color:rgba(245,245,245,.55);line-height:1.7;">
                        ℹ Paste any YouTube, Google Drive, or direct video URL. The video player on the project page will embed or link it.
                    </div>
                </div>

                <!-- Upload panel -->
                <div class="vid-panel ${isFile ? 'active' : ''}" id="vid-upload-panel">
                    <div class="field-group">
                        <label class="field-label">Upload Video File</label>
                        <div class="upload-zone" id="videoDropZone">
                            <input type="file" id="videoFileInput" accept="video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo,.mp4,.webm,.ogv,.mov,.avi">
                            <span class="upload-icon">🎬</span>
                            <span class="upload-label">Click to browse or drag &amp; drop</span>
                            <span class="upload-sub">MP4, WebM, MOV, AVI · Max 500 MB</span>
                        </div>
                    </div>
                    <div class="upload-progress" id="videoUploadProgress">
                        <div class="progress-bar-wrap"><div class="progress-bar-fill" id="videoProgressFill" style="width:0%"></div></div>
                        <span class="progress-label" id="videoProgressLabel">Uploading…</span>
                    </div>
                    <div style="padding:12px 14px;background:rgba(155,70,212,.08);border:1px solid rgba(155,70,212,.20);border-radius:10px;font-size:12px;color:rgba(245,245,245,.55);line-height:1.7;">
                        ℹ The file will be saved to <code style="color:#cc88f5;">Videos/</code> on the server when you click <strong>Save</strong>. Make sure your php.ini allows large uploads (see Setup Guide).
                    </div>
                </div>

            </div>`;
        }
    },

    tool: {
        icon: '💻', label: 'Simulation Tool',
        create: (d={}) => `
            <div class="field-group">
                <label class="field-label">Tool Name</label>
                <input class="field-input" id="f_tool_name" type="text" value="${escAttr(d.tool_name||'')}" placeholder="e.g. MULTISIM">
            </div>
            <div class="field-group">
                <label class="field-label">URL / Page Path</label>
                <input class="field-input" id="f_url_path" type="text" value="${escAttr(d.url_path||'')}" placeholder="e.g. multisim_view.html">
            </div>
            <div class="field-group">
                <label class="field-label">Description</label>
                <textarea class="field-textarea" id="f_description" placeholder="What does this tool do?">${escHtml(d.description||'')}</textarea>
            </div>`,
    },
};

/* ── VIDEO URL SYNC ── */
function syncVideoUrl(value, type) {
    const hiddenUrl  = document.getElementById('f_video_url');
    const hiddenType = document.getElementById('f_video_type');
    if (hiddenUrl)  hiddenUrl.value  = value;
    if (hiddenType) hiddenType.value = value ? type : '';

    const badge    = document.getElementById('videoCurrentBadge');
    const badgeName= document.getElementById('videoCurrentName');
    const badgeIcon= document.getElementById('videoCurrentIcon');
    if (badge && badgeName) {
        if (value) {
            badgeName.textContent = value;
            if (badgeIcon) badgeIcon.textContent = '🔗';
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

function clearPdf() {
    const hidden = document.getElementById('f_pdf_filename');
    if (hidden) hidden.value = '';
    const badge = document.getElementById('pdfCurrentBadge');
    if (badge) badge.style.display = 'none';
    const zone = document.getElementById('pdfDropZone');
    if (zone) {
        zone.classList.remove('has-file');
        zone.querySelector('.upload-label').textContent = 'Click to browse or drag & drop';
        zone.querySelector('.upload-sub').textContent   = 'PDF only · Max 50 MB';
    }
    pendingUpload = null;
}

function clearVideo() {
    const hiddenUrl  = document.getElementById('f_video_url');
    const hiddenType = document.getElementById('f_video_type');
    if (hiddenUrl)  hiddenUrl.value  = '';
    if (hiddenType) hiddenType.value = '';
    const badge = document.getElementById('videoCurrentBadge');
    if (badge) badge.style.display = 'none';
    const zone = document.getElementById('videoDropZone');
    if (zone) {
        zone.classList.remove('has-file');
        zone.querySelector('.upload-label').textContent = 'Click to browse or drag & drop';
        zone.querySelector('.upload-sub').textContent   = 'MP4, WebM, MOV, AVI · Max 500 MB';
    }
    const urlInput = document.getElementById('f_video_url_input');
    if (urlInput) urlInput.value = '';
    pendingUpload = null;
}

/* ── DYNAMIC CARD BUILDERS ── */
function buildActCard(a, i) {
    return `<div class="act-card" id="act-${i}">
        <div class="step-card-num">Activity / Exercise / Experiment ${i+1}</div>
        <button class="btn-remove-step" onclick="removeCard('act-${i}')">✕ Remove</button>
        <div class="field-row">
            <div class="field-group" style="max-width:160px;">
                <label class="field-label">Type</label>
                <select class="field-select act-type">
                    ${['Exercise','Activity','Experiment'].map(t=>`<option${(a.type||'')==t?' selected':''}>${t}</option>`).join('')}
                </select>
            </div>
            <div class="field-group">
                <label class="field-label">Title</label>
                <input class="field-input act-title" type="text" value="${escAttr(a.title||'')}" placeholder="Activity title">
            </div>
        </div>
        <div class="field-group">
            <label class="field-label">Description</label>
            <textarea class="field-textarea act-desc" style="min-height:70px;" placeholder="Describe what students should do...">${escHtml(a.description||'')}</textarea>
        </div>
    </div>`;
}
function addActCard() {
    const list = document.getElementById('actList');
    const i = list.children.length;
    const div = document.createElement('div');
    div.innerHTML = buildActCard({}, i);
    list.appendChild(div.firstElementChild);
}

function buildCompCard(c, i) {
    return `<div class="comp-card" id="comp-${i}">
        <div class="field-group" style="flex:1.5;">
            <label class="field-label">Component</label>
            <input class="field-input comp-name" type="text" value="${escAttr(c.name||'')}" placeholder="e.g. ESP32 DevKit">
        </div>
        <div class="field-group" style="flex:2;">
            <label class="field-label">Specification</label>
            <input class="field-input comp-spec" type="text" value="${escAttr(c.spec||'')}" placeholder="e.g. 240MHz dual-core, Wi-Fi+BT">
        </div>
        <div class="field-group" style="max-width:80px;">
            <label class="field-label">Qty</label>
            <input class="field-input comp-qty" type="text" value="${escAttr(c.qty||'')}" placeholder="×1">
        </div>
        <button class="btn-remove-step" onclick="removeCard('comp-${i}')">✕</button>
    </div>`;
}
function addCompCard() {
    const list = document.getElementById('compList');
    const i = list.children.length;
    const div = document.createElement('div');
    div.innerHTML = buildCompCard({}, i);
    list.appendChild(div.firstElementChild);
}

function buildStepCard(s, i) {
    return `<div class="step-card" id="step-${i}">
        <div class="step-card-num">Step ${i+1}</div>
        <button class="btn-remove-step" onclick="removeCard('step-${i}')">✕ Remove</button>
        <div class="field-group">
            <label class="field-label">Step Title</label>
            <input class="field-input step-title" type="text" value="${escAttr(s.title||'')}" placeholder="e.g. Schematic Design & PCB Layout">
        </div>
        <div class="field-group">
            <label class="field-label">Description</label>
            <textarea class="field-textarea step-desc" style="min-height:80px;" placeholder="Detailed instructions...">${escHtml(s.description||'')}</textarea>
        </div>
        <div class="field-group">
            <label class="field-label">Note / Warning <span style="font-weight:400;text-transform:none;letter-spacing:0;color:rgba(245,245,245,.40);font-size:10px;">(optional)</span></label>
            <input class="field-input step-note" type="text" value="${escAttr(s.note||'')}" placeholder="e.g. ⚠ Do not solder until voltages are verified">
        </div>
    </div>`;
}
function addStepCard() {
    const list = document.getElementById('stepList');
    const i = list.children.length;
    const div = document.createElement('div');
    div.innerHTML = buildStepCard({}, i);
    list.appendChild(div.firstElementChild);
}

function removeCard(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

/* ── COLLECT JSON ── */
function collectActivities() {
    const cards = document.querySelectorAll('#actList .act-card');
    return JSON.stringify([...cards].map(c => ({
        type:        c.querySelector('.act-type')?.value  || 'Activity',
        title:       c.querySelector('.act-title')?.value || '',
        description: c.querySelector('.act-desc')?.value  || '',
    })));
}
function collectComponents() {
    const cards = document.querySelectorAll('#compList .comp-card');
    return JSON.stringify([...cards].map(c => ({
        name: c.querySelector('.comp-name')?.value || '',
        spec: c.querySelector('.comp-spec')?.value || '',
        qty:  c.querySelector('.comp-qty')?.value  || '',
    })));
}
function collectSteps() {
    const cards = document.querySelectorAll('#stepList .step-card');
    return JSON.stringify([...cards].map(c => ({
        title:       c.querySelector('.step-title')?.value || '',
        description: c.querySelector('.step-desc')?.value  || '',
        note:        c.querySelector('.step-note')?.value  || null,
    })));
}

const g = id => document.getElementById(id)?.value ?? '';

/* ── OPEN CRUD ── */
function openCrud(type, mode, data={}) {
    pendingUpload = null;
    crudType = type; crudMode = mode; crudId = data.id || 0;
    const f = forms[type];
    document.getElementById('crudIcon').textContent  = f.icon;
    document.getElementById('crudTitle').textContent = (mode==='create'?'Add ':'Edit ') + f.label;
    document.getElementById('crudBody').innerHTML    = f.create(data);
    document.getElementById('btnSave').textContent   = mode==='create' ? 'Create' : 'Save Changes';
    document.getElementById('btnSave').classList.remove('success');
    document.getElementById('crudOverlay').classList.add('open');

    // Init upload zones after DOM is rendered
    if (type === 'topic') {
        initPdfUploadZone(data.id || 0, data.pdf_filename || '');
    }
    if (type === 'project') {
        initVideoUploadZone(data.id || 0, data.video_url || '', data.video_type || '');
    }
}
function closeCrud() {
    document.getElementById('crudOverlay').classList.remove('open');
    pendingUpload = null;
}

/* ═══════════════════════════════════════════════════════════════
   SAVE  —  uploads file first (if any), then saves record
═══════════════════════════════════════════════════════════════ */
async function saveCrud() {
    const saveBtn = document.getElementById('btnSave');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';

    try {
        // ── STEP 1: Upload file if one is pending ─────────────────────────────
        if (pendingUpload) {
            const uploadOk = await doFileUpload(pendingUpload.type, crudId);
            if (!uploadOk) {
                saveBtn.disabled = false;
                saveBtn.textContent = crudMode === 'create' ? 'Create' : 'Save Changes';
                return;
            }
        }

        // ── STEP 2: Build JSON payload and POST ───────────────────────────────
        let payload = { action: '' };
        const action = crudMode === 'edit' ? 'update' : 'create';

        if (crudType === 'topic') {
            payload = {
                action:           'topic_' + action,
                topic_num:        +g('f_topic_num'),
                name:              g('f_name'),
                description:       g('f_description'),
                category:          g('f_category'),
                overview_body:     g('f_overview_body'),
                pdf_filename:      g('f_pdf_filename'),
                activities_json:   document.getElementById('actList') ? collectActivities() : '',
            };
        } else if (crudType === 'project') {
            payload = {
                action:           'project_' + action,
                title:             g('f_title'),
                description:       g('f_description'),
                difficulty:        g('f_difficulty'),
                category:          g('f_category'),
                year:             +g('f_year'),
                hero_tag:          g('f_hero_tag'),
                overview_body:     g('f_overview_body'),
                components_json:   document.getElementById('compList') ? collectComponents() : '',
                procedure_steps:   document.getElementById('stepList') ? collectSteps()      : '',
                video_title:       g('f_video_title'),
                video_duration:    g('f_video_duration'),
                video_url:         g('f_video_url'),
                video_type:        g('f_video_type'),
                requirements:      g('f_requirements') || '',
            };
        } else if (crudType === 'tool') {
            payload = {
                action:    'tool_' + action,
                tool_name:  g('f_tool_name'),
                description:g('f_description'),
                url_path:   g('f_url_path'),
            };
        }

        if (crudMode === 'edit') payload.id = crudId;

        const res = await api(payload);
        if (res.success) {
            saveBtn.textContent = '✓ Saved!';
            saveBtn.classList.add('success');
            toast('ok', res.message);
            setTimeout(() => { closeCrud(); location.reload(); }, 900);
        } else {
            toast('err', res.message);
            saveBtn.disabled = false;
            saveBtn.textContent = crudMode === 'create' ? 'Create' : 'Save Changes';
        }

    } catch (err) {
        toast('err', 'Unexpected error: ' + err.message);
        saveBtn.disabled = false;
        saveBtn.textContent = crudMode === 'create' ? 'Create' : 'Save Changes';
    }
}

/* ── FILE UPLOAD via XMLHttpRequest (supports progress bar) ── */
function doFileUpload(type, recordId) {
    return new Promise((resolve) => {
        const fileInput = type === 'pdf'
            ? document.getElementById('pdfFileInput')
            : document.getElementById('videoFileInput');

        if (!fileInput || !fileInput.files[0]) {
            // No file to upload (cleared or not chosen)
            resolve(true);
            return;
        }

        const file       = fileInput.files[0];
        const progressEl = document.getElementById(type === 'pdf' ? 'pdfUploadProgress'  : 'videoUploadProgress');
        const fillEl     = document.getElementById(type === 'pdf' ? 'pdfProgressFill'     : 'videoProgressFill');
        const labelEl    = document.getElementById(type === 'pdf' ? 'pdfProgressLabel'    : 'videoProgressLabel');

        if (progressEl) progressEl.classList.add('show');

        const formData = new FormData();
        formData.append('upload_type', type);
        formData.append('record_id',   recordId);
        formData.append(type === 'pdf' ? 'pdf_file' : 'video_file', file);

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', e => {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                if (fillEl)  fillEl.style.width   = pct + '%';
                if (labelEl) labelEl.textContent   = `Uploading… ${pct}%`;
            }
        });

        xhr.addEventListener('load', () => {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    if (labelEl) labelEl.textContent = '✓ Upload complete';
                    if (fillEl)  fillEl.style.background = '#4ade80';

                    // Update the relevant hidden/text field with the server-returned filename
                    if (type === 'pdf') {
                        const hiddenPdf = document.getElementById('f_pdf_filename');
                        if (hiddenPdf) hiddenPdf.value = res.filename;
                    } else {
                        const hiddenUrl  = document.getElementById('f_video_url');
                        const hiddenType = document.getElementById('f_video_type');
                        if (hiddenUrl)  hiddenUrl.value  = res.filename;
                        if (hiddenType) hiddenType.value = 'file';
                    }
                    resolve(true);
                } else {
                    if (progressEl) progressEl.classList.remove('show');
                    toast('err', 'Upload failed: ' + res.message);
                    resolve(false);
                }
            } catch {
                toast('err', 'Upload response parse error.');
                resolve(false);
            }
        });

        xhr.addEventListener('error', () => {
            toast('err', 'Upload network error.');
            resolve(false);
        });

        xhr.open('POST', 'upload_handler.php');
        xhr.send(formData);
    });
}

/* ── DELETE CONFIRM ── */
let pendingDelete = null;
function confirmDelete(type, id, name) {
    pendingDelete = { type, id };
    document.getElementById('confirmSub').textContent = `"${name}" will be permanently removed.`;
    document.getElementById('confirmOverlay').classList.add('open');
    document.getElementById('btnConfirmDel').onclick = doDelete;
}
function closeConfirm() { document.getElementById('confirmOverlay').classList.remove('open'); pendingDelete = null; }

async function doDelete() {
    if (!pendingDelete) return;
    const res = await api({ action: pendingDelete.type + '_delete', id: pendingDelete.id });
    closeConfirm();
    if (res.success) { toast('ok', res.message); setTimeout(() => location.reload(), 800); }
    else              { toast('err', res.message); }
}

/* ── API HELPER ── */
async function api(payload) {
    try {
        const r = await fetch('admin_dashboard.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify(payload),
        });
        return await r.json();
    } catch { return { success:false, message:'Network error.' }; }
}

/* ── TOAST ── */
let toastTimer;
function toast(type, msg) {
    const el = document.getElementById('toast');
    el.className = `toast ${type}`;
    el.textContent = (type==='ok'?'✓ ':'✗ ') + msg;
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 3200);
}

/* ── ESC TO CLOSE ── */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeCrud(); closeConfirm(); }
});
</script>

</body>
</html>