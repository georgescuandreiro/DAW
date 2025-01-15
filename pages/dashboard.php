<?php
session_start();
require_once "../database/db_connect.php";

// Include header si navbar
include "../includes/header.php";
include "../includes/navbar.php";

// Categorii articole cu imagini specifice 
$categories = [
    'World News & Politics' => [
        'link' => '/pages/category1',
        'image' => '/assets/world-news.png' // URL imagine categorie
    ],
    'Science, Tech & Innovation' => [
        'link' => '/pages/category2',
        'image' => '/assets/science-tech.png'
    ],
    'Lifestyle & Culture' => [
        'link' => '/pages/category3',
        'image' => '/assets/lifestyle-culture.png'
    ]
];

// Trage articole favorite din db pentru userii logati ( FETCH )
$favorites = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT title, link FROM favorites WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $favorites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Indice pentru carousel
$loopIndex = 0;
?>
<div class="container mt-5">

    <!-- Carousel pentru categorii -->
    <div id="mainCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($categories as $category => $data): ?>
                <div class="carousel-item <?= $loopIndex++ === 0 ? 'active' : '' ?>">
                    <div class="d-flex justify-content-center align-items-center" style="height: 300px; background: url('<?= htmlspecialchars($data['image']) ?>') no-repeat center center; background-size: cover;">
                        <div class="carousel-caption d-none d-md-block">
                            <h5 style="background: rgba(0, 0, 0, 0.6); color: white; padding: 5px 10px;"><?= htmlspecialchars($category) ?></h5>
                            <a href="<?= htmlspecialchars($data['link']) ?>" class="btn btn-primary">Explore <?= htmlspecialchars($category) ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Categorii principale -->
    <div class="row mt-4">
        <?php foreach ($categories as $category => $data): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= htmlspecialchars($category) ?></h5>
                        <p class="card-text">Discover articles and news in <?= htmlspecialchars($category) ?>.</p>
                        <a href="<?= htmlspecialchars($data['link']) ?>" class="btn btn-dark">Explore</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Sectiune de articole favorite ( doar pentru userii logati ) -->
        <?php if (!empty($favorites)): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Favorites</h5>
                        <p class="card-text">View your saved articles in one place.</p>
                        <a href="/pages/favorites" class="btn btn-dark">Explore</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Secțiune Despre Noi -->
    <div class="mt-5 p-4 bg-light">
        <h3 class="text-center">About Echo News</h3>
        <p class="text-center">
            Echo News is your one-stop destination for the latest updates, trends, and insights across multiple categories.
            Join our community and stay informed!
        </p>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
