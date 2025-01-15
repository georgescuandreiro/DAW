<?php
session_start();
require_once "../database/db_connect.php";

// Include header si navbar
include "../includes/header.php";
include "../includes/navbar.php";

// Feed RSS din BBC NEWS 
$feedUrl = "https://feeds.bbci.co.uk/news/world/rss.xml";
$rssContent = @simplexml_load_file($feedUrl);

// Array articole pentru procesare
$articles = [];
if ($rssContent && isset($rssContent->channel->item)) {
    foreach ($rssContent->channel->item as $item) {
        $title       = (string) $item->title;
        $link        = (string) $item->link;
        $description = strip_tags((string) $item->description); // Descriere
        $pubDate     = (string) $item->pubDate;

        // Generare Id unic pentru fiecare articol (hash)
        $articleId = md5($title);

        // Adaugare articole in array
        $articles[] = [
            'id'          => $articleId,
            'title'       => $title,
            'link'        => $link,
            'description' => $description,
            'pubDate'     => $pubDate
        ];
    }
}

// Sortare articole dupa data (Cel mai nou primul)
usort($articles, function ($a, $b) {
    return strtotime($b['pubDate']) - strtotime($a['pubDate']);
});

// Paginare: 6 articole pe pagina
$articlesPerPage = 6;
$totalArticles   = count($articles);
$totalPages      = ceil($totalArticles / $articlesPerPage);

// Pagina curenta (from ?page= ), default este 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}

// Determina articolele din pagina curenta
$startIndex      = ($page - 1) * $articlesPerPage;
$displayArticles = array_slice($articles, $startIndex, $articlesPerPage);

// Functie helper ( pentru like-uri )
function hasLiked($conn, $userId, $articleId) {
    $stmt = $conn->prepare("SELECT 1 FROM likes WHERE user_id = ? AND article_id = ?");
    $stmt->bind_param("is", $userId, $articleId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getLikeCount($conn, $articleId) {
    $stmt = $conn->prepare("SELECT COUNT(*) as like_count FROM likes WHERE article_id = ?");
    $stmt->bind_param("s", $articleId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['like_count'] ?? 0;
}

function isFavorite($conn, $userId, $articleId) {
    $stmt = $conn->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND article_id = ?");
    $stmt->bind_param("is", $userId, $articleId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}
?>

<div class="container mt-5">
    <!-- Titlu pagina -->
    <div class="container mt-5" style="position: relative; height: 300px; background: url('/assets/world-news.png') no-repeat center center; background-size: cover; border-radius: 10px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);">
    <!-- Titlu pagina -->
    <h2 style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; padding: 15px 30px; background: rgba(0, 0, 0, 0.7); box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5); border-radius: 10px; text-align: center; font-weight: bold; z-index: 1;">
        World News & Politics
    </h2>
</div>
    <p class="text-center" style="font-style: italic;">Get the latest updates from around the globe.</p>

    <!-- Sectiunea Top Stories -->
    <div class="newspaper-container mb-5 p-4" style="border: 2px solid #000; background-color: #f8f8f8; font-family: 'Times New Roman', serif;">
        <h4 class="text-center mb-4" style="text-transform: uppercase;">Top Stories</h4>
        <div class="row gx-4">
            <?php foreach (array_slice($articles, 0, 3) as $article): ?>
                <div class="col-md-4 mb-4">
                    <div class="newspaper-article p-3" style="border: 1px solid #ccc; background: #fff;">
                        <h5 style="font-weight: bold;">
                            <?= htmlspecialchars($article['title']) ?>
                        </h5>
                        <p style="font-size: 14px;">
                            <?= htmlspecialchars($article['description']) ?>
                        </p>
                        <a href="<?= htmlspecialchars($article['link']) ?>" target="_blank" style="text-decoration: underline; color: #000;">Read full article</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Temporizator pentru noutati -->
    <div class="news-ticker mb-4" style="background-color: #000; color: #fff; padding: 10px; font-size: 16px;">
        <marquee behavior="scroll" direction="left">
            <?php foreach (array_slice($articles, 0, 5) as $article): ?>
                <span><?= htmlspecialchars($article['title']) ?> | </span>
            <?php endforeach; ?>
        </marquee>
    </div>

    <!-- Grid articole -->
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php if (empty($displayArticles)): ?>
            <div class="col">
                <div class="alert alert-warning">No articles found on this page.</div>
            </div>
        <?php else: ?>
            <?php foreach ($displayArticles as $article): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm p-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?= htmlspecialchars($article['link']) ?>" target="_blank" style="text-decoration: none; color: inherit;">
                                    <?= htmlspecialchars($article['title']) ?>
                                </a>
                            </h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= date('F j, Y, g:i a', strtotime($article['pubDate'])) ?></h6>
                            <p class="card-text"><?= htmlspecialchars($article['description']) ?></p>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <!-- Buton Like -->
                            <button class="btn btn-sm btn-light like-btn" 
                                    data-article-id="<?= $article['id'] ?>" 
                                    data-liked="<?= isset($_SESSION['user_id']) && hasLiked($conn, $_SESSION['user_id'], $article['id']) ? 'true' : 'false' ?>">
                                <span class="like-icon"><?= isset($_SESSION['user_id']) && hasLiked($conn, $_SESSION['user_id'], $article['id']) ? '❤️' : '🤍' ?></span>
                                <span class="like-count"><?= getLikeCount($conn, $article['id']) ?></span>
                                Like
                            </button>
                            <!-- Buton Favorite -->
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button class="btn btn-sm btn-light favorite-btn" 
                                        data-article-id="<?= $article['id'] ?>" 
                                        data-title="<?= htmlspecialchars($article['title']) ?>" 
                                        data-link="<?= htmlspecialchars($article['link']) ?>" 
                                        data-favorited="<?= isFavorite($conn, $_SESSION['user_id'], $article['id']) ? 'true' : 'false' ?>">
                                    <span class="favorite-icon"><?= isFavorite($conn, $_SESSION['user_id'], $article['id']) ? '⭐' : '☆' ?></span>
                                    Favorite
                                </button>
                            <?php endif; ?>

                            <!-- Buton Share -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Share
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="https://facebook.com/share?url=<?= htmlspecialchars($article['link']) ?>" target="_blank">Facebook</a></li>
                                    <li><a class="dropdown-item" href="https://twitter.com/intent/tweet?url=<?= htmlspecialchars($article['link']) ?>" target="_blank">Twitter</a></li>
                                    <li><a class="dropdown-item" href="https://www.linkedin.com/shareArticle?mini=true&url=<?= htmlspecialchars($article['link']) ?>" target="_blank">LinkedIn</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Paginare -->
    <div class="mt-4 text-center">
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <!-- Link pagina precedenta -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= max(1, $page - 1) ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php
                    // Determina range-ul de pagini pentru afisaj
                    $maxVisiblePages = 6; // Maximul de pagini care sa fie afisate
                    $startPage = max(1, $page - floor($maxVisiblePages / 2));
                    $endPage = min($totalPages, $startPage + $maxVisiblePages - 1);

                    // Ajustare pagina de start daca este spre finalul sirului
                    if ($endPage - $startPage + 1 < $maxVisiblePages) {
                        $startPage = max(1, $endPage - $maxVisiblePages + 1);
                    }
                    ?>

                    <!-- Linkuri numar de pagina -->
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Link pagina noua -->
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function () {
            const articleId = this.getAttribute('data-article-id');
            const isLiked = this.getAttribute('data-liked') === 'true';

            fetch('like_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `article_id=${articleId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'liked') {
                    this.querySelector('.like-icon').textContent = '❤️';
                    this.querySelector('.like-count').textContent = parseInt(this.querySelector('.like-count').textContent) + 1;
                } else if (data.status === 'unliked') {
                    this.querySelector('.like-icon').textContent = '🤍';
                    this.querySelector('.like-count').textContent = parseInt(this.querySelector('.like-count').textContent) - 1;
                }
            });
        });
    });

    document.querySelectorAll('.favorite-btn').forEach(button => {
        button.addEventListener('click', function () {
            const articleId = this.getAttribute('data-article-id');
            const title = this.getAttribute('data-title');
            const link = this.getAttribute('data-link');
            const isFavorited = this.getAttribute('data-favorited') === 'true';

            fetch('favorite_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `article_id=${articleId}&title=${encodeURIComponent(title)}&link=${encodeURIComponent(link)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'favorited') {
                    this.querySelector('.favorite-icon').textContent = '⭐';
                    this.setAttribute('data-favorited', 'true');
                } else if (data.status === 'unfavorited') {
                    this.querySelector('.favorite-icon').textContent = '☆';
                    this.setAttribute('data-favorited', 'false');
                }
            });
        });
    });
</script>

<?php include "../includes/footer.php"; ?>
