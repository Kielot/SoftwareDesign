<?php require_once __DIR__ . '/auth.php'; ?>
<?php require_login(); ?>
<?php require_once __DIR__ . '/nav_auth.php'; ?>
<?php
$db = get_db();

$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$topic = null;
if ($topic_id > 0) {
    $stmt = $db->prepare('SELECT * FROM topics WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $topic  = $result->fetch_assoc();
    $stmt->close();
}

if (!$topic) {
    header('Location: topics_section.php');
    exit;
}

$activities = [];
if (!empty($topic['activities'])) {
    $decoded = json_decode($topic['activities'], true);
    if (is_array($decoded)) $activities = $decoded;
}

$overview_paras = [];
$overview_src   = !empty($topic['overview_body']) ? $topic['overview_body'] : $topic['description'];
foreach (explode('|', $overview_src) as $para) {
    $para = trim($para);
    if ($para !== '') $overview_paras[] = $para;
}
if (empty($overview_paras)) {
    $overview_paras[] = $topic['description'];
}

$name        = htmlspecialchars($topic['name']);
$category    = htmlspecialchars($topic['category']);
$topic_num   = str_pad((int)$topic['topic_num'], 2, '0', STR_PAD_LEFT);
$pdf_file    = $topic['pdf_filename'] ?? '';
$pdf_exists  = !empty($pdf_file);
$pdf_path    = 'Materials/' . $pdf_file;
$hero_tag    = "Topic {$topic_num} · {$category}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BICpES — <?= $name ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="topic_view_design.css">
    <link rel="stylesheet" type="text/css" href="user_design.css">
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
        <a href="topics_section.php">Topics</a>
        <span class="sep">›</span>
        <span class="current"><?= $name ?></span>
    </div>

    <!-- HERO -->
    <div class="hero">
        <span class="hero-tag"><?= htmlspecialchars($hero_tag) ?></span>
        <div class="hero-body">
            <?php
                $words     = explode(' ', $topic['name']);
                $last_word = array_pop($words);
                $rest      = implode(' ', $words);
            ?>
            <div class="hero-title">
                <?= htmlspecialchars($rest) ?> <em><?= htmlspecialchars($last_word) ?></em>
            </div>
            <p class="hero-desc"><?= htmlspecialchars($topic['description']) ?></p>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- 1. OVERVIEW -->
        <div class="section">
            <div class="section-eyebrow">Overview</div>
            <h2>What is <?= $name ?>?</h2>
            <?php if (!empty($overview_paras)): ?>
                <?php foreach ($overview_paras as $para): ?>
                    <p><?= htmlspecialchars($para) ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?= htmlspecialchars($topic['description']) ?></p>
            <?php endif; ?>
        </div>

        <!-- 2. LESSONS — PDF VIEWER -->
        <div class="section">
            <div class="section-eyebrow">Lessons</div>
            <h2>Course Materials</h2>

            <div class="pdf-viewer-wrap">
                <div class="pdf-toolbar">
                    <div class="pdf-toolbar-left">
                        <div class="pdf-icon">📄</div>
                        <span class="pdf-filename">
                            <?= $pdf_exists ? htmlspecialchars($pdf_file) : 'No file linked yet' ?>
                        </span>
                        <?php if ($pdf_exists): ?>
                            <span class="pdf-filetype">PDF</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($pdf_exists): ?>
                    <div class="pdf-toolbar-right">
                        <a href="<?= htmlspecialchars($pdf_path) ?>" download class="pdf-btn">⬇ Download</a>
                        <a href="<?= htmlspecialchars($pdf_path) ?>" target="_blank" class="pdf-btn">↗ Open</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="pdf-frame-container">
                    <?php if ($pdf_exists): ?>
                        <iframe
                            src="<?= htmlspecialchars($pdf_path) ?>"
                            title="<?= $name ?> — Course Materials"
                            loading="lazy"
                            onerror="this.style.display='none'; document.getElementById('pdf-fallback').style.display='flex';"
                        ></iframe>
                    <?php endif; ?>

                    <div class="pdf-placeholder" id="pdf-fallback"
                         style="display:<?= $pdf_exists ? 'none' : 'flex' ?>;position:absolute;inset:0;">
                        <div class="pdf-placeholder-icon">
                            <svg viewBox="0 0 64 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="1" y="1" width="62" height="78" rx="5" stroke="rgba(155,70,212,0.4)" stroke-width="2" fill="rgba(155,70,212,0.06)"/>
                                <path d="M14 24h36M14 34h36M14 44h24" stroke="rgba(245,245,245,0.15)" stroke-width="2" stroke-linecap="round"/>
                                <path d="M38 54l8 8m0-8l-8 8" stroke="rgba(155,70,212,0.5)" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="pdf-placeholder-text">No file linked yet</span>
                        <span class="pdf-placeholder-sub">
                            Set the <code>pdf_filename</code> for this topic in the Admin Dashboard
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. SUGGESTED ACTIVITIES -->
        <?php if (!empty($activities)): ?>
        <div class="section">
            <div class="section-eyebrow">Suggested Exercise / Activity / Experiment</div>
            <h2>Apply What You've Learned</h2>
            <div class="activity-grid">
                <?php $last_idx = count($activities) - 1; ?>
                <?php foreach ($activities as $idx => $act): ?>
                    <?php $is_last = ($idx === $last_idx) && (count($activities) % 2 !== 0); ?>
                    <div class="activity-card<?= $is_last ? ' full' : '' ?>">
                        <div class="act-type"><?= htmlspecialchars($act['type'] ?? 'Activity') ?></div>
                        <div class="act-title"><?= htmlspecialchars($act['title'] ?? '') ?></div>
                        <div class="act-desc"><?= htmlspecialchars($act['description'] ?? '') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="section">
            <div class="section-eyebrow">Suggested Exercise / Activity / Experiment</div>
            <h2>Apply What You've Learned</h2>
            <p style="color:rgba(245,245,245,.40);">
                No activities have been added for this topic yet.
                Check back later or ask your instructor.
            </p>
        </div>
        <?php endif; ?>

    </div>

    <!-- BACK -->
    <div class="back-bar">
        <a href="topics_section.php" class="back-link">← Back to Topics</a>
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