<?php

// files included
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';
require_once 'inc/utils.inc.php';

$host = "mysql";
$dbname = "lowify";
$username = "lowify";
$password = "lowifypassword";

$db = null;

$search = $_POST["search"] ?? '';
$searchLike = "%". $search . "%";

$artistsFound = [];
$artistsFoundAsHTML = "";
$albumsFound = [];
$albumsFoundAsHTML = "";
$songsFound = [];
$songsFoundAsHTML = "";

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
 * Query artists corresponding to the search
 *
 * This query retrieves the artist name, its cover
 * if he is corresponding to the search.
 **/
try {
    $artistsFound = $db->executeQuery(<<<SQL
    SELECT
        name,
        id,
        cover
    FROM artist
    WHERE (
        MATCH(name) AGAINST(:search IN NATURAL LANGUAGE MODE) OR
        name LIKE :searchLike
    )
SQL, ["search" => $search, "searchLike" => $searchLike]);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// Return message if no artist founded
// or generating HTML for each artist
if (sizeof($artistsFound) == 0) {
    $artistsFoundAsHTML .= <<<HTML
        <p class="no-result">Aucun artiste ne correspond à votre recherche.</p>
    HTML;
} else {
    foreach ($artistsFound as $artist) {
        $artistId = $artist['id'];
        $artistName = $artist['name'];
        $artistCover = $artist['cover'];

        $artistsFoundAsHTML .= <<<HTML
            <div class="card-item artist">
                <a href="artist.php?id=$artistId" title="$artistName - Détails de l'artiste">
                    <img src="$artistCover" alt="Photo de l'artiste: $artistName">
                    <h5>$artistName</h5>
                </a>
            </div>
        HTML;
    }
}

/**
 * Query albums corresponding to the search
 *
 * This query retrieves the album name, its cover, the release date,
 * the name of its artist,
 * if it is corresponding to the search.
 **/
try {
    $albumsFound = $db->executeQuery(<<<SQL
    SELECT 
        album.name AS album_name,
        album.id AS album_id,
        album.artist_id AS artist_id,
        album.cover AS album_cover,
        album.release_date AS album_release_date,
        artist.name AS artist_name
    FROM album
    INNER JOIN artist ON album.artist_id = artist.id
    WHERE (
        MATCH(album.name) AGAINST(:search IN NATURAL LANGUAGE MODE) OR
        album.name LIKE :searchLike
    )
SQL, ["search" => $search, "searchLike" => $searchLike]);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// Return message if no album founded
// or generating HTML for each album
if (sizeof($albumsFound) == 0) {
    $albumsFoundAsHTML .= <<<HTML
        <p class="no-result">Aucun album ne correspond à votre recherche.</p>
    HTML;
} else {
    foreach ($albumsFound as $album) {
        $albumId = $album['album_id'];
        $albumName = $album['album_name'];
        $albumCover = $album['album_cover'];
        $albumReleaseDate = $album['album_release_date'];
        $artistName = $album['artist_name'];
        $artistId = $album['artist_id'];

        $albumReleaseDateInDMY = dateInDMY($albumReleaseDate);

        $albumsFoundAsHTML .= <<<HTML
        <div class="card-item album">
            <a href="album.php?id=$albumId" title="$albumName - Détails de l'album">
                <img src="$albumCover" alt="Pochette de l'album: $albumName">
                <h5>$albumName</h5>
                <p><a href="artist.php?id=$artistId" title="$artistName - Détails de l'artiste">$artistName</a></p>
                <p>$albumReleaseDateInDMY</p>
            </a>
        </div>
        HTML;
    }
}

/**
 * Query songs corresponding to the search
 *
 * This query retrieves the song name, duration, note, its album name,
 * its artist name,
 * if it is corresponding to the search.
 **/
try {
    $songsFound = $db->executeQuery(<<<SQL
    SELECT 
        song.name AS song_name,
        song.duration AS song_duration,
        song.note AS song_note,
        song.id AS song_id,
        album.name AS album_name,
        album.id AS album_id,
        artist.name AS artist_name,
        artist.id AS artist_id
    FROM song
    INNER JOIN album ON song.album_id = album.id
    INNER JOIN artist ON song.artist_id = artist.id
    WHERE (
        MATCH(song.name) AGAINST(:search IN NATURAL LANGUAGE MODE) OR
        song.name LIKE :searchLike
    )
SQL, ["search" => $search, "searchLike" => $searchLike]);
} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// Return message if no song founded
// or generating HTML for each song
if (sizeof($songsFound) == 0) {
    $songsFoundAsHTML .= <<<HTML
        <p class="no-result">Aucune chanson ne correspond à votre recherche.</p>
    HTML;
} else {
    $songsFoundAsHTML .= <<<HTML
        <div class="track-list track-list-search">
    HTML;

    foreach ($songsFound as $song) {
        $songName = $song['song_name'];
        $songDuration = $song['song_duration'];
        $songNote = $song['song_note'];
        $songId = $song['song_id'];
        $albumName = $song['album_name'];
        $albumId = $song['album_id'];
        $artistName = $song['artist_name'];
        $artistId = $song['artist_id'];

        $songDurationInMMSS = timeInMMSS($songDuration);
        $songNoteFormatted = noteFormatted($songNote);


        $songsFoundAsHTML .= <<<HTML
        <div class="track-item track-item-album">
            <div class="track-info">
                <div class="track-text-info">
                    <span class="track-name">$songName</span>
                    <span class="track-artist">
                        <a href="artist.php?id=$artistId" title="$artistName - Détails de l'artiste">$artistName</a>
                        <span class="meta-separator"> • </span>
                        <a href="album.php?id=$albumId" title="$albumName - Détails de l'album">$albumName</a>
                    </span>
                </div>
            </div>
            <div class="track-details">
                <span class="track-duration">$songDurationInMMSS</span>
                <span class="track-note-small">Note: $songNoteFormatted</span>
            </div>
        </div>
        HTML;
    }
    $songsFoundAsHTML .= <<<HTML
        </div>
    HTML;
}

// final HTML structure of the page
$html = <<< HTML
<div class="page-container">
    <a href="index.php" class="back-link" title="Retour à l'accueil">← Retour à l'accueil</a>
    
    <h1 class="search-title">Résultats pour : "$search"</h1>

    <div class="content-section">
        <h2>Artistes</h2>
        <div class="card-grid">
            $artistsFoundAsHTML
        </div>
    </div>
    
    <div class="content-section">
        <h2>Albums</h2>
        <div class="card-grid">
            $albumsFoundAsHTML
        </div>
    </div>
    
    <div class="content-section">
        <h2>Chansons</h2>
        $songsFoundAsHTML
    </div>
</div>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify - Recherche"))
    ->addContent($html)
    ->addHead('<meta charset="utf-8">')
    ->addHead('<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">')
    ->addStylesheet("inc/style.css")
    ->render();