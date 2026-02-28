<?php
/* ============================================================
   THE ARCHIVES OF CLAN LAR — Configuration v2
   ============================================================ */

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'arkh574q_andrey');
define('DB_USER', 'arkh574q_andrey');
define('DB_PASS', 'JoneRinaldo34');

// --- Site ---
define('SITE_NAME', 'The Archives of Clan Lar');
define('SITE_URL', 'https://andreykuznetcoveso.com');
define('ADMIN_EMAIL', 'support@andreykuznetcoveso.com');
define('ADMIN_USER_ID', 1);

// --- Email ---
define('SMTP_FROM', 'noreply@andreykuznetcoveso.com');
define('SMTP_FROM_NAME', 'Clan Lar Archives');

// --- Session ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Database connection ---
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

// --- Auth helpers ---
function currentUser(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    static $user = null;
    if ($user === null) {
        $stmt = getDB()->prepare('
            SELECT u.*, t.name as title_name, t.color as title_color, t.icon as title_icon,
                   g.name as guild_name, g.server as guild_server
            FROM users u
            LEFT JOIN titles t ON u.title_id = t.id
            LEFT JOIN guilds g ON u.guild_id = g.id
            WHERE u.id = ?
        ');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function requireAuth(): array {
    $user = currentUser();
    if (!$user) {
        if (isAjax()) {
            http_response_code(401);
            die(json_encode(['error' => 'Not authenticated']));
        }
        header('Location: /login');
        exit;
    }
    return $user;
}

function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        die(json_encode(['error' => 'Admin access required']));
    }
    return $user;
}

function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// --- CSRF ---
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals(csrfToken(), $token);
}

// --- Sanitization ---
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function sanitizeHtml(string $html): string {
    $allowed = '<p><br><em><strong><a><ul><ol><li><blockquote><h2><h3><span><div><img>';
    return strip_tags($html, $allowed);
}

// --- Email ---
function sendEmail(string $to, string $subject, string $htmlBody): bool {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    return mail($to, $subject, $htmlBody, $headers);
}

function sendVerificationEmail(string $email, string $username, string $code): bool {
    $subject = "Your key to the Archives — " . SITE_NAME;
    $body = emailTemplateWelcome($username, $code);
    return sendEmail($email, $subject, $body);
}

function emailTemplateWelcome(string $username, string $code): string {
    $bgUrl = SITE_URL . '/img/backgrounds/email-bg.jpg';
    return '
    <div style="max-width:560px;margin:0 auto;background:#0a0a0c;font-family:Georgia,serif;">
        <div style="background:linear-gradient(180deg,rgba(10,10,14,0.3),rgba(10,10,14,0.95)),url(' . $bgUrl . ') center/cover;padding:3rem 2.5rem 2rem;">
            <div style="text-align:center;margin-bottom:1rem;">
                <span style="color:#5e5a66;font-size:0.7rem;letter-spacing:0.5em;">&#9670; &mdash; &#9671; &mdash; &#9670;</span>
            </div>
            <h1 style="text-align:center;color:#d4d0c8;font-size:1.4rem;font-weight:400;letter-spacing:0.1em;margin:0 0 0.5rem;">Welcome, ' . e($username) . '</h1>
            <p style="text-align:center;color:#706c64;font-size:0.7rem;letter-spacing:0.3em;text-transform:uppercase;margin:0;">The Archives await</p>
        </div>
        <div style="padding:2rem 2.5rem;">
            <p style="color:#9e9a92;font-size:1rem;line-height:1.8;margin:0 0 1.2rem;">
                You have taken the first step. The Archives of Clan Lar are not simply a collection of walls and furnishings — they are records of places that carry weight. Stories told in stone, flame, and ruin.
            </p>
            <p style="color:#9e9a92;font-size:1rem;line-height:1.8;margin:0 0 1.5rem;">
                Before you may enter, we need to know you are who you say you are. Use the code below to verify your account. It will remain valid for thirty minutes.
            </p>
            <div style="text-align:center;margin:2rem 0;padding:1.5rem;background:rgba(18,19,26,0.8);border:1px solid #22212c;">
                <div style="color:#706c64;font-size:0.65rem;letter-spacing:0.3em;text-transform:uppercase;margin-bottom:0.75rem;">Verification Code</div>
                <div style="font-size:2.2rem;letter-spacing:0.6em;color:#d4d0c8;font-family:monospace;font-weight:bold;">' . e($code) . '</div>
                <div style="color:#4a4640;font-size:0.8rem;margin-top:0.75rem;font-style:italic;">Expires in 30 minutes</div>
            </div>
            <p style="color:#706c64;font-size:0.9rem;line-height:1.7;margin:0 0 0.5rem;">
                Once verified, the doors open. You will be able to leave your mark in the comments, reach out directly through the messaging system, and — if you wish — request a visual creation for your own builds.
            </p>
            <p style="color:#4a4640;font-size:0.85rem;line-height:1.7;margin:0;font-style:italic;">
                If you did not create this account, you can safely ignore this message.
            </p>
        </div>
        <div style="border-top:1px solid #1a1a1e;padding:1.5rem 2.5rem;text-align:center;">
            <span style="color:#3a3840;font-size:0.6rem;letter-spacing:0.4em;">THE ARCHIVES OF CLAN LAR</span><br>
            <span style="color:#3a3840;font-size:0.55rem;letter-spacing:0.2em;">&#9671; &mdash; &#9670; &mdash; &#9671;</span>
        </div>
    </div>';
}

function emailTemplate(string $content): string {
    return '
    <div style="max-width:500px;margin:0 auto;padding:2rem;background:#0a0a0c;font-family:Georgia,serif;">
        <div style="text-align:center;margin-bottom:2rem;">
            <span style="color:#555;font-size:0.7rem;letter-spacing:0.4em;">&#9670; &mdash; &#9671; &mdash; &#9670;</span>
        </div>
        ' . $content . '
        <div style="text-align:center;margin-top:2rem;border-top:1px solid #1a1a1e;padding-top:1.5rem;">
            <span style="color:#555;font-size:0.65rem;letter-spacing:0.3em;">THE ARCHIVES OF CLAN LAR</span>
        </div>
    </div>';
}

function sendMessageNotificationEmail(string $to, string $fromName, string $subject, string $preview): bool {
    $emailSubject = "New message from {$fromName} — " . SITE_NAME;
    $body = emailTemplate("
        <h2 style='color:#e0e0e2;font-family:Georgia,serif;font-size:1.1rem;'>New Message</h2>
        <p style='color:#a0a0a5;'>From: <strong style=\"color:#d4d0c8;\">" . e($fromName) . "</strong></p>
        <p style='color:#a0a0a5;'>Subject: <strong style=\"color:#d4d0c8;\">" . e($subject) . "</strong></p>
        <div style='margin:1.5rem 0;padding:1rem;background:rgba(18,19,26,0.8);border:1px solid #22212c;'>
            <p style='color:#9e9a92;font-size:0.95rem;line-height:1.7;margin:0;'>" . e(mb_substr(strip_tags($preview), 0, 200)) . "...</p>
        </div>
        <p style='color:#706c64;font-size:0.85rem;font-style:italic;'>Log in to the Archives to read and reply.</p>
    ");
    return sendEmail($to, $emailSubject, $body);
}

// --- JSON response ---
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// --- Time formatting ---
function timeAgo(string $datetime): string {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

// --- Avatar options ---
function avatarSymbols(): array {
    return [
        'default' => '◆',
        'crimson' => '✠',
        'ayleid'  => '⌘',
        'wyrd'    => '❧',
        'sheoth'  => '✦',
        'scroll'  => '☙',
        'star'    => '✧',
        'crown'   => '♛',
    ];
}

function getAvatarSymbol(string $key): string {
    $symbols = avatarSymbols();
    return $symbols[$key] ?? $symbols['default'];
}

// --- Background options ---
function backgroundOptions(): array {
    return [
        'default'      => ['name' => 'Void',           'file' => 'none',                   'desc' => 'Pure darkness'],
        'dwemer'       => ['name' => 'Dwemer Ruin',     'file' => 'bg-dwemer.jpg',          'desc' => 'Ancient machinery'],
        'ebonheart'    => ['name' => 'Ebonheart Pact',  'file' => 'bg-ebonheart.jpg',       'desc' => 'Northern warriors'],
        'aldmeri'      => ['name' => 'Aldmeri Dominion', 'file' => 'bg-aldmeri.jpg',         'desc' => 'Three races united'],
        'ayrenn'       => ['name' => 'Queen Ayrenn',     'file' => 'bg-ayrenn.jpg',          'desc' => 'Golden banners'],
        'jorunn'       => ['name' => 'King Jorunn',      'file' => 'bg-jorunn.jpg',          'desc' => 'Crimson war'],
        'emeric'       => ['name' => 'King Emeric',      'file' => 'bg-emeric.jpg',          'desc' => 'The High King'],
        'cyrodiil'     => ['name' => 'Cyrodiil Siege',   'file' => 'bg-cyrodiil.jpg',        'desc' => 'Walls under fire'],
        'alliance'     => ['name' => 'Alliance Battle',  'file' => 'bg-alliance.jpg',        'desc' => 'The great war'],
        'daggerfall'   => ['name' => 'Daggerfall',       'file' => 'bg-daggerfall.jpg',      'desc' => 'Breton streets'],
        'eldenroot'    => ['name' => 'Elden Root',       'file' => 'bg-eldenroot.jpg',       'desc' => 'Grahtwood capital'],
        'eastmarch'    => ['name' => 'Eastmarch',        'file' => 'bg-eastmarch.jpg',       'desc' => 'Frozen north'],
        'alikr'        => ['name' => 'Alik\'r Desert',   'file' => 'bg-alikr.jpg',           'desc' => 'Sand and ruins'],
        'stonethorn'   => ['name' => 'Stonethorn',       'file' => 'bg-stonethorn.jpg',      'desc' => 'Dark heart'],
        'dragonhold'   => ['name' => 'Dragonhold',       'file' => 'bg-dragonhold.jpg',      'desc' => 'Dragon\'s domain'],
        'elsweyr'      => ['name' => 'Elsweyr Battle',   'file' => 'bg-elsweyr-battle.jpg',  'desc' => 'Dragons unleashed'],
        'elsweyr2'     => ['name' => 'Elsweyr',          'file' => 'bg-elsweyr.jpg',         'desc' => 'Khajiit homeland'],
        'wolfhunter'   => ['name' => 'Wolfhunter',       'file' => 'bg-wolfhunter.jpg',      'desc' => 'Hircine\'s hunt'],
        'summerset'    => ['name' => 'Summerset',        'file' => 'bg-summerset.jpg',       'desc' => 'Psijic scrying'],
        'reach'        => ['name' => 'Horns of Reach',   'file' => 'bg-reach.jpg',           'desc' => 'Blood and ritual'],
        'thieves'      => ['name' => 'Thieves Guild',    'file' => 'bg-thieves.jpg',         'desc' => 'Market chase'],
    ];
}

function getBackgroundCss(string $key): string {
    $bgs = backgroundOptions();
    if ($key === 'default' || !isset($bgs[$key]) || $bgs[$key]['file'] === 'none') {
        return '';
    }
    $file = $bgs[$key]['file'];
    return "background-image: linear-gradient(180deg, rgba(6,6,8,0.55), rgba(6,6,8,0.78)), url('img/backgrounds/{$file}'); background-size: cover; background-position: center; background-attachment: fixed;";
}

// --- Message helpers ---
function getUnreadCount(int $userId): int {
    $stmt = getDB()->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function getCategoryLabel(string $cat): string {
    $labels = [
        'review'          => '☙ Review',
        'problem'         => '⚠ Problem',
        'visual_creation' => '✦ Visual Creation',
        'collaboration'   => '◇ Collaboration',
        'other'           => '◆ Other',
    ];
    return $labels[$cat] ?? $labels['other'];
}

function getCategoryIcon(string $cat): string {
    $icons = [
        'review' => '☙', 'problem' => '⚠', 'visual_creation' => '✦',
        'collaboration' => '◇', 'other' => '◆',
    ];
    return $icons[$cat] ?? '◆';
}

// --- User profile helpers ---
function getUserTitles(int $userId): array {
    $stmt = getDB()->prepare('
        SELECT t.* FROM titles t
        JOIN user_titles ut ON t.id = ut.title_id
        WHERE ut.user_id = ?
        ORDER BY t.sort_order
    ');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getGuilds(): array {
    $stmt = getDB()->query('SELECT * FROM guilds ORDER BY name');
    return $stmt->fetchAll();
}

// --- Game ID validation ---
function validateGameId(string $gameId): string {
    $gameId = trim($gameId);
    if (empty($gameId)) return '';

    // Auto-prepend @ if missing
    if (!str_starts_with($gameId, '@')) {
        $gameId = '@' . $gameId;
    }

    // Validate length (@ + at least 1 char)
    if (mb_strlen($gameId) < 2) {
        throw new InvalidArgumentException('In-game ID must start with @ followed by your name');
    }

    // Validate characters (@ + alphanumeric, dots, underscores, hyphens)
    if (!preg_match('/^@[\w.\-]+$/u', $gameId)) {
        throw new InvalidArgumentException('In-game ID contains invalid characters');
    }

    return $gameId;
}
