<?php require_once __DIR__ . '/auth.php'; ?>
<?php require_login(); ?>
<?php require_once __DIR__ . '/nav_auth.php'; ?>
<?php
$db = get_db();

$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$project = null;
if ($project_id > 0) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $project_id);
    $stmt->execute();
    $result  = $stmt->get_result();
    $project = $result->fetch_assoc();
    $stmt->close();
}

if (!$project) {
    header('Location: projects_section.php');
    exit;
}

$steps      = [];
$components = [];

if (!empty($project['procedure_steps'])) {
    $decoded = json_decode($project['procedure_steps'], true);
    if (is_array($decoded)) $steps = $decoded;
}
if (!empty($project['components_json'])) {
    $decoded = json_decode($project['components_json'], true);
    if (is_array($decoded)) $components = $decoded;
}

$overview_paras = [];
$overview_src   = !empty($project['overview_body']) ? $project['overview_body'] : $project['description'];
foreach (explode('|', $overview_src) as $para) {
    $para = trim($para);
    if ($para !== '') $overview_paras[] = $para;
}
if (empty($overview_paras)) {
    $overview_paras[] = $project['description'];
}

$requirements_text = $project['requirements'] ?? '';
$requirements_list = [];
if (empty($components) && !empty($requirements_text)) {
    foreach (explode(',', $requirements_text) as $item) {
        $item = trim($item);
        if ($item !== '') $requirements_list[] = $item;
    }
}

$title      = htmlspecialchars($project['title']);
$hero_tag   = htmlspecialchars($project['hero_tag'] ?? ($project['category'] . ' · ' . $project['year']));
$difficulty = htmlspecialchars($project['difficulty']);
$category   = htmlspecialchars($project['category']);
$year       = (int)($project['year'] ?? 2026);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BICpES — <?= $title ?></title>
    <link rel="stylesheet" type="text/css" href="project_view_design.css">
    <link rel="stylesheet" type="text/css" href="user_design.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>html { scroll-behavior: smooth; }</style>
</head>
<body>

    <!-- NAV -->
    <nav>
        <a href="index.php" class="left-side"><img src="Images/Logo/BICpES Learning Hub Logo.png" alt="Logo"></a>
        <div class="center">
            <a href="index.php"><span><strong>BICpES</strong></span> Learning Hub</a>
        </div>
        <div class="right-side">
            <?php echo nav_right_html(); ?>
        </div>
    </nav>

    <!-- BREADCRUMB -->
    <div class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="sep">›</span>
        <a href="projects_section.php">Projects</a>
        <span class="sep">›</span>
        <span class="current"><?= $title ?></span>
    </div>

    <!-- HERO -->
    <div class="hero">
        <div class="hero-left">
            <span class="hero-tag"><?= $hero_tag ?></span>
            <?php
                $words     = explode(' ', $project['title']);
                $last_word = array_pop($words);
                $rest      = implode(' ', $words);
            ?>
            <div class="hero-title">
                <?= htmlspecialchars($rest) ?> <em><?= htmlspecialchars($last_word) ?></em>
            </div>
        </div>
        <div class="hero-right">
            <p class="hero-brief"><?= htmlspecialchars($project['description']) ?></p>
        </div>
    </div>

    <!-- BANNER -->
    <div class="banner">
        <div class="banner-bg">
            <div class="banner-deco">
                <svg viewBox="0 0 900 380" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <line x1="0" y1="100" x2="900" y2="100" stroke="white" stroke-width="0.8"/>
                    <line x1="0" y1="200" x2="900" y2="200" stroke="white" stroke-width="0.8"/>
                    <line x1="0" y1="300" x2="900" y2="300" stroke="white" stroke-width="0.8"/>
                    <line x1="150" y1="0" x2="150" y2="380" stroke="white" stroke-width="0.8"/>
                    <line x1="350" y1="0" x2="350" y2="380" stroke="white" stroke-width="0.8"/>
                    <line x1="550" y1="0" x2="550" y2="380" stroke="white" stroke-width="0.8"/>
                    <line x1="750" y1="0" x2="750" y2="380" stroke="white" stroke-width="0.8"/>
                    <circle cx="150" cy="100" r="5" fill="white"/>
                    <circle cx="350" cy="200" r="5" fill="white"/>
                    <circle cx="550" cy="100" r="5" fill="white"/>
                    <circle cx="750" cy="300" r="5" fill="white"/>
                    <circle cx="150" cy="300" r="3" fill="white"/>
                    <circle cx="550" cy="300" r="3" fill="white"/>
                    <path d="M150 100 L350 100 L350 200" stroke="white" stroke-width="1.5" fill="none"/>
                    <path d="M350 200 L550 200 L550 100 L750 100" stroke="white" stroke-width="1.5" fill="none"/>
                    <path d="M150 300 L350 300 L350 200" stroke="white" stroke-width="1.5" fill="none"/>
                    <path d="M550 300 L750 300" stroke="white" stroke-width="1.5" fill="none"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <div class="content">

        <!-- 1. OVERVIEW -->
        <div class="section">
            <div class="section-eyebrow">Overview</div>
            <h2>About this Project</h2>
            <?php if (!empty($overview_paras)): ?>
                <?php foreach ($overview_paras as $para): ?>
                    <p><?= htmlspecialchars($para) ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?= htmlspecialchars($project['description']) ?></p>
            <?php endif; ?>
            <p style="margin-top:14px;">
                <span style="display:inline-block;font-size:10px;letter-spacing:2px;text-transform:uppercase;font-weight:700;padding:4px 14px;border-radius:50px;background:var(--violet-dim);color:#cc88f5;border:1px solid var(--border-v);">
                    <?= $difficulty ?> · <?= $category ?>
                </span>
            </p>
        </div>

        <!-- 2. REQUIRED COMPONENTS -->
        <?php if (!empty($components)): ?>
        <div class="section">
            <div class="section-eyebrow">Required Components</div>
            <h2>Parts &amp; Materials</h2>
            <table class="comp-table">
                <thead>
                    <tr>
                        <th style="width:30%">Component</th>
                        <th style="width:45%">Specification</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($components as $comp): ?>
                    <tr>
                        <td><?= htmlspecialchars($comp['name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($comp['spec'] ?? '') ?></td>
                        <td><span class="qty"><?= htmlspecialchars($comp['qty'] ?? '') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php elseif (!empty($requirements_list)): ?>
        <div class="section">
            <div class="section-eyebrow">Required Components</div>
            <h2>Parts &amp; Materials</h2>
            <table class="comp-table">
                <thead>
                    <tr><th>Component / Material</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($requirements_list as $item): ?>
                    <tr><td><?= htmlspecialchars($item) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php elseif (!empty($requirements_text)): ?>
        <div class="section">
            <div class="section-eyebrow">Required Components</div>
            <h2>Parts &amp; Materials</h2>
            <p><?= nl2br(htmlspecialchars($requirements_text)) ?></p>
        </div>
        <?php endif; ?>

        <!-- 3. PROCEDURE -->
        <?php if (!empty($steps)): ?>
        <div class="section">
            <div class="section-eyebrow">Procedure</div>
            <h2>Step-by-Step Build Guide</h2>
            <div class="steps">
                <?php foreach ($steps as $idx => $step): ?>
                <div class="step">
                    <div class="step-num-wrap">
                        <div class="step-circle"><?= $idx + 1 ?></div>
                    </div>
                    <div class="step-body">
                        <div class="step-title"><?= htmlspecialchars($step['title'] ?? '') ?></div>
                        <div class="step-desc"><?= htmlspecialchars($step['description'] ?? '') ?></div>
                        <?php if (!empty($step['note'])): ?>
                        <div class="step-note">
                            <p><?= htmlspecialchars($step['note']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="section">
            <div class="section-eyebrow">Procedure</div>
            <h2>Step-by-Step Build Guide</h2>
            <p style="color:rgba(245,245,245,.40);">
                Detailed procedure steps have not been added for this project yet.
                Check back later or ask your instructor for the build guide.
            </p>
        </div>
        <?php endif; ?>

        <!-- 4. VIDEO TUTORIAL -->
        <div class="section">
            <div class="section-eyebrow">Video Tutorial</div>
            <h2>Watch &amp; Learn</h2>
            <div class="video-wrap">
                <div class="video-thumb">
                    <div class="play-btn">▶</div>
                    <span class="video-label">Full Build Walkthrough</span>
                </div>
                <div class="video-meta">
                    <span class="video-title">
                        <?= htmlspecialchars($project['video_title'] ?? ($project['title'] . ' — Complete Build Guide')) ?>
                    </span>
                    <span class="video-dur">
                        <?= htmlspecialchars($project['video_duration'] ?? '--:--') ?>
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- BACK -->
    <div class="back-bar">
        <a href="projects_section.php" class="back-link">← Back to Projects</a>
    </div>

    <footer class="footer">
        <p>@ 2026 BICpES Learning Hub | Do not share my personal information</p>
        <div class="links">
            <a href="https://www.facebook.com/BICpES" target="_blank"><strong>Facebook</strong></a>
        </div>
    </footer>

    <?php echo nav_scripts_html(); ?>
</body>
</html>