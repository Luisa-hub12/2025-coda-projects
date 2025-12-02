<?php

// -- importation des librairies à l'aide de require_once
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';
require_once 'inc/utils.inc.php';

// -- initialisation de la connexion à la base de données
$host = "mysql";
$dbname = "lowify";
$username = "lowify";
$password = "lowifypassword";

$db = null;
// on demande d'id pour chauqe artiste, si l'id est inconnu, on retourne erreur.
$idArtist = $_GET["id"];
$error = "error.php?message=Artiste inconnu";
// on initialise les variables infos
$artistInfos = [];
$artistInfoAsHTML = "";
$artistTop5Songs = [];
$artistTop5SongsAsHTML = "";
$artistAlbums = [];
$artistAlbumsAsHTML = "";
// c'est une opération dangereuse, donc on utilise try/catch
// et on affiche le message d'erreur si erreur.
try {
    $db = new DatabaseManager(
        dsn: "mysql:host=mysql;dbname=$dbname;charset=utf8mb4",
        username: $username,
        password: $password
    );
} catch (PDOException $ex) {
    echo "Erreur lors de la connexion à la base de données : " . $ex->getMessage();
    exit;
}

// c'est une opération dangereuse, donc on utilise try/catch
// et on affiche le message d'erreur si une erreur survient
try {
    $artistInfos = $db->executeQuery(<<<SQL
    SELECT *
    FROM artist
    WHERE id = :idArtist
    SQL, ["idArtist" => $idArtist]);

    // redirection to error page if idArtist doesn't exist
    if (empty($artistInfos)) {
        header("Location: $error");
        exit;
    }


} catch (PDOException $ex) {
    header("Location: $error");
    exit;
}
// check that our array is only line
if (!is_array($artistInfos) || count($artistInfos) !== 1) {
    header("Location: $error");
    exit;
}

// converting the result into a simple array
$artistInfosInArray = $artistInfos[0];

// artist infos.
$artistName = $artistInfosInArray['name'];
$artistCover = $artistInfosInArray['cover'];
$artistBio = $artistInfosInArray['biography'];
$artistMonthlyListeners = $artistInfosInArray['monthly_listeners'];

// utils.inc.php -> fonction pour dire d'arrondir avec k et m .k and .M
$artistMonthlyListenersInLetter = numberWithLetter($artistMonthlyListeners);

$artistInfoAsHTML .= <<<HTML
    <header>
        <img src="$artistCover" alt="Photo de l'artiste: $artistName" class="artist-cover-large">
        <div class="artist-details">
            <h1 class="artist-name">$artistName</h1>
            <p class="monthly-listeners">
                <span class="listener-count">$artistMonthlyListenersInLetter</span>
                <span class="listener-label">d'auditeurs mensuels</span>
            </p>
            <p class="artist-bio">$artistBio</p>
        </div>
</header>
<style>
.header {
    display: flex;
    align-items: flex-end;
    gap: 30px;
    margin-bottom: 40px;
    padding: 30px 0;
}

.artist-cover-large {
    width: 250px;
    height: 250px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 4px 60px rgba(0, 0, 0, 0.5);
    align-self: center;
}

.artist-details {
    display: flex;
    flex-direction: column;
    color: black;
}
.artist-name {
    font-size: 6em;
    font-weight: 900;
    line-height: 1.1;
    margin: 0;
}

.monthly-listeners {
    font-size: 1.5em;
    color: black;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 5px;
    border-radius: 20px;
    background-color: lightpink;
    transition: background-color 0.8s;
}
.monthly-listeners:hover {
background-color: lightskyblue;
}

.artist-bio {
    color: black;
    font-style: italic;
    max-width: 800px;
    line-height: 1.5;
    border-style: solid;
    padding: 10px 50px;
    align-content: center;
    font-size: 20px;
}

}

</style>
HTML;

try {
    $artistTop5Songs = $db->executeQuery(<<<SQL
    SELECT
        song.name AS song_name,
        song.duration AS song_duration,
        song.note AS song_note,
        album.cover AS album_cover,
        album.id AS album_id,
        album.name AS album_name
    FROM song
    INNER JOIN album ON album.id = song.album_id
    WHERE song.artist_id = :idArtist
    ORDER BY song.note DESC
    LIMIT 5
SQL, ["idArtist" => $idArtist]);

} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

// generating HTML for each top 5 song
foreach ($artistTop5Songs as $song) {
    $songName = $song['song_name'];
    $songDuration = $song['song_duration'];
    $songNote = $song['song_note'];
    $albumCover = $song['album_cover'];
    $albumId = $song['album_id'];
    $albumName = $song['album_name'];

    // convert duration into MM:SS format
    $songDurationInMMSS = timeInMMSS($songDuration);



    $songNoteFormatted = noteFormatted($songNote);


    $artistTop5SongsAsHTML .= <<<HTML
        <div class="track-item">
            <div class="track-info">
                <a href="album.php?id=$albumId"  title="$albumName - Détails de l'album" class="track-link">
                    <img src="$albumCover" alt="Pochette de l'album" class="track-album-cover">
                    <span class="track-name">$songName</span>
                </a>
            </div>
            <div class="track-details">
                <span class="track-duration">$songDurationInMMSS</span>
                <span class="track-note">Note: $songNote/5</span>
            </div>
        </div>
    <style>
    .track-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    }

    .track-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-radius: 30px;
    background-color: lightpink;
    transition: background-color 0.8s;
    }

    .track-item:hover {
    background-color: lightblue;
    }

    .track-info {
    display: flex;
    align-items: center;
    gap: 15px;
    }

    .track-number {
    width: 20px;
    text-align: right;
    color: black;
    }

    .track-link {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    color: deeppink;
    }
    
    .track-link:hover {
    color: deepskyblue;
    }

    .track-album-cover {
    width: 45px;
    height: 45px;
    border-radius: 30px;
    object-fit: cover;
    }

    .track-details {
    display: flex;
    gap: 30px;
    font-size: 0.9em;
    color: black;
    }

    </style>
    HTML;
}

try {
    $artistAlbums = $db->executeQuery(<<<SQL
    SELECT
        album.id AS album_id,
        album.name AS album_name,
        album.cover AS album_cover,
        album.release_date AS album_release_date
    FROM album
    WHERE album.artist_id = :idArtist
    ORDER BY album.release_date DESC
SQL, ["idArtist" => $idArtist]);

} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de donnée : " . $ex->getMessage();
    exit;
}

foreach ($artistAlbums as $album) {
    $albumId = $album['album_id'];
    $albumName = $album['album_name'];
    $albumCover = $album['album_cover'];
    $albumReleaseDate = $album['album_release_date'];

    // DD/MM/YYYY
    $albumReleaseDateInDMY = dateInDMY($albumReleaseDate);

    // HTML block
    $artistAlbumsAsHTML .= <<<HTML
        <div class="card-item album">
            <a href="album.php?id=$albumId" title="$albumName - Détails de l'album">
                <img src="$albumCover" alt="Pochette de l'album: $albumName">
                <h5>$albumName</h5>
                <p>$albumReleaseDateInDMY</p>
            </a>
        </div>
    HTML;
}

$html = <<< HTML
<div class="page-container">
    <a href="index.php" class="back-link" title="Retour à l'accueil">⬅ ACCUEIL !</a>
    $artistInfoAsHTML
    <div class="content-section">
        <h2>Morceaux Populaires</h2>
        <div class="track-list">
            $artistTop5SongsAsHTML
        </div>
    </div>
    <div class="content-section">
        <h2>Albums</h2>
        <div class="card-grid">
            $artistAlbumsAsHTML
        </div>
    </div>
</div>
<style>

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    font-size: 20px;
    color: deeppink;
}
.back-link:hover {
color: deepskyblue;
}
.card-item {
background-color: lightpink;
}
.card-item:hover {
background-color: lightblue;
}


</style>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify - $artistName"))
    ->addContent($html)
    ->addHead('<meta charset="utf-8">')
    ->addHead('<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">')
    ->render();