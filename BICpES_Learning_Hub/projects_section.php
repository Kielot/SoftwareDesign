<?php require_once __DIR__ . '/auth.php'; ?>
<?php require_login(); ?>
<?php require_once __DIR__ . '/nav_auth.php'; ?>
<?php
$db = get_db();
$projects = [];
try {
    $res = $db->query('SELECT id, title, category, difficulty, year FROM projects ORDER BY id ASC');
    if ($res) $projects = $res->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) { /* non-fatal */ }

$pill_labels = [
    'Embedded'   => 'Embedded',
    'PCB Design' => 'PCB Design',
    'Circuits'   => 'Circuits',
    'IoT'        => 'IoT',
    'Robotics'   => 'Robotics',
    'General'    => 'Featured',
];

// Category → image filename map
$category_images = [
    'General'    => 'Images/Projects/general.jpg',
    'Circuits'   => 'Images/Projects/circuits.jpg',
    'Embedded'   => 'Images/Projects/embedded.jpg',
    'IoT'        => 'Images/Projects/iot.jpg',
    'PCB Design' => 'Images/Projects/pcb_design.jpg',
    'Robotics'   => 'Images/Projects/robotics.jpg',
];
$category_img_fallback = 'Images/Projects/general.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BICpES Learning Hub — Projects</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="projects_design.css">
    <link rel="stylesheet" type="text/css" href="user_design.css">
    <style>html { scroll-behavior: smooth; }</style>
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
        <div class="hero-title">Pro<em>jects</em></div>
        <div class="hero-right">
            <span class="hero-count"><?= count($projects) ?></span>
            <p class="hero-sub">Hands-on builds from the CPE community — circuits, embedded systems, IoT, and more.</p>
        </div>
    </div>

    <div class="filter-bar">
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="Circuits">Circuits</button>
        <button class="filter-btn" data-filter="Embedded">Embedded</button>
        <button class="filter-btn" data-filter="IoT">IoT</button>
        <button class="filter-btn" data-filter="PCB Design">PCB Design</button>
        <button class="filter-btn" data-filter="Robotics">Robotics</button>
    </div>

    <div class="projects-grid" id="projectsGrid">
        <?php if (empty($projects)): ?>
            <p style="color:rgba(255,255,255,.4);padding:40px;">No projects found. Add some from the Admin Dashboard.</p>
        <?php else: ?>
            <?php foreach ($projects as $i => $proj): ?>
                <?php
                    $cat_img = $category_images[$proj['category']] ?? $category_img_fallback;
                    $pill  = $pill_labels[$proj['category']] ?? $proj['category'];
                    $year  = $proj['year'] ?? 2026;
                    $cat   = htmlspecialchars($proj['category']);
                    $diff  = htmlspecialchars($proj['difficulty']);
                ?>
                <div class="project-card" data-category="<?= $cat ?>" style="cursor:pointer;" onclick="location.href='project_view.php?id=<?= $proj['id'] ?>'">
                    <div class="card-thumb">
                        <img class="ph" src="<?= htmlspecialchars($cat_img) ?>" alt="<?= $cat ?>">
                        <span class="card-pill"><?= htmlspecialchars($pill) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="card-name"><?= htmlspecialchars($proj['title']) ?></div>
                        <div class="card-footer-row">
                            <span class="card-meta"><?= $cat ?> · <?= $year ?></span>
                            <div class="card-btn">›</div>
                        </div>
                    </div>
                </div>
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
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const filter = btn.dataset.filter;
                document.querySelectorAll('#projectsGrid .project-card').forEach(card => {
                    card.style.display = (filter === 'all' || card.dataset.category === filter) ? '' : 'none';
                });
            });
        });
    </script>
</body>
</html>