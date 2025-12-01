<?php

// files included
require_once 'inc/page.inc.php';

$message = $_GET["message"] ?? "Erreur inconnue";

// final HTML structure of the page
$html = <<< HTML
<div class="page-container error-page-content">
    <h1 class="error-title">$message</h1>
    
    <div class="error-details">
        <p>Votre recherche n'existe pas, veuillez rééssayer !</p>
        <a href="index.php" class="button primary-button large-button" title="Retour à l'accueil">⬅ACCUEIL !</a>
    </div>
</div>
<style>
.error-page-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 80vh;
    text-align: center;
}

.error-title {
    font-size: 3em;
    color: red;
    margin-bottom: 20px;
}

.error-details p {
    font-size: 1.2em;
    color: black;
    margin-bottom: 30px;
}

.large-button {
    padding: 15px 30px;
    font-size: 1.1em;
}
</style>
HTML;

// displaying the page using HTMLPage class
echo (new HTMLPage(title: "Lowify - $message"))
    ->addContent($html)
    ->addHead('<meta charset="utf-8">')
    ->addHead('<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">')
    ->render();