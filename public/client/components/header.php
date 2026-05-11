<?php
/**
 * Header / Navbar Component
 * public/client/components/header.php
 *
 * Usage:
 *   $active_page = 'home';
 *   require __DIR__ . '/../components/header.php';
 */

// Guard: start session only if one isn't already running
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$active_page = $active_page ?? 'home';
$page_title  = $page_title  ?? 'Shift Studio Barbershop';
$asset_root  = 'assets';

// ── Auth state ───────────────────────────────────────────────────────────────
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_name    = $is_logged_in ? htmlspecialchars($_SESSION['user']['nama'] ?? 'User') : '';

$nav_items = [
    'home'       => ['Home',       'index.php'],
    'about'      => ['About Us',   'about.php'],
    'rating'     => ['Rating',     'rating.php'],
    'membership' => ['Membership', 'membership.php'],
    'contact'    => ['Contact Us', 'contact.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="Shift Studio Barbershop — professional cuts, coloring, and grooming.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="<?= $asset_root ?>/css/variables.css">
    <link rel="stylesheet" href="<?= $asset_root ?>/css/base.css">
    <link rel="stylesheet" href="<?= $asset_root ?>/css/components.css">
    <?php if (!empty($extra_css)): ?>
        <?php foreach ((array)$extra_css as $css): ?>
            <link rel="stylesheet" href="<?= $asset_root ?>/css/<?= htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<!-- ── Navbar ── -->
<header class="navbar" id="navbar">
    <a href="index.php" class="navbar__logo" aria-label="Shift Studio Home">
        <img src="<?= $asset_root ?>/img/Logo.png" alt="Shift Studio Barbershop">
    </a>

    <nav class="navbar__nav" id="nav-menu" role="navigation" aria-label="Main navigation">
        <ul class="navbar__links">
            <?php foreach ($nav_items as $slug => [$label, $href]): ?>
                <li>
                    <a href="<?= $href ?>"
                       class="<?= $active_page === $slug ? 'active' : '' ?>"
                       <?= $active_page === $slug ? 'aria-current="page"' : '' ?>>
                        <?= $label ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Auth links: mobile drawer -->
        <div class="navbar__mobile-only">
            <?php if ($is_logged_in): ?>
                <span style="font-size:14px; opacity:.8;">Hi, <?= $user_name ?></span>
                <a href="../pages/logout.php" class="btn btn-outline">Logout</a>
            <?php else: ?>
                <a href="../pages/register.php" class="btn btn-outline">Sign Up</a>
                <a href="../pages/login.php"    class="btn btn-primary">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Auth links: desktop -->
    <div class="navbar__auth">
        <?php if ($is_logged_in): ?>
            <span style="font-size:14px; opacity:.8;">Hi, <?= $user_name ?></span>
            <a href="../pages/logout.php" class="btn btn-outline">Logout</a>
        <?php else: ?>
            <a href="../pages/register.php" class="btn btn-outline">Sign Up</a>
            <a href="../pages/login.php"    class="btn btn-primary">Sign In</a>
        <?php endif; ?>
    </div>

    <!-- Hamburger toggle -->
    <button class="navbar__toggle" id="nav-toggle" aria-controls="nav-menu"
            aria-expanded="false" aria-label="Toggle menu">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </button>
</header>

<!-- Mobile overlay -->
<div class="navbar__overlay" id="nav-overlay" aria-hidden="true"></div>