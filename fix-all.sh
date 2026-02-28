#!/bin/bash
# ============================================================
# TESOHousing — Comprehensive Fix Script
# Run this on your server in the site root directory
# ============================================================
# Usage: cd /path/to/site && bash fix-all.sh
# ============================================================

set -e
echo ""
echo "◆ ━━━ THE ARCHIVES OF CLAN LAR — Fix Script ━━━ ◆"
echo ""

# Safety check
if [ ! -f "index.html" ] || [ ! -f ".htaccess" ]; then
    echo "ERROR: Run this script from the site root directory"
    echo "       (where index.html and .htaccess live)"
    exit 1
fi

# Create backup
BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"
echo "Creating backup in $BACKUP_DIR..."
mkdir -p "$BACKUP_DIR"
for f in index.html bastion.html abagarlas.html creature.html 404.html \
         admin.php cabinet.php contact.php login.php news.php verify.php \
         config/db.php includes/header.php js/main.js api/admin.php api/logout.php; do
    if [ -f "$f" ]; then
        mkdir -p "$BACKUP_DIR/$(dirname "$f")"
        cp "$f" "$BACKUP_DIR/$f"
    fi
done
echo "  ✓ Backup complete"
echo ""

# ============================================================
# FIX 1: Link extensions in static HTML files
# ============================================================
echo "FIX 1: Removing .html/.php extensions from links..."

HTML_FILES="index.html bastion.html abagarlas.html creature.html 404.html"
for f in $HTML_FILES; do
    if [ -f "$f" ]; then
        sed -i \
            -e 's|href="index\.html"|href="/"|g' \
            -e "s|href='index\.html'|href='/'|g" \
            -e 's|href="bastion\.html"|href="bastion"|g' \
            -e 's|href="abagarlas\.html"|href="abagarlas"|g' \
            -e 's|href="creature\.html"|href="creature"|g' \
            -e 's|href="news\.php"|href="news"|g' \
            -e 's|href="login\.php"|href="login"|g' \
            -e 's|href="contact\.php"|href="contact"|g' \
            -e 's|href="cabinet\.php"|href="cabinet"|g' \
            -e 's|href="admin\.php"|href="admin"|g' \
            -e 's|href="index\.html#|href="/#|g' \
            -e "s|location\.href='cabinet\.php'|location.href='cabinet'|g" \
            -e "s|location\.href='login\.php'|location.href='login'|g" \
            "$f"
        echo "  ✓ $f"
    else
        echo "  ⚠ $f not found, skipping"
    fi
done
echo ""

# ============================================================
# FIX 2: Link extensions in PHP files (nav, redirects)
# ============================================================
echo "FIX 2: Fixing PHP file links and redirects..."

PHP_FILES="admin.php cabinet.php contact.php login.php news.php verify.php includes/header.php"
for f in $PHP_FILES; do
    if [ -f "$f" ]; then
        sed -i \
            -e 's|href="index\.html"|href="/"|g' \
            -e 's|href="bastion\.html"|href="bastion"|g' \
            -e 's|href="abagarlas\.html"|href="abagarlas"|g' \
            -e 's|href="creature\.html"|href="creature"|g' \
            -e 's|href="news\.php"|href="news"|g' \
            -e 's|href="login\.php"|href="login"|g' \
            -e 's|href="contact\.php"|href="contact"|g' \
            -e 's|href="cabinet\.php"|href="cabinet"|g' \
            -e 's|href="admin\.php"|href="admin"|g' \
            -e 's|href="admin\.php?|href="admin?|g' \
            -e 's|href="news\.php?|href="news?|g' \
            -e "s|location\.href='cabinet\.php'|location.href='cabinet'|g" \
            -e "s|location\.href = 'admin\.php|location.href = 'admin|g" \
            -e 's|location\.href = "admin\.php|location.href = "admin|g' \
            -e 's|location\.href = "news\.php|location.href = "news|g' \
            "$f"
        echo "  ✓ $f"
    else
        echo "  ⚠ $f not found, skipping"
    fi
done
echo ""

# ============================================================
# FIX 3: config/db.php — requireAuth redirect
# ============================================================
echo "FIX 3: Fixing config/db.php redirect..."
if [ -f "config/db.php" ]; then
    sed -i \
        -e "s|header('Location: login\.php')|header('Location: /login')|g" \
        -e 's|header("Location: login\.php")|header("Location: /login")|g' \
        -e "s|header('Location: /login\.php')|header('Location: /login')|g" \
        -e 's|header("Location: /login\.php")|header("Location: /login")|g' \
        config/db.php
    echo "  ✓ config/db.php"
else
    echo "  ⚠ config/db.php not found"
fi
echo ""

# ============================================================
# FIX 4: API logout redirect
# ============================================================
echo "FIX 4: Fixing API redirects..."
if [ -f "api/logout.php" ]; then
    sed -i \
        -e "s|header('Location: /login\.php')|header('Location: /login')|g" \
        -e 's|header("Location: /login\.php")|header("Location: /login")|g' \
        -e "s|header('Location: login\.php')|header('Location: /login')|g" \
        -e "s|header('Location: /index\.html')|header('Location: /')|g" \
        -e 's|header("Location: /index\.html")|header("Location: /")|g' \
        api/logout.php
    echo "  ✓ api/logout.php"
fi

# Fix api/login.php, api/register.php, api/verify.php redirects
for f in api/login.php api/register.php api/verify.php; do
    if [ -f "$f" ]; then
        sed -i \
            -e "s|'redirect' => 'cabinet\.php'|'redirect' => 'cabinet'|g" \
            -e 's|"redirect" => "cabinet\.php"|"redirect" => "cabinet"|g' \
            -e "s|'redirect' => 'login\.php'|'redirect' => 'login'|g" \
            -e "s|'redirect' => '/login\.php'|'redirect' => '/login'|g" \
            -e "s|'redirect' => 'admin\.php'|'redirect' => 'admin'|g" \
            -e "s|'redirect' => 'verify\.php'|'redirect' => 'verify'|g" \
            "$f"
        echo "  ✓ $f"
    fi
done
echo ""

# ============================================================
# FIX 5: js/main.js search index
# ============================================================
echo "FIX 5: Fixing js/main.js search index URLs..."
if [ -f "js/main.js" ]; then
    sed -i \
        -e "s|u:'bastion\.html'|u:'bastion'|g" \
        -e "s|u:'abagarlas\.html'|u:'abagarlas'|g" \
        -e "s|u:'creature\.html'|u:'creature'|g" \
        -e "s|u:'index\.html'|u:'/'|g" \
        -e "s|u:'index\.html#|u:'/#|g" \
        -e "s|u:'news\.php'|u:'news'|g" \
        -e "s|u:'login\.php'|u:'login'|g" \
        -e "s|u:'contact\.php'|u:'contact'|g" \
        -e "s|u:'cabinet\.php'|u:'cabinet'|g" \
        -e "s|u:'admin\.php'|u:'admin'|g" \
        js/main.js
    echo "  ✓ js/main.js"
else
    echo "  ⚠ js/main.js not found"
fi
echo ""

# ============================================================
# FIX 6: Replace api/admin.php (CRITICAL — was broken duplicate)
# ============================================================
echo "FIX 6: Replacing api/admin.php (was broken duplicate of admin page)..."
cat > api/admin.php << 'PHPEOF'
<?php
/* ============================================================
   Admin API — handles admin actions (save news, delete news)
   ============================================================ */
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
if (!verifyCsrf()) {
    jsonResponse(['error' => 'Invalid security token.'], 403);
}

$user = requireAdmin();
$db = getDB();

$action = $_POST['action'] ?? '';

switch ($action) {

    case 'save_news':
        $newsId = !empty($_POST['news_id']) ? (int) $_POST['news_id'] : null;
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $content = $_POST['content'] ?? '';
        $published = isset($_POST['published']) ? 1 : 0;

        if (mb_strlen($title) < 2) {
            jsonResponse(['error' => 'Title must be at least 2 characters.'], 400);
        }

        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
        $slug = trim($slug, '-');
        $content = sanitizeHtml($content);

        if ($newsId) {
            $stmt = $db->prepare('UPDATE news SET title = ?, slug = ?, excerpt = ?, image = ?, content = ?, published = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$title, $slug, $excerpt ?: null, $image ?: null, $content, $published, $newsId]);
            jsonResponse(['success' => true, 'message' => 'Post updated.', 'slug' => $slug]);
        } else {
            $stmt = $db->prepare('INSERT INTO news (title, slug, excerpt, image, content, published, author_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
            $stmt->execute([$title, $slug, $excerpt ?: null, $image ?: null, $content, $published, $user['id']]);
            jsonResponse(['success' => true, 'message' => 'Post created.', 'slug' => $slug, 'id' => (int) $db->lastInsertId()]);
        }
        break;

    case 'delete_news':
        $newsId = (int) ($_POST['news_id'] ?? 0);
        if (!$newsId) jsonResponse(['error' => 'Invalid news ID.'], 400);
        $db->prepare('DELETE FROM comments WHERE news_id = ?')->execute([$newsId]);
        $db->prepare('DELETE FROM news WHERE id = ?')->execute([$newsId]);
        jsonResponse(['success' => true, 'message' => 'Post deleted.']);
        break;

    default:
        jsonResponse(['error' => 'Unknown action.'], 400);
}
PHPEOF
echo "  ✓ api/admin.php (complete rewrite)"
echo ""

# ============================================================
# FIX 7: Replace 404.html (fix link back to home)
# ============================================================
echo "FIX 7: Replacing 404.html..."
cat > 404.html << 'HTMLEOF'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lost in the Passage — 404</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=IBM+Plex+Mono:wght@300;400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>◆</text></svg>">
  <style>
    body { background: var(--bg-void); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .lost { text-align: center; padding: 2rem; }
    .lost__code {
      font-family: var(--font-display);
      font-size: clamp(4rem, 10vw, 8rem);
      letter-spacing: 0.3em;
      color: rgba(160,160,170,0.12);
      line-height: 1;
    }
    .lost__title {
      font-family: var(--font-display);
      font-size: clamp(1rem, 2.5vw, 1.5rem);
      letter-spacing: 0.25em;
      text-transform: uppercase;
      color: var(--text-secondary);
      margin-top: 1rem;
    }
    .lost__desc {
      font-family: var(--font-body);
      font-style: italic;
      color: var(--text-dim);
      font-size: 1.05rem;
      max-width: 400px;
      margin: 1.5rem auto 0;
      line-height: 1.8;
    }
    .lost__link {
      display: inline-block;
      margin-top: 2.5rem;
      font-family: var(--font-label);
      font-size: 0.6rem;
      letter-spacing: 0.3em;
      text-transform: uppercase;
      color: var(--text-secondary);
      text-decoration: none;
      padding: 0.6em 2em;
      border: 1px solid rgba(160,160,170,0.15);
      transition: all 0.4s ease;
    }
    .lost__link:hover {
      border-color: rgba(160,160,170,0.4);
      color: var(--text-primary);
      transform: translateY(-1px);
    }
    .lost__ornament {
      color: rgba(160,160,170,0.15);
      font-size: 0.7rem;
      letter-spacing: 0.4em;
      margin-top: 3rem;
    }
  </style>
</head>
<body>
  <div class="lost">
    <div class="lost__code">404</div>
    <h1 class="lost__title">The passage leads nowhere</h1>
    <p class="lost__desc">Whatever was here has been sealed, moved, or never existed. The archives have no record of this path.</p>
    <a href="/" class="lost__link">← Return to Clan Lar</a>
    <div class="lost__ornament">◆ — — ◇ — — ◆ — — ◇ — — ◆</div>
  </div>
</body>
</html>
HTMLEOF
echo "  ✓ 404.html"
echo ""

# ============================================================
# VERIFICATION
# ============================================================
echo "━━━ VERIFICATION ━━━"
echo ""
echo "Checking for remaining .html/.php extension links..."
REMAINING=$(grep -rnl 'href="[^"]*\.html\|href="[^"]*\.php' \
    index.html bastion.html abagarlas.html creature.html 404.html \
    admin.php cabinet.php contact.php login.php news.php verify.php \
    includes/header.php js/main.js 2>/dev/null | \
    grep -v 'googleapis\|gstatic\|css/\|js/\|fonts\|display=\|svg\|api/' || true)

if [ -z "$REMAINING" ]; then
    echo "  ✓ No .html/.php extension links remaining"
else
    echo "  ⚠ Files with possible remaining extension links:"
    echo "$REMAINING"
    echo ""
    echo "  Checking details..."
    for f in $REMAINING; do
        echo "  --- $f ---"
        grep -n 'href="[^"]*\.html\|href="[^"]*\.php' "$f" 2>/dev/null | \
            grep -v 'googleapis\|gstatic\|css/\|js/\|fonts\|display=\|svg\|api/' | head -5
    done
fi

echo ""
echo "Checking config/db.php redirect..."
if grep -q "login\.php" config/db.php 2>/dev/null; then
    echo "  ⚠ config/db.php still has login.php reference"
else
    echo "  ✓ config/db.php redirects clean"
fi

echo ""
echo "◆ ━━━ Fix script complete ━━━ ◆"
echo ""
echo "Backup saved in: $BACKUP_DIR/"
echo ""
echo "REMAINING MANUAL STEPS:"
echo "  1. Upload the new config/db.php (if you want the full rewrite)"
echo "  2. Add progressive form JS to contact.php (see contact-form-fix.js)"
echo "  3. Add @ validation to contact.php and cabinet.php (see validation-fix.js)"
echo "  4. Test all navigation links"
echo "  5. Test cabinet page access"
echo "  6. Test news creation in admin panel"
echo ""
