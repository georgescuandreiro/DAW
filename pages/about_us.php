<?php
session_start();
require_once "../database/db_connect.php";

// Include header și navbar
include "../includes/header.php";
include "../includes/navbar.php";

// Funcția pentru a parsa tabelul specific de pe Wikipedia
function parseSpecificTable($url)
{
    if (!ini_get('allow_url_fopen')) {
        return "Eroare: allow_url_fopen este dezactivat pe server.";
    }

    $html = file_get_contents($url);

    if (!$html) {
        return "Eroare: Nu s-a putut accesa pagina.";
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//table[contains(@class, 'infobox') and contains(@class, 'ib-company') and contains(@class, 'vcard')]");

    if ($nodes->length > 0) {
        $tableContent = '';
        foreach ($nodes as $node) {
            $tableContent .= $dom->saveHTML($node);
        }
        return $tableContent;
    } else {
        return "Tabelul specific nu a fost găsit.";
    }
}

// URL-ul pentru pagina Wikipedia
$wikiUrl = "https://en.wikipedia.org/wiki/BBC_News";

// Obține tabelul
$wikiTable = parseSpecificTable($wikiUrl);
?>

<div class="container mt-5">
    <!-- Titlu principal -->
    <h1 class="mb-4 text-center" style="font-family: 'Times New Roman', serif;">About Us</h1>
    <p class="text-center" style="font-style: italic;">Discover who we are and our mission to bring the world closer to you.</p>

    <!-- Secțiune cu informații generale -->
    <div class="about-us-section my-5 p-4" style="background-color: #f8f8f8; border: 1px solid #ccc; border-radius: 8px;">
        <h2 class="mb-3" style="font-family: 'Georgia', serif; text-transform: uppercase;">Our Mission</h2>
        <p>
            Welcome to EchoNewsMagazine, your trusted source for the latest updates in world news, technology, lifestyle, and culture. 
            We aim to provide accurate, reliable, and timely information to keep you informed about the issues that matter most.
        </p>
        <p>
            Our mission is to bring people closer to the world by offering engaging, diverse, and high-quality content. Whether it’s 
            breaking news, the latest innovations in technology, or cultural insights, we’ve got you covered.
        </p>
    </div>

    <!-- Secțiune cu sursele (Tabelul preluat din Wikipedia) -->
    <div class="sources-section my-5 p-4" style="background-color: #f8f8f8; border: 1px solid #ccc; border-radius: 8px;">
        <h2 class="mb-3" style="font-family: 'Georgia', serif; text-transform: uppercase;">Sources</h2>
        <div class="wiki-table">
            <?= $wikiTable ?>
        </div>
    </div>

    <!-- Secțiune cu echipa -->
    <div class="team-section my-5">
        <h2 class="mb-3 text-center" style="font-family: 'Georgia', serif; text-transform: uppercase;">Meet the Team</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Membru/i echipă -->
            <div class="col">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Georgescu Andrei</h5>
                        <p class="card-text">Founder & Editor-in-Chief</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secțiune cu valorile și obiectivele -->
    <div class="values-section my-5 p-4" style="background-color: #f8f8f8; border: 1px solid #ccc; border-radius: 8px;">
        <h2 class="mb-3" style="font-family: 'Georgia', serif; text-transform: uppercase;">Our Values</h2>
        <ul>
            <li><strong>Integrity:</strong> We are committed to providing unbiased and accurate news.</li>
            <li><strong>Innovation:</strong> We embrace technology to bring you the latest updates efficiently.</li>
            <li><strong>Diversity:</strong> We celebrate diverse perspectives and stories from around the world.</li>
        </ul>
    </div>

    <!-- Secțiune cu CTA pentru utilizatori -->
    <div class="cta-section text-center my-5">
        <h3>Stay Connected with Us</h3>
        <p>Want to stay updated? <a href="newsletter.php">Subscribe to our newsletter</a> for the latest news and insights!</p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
