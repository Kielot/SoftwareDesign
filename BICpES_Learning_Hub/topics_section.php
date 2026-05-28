<?php require_once __DIR__ . '/auth.php'; ?>
<?php require_login(); ?>
<?php require_once __DIR__ . '/nav_auth.php'; ?>
<?php
$db = get_db();
$topics = [];
try {
    $res = $db->query('SELECT id, topic_num, name, description, category FROM topics ORDER BY topic_num ASC');
    if ($res) $topics = $res->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) { /* non-fatal */ }

$by_category = [];
foreach ($topics as $t) {
    $by_category[$t['category']][] = $t;
}

$cat_icons = [
    'Circuits & Electronics'  => '⚡',
    'Digital Systems'         => '💡',
    'Embedded & Programming'  => '🔧',
    'Soldering & Fabrication' => '🔩',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BICpES Learning Hub — Topics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="topics_design.css">
    <link rel="stylesheet" type="text/css" href="user_design.css">
    <style>
        html { scroll-behavior: smooth; }

        #topicList a {
            display: block;
            text-decoration: none;
            color: inherit;
            margin-bottom: 4px;
        }

        #topicList a:hover .topic-row {
            background: var(--bg-glass-hov);
            transform: translateX(4px);
            border-color: var(--border);
            border-left-color: var(--bg-lighter);
            box-shadow: 0 4px 20px var(--shadow);
            backdrop-filter: blur(8px);
        }

        #topicList a:hover .topic-expand {
            max-height: 80px;
            opacity: 1;
            margin-top: 8px;
        }

        #topicList a:hover .topic-num {
            color: var(--white);
        }

        #topicList a:hover .topic-row::after {
            opacity: 1;
            background: #fff;
            box-shadow: 0 0 8px 3px rgba(255,255,255,0.35), 0 0 0 4px rgba(204,150,240,0.2);
        }
    </style>
</head>
<body>
    <nav>
        <a href="index.php" class="left-side"><img src="Images/Logo/BICpES Learning Hub Logo.png" alt="Logo"></a>
        <div class="center">
            <a href="index.php"><span><strong>BICpES</strong></span> Learning Hub</a>
        </div>
        <div class="right-side">
            <?php echo nav_right_html(); ?>
        </div>
    </nav>

    <div class="hero">
        <div class="hero-title">To<span>pics</span></div>
        <div class="hero-right">
            <p class="hero-sub">Everything you need for your CPE journey — from basic circuits to embedded systems.</p>
        </div>
    </div>

    <div class="search-row">
        <input class="topic-search" type="text" placeholder="Search topics..." id="topicSearch">
    </div>

    <!-- DISCIPLINE CARDS -->
    <div class="discipline-strip">
        <div class="discipline-label">Core Disciplines</div>
        <div class="discipline-cards">
            <?php foreach ($by_category as $cat => $cat_topics): ?>
            <div class="disc-card">
                <span class="disc-icon"><?= $cat_icons[$cat] ?? '📖' ?></span>
                <div class="disc-name"><?= htmlspecialchars($cat) ?></div>
                <div class="disc-count"><?= count($cat_topics) ?> topic<?= count($cat_topics) !== 1 ? 's' : '' ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- TOPIC LIST -->
    <div class="topics-body" id="topicList">

        <?php if (empty($topics)): ?>
            <p style="color:rgba(255,255,255,.4);padding:40px 0;">No topics found. Add some from the Admin Dashboard.</p>
        <?php else: ?>
            <?php foreach ($by_category as $cat => $cat_topics): ?>

                <div class="cat-header">
                    <span class="cat-title"><?= htmlspecialchars($cat) ?></span>
                    <div class="cat-line"></div>
                </div>

                <?php foreach ($cat_topics as $t): ?>
                <a href="topic_view.php?id=<?= $t['id'] ?>">
                    <div class="topic-row">
                        <div class="topic-num"><?= str_pad($t['topic_num'], 2, '0', STR_PAD_LEFT) ?></div>
                        <div class="topic-center">
                            <div class="topic-name"><?= htmlspecialchars($t['name']) ?></div>
                            <div class="topic-expand"><?= htmlspecialchars($t['description']) ?></div>
                            <div class="dots"></div>
                        </div>
                        <div class="topic-right">
                            <span class="topic-tag"></span>
                            <span class="topic-arrow">›</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>

            <?php endforeach; ?>
        <?php endif; ?>

    </div>

    <div class="back-bar">
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>

    <footer class="footer">
        <p>@ 2026 BICpES Learning Hub | Do not share my personal information</p>
        <div class="links">
            <a href="https://www.facebook.com/BICpES" target="_blank"><strong>Facebook</strong></a>
        </div>
    </footer>

    <?php echo nav_scripts_html(); ?>

    <script>
        const searchInput = document.getElementById('topicSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const q = this.value.toLowerCase();
                document.querySelectorAll('#topicList a').forEach(anchor => {
                    const row    = anchor.querySelector('.topic-row');
                    if (!row) return;
                    const name   = row.querySelector('.topic-name')?.textContent.toLowerCase()   || '';
                    const expand = row.querySelector('.topic-expand')?.textContent.toLowerCase() || '';
                    anchor.style.display = (name.includes(q) || expand.includes(q)) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>