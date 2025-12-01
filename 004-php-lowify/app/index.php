<?php
// files included
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

$host = "mysql";
$dbname = "lowify";
$username = "lowify";
$password = "lowifypassword";

$db = null;

$top5Artists = [];
$top5ArtistsAsHTML = "";
$top5RecentAlbums = [];
$top5RecentAlbumsAsHTML = "";
$top5NotationAlbums = [];
$top5NotationAlbumsAsHTML = "";
$allAlbumsForSearch = [];
$allArtistsForSearch = [];
$allSongsForSearch = [];
$allNamesForSearchAsHTML = "";

/**
 * Initialize the data base
 *
 * @param string $host name of the host
 * @param string $dbname name of the data base
 * @param string $username name of the user
 * @param string $password password of the data base
 **/
try {
    // check if the connexion is ok
    $db = new DatabaseManager(
        dsn: "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        username: $username,
        password: $password
    );
} catch (PDOException $ex) {
    echo "Erreur lors de la connexion à la base de données: " . $ex->getMessage();
    exit;
}

/**
 * Query top 5 artist most listened information
 *
 * This query retrieves the artist name, its cover, id,
 * and order by monthly listeners.
 **/
try {
    $top5Artists = $db->executeQuery(<<<SQL
    SELECT
        name,
        id,
        cover,
        monthly_listeners
    FROM artist
    ORDER BY monthly_listeners DESC
    LIMIT 5
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each top 5 artist
foreach ($top5Artists as $artist) {
    $artistId = $artist['id'];
    $artistName = $artist['name'];
    $artistCover = $artist['cover'];

    $top5ArtistsAsHTML .= <<<HTML
        <div class="card-item artist">
            <a href="artist.php?id=$artistId" title="$artistName - Détails de l'artiste">
                <img src="$artistCover" alt="Photo de l'artiste: $artistName">
                <h5>$artistName</h5>
            </a>
        </div>
    HTML;
}

/**
 * Query top 5 album most recent
 *
 * This query retrieves the album name, its cover, its release date,
 * the name of his artist, and order by release date.
 **/
try {
    $top5RecentAlbums = $db->executeQuery(<<<SQL
    SELECT
        album.name AS album_name,
        album.id AS album_id,
        album.cover AS album_cover,
        album.release_date AS album_release_date,
        artist.name AS artist_name,
        artist.id AS artist_id
    FROM album
    INNER JOIN artist ON album.artist_id = artist.id
    ORDER BY album.release_date DESC
    LIMIT 5
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each album of the top 5
foreach ($top5RecentAlbums as $album) {
    $albumId = $album['album_id'];
    $albumName = $album['album_name'];
    $albumCover = $album['album_cover'];
    $artistName = $album['artist_name'];
    $artistId = $album['artist_id'];

    $top5RecentAlbumsAsHTML .= <<<HTML
        <div class="card-item album">
            <a href="album.php?id=$albumId" title="$albumName - Détails de l'album">
                <img src="$albumCover" alt="Pochette de l'album: $albumName">
                <h5>$albumName</h5>
                <p><a href="artist.php?id=$artistId"  title="$artistName - Détails de l'artiste">$artistName</a></p>
            </a>
        </div>
    HTML;
}

/**
 * Query top 5 album with best notes
 *
 * This query retrieves the album name, its cover, its release date,
 * the name of his artist, and order by notes descendent.
 **/
try {
    $top5NotationAlbums = $db->executeQuery(<<<SQL
    SELECT
        album.name AS album_name,
        album.id AS album_id,
        album.cover AS album_cover,
        album.release_date AS album_release_date,
        artist.name AS artist_name,
        artist.id AS artist_id,
        AVG(song.note) AS song_avg_note
    FROM album
    INNER JOIN artist ON album.artist_id = artist.id
    INNER JOIN song ON album.id = song.album_id
    GROUP BY album.id, album.name, album.cover, album.release_date, artist.name, artist.id
    ORDER BY song_avg_note DESC
    LIMIT 5
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each album of the top 5 notation
foreach ($top5NotationAlbums as $album) {
    $albumId = $album['album_id'];
    $albumName = $album['album_name'];
    $albumCover = $album['album_cover'];
    $artistName = $album['artist_name'];
    $artistId = $album['artist_id'];

    $top5NotationAlbumsAsHTML .= <<<HTML
        <div class="card-item album">
            <a href="album.php?id=$albumId"  title="$albumName - Détails de l'album">
                <img src="$albumCover" alt="Pochette de l'album: $albumName">
                <h5>$albumName</h5>
                <p><a href="artist.php?id=$artistId" title="$artistName - Détails de l'artiste">$artistName</a></p>
            </a>
        </div>
    HTML;
}

/**
 * Querys every artist, album, song to auto-complete search
 *
 * This query retrieves albums name, artists name, and songs name
 **/
try {
    $allArtistsForSearch = $db->executeQuery(<<<SQL
    SELECT
        name AS artist_name
    FROM artist
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each album of the top 5 notation
foreach ($allArtistsForSearch as $artist) {
    $artistName = $artist['artist_name'];

    $allNamesForSearchAsHTML .= <<<HTML
        <option value="$artistName">
    HTML;
}

try {
    $allAlbumsForSearch = $db->executeQuery(<<<SQL
    SELECT
        name AS album_name
    FROM album
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each album of the top 5 notation
foreach ($allAlbumsForSearch as $album) {
    $albumName = $album['album_name'];

    $allNamesForSearchAsHTML .= <<<HTML
        <option value="$albumName">
    HTML;
}

try {
    $allSongsForSearch = $db->executeQuery(<<<SQL
    SELECT
        name AS song_name
    FROM song
SQL);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each album of the top 5 notation
foreach ($allSongsForSearch as $song) {
    $songName = $song['song_name'];

    $allNamesForSearchAsHTML .= <<<HTML
        <option value="$songName">
    HTML;
}

// final HTML structure of the page
$html = <<< HTML
<div class="page-container">
    <h1>Lowify</h1>

    <div class="search-section">
        <form action="search.php" method="POST" class="search-form">
            <input type="search" id="site-search" name="search" list="suggestions" placeholder="Artistes, chansons ou albums..." />
            <datalist id="suggestions">
                $allNamesForSearchAsHTML
            </datalist>
            <button type="submit">Rechercher</button>
        </form>
    </div>
    
    <div class="content-section">
        <h2>Top 5 des artistes les plus populaires</h2>
        <div class="card-grid">
            $top5ArtistsAsHTML
        </div>
        <p class="view-all-link">
            <a href="artists.php" class="button primary-button" title="Voir tous les artistes">➡Voir tout les artistes !</a>
        </p>
    </div>
    
    <div class="content-section">
        <h2>Top 5 des albums les plus récents</h2>
        <div class="card-grid">
            $top5RecentAlbumsAsHTML
        </div>
    </div>
    
    <div class="content-section">
        <h2>Top 5 des albums les mieux notés</h2>
        <div class="card-grid">
            $top5NotationAlbumsAsHTML
        </div>
    </div>
</div>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify"))
    ->addContent($html)
    ->addHead('<meta charset="utf-8">')
    ->addHead('<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">')
    ->addStylesheet("inc/style.css")
    ->render();