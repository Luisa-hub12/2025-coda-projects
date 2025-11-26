<?php
session_start(); // Démarrage de la session pour stocker les scores

// ----------------------
// 1. Initialisation des scores si non existants
// ----------------------
if (!isset($_SESSION['playerScore'])) {
    $_SESSION['playerScore'] = 0;
}
if (!isset($_SESSION['BOTScore'])) {
    $_SESSION['BOTScore'] = 0;
}

// ----------------------
// 2. Récupération du choix du joueur
// ----------------------
$playerChoice = $_GET['player'] ?? null;

// ----------------------
// 3. Choix possible pour PHP
// ----------------------
$choices = ["pierre", "feuille", "ciseaux"];
$BOTChoice = null;
$result = null;
$lastWinner = null; // Pour indiquer qui a gagné le dernier tour

// ----------------------
// 4. Gestion du jeu si le joueur a fait un choix
// ----------------------
if ($playerChoice !== null) {
    // Choix aléatoire pour PHP
    $BOTChoice = $choices[array_rand($choices)];

    // Détermination du résultat
    if ($playerChoice === $BOTChoice) {
        $result = "Égalité";
        $lastWinner = "Égalite";
    } elseif (
        ($playerChoice === "pierre" && $BOTChoice === "ciseaux") ||
        ($playerChoice === "feuille" && $BOTChoice === "pierre") ||
        ($playerChoice === "ciseaux" && $BOTChoice === "feuille")
    ) {
        $result = "Gagné";
        $_SESSION['playerScore']++;
        $lastWinner = "player";
    } else {
        $result = "Perdu";
        $_SESSION['BOTScore']++;
        $lastWinner = "BOT";
    }
}

// ----------------------
// 5. Préparation des variables pour affichage
// ----------------------
$displayPlayer = empty($playerChoice) ? "Aucun choix" : $playerChoice;
$displayPhp = empty($BOTChoice) ? "En attente" : $BOTChoice;
$displayResult = empty($result) ? "En attente de votre choix" : $result;
$playerScore = $_SESSION['playerScore'];
$BOTScore = $_SESSION['BOTScore'];

// ----------------------
// 6. HTML avec heredoc
// ----------------------
$html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Jeu Pierre, Feuille, Ciseaux</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .winner { font-weight: bold; color: green; animation: pulse 2s; }
        .loser { color: red; }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(2); }
        }
    </style>
</head>


<div class="container py-5">
    <h1 class="text-center mb-4">Jeu Pierre, Feuille, Ciseaux</h1>
<style>
h1 { 
color: darkcyan;
 }
body { background-color: cyan; }
</style>
    <div class="row mb-4">
        <div class="col text-center">
            <h4>Choix du joueur :</h4>
            <p class="fs-4">$displayPlayer</p>
        </div>
        <div class="col text-center">
            <h4>Choix du BOT :</h4>
            <p class="fs-4">$displayPhp</p>
        </div>
    </div>

    <div class="text-center mb-4">
        <h3>Résultat :</h3>
        <p class="fs-3 fw-bold">$displayResult</p>
    </div>

    <div class="text-center mb-4">
        <a href="?player=pierre" class="btn btn-primary mx-2">Pierre</a>
        <a href="?player=feuille" class="btn btn-success mx-2">Feuille</a>
        <a href="?player=ciseaux" class="btn btn-danger mx-2">Ciseaux</a>
    </div>

    <div class="text-center mb-4">
        <h4>Score :</h4>
        <p class="fs-5">
HTML;

// Appliquer des classes selon le gagnant du dernier tour
if ($lastWinner === "Player") {
    $html .= "<span class='Winner'>Joueur: $playerScore</span> | <span class='Loser'>BOT: $BOTScore</span>";
} elseif ($lastWinner === "BOT") {
    $html .= "<span class='Loser'>Joueur: $playerScore</span> | <span class='Winner'>BOT: $BOTScore</span>";
} else {
    $html .= "Joueur: $playerScore | BOT: $BOTScore";
}

$html .= <<<HTML
        </p>
    </div>

    <div class="text-center">
        <a href="?reset=1" class="btn btn-secondary">Réinitialiser</a>
    </div>
</div>

</body>
</html>
HTML;

// ----------------------
// 7. Réinitialisation si demandé
// ----------------------
if (isset($_GET['reset'])) {
    $_SESSION['playerScore'] = 0;
    $_SESSION['BOTScore'] = 0;
    header("Location: ?"); // Redirection pour éviter d'envoyer à nouveau le paramètre reset
    exit;
}

// ----------------------
// 8. Affichage final
// ----------------------
echo $html;
?>
