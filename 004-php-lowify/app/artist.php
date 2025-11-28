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

    $Artists = [];

// c'est une opération dangereuse, donc on utilise try/catch
// et on affiche le message d'erreur si une erreur survient
    try {
        // version en une ligne
        $Artists = $db->executeQuery("SELECT id, name, cover FROM artist");
$Artists = $db ->executeQuery(<<<SQL
-- détail de l'artiste
        SELECT * FROM artist WHERE id = :artistId

-- top 5 des chansons
        SELECT 
            s.id as song_id,
            s.name as song_name, 
            s.duration as song_duration, 
            s.note as song_note, 
            a.cover as album_cover, 
            a.name as album_name
        FROM songs
        INNER JOIN album  a ON s.album_id = a.id
        WHERE s.artist_id = :artist_id
        ORDER BY s.note DESC
            LIMIT 5

-- albums
        SELECT 
        *
        FROM album
        WHERE artist_id = :artistId
        ORDER BY release_date DESC
SQL);


    /**
     * Transforms a string date+time into a formatted string date+time
     *
     * @param string $date The date to transform
     * @return string The formatted date.
     * @see https://www.php.net/manual/en/datetime.format.php
     **/
        function format_date(string $date): ?string
        {
            try {
                $dateTimeObject = new DateTime($date);
                return $dateTimeObject->format("d/m/Y");
            } catch (Exception $e) {
                return null; // ou return $date; ou autre
            }
        }
