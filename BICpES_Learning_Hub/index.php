<?php require_once __DIR__ . '/auth.php'; ?>
<?php require_once __DIR__ . '/nav_auth.php'; ?>
<?php
// ── Fetch first 3 projects and topics for homepage previews ──────────────────
$db = get_db();

$home_projects = [];
$home_topics   = [];

try {
    $res = $db->query('SELECT id, title, category, difficulty, year FROM projects ORDER BY id ASC LIMIT 3');
    if ($res) $home_projects = $res->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) { /* non-fatal */ }

try {
    $res = $db->query('SELECT id, topic_num, name, category FROM topics ORDER BY topic_num ASC LIMIT 3');
    if ($res) $home_topics = $res->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) { /* non-fatal */ }

// Category → image filename map for project cards
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
        <link rel="stylesheet" type="text/css" href="design.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="user_design.css">
        <script src="script.js"></script>
        <title>BICpES Learning Hub</title>
        <style>
            html { scroll-behavior: smooth; }

            /* ── SKILLS: photo sits centered inside .box; gradient & glass still visible ── */
            .skills_box {
                position: relative;
            }
            /* .box = gradient background — clips nothing, shows as frame */
            .box {
                position: relative !important;
                overflow: visible !important;  /* gradient peeks around photo */
            }
            /* .box_in = glass panel, bigger than .box, behind everything */
            .box_in {
                position: absolute !important;
                top: 46% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                width: 113% !important;   /* wider than .box so it peeks out */
                height: 110% !important;  /* taller than .box so it peeks out */
                z-index: 0 !important;
                pointer-events: none !important;
            }
            /* photo sits centered on top, smaller than .box so gradient shows */
            .skill-img {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                width: 95% !important;
                height: 95% !important;
                object-fit: cover !important;
                object-position: center !important;
                border-radius: 16px !important;
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                z-index: 2 !important;
                visibility: hidden;
                opacity: 0;
                transition: opacity 0.4s ease;
            }
            .skill-tab,
            .skills_section,
            .skills-container,
            .skills_items,
            .skill_list ul li,
            [class*="skill"] {
                font-family: 'Manrope', sans-serif !important;
            }
        </style>
    </head>

    <body>
        <nav>
            <a href="#home" class="left-side"><img src="Images/Logo/BICpES Learning Hub Logo.png" alt="Logo"></a>

            <div class="center">
                <a href="#about_us">About Us</a>
                <a href="#topics">Topics</a>
                <a href="#projects">Projects</a>
                <a href="#tools">Tools</a>
            </div>

            <div class="right-side">
                <?php echo nav_right_html(); ?>
            </div>
        </nav>

        <!-- Login/Signup Form -->
        <section class="container-log" id="login">
            <div class="log-container">
                <a href="#stay" class="close_btn">&times;</a>
                <div class="new_acc">
                    <form>
                        <h1>Create Account</h1>
                        <input type="number" placeholder="Student Number"/><br>
                        <input type="text" placeholder="Last Name"/><br>
                        <input type="text" placeholder="First Name"/><br>
                        <input type="date" name="birthdate"/><br>
                        <input type="password" placeholder="Password"/><br>
                        <input type="password" placeholder="Confirm Password"/><br>
                        <button type="submit">Sign Up</button>
                    </form>
                </div>

                <div class="old_acc">
                    <form>
                        <h1>Login</h1>
                        <p style="font-size:12px;color:rgba(245,245,245,.55);margin-bottom:8px;line-height:1.5;">
                            Students: enter your Student Number.<br>
                            Admin: enter <strong>ADMIN</strong> as the identifier.
                        </p>
                        <input type="text" placeholder="Student Number"/><br>
                        <input type="password" placeholder="Password"/><br>
                        <button type="submit">Login</button>
                    </form>
                </div>

                <div class="overlay-container">
                    <div class="overlay">
                        <div class="overlay_left">
                            <h1>Welcome Back!</h1>
                            <p>Explore more with us — please login with your existing account</p>
                            <button id="signIn">Login</button>
                        </div>
                        <div class="overlay_right">
                            <h1>Hello, Fellow CPE Student!</h1>
                            <p>Enter your personal details and start your journey with us</p>
                            <button id="signUp">Sign Up</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="home">
            <div class="headings">
                <h1>BICpES Learning Hub</h1>
                <h2>Discover, Learn, and Grow with us</h2>
                <h3>Explore topics, tools, and concepts that will guide you through your CpE journey</h3>
            </div>
            <a href="<?= is_logged_in() ? '#topics' : '#login' ?>">
                <button class="learn">Start Learning</button>
            </a>

            <!-- ── SKILLS BOX: images live inside .box and .box_in ── -->
            <div class="skills_box">
                <div class="box">
                    <div class="box_in"></div>
                    <img class="skill-img" id="skill-img-solving"
                        src="Images/Skills/Solving.jpg" alt="Solving"
                        onerror="this.style.display='none'">
                    <img class="skill-img" id="skill-img-designing"
                        src="Images/Skills/Designing.jpg" alt="Designing"
                        onerror="this.style.display='none'">
                    <img class="skill-img" id="skill-img-etching"
                        src="Images/Skills/Etching.jpg" alt="Etching"
                        onerror="this.style.display='none'">
                    <img class="skill-img" id="skill-img-soldering"
                        src="Images/Skills/Soldering.jpg" alt="Soldering"
                        onerror="this.style.display='none'">
                </div>
            </div>
        </section>

        <section id="skills">
            <ul>
                <li class="skill-tab active" data-skill="solving">Solving</li>
                <li class="skill-tab"        data-skill="designing">Designing</li>
                <li class="skill-tab"        data-skill="etching">Etching</li>
                <li class="skill-tab"        data-skill="soldering">Soldering</li>
            </ul>
        </section>

        <!-- ── PROJECTS SECTION ─────────────────────────────────────────── -->
        <section id="projects">
            <h1>Projects</h1>
            <div class="projects_container">
                <?php if (!empty($home_projects)): ?>
                    <?php foreach ($home_projects as $i => $proj): ?>
                        <?php
                            $cat_img = $category_images[$proj['category']] ?? $category_img_fallback;
                        ?>
                        <?php if (is_logged_in()): ?>
                            <a class="project_box" href="project_view.php?id=<?= $proj['id'] ?>">
                                <img src="<?= htmlspecialchars($cat_img) ?>" alt="<?= htmlspecialchars($proj['title']) ?>">
                                <p><?= htmlspecialchars($proj['title']) ?></p>
                            </a>
                        <?php else: ?>
                            <a class="project_box" href="#login">
                                <img src="<?= htmlspecialchars($cat_img) ?>" alt="<?= htmlspecialchars($proj['title']) ?>">
                                <p><?= htmlspecialchars($proj['title']) ?></p>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php /* Fallback if DB is empty */ ?>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <a class="project_box" href="<?= is_logged_in() ? 'project_view.php' : '#login' ?>">
                            <img src="<?= $category_img_fallback ?>" alt="Project <?= $i ?>">
                            <p>Project <?= $i ?></p>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
            <?php if (is_logged_in()): ?>
                <a href="projects_section.php"><h2>VIEW MORE</h2></a>
            <?php else: ?>
                <a href="#login"><h2>VIEW MORE</h2></a>
            <?php endif; ?>
        </section>

        <!-- ── TOPICS SECTION ──────────────────────────────────────────── -->
        <section id="topics">
            <div class="topics_container">
                <h1>Topics</h1>
                <?php if (!empty($home_topics)): ?>
                    <?php foreach ($home_topics as $topic): ?>
                        <?php if (is_logged_in()): ?>
                            <a href="topic_view.php?id=<?= $topic['id'] ?>">
                                <p><?= htmlspecialchars($topic['name']) ?></p>
                            </a>
                        <?php else: ?>
                            <a href="#login">
                                <p><?= htmlspecialchars($topic['name']) ?></p>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <a href="<?= is_logged_in() ? 'topic_view.php' : '#login' ?>">
                            <p>Topic <?= $i ?></p>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
                <?php if (is_logged_in()): ?>
                    <a href="topics_section.php"><h2>VIEW MORE</h2></a>
                <?php else: ?>
                    <a href="#login"><h2>VIEW MORE</h2></a>
                <?php endif; ?>
            </div>

            <!-- ── TOPICS RIGHT BOX: topic image inside .inside_right_box ── -->
            <div class="right_box">
                <div class="inside_right_box">
                    <!-- Replace src with your actual Topics section image -->
                    <img src="Images/Topics/topics_preview.jpg" alt="Topics Preview" class="topics-preview-img">
                </div>
            </div>

            <div class="under_box"></div>
        </section>

        <section id="tools">
            <h1>Simulation Tools</h1>
            <div class="simulation_tools">
                <?php if (is_logged_in()): ?>
                    <a href="multisim_view.html" class="tool_box_border">
                        <span class="tool_title">MULTISIM</span>
                        <p>An apllication that let you simulate electronics exercises.</p>
                    </a>
                    <a href="tinkercad_view.html" class="tool_box_border">
                        <span class="tool_title">TINKERCAD</span>
                        <p>A website tool that let you simulate electronics experiment with nice visuals.</p>
                    </a>
                <?php else: ?>
                    <a href="#login" class="tool_box_border">
                        <span class="tool_title">MULTISIM</span>
                        <p>An apllication that let you simulate electronics exercies.</p>
                    </a>
                    <a href="#login" class="tool_box_border">
                        <span class="tool_title">TINKERCAD</span>
                        <p>A website tool that let you simulate electronics experiment with nice visuals.</p>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section id="about_us">
            <div class="left_box">BICpES Learning Hub, a dedicated e-learning system, engineered to bridge the gap between complex engineering concepts and practical academic success. 
                                As a specialized e-learning platform, our mission is to empower the next generation of Computer Engineers by providing high-quality, structured, and accessible 
                                learning resources. We foster an inclusive and technically robust learning environment where lower-year engineering students can confidently master core concepts, 
                                accelerate their proficiency and smoothly transition from theoretical knowledge to hands-on implementation. </div>
            <div class="about_text"><p>About Us</p></div>
        </section>

        <footer class="footer">
            <p>@ 2026 BICpES Learning Hub | Do not share my personal information</p>
            <div class="links">
                <a href="https://www.facebook.com/BICpES" target="_blank"><strong>Facebook</strong></a>
            </div>
        </footer>

        <?php echo nav_scripts_html(); ?>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tabs = document.querySelectorAll('li.skill-tab');
            var imgs = {
                solving:   document.getElementById('skill-img-solving'),
                designing: document.getElementById('skill-img-designing'),
                etching:   document.getElementById('skill-img-etching'),
                soldering: document.getElementById('skill-img-soldering')
            };

            /* Hide all images immediately on load */
            Object.values(imgs).forEach(function(img) {
                if (img) {
                    img.style.visibility = 'hidden';
                    img.style.opacity    = '0';
                }
            });

            /* Remove active highlight from all tabs */
            function clearTabs() {
                tabs.forEach(function(t) {
                    t.style.color            = '';
                    t.style.backgroundColor  = '';
                    t.style.borderRadius     = '';
                    t.style.padding          = '';
                });
            }

            /* Show one skill: highlight tab + show matching image */
            function showSkill(key) {
                /* Hide all images */
                Object.values(imgs).forEach(function(img) {
                    if (img) {
                        img.style.visibility = 'hidden';
                        img.style.opacity    = '0';
                    }
                });

                /* Clear all tab highlights */
                clearTabs();

                /* Highlight the active tab */
                var activeTab = document.querySelector('li.skill-tab[data-skill="' + key + '"]');
                if (activeTab) {
                    activeTab.style.color           = '#101010';
                    activeTab.style.backgroundColor = '#ffffff';
                    activeTab.style.borderRadius    = '50px';
                    activeTab.style.padding         = '18px 22px';
                }

                /* Show the matching image */
                var img = imgs[key];
                if (img) {
                    img.style.visibility = 'visible';
                    img.style.opacity    = '1';
                }
            }

            /* Attach click to each tab */
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    showSkill(tab.dataset.skill);
                });
            });

            /* Default: show Solving on page load */
            showSkill('solving');
        });
        </script>
    </body>
</html>