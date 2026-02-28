<?php
$pageTitle = $pageTitle ?? SITE_NAME;
$pageDesc = $pageDesc ?? 'ESO Housing portfolio by @Vaelarn. PC–EU.';
$pageAccent = $pageAccent ?? 'rgba(160,160,170,0.4)';
$user = currentUser();
$unreadCount = $user ? getUnreadCount($user['id']) : 0;

// Default background for everyone — Eastmarch wallpaper so the site feels alive even pre-login
$bodyBg = "background-image: linear-gradient(180deg, rgba(6,6,8,0.55), rgba(6,6,8,0.78)), url('img/backgrounds/bg-eastmarch.jpg'); background-size: cover; background-position: center; background-attachment: fixed;";
if ($user && $user['background'] !== 'default') {
    $bodyBg = getBackgroundCss($user['background']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDesc) ?>">
  <meta property="og:type" content="website">
  <meta name="theme-color" content="#060608">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=IBM+Plex+Mono:wght@300;400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>◆</text></svg>">
  <style>
    :root { --house-accent: <?= $pageAccent ?>; }
    .page-content { padding-top: 5rem; min-height: 80vh; }

    /* --- Enlarged form / UI elements --- */
    .form-group { margin-bottom: 1.75rem; }
    .form-label { display:block; font-family:var(--font-label); font-size:0.72rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--text-secondary); margin-bottom:0.6rem; }
    .form-input, .form-select, .form-textarea {
      width:100%; padding:0.9rem 1.1rem; background:rgba(16,16,20,0.8); border:1px solid var(--border-card);
      color:var(--text-primary); font-family:var(--font-body); font-size:1.1rem; outline:none;
      transition:border-color 0.3s ease;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color:rgba(160,160,170,0.3); }
    .form-textarea { min-height:120px; resize:vertical; line-height:1.7; }
    .form-select { cursor:pointer; }
    .form-select option { background:#0a0a0c; }
    .btn {
      display:inline-block; font-family:var(--font-label); font-size:0.68rem; letter-spacing:0.22em; text-transform:uppercase;
      padding:0.85em 2.5em; border:1px solid rgba(160,160,170,0.2); background:transparent; color:var(--text-secondary);
      cursor:pointer; transition:all 0.4s ease; text-decoration:none;
    }
    .btn:hover { border-color:rgba(160,160,170,0.5); color:var(--text-primary); transform:translateY(-1px); }
    .btn--primary { border-color:var(--house-accent); color:var(--text-primary); }
    .btn--primary:hover { background:rgba(160,160,170,0.05); }
    .btn--small { font-size:0.58rem; padding:0.6em 1.5em; }
    .btn--danger { border-color:rgba(200,80,80,0.3); color:#e08080; }
    .btn--danger:hover { background:rgba(200,80,80,0.05); border-color:rgba(200,80,80,0.5); }
    .alert { padding:1.1rem 1.5rem; margin-bottom:1.5rem; font-family:var(--font-body); font-size:1.05rem; border:1px solid; line-height:1.65; }
    .alert--error { border-color:rgba(200,80,80,0.3); color:#e08080; background:rgba(200,80,80,0.05); }
    .alert--success { border-color:rgba(80,200,120,0.3); color:#80c880; background:rgba(80,200,120,0.05); }
    .alert--info { border-color:rgba(100,160,200,0.3); color:#80b8d0; background:rgba(100,160,200,0.05); }
    .user-badge {
      display:inline-flex; align-items:center; gap:0.5rem;
      font-family:var(--font-label); font-size:0.72rem; letter-spacing:0.15em; color:var(--text-secondary);
    }
    .user-badge__avatar { font-size:1.2rem; }
    .card {
      background:rgba(12,12,16,0.6); border:1px solid var(--border-card); padding:2rem;
      transition:border-color 0.3s ease;
    }
    .card:hover { border-color:rgba(160,160,170,0.12); }
    .badge-count {
      display:inline-flex; align-items:center; justify-content:center;
      min-width:18px; height:18px; padding:0 5px; border-radius:9px;
      background:#e0607a; color:#fff; font-size:0.6rem; font-family:var(--font-mono);
      line-height:1; margin-left:0.3rem; vertical-align:super;
    }
    .title-badge {
      display:inline-block; font-family:var(--font-label); font-size:0.58rem;
      letter-spacing:0.18em; text-transform:uppercase; padding:0.25em 0.8em;
      border:1px solid; opacity:0.9;
    }
    /* Rich text editor toolbar */
    .rte-toolbar { display:flex; flex-wrap:wrap; gap:2px; padding:0.5rem; background:rgba(10,10,14,0.9); border:1px solid var(--border-card); border-bottom:none; }
    .rte-btn { background:none; border:1px solid transparent; color:var(--text-dim); padding:0.35rem 0.6rem; cursor:pointer; font-size:1rem; transition:all 0.2s; font-family:var(--font-body); }
    .rte-btn:hover { color:var(--text-primary); border-color:var(--border-card); }
    .rte-btn.active { color:var(--text-primary); border-color:var(--text-dim); }
    .rte-editor { min-height:150px; padding:1rem; background:rgba(16,16,20,0.8); border:1px solid var(--border-card); color:var(--text-primary); font-family:var(--font-body); font-size:1.1rem; line-height:1.7; outline:none; overflow-y:auto; }
    .rte-editor:focus { border-color:rgba(160,160,170,0.3); }
    .rte-editor p { margin:0 0 0.5rem; }
    <?php if (isset($extraCss)) echo $extraCss; ?>
  </style>
</head>
<body style="<?= $bodyBg ?>">
  <div class="loader" id="loader">
    <div class="loader__ornament">◆ — ◇ — ◆</div>
    <div class="loader__brand">Clan Lar</div>
  </div>
  <div class="page-wrapper">
    <nav class="nav" id="nav">
      <a href="/" class="nav-brand">◆ Clan Lar</a>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation"><span></span><span></span><span></span></button>
      <ul class="nav-links" id="navLinks">
        <li><a href="/">Houses</a></li>
        <li><a href="news">News</a></li>
        <?php if ($user): ?>
          <li><a href="cabinet">Cabinet<?php if ($unreadCount > 0): ?><span class="badge-count"><?= $unreadCount ?></span><?php endif; ?></a></li>
          <?php if ($user['role'] === 'admin'): ?>
            <li><a href="admin">Admin</a></li>
          <?php endif; ?>
          <li><a href="api/logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login">Enter</a></li>
        <?php endif; ?>
      </ul>
      <button class="nav__search" id="searchBtn">⌕ Search</button>
    </nav>
