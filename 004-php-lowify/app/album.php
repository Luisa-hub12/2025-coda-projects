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
$idAlbum = $_GET["id"];
$error = "error.php?message=Album inconnu";

$albumInfos = [];
$albumInfoAsHTML = "";
$songsOfAlbum = [];
$songsOfAlbumAsHTML = "";

/**
 * Initialize the data base
 *
 * @param string $host name of the host
 * @param string $dbname name of the data base
 * @param string $username name of the user
 * @param string $password password of the data base
 **/
try {
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
 * Query album information with the corresponding artist
 *
 * This query retrieves the album name, its cover, release date,
 * and the artist associated.
 **/
try {
    $albumInfos = $db->executeQuery(<<<SQL
    SELECT 
        album.name AS album_name,
        album.artist_id AS artist_id,
        album.cover AS album_cover,
        album.release_date AS album_release_date,
        artist.name AS artist_name
    FROM album
    INNER JOIN artist ON album.artist_id = artist.id
    WHERE album.id = :idAlbum
    SQL, ["idAlbum" => $idAlbum]);

    // redirection to error page if album doesn't exist
    if (sizeof($albumInfos) == 0) {
        header("Location: $error");
        exit;
    }

} catch (PDOException $ex) {
    $error = "error.php?message=Album inconnu";
    header("Location: $error");
    exit;
}

// converting the result into a simple array
$albumInfosInArray = $albumInfos[0];

// storing album information in variables
$albumName = $albumInfosInArray['album_name'];
$albumCover = $albumInfosInArray['album_cover'];
$albumReleaseDate = $albumInfosInArray['album_release_date'];
$artistId = $albumInfosInArray['artist_id'];
$artistName = $albumInfosInArray['artist_name'];

// formatting release date as DD/MM/YYYY
$albumReleaseDateInDMY = dateInDMY($albumReleaseDate);

// generating the HTML block containing album information
$albumInfoAsHTML = <<<HTML
    <header class="album-header">
        <img src="$albumCover" alt="Pochette de l'album: $albumName" class="album-cover-large">
        <div class="album-details">
            <p class="album-type">Album</p>
            <h1 class="album-name-title">$albumName</h1>
            <p class="album-meta">
                Par <a href="artist.php?id=$artistId" title="$artistName - Détails de l'artiste" class="artist-link-small">$artistName</a>
                <span class="meta-separator"> • </span>
                $albumReleaseDateInDMY
            </p>
        </div>
    </header>
HTML;

/**
 * Query all the songs of the current album
 *
 * This query returns each song name, duration, and note,
 * ordered by song id in ascending order.
 **/
try {
    $songsOfAlbum = $db->executeQuery(<<<SQL
    SELECT 
        song.name AS song_name,
        song.duration AS song_duration,
        song.note AS song_note,
        song.id AS song_id
    FROM song
    WHERE song.album_id = :idAlbum
    ORDER BY song.id ASC
    SQL, ["idAlbum" => $idAlbum]);

} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each song of the album
foreach ($songsOfAlbum as $song) {
    $songName = $song['song_name'];
    $songDuration = $song['song_duration'];
    $songNote = $song['song_note'];
    $songId = $song['song_id'];

    // convert duration into MM:SS format
    $songDurationInMMSS = timeInMMSS($songDuration);
    $songNoteFormatted = noteFormatted($songNote);


    $songsOfAlbumAsHTML .= <<<HTML
        <div class="track-item track-item-album">
            <div class="track-info">
                <div class="track-text-info">
                    <span class="track-name">$songName</span>
                    <span class="track-artist">$artistName</span>
                </div>
            </div>
            <div class="track-details">
                <span class="track-duration">$songDurationInMMSS</span>
                <span class="track-note-small">$songNoteFormatted</span>
            </div>
        </div>
    HTML;
}

// final HTML structure of the page
$html = <<< HTML
<div class="page-container">
    <a href="index.php" class="back-link" title="Retour à l'accueil">← Retour à l'accueil</a>
    $albumInfoAsHTML
    <div class="content-section">
        <h2>Pistes de l'album</h2>
        <div class="track-list">
            <div class="track-header-row">
                <span class="track-name-header">TITRE</span>
                <span class="track-duration-header">DURÉE</span>
                <span class="track-note-header">NOTE</span>
            </div>
            $songsOfAlbumAsHTML
        </div>
    </div>
</div>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify - $albumName"))
    ->addContent($html)
    ->addHead('<meta charset="utf-8">')
    ->addHead('<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">')
    ->addStylesheet("inc/style.css")
    ->render();