<?php
/**
 * Student Organization Recruitment Management System - Main Entry Point
 *
 * Shows the public landing page to all visitors.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

startSession();

$openCampaigns = [];
$organizations = [];
$stats = [
    'organizations' => 0,
    'open_campaigns' => 0,
    'applications' => 0,
    'approved' => 0,
];

try {
    $stats['organizations'] = (int) Database::value(
        "SELECT COUNT(*) FROM organizations WHERE status = 'active'"
    );

    $stats['open_campaigns'] = (int) Database::value(
        "SELECT COUNT(*) FROM campaigns
         WHERE status = 'open' AND recruitment_start <= CURDATE() AND recruitment_end >= CURDATE()"
    );

    $stats['applications'] = (int) Database::value('SELECT COUNT(*) FROM applications');
    $stats['approved'] = (int) Database::value(
        "SELECT COUNT(*) FROM applications WHERE status = 'approved'"
    );

    $openCampaigns = Database::query(
        "SELECT c.*, o.name AS org_name, o.color_theme
         FROM campaigns c
         JOIN organizations o ON c.organization_id = o.id
         WHERE c.status = 'open'
           AND c.recruitment_start <= CURDATE()
           AND c.recruitment_end >= CURDATE()
         ORDER BY c.featured DESC, c.priority DESC, c.recruitment_end ASC
         LIMIT 6"
    );

    $organizations = Database::query(
        "SELECT o.id, o.name, o.description, o.color_theme, o.logo,
                COUNT(c.id) AS open_campaign_count
         FROM organizations o
         LEFT JOIN campaigns c
                ON c.organization_id = o.id
               AND c.status = 'open'
               AND c.recruitment_start <= CURDATE()
               AND c.recruitment_end >= CURDATE()
         WHERE o.status = 'active'
         GROUP BY o.id, o.name, o.description, o.color_theme, o.logo
         ORDER BY o.name ASC
         LIMIT 6"
    );
} catch (PDOException $e) {
    error_log('Landing page data error: ' . $e->getMessage());
}

$page_title = 'Home';
$page_main_class = 'landing-page-main';
$page_csrf_token = csrfToken();
ob_start();
?>
<div class="organization-ambient" aria-hidden="true">
    <svg viewBox="0 0 1000 900" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <symbol id="organization-building-icon" viewBox="0 0 100 100">
                <path d="M15 88h70M22 84V40h56v44M16 40l34-25 34 25M34 84V68h16v16M32 50h6m12 0h6m12 0h6m-42 9h6m12 0h6m12 0h6" />
            </symbol>
            <symbol id="organization-people-icon" viewBox="0 0 100 100">
                <circle cx="50" cy="28" r="11" />
                <circle cx="23" cy="38" r="8" />
                <circle cx="77" cy="38" r="8" />
                <path d="M29 83v-8c0-13 9-23 21-23s21 10 21 23v8M7 79v-6c0-10 6-17 16-17 5 0 9 2 12 6m58 17v-6c0-10-6-17-16-17-5 0-9 2-12 6M39 82h22" />
            </symbol>
        </defs>
        <g class="organization-icon organization-icon-one"><use href="#organization-building-icon" x="90" y="115" width="72" height="72" /><use href="#organization-people-icon" x="148" y="140" width="55" height="55" /></g>
        <g class="organization-icon organization-icon-two"><use href="#organization-people-icon" x="760" y="160" width="82" height="82" /><use href="#organization-building-icon" x="820" y="196" width="65" height="65" /></g>
        <g class="organization-icon organization-icon-three"><use href="#organization-building-icon" x="250" y="480" width="78" height="78" /><use href="#organization-people-icon" x="308" y="510" width="56" height="56" /></g>
        <g class="organization-icon organization-icon-four"><use href="#organization-people-icon" x="670" y="565" width="76" height="76" /><use href="#organization-building-icon" x="725" y="600" width="62" height="62" /></g>
        <g class="organization-icon organization-icon-five"><use href="#organization-building-icon" x="100" y="735" width="58" height="58" /><use href="#organization-people-icon" x="145" y="758" width="46" height="46" /></g>
        <g class="organization-icon organization-icon-six"><use href="#organization-people-icon" x="865" y="730" width="68" height="68" /><use href="#organization-building-icon" x="820" y="755" width="54" height="54" /></g>
    </svg>
</div>
<section class="landing-hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="hero-eyebrow">Student Organization Recruitment</span>
                <h1 class="hero-title" aria-label="FIND YOUR PLACE. BUILD SOMETHING BIGGER.">
                    <?php foreach (['FIND YOUR PLACE.', 'BUILD SOMETHING BIGGER.'] as $heroLine): ?>
                        <?php $glyphIndex = 0; ?>
                        <span class="hero-title-line" aria-hidden="true"><?php foreach (explode(' ', $heroLine) as $heroWord): ?><span class="hero-word"><?php foreach (str_split($heroWord) as $heroGlyph): ?><?php $glyphSeed = hexdec(substr(hash('sha256', $heroLine . ':' . $glyphIndex++), 0, 6)); ?><span class="hero-glyph" style="--glyph-delay: <?php echo ($glyphSeed % 2600) / 1000; ?>s; --glyph-duration: <?php echo 1.8 + (intdiv($glyphSeed, 2600) % 1300) / 1000; ?>s"><?php echo htmlspecialchars($heroGlyph, ENT_QUOTES, 'UTF-8'); ?></span><?php endforeach; ?></span><?php endforeach; ?></span>
                    <?php endforeach; ?>
                </h1>
                <p class="hero-text">
                    Discover student organizations, apply to recruitment campaigns,
                    and follow every application from submission to membership approval.
                </p>
                <div class="hero-actions">
                    <a href="<?php echo SORMS_URL; ?>src/auth/register.php" class="btn btn-primary btn-lg">Create an Account</a>
                    <a href="<?php echo SORMS_URL; ?>src/auth/login.php" class="btn btn-outline-primary btn-lg">Sign In</a>
                    <a href="<?php echo SORMS_URL; ?>src/campaign/index.php" class="btn btn-outline-secondary btn-lg">Browse Campaigns</a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="hero-orbit-stage">
                    <div class="hero-logo-card">
                        <img src="<?php echo SORMS_URL; ?>SORMS_Logo.jpg" alt="Student Organization Recruitment System" class="hero-logo">
                        <img src="<?php echo SORMS_URL; ?>SORMS_Logo.jpg" alt="" aria-hidden="true" class="hero-logo hero-logo-back">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="landing-stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?php echo $stats['organizations']; ?></div>
                <div class="label">Active Organizations</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['open_campaigns']; ?></div>
                <div class="label">Open Campaigns</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['applications']; ?></div>
                <div class="label">Applications Submitted</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['approved']; ?></div>
                <div class="label">Members Approved</div>
            </div>
        </div>
    </div>
</section>

<section class="landing-section">
    <div class="container">
        <div class="section-heading">
            <div>
                <h2>Open Recruitment Campaigns</h2>
                <p>Organizations currently looking for new members.</p>
            </div>
            <a href="<?php echo SORMS_URL; ?>src/campaign/index.php" class="btn btn-outline-primary">View All</a>
        </div>

        <?php if (empty($openCampaigns)): ?>
            <div class="empty-state">
                <h4>No open campaigns right now</h4>
                <p>New recruitment campaigns will appear here as soon as they open.</p>
            </div>
        <?php else: ?>
            <div class="campaign-grid">
                <?php foreach ($openCampaigns as $campaign): ?>
                <article class="campaign-card">
                    <div class="campaign-card-top">
                        <span class="org-chip"><?php echo htmlspecialchars($campaign['org_name']); ?></span>
                        <?php if (!empty($campaign['featured'])): ?>
                            <span class="badge bg-warning">Featured</span>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($campaign['title']); ?></h3>
                    <p class="campaign-description">
                        <?php echo htmlspecialchars(mb_strimwidth((string) $campaign['description'], 0, 150, '...')); ?>
                    </p>
                    <div class="campaign-meta">
                        <span><?php echo (int) $campaign['total_positions']; ?> positions</span>
                        <span>Closes <?php echo formatDate($campaign['recruitment_end']); ?></span>
                    </div>
                    <a href="<?php echo SORMS_URL; ?>src/campaign/view.php?id=<?php echo (int) $campaign['id']; ?>" class="btn btn-outline-primary w-100">
                        View Campaign
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="landing-section landing-section-alt">
    <div class="container">
        <div class="section-heading">
            <div>
                <h2>Student Organizations</h2>
                <p>Find a community that matches your interests.</p>
            </div>
        </div>

        <?php if (empty($organizations)): ?>
            <div class="empty-state">
                <h4>No organizations yet</h4>
                <p>Organizations will appear here once they are created.</p>
            </div>
        <?php else: ?>
            <div class="org-grid">
                <?php foreach ($organizations as $organization): ?>
                <article class="org-card">
                    <div class="org-mark" style="background-color: <?php echo htmlspecialchars($organization['color_theme'] ?: '#1a73e8'); ?>">
                        <?php echo htmlspecialchars(mb_strtoupper(mb_substr($organization['name'], 0, 1))); ?>
                    </div>
                    <div>
                        <h3><?php echo htmlspecialchars($organization['name']); ?></h3>
                        <p>
                            <?php echo htmlspecialchars(mb_strimwidth((string) $organization['description'], 0, 120, '...')); ?>
                        </p>
                        <span class="org-meta">
                            <?php echo (int) $organization['open_campaign_count']; ?> open campaign(s)
                        </span>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="landing-section portfolio-section" id="portfolio">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="section-kicker">Product portfolio</span>
                <h2>One workflow from discovery to decision</h2>
                <p>A practical recruitment workspace for student organizations and applicants.</p>
            </div>
        </div>

        <div class="portfolio-grid">
            <article class="portfolio-card">
                <span class="portfolio-index">01 / CAMPAIGNS</span>
                <h3>Recruitment, organized</h3>
                <p>Publish opportunities with eligibility, schedules, available positions, and clear application steps.</p>
                <div class="tag-list" aria-label="Campaign features">
                    <span>Campaign management</span><span>Open positions</span><span>Recruitment dates</span>
                </div>
            </article>
            <article class="portfolio-card">
                <span class="portfolio-index">02 / SCREENING</span>
                <h3>Review with context</h3>
                <p>Give reviewers a consistent place to assess applications, update decisions, and keep a useful history.</p>
                <div class="tag-list" aria-label="Screening features">
                    <span>Application review</span><span>Status history</span><span>Approvals</span>
                </div>
            </article>
            <article class="portfolio-card">
                <span class="portfolio-index">03 / APPLICANTS</span>
                <h3>Keep students informed</h3>
                <p>Students build a profile, apply to open campaigns, and follow progress through notifications.</p>
                <div class="tag-list" aria-label="Applicant features">
                    <span>Applicant profiles</span><span>Notifications</span><span>Progress tracking</span>
                </div>
            </article>
        </div>

        <div class="skills-panel">
            <h3>Built with a focused, maintainable stack</h3>
            <div class="skill-badges" aria-label="Technology stack">
                <span>PHP</span><span>MySQL</span><span>PDO</span><span>Bootstrap</span>
                <span>Responsive UI</span><span>Session security</span>
            </div>
        </div>

        <div class="download-panel">
            <div>
                <span class="section-kicker">Take it with you</span>
                <h3>Explore my portfolio</h3>
                <p>A concise overview of the platform, its workflow, and its core modules.</p>
            </div>
            <a
                class="btn btn-primary btn-lg portfolio-open-link"
                href="<?php echo SORMS_URL; ?>fernando-mendoza-jr-portfolio/fernando-mendoza-jr/index.html"
                target="_blank"
                rel="noopener"
            ><span>Open my portfolio</span></a>
        </div>
    </div>
</section>

<section class="landing-section landing-section-alt tutorial-section" id="tutorial">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="section-kicker">Quick start</span>
                <h2>See how the system works</h2>
                <p>Follow the path from creating an account to completing a recruitment workflow.</p>
            </div>
        </div>

        <div class="tutorial-layout">
            <div class="nav nav-pills tutorial-tabs" id="tutorial-tabs" role="tablist" aria-label="System walkthrough">
                <button class="nav-link active" id="tutorial-account-tab" type="button" role="tab" aria-controls="tutorial-account" aria-selected="true" tabindex="0">
                    <span>Step 1</span> Create an account
                </button>
                <button class="nav-link" id="tutorial-modules-tab" type="button" role="tab" aria-controls="tutorial-modules" aria-selected="false" tabindex="-1">
                    <span>Step 2</span> Navigate core modules
                </button>
                <button class="nav-link" id="tutorial-api-tab" type="button" role="tab" aria-controls="tutorial-api" aria-selected="false" tabindex="-1">
                <span>Step 3</span> Feature walkthrough
                </button>
            </div>
            <div class="tab-content tutorial-content">
                <article class="tab-pane fade show active" id="tutorial-account" role="tabpanel" aria-labelledby="tutorial-account-tab" tabindex="0">
                    <div class="tutorial-preview">
                        <div class="preview-topbar"><span></span><span></span><span></span><b>Applicant workspace</b></div>
                        <div class="preview-lines"><i></i><i></i><i></i></div>
                        <div class="preview-action">Create your student profile</div>
                    </div>
                    <div>
                        <span class="section-kicker">Step 1</span>
                        <h3>Create an account and sign in</h3>
                        <p>Register with your name, username, email, and a strong password. Your account opens the applicant workspace.</p>
                        <a class="text-link" href="<?php echo SORMS_URL; ?>src/auth/register.php">Create a student account</a>
                    </div>
                </article>
                <article class="tab-pane fade" id="tutorial-modules" role="tabpanel" aria-labelledby="tutorial-modules-tab" tabindex="0">
                    <div class="tutorial-preview preview-campaigns">
                        <div class="preview-topbar"><span></span><span></span><span></span><b>Recruitment campaigns</b></div>
                        <div class="preview-module-grid"><i></i><i></i><i></i></div>
                        <div class="preview-action">Browse open opportunities</div>
                    </div>
                    <div>
                        <span class="section-kicker">Step 2</span>
                        <h3>Explore campaigns and modules</h3>
                        <p>Browse active opportunities, review each organization's requirements, and use your profile to prepare an application.</p>
                        <a class="text-link" href="<?php echo SORMS_URL; ?>src/campaign/index.php">Browse campaigns</a>
                    </div>
                </article>
                <article class="tab-pane fade" id="tutorial-api" role="tabpanel" aria-labelledby="tutorial-api-tab" tabindex="0">
                    <div class="tutorial-preview preview-status">
                        <div class="preview-topbar"><span></span><span></span><span></span><b>Application status</b></div>
                        <div class="preview-status-row"><i></i><span>Submitted</span><b>Complete</b></div>
                        <div class="preview-status-row"><i></i><span>Under review</span><b>In progress</b></div>
                        <div class="preview-status-row"><i></i><span>Decision</span><b>Next</b></div>
                    </div>
                    <div>
                        <span class="section-kicker">Step 3</span>
                        <h3>Apply and follow the review</h3>
                        <p>Submit your application, watch for status changes, and use notifications to keep up with screening and decisions.</p>
                        <a class="text-link" href="<?php echo SORMS_URL; ?>src/applicant/index.php">Open applicant workspace</a>
                    </div>
                </article>
            </div>
        </div>

    </div>
</section>

<section class="landing-section access-section" id="access">
    <div class="container">
        <div class="access-card">
            <div>
                <span class="section-kicker">System access</span>
                <h2>Ready to join the recruitment community?</h2>
                <p>Sign in to continue or create a student account to discover open opportunities.</p>
            </div>
            <div class="access-switch" role="tablist" aria-label="Choose account access">
                <button class="active" type="button" role="tab" id="access-login-tab" aria-controls="access-login" aria-selected="true" tabindex="0" data-access-target="access-login">Login</button>
                <button type="button" role="tab" id="access-signup-tab" aria-controls="access-signup" aria-selected="false" tabindex="-1" data-access-target="access-signup">Sign Up</button>
            </div>
            <div class="access-panel" id="access-login" role="tabpanel" aria-labelledby="access-login-tab">
                <p>Already have an account? Continue to your role-based workspace.</p>
                <a class="btn btn-primary btn-lg" href="<?php echo SORMS_URL; ?>src/auth/login.php">Sign In</a>
            </div>
            <div class="access-panel" id="access-signup" role="tabpanel" aria-labelledby="access-signup-tab" hidden>
                <p>New to the system? Create a student account and complete your applicant profile.</p>
                <a class="btn btn-primary btn-lg" href="<?php echo SORMS_URL; ?>src/auth/register.php">Create an Account</a>
            </div>
        </div>
    </div>
</section>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/templates/layout.php';
