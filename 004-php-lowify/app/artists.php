<?php

// -- importation des librairies à l'aide de require_once
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

// -- initialisation de la connexion à la base de données

// c'est une opération dangereuse, donc on utilise try/catch
// et on affiche le message d'erreur si une erreur survient
try {
    $db = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: 'lowify',
        password: 'lowifypassword'
    );
} catch (PDOException $ex) {
    echo "Erreur lors de la connexion à la base de données : " . $ex->getMessage();
    exit;
}

// -- on récupère les infors de tout les artistes depuis la base de données
$allArtists = [];

// c'est une opération dangereuse, donc on utilise try/catch
// et on affiche le message d'erreur si une erreur survient
try {
    // version en une ligne
    $allArtists = $db->executeQuery("SELECT id, name, cover FROM artist");

    // version multi-ligne
    $allArtists = $db->executeQuery(<<<SQL
    SELECT 
        id,
        name,
        cover
    FROM artist
SQL);


} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de données : " . $ex->getMessage();
    exit;
}

// -- on crée une variable pour contenir le HTML qui rerpésentera la liste des artistes
$artistsAsHTML = "";

$iterator = 0;

// -- pour chaque artiste récupéré depuis la base de donnée
foreach ($allArtists as $artist) {
    // on pré-réserve des variables pour injecter le nom, l'id et la cover de l'artiste dans le HTML
    $artistName = $artist['name'];
    $artistId = $artist['id'];
    $artistCover = $artist['cover'];

    // juste pour l'affichage, pas obligé
    if ($iterator % 4 == 0) {
        $artistsAsHTML .= '<div class="row mb-4">';
    }

    // -- on ajoute une carte HTML représentant l'artiste courant
    $artistsAsHTML .= <<<HTML
            <div class="artist-container">
    <a href="artist.php?id=$artistId" class="artist-link">
            <img src="$artistCover" class="artist-img" alt="Image de $artistName">
                <h5 class="artist-name">$artistName</h5>
            </div>
        </div>
    </a>
</div>

<style>
/* Style par défaut */
.artist-container {
    float: left;
    margin: 15px;
    text-align: center;
}
@media screen and (min-width: 80rem) {
  .artist-container {
    margin: 1em 2em;
  }
}
.artist-img {
    width: 250px;
    height: 250px;
    border-radius: 50%;
    border: 2px solid #fff;
    object-fit: cover;
}
.artist-link {
    color: inherit; /* Utiliser la couleur du parent */
}


.artist-name {
    color: black;
    font-size: 1rem;
    margin-top: 10px;
}

</style>

<script>
// Fonction pour changer le style dynamiquement
function changerStyleArtist(artistId, styleSuffix) {
    const card = document.getElementById('artistCard-' + artistId);
    if (!card) return;

    card.className = 'artist-card' + styleSuffix;

    const img = card.querySelector('.artist-img');
    if (img) img.className = 'artist-img' + styleSuffix;

    const body = card.querySelector('.artist-body');
    if (body) body.className = 'artist-body' + styleSuffix;
}

// Exemple : appliquer le style alternatif après 2 secondes
// changerStyleArtist($artistId, '-alt');
</script>

HTML;

    // juste pour l'affichage, pas obligé
    if ($iterator % 4 == 3) {
        $artistsAsHTML .= '</div>';
    }

    $iterator++;
}

// -- on crée la structure HTML de notre page
// en injectant le HTML correspondant à la liste des artistes
$html = <<<HTML
<div class="container bg-dark text-white p-4">
    <a href="index.php" class="link text-white"> ACCUEIL !</a>
    <h1 class="mb-4">Artistes</h1>
    {$artistsAsHTML}
</div>
<style>
h1 {
font-size: 50px;
}
.link {
    font-size: 30px;
}
</style>

HTML;

// -- on génère et on affiche la page
// je vous conseille de faire comme ça
$page = new HTMLPage("Artistes - Lowify");
$page->addContent($html);
echo $page->render();

