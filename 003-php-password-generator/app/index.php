<?php

// -- params
$generated = "...";
$size = $_POST['size'] ?? 12;
$useAlphaMin = $_POST['use-alpha-min'] ?? 0;
$useAlphaMaj = $_POST['use-alpha-maj'] ?? 0;
$useNum = $_POST['use-num'] ?? 0;
$useSymbols = $_POST['use-symbols'] ?? 0;

function isChecked($nameCheckbox) : string {
    $check = "";
    if ($nameCheckbox === "1") {
        $check = "checked";
    }
    return $check;
}

$isUseAlphaMinCheck = isChecked($useAlphaMin);
$isUseAlphaMajCheck = isChecked($useAlphaMaj);
$isUseNumCheck = isChecked($useNum);
$isUseSymbolsCheck = isChecked($useSymbols);
function generateSelectOptions(int $size = 12): string
{
    // on initialise une variable html vide
    $html = "";

    // utilisation de la fonction range pour générer un tableau de valeurs
    $options = range(8, 42);

    // pour chaque nombre de 8 à 42
    foreach ($options as $value) {
        $attribute = "";
        if ((int) $value == (int) $size) {
            $attribute = "selected";
        }

        // on crée une option avec l'attribut et la valeur'
        $html .= "<option $attribute value=\"$value\">$value</option>";
    }

    return $html;
}

$optionsGenerared = generateSelectOptions($size);

function takeRandom(string $subject): string {
    // on prend un index au hasard dans la chaine
    $index = random_int(0, strlen($subject) - 1);

    // en PHP, les chaines sont considérés implicitement comme des tableaux
    // on peut donc récupérer un char via son index comme suit
    $randomChar = $subject[$index];

    return $randomChar;
}

/**
 * Generates a random password based on the given parameters and ensures character type diversity.
 *
 * @param int $size The length of the password to be generated.
 * @param bool $useAlphaMin Whether to include lowercase alphabetic characters in the password.
 * @param bool $useAlphaMaj Whether to include uppercase alphabetic characters in the password.
 * @param bool $useNum Whether to include numerical characters in the password.
 * @param bool $useSymbols Whether to include special symbols in the password.
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generated = generatePassword($size, $useAlphaMin, $useAlphaMaj, $useNum, $useSymbols);
} else {
    $useAlphaMin = 1;
    $useAlphaMaj = 1;
    $useNum = 1;
    $useSymbols = 1;
}

function generatePassword(
    int $size,
    bool $useAlphaMin,
    bool $useAlphaMaj,
    bool $useNum,
    bool $useSymbols
): string {
    if ($useAlphaMin == 0 &&
        $useAlphaMaj == 0 &&
        $useNum == 0 &&
        $useSymbols == 0) {
        return "Erreur : veuillez choisir au moins un type de caractère.";
    }

    $password = "";

    $sequences = [];

    if ($useAlphaMaj == 1) {
        $sequences["maj"] = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }

    if ($useAlphaMin == 1) {
        $sequences["min"] = "abcdefghijklmnopqrstuvwxyz";
    }

    if ($useNum == 1) {
        $sequences["num"] = "0123456789";
    }

    if ($useSymbols == 1) {
        $sequences["symbols"] = "!@#$%^&*?(),.=+";
    }

    if ($useAlphaMaj == 1) {
        $password .= takeRandom($sequences["maj"]);
    }
    if ($useAlphaMin == 1) {
        $password .= takeRandom($sequences["min"]);
    }
    if ($useNum == 1) {
        $password .= takeRandom($sequences["num"]);
    }
    if ($useSymbols == 1) {
        $password .= takeRandom($sequences["symbols"]);
    }

    $limitBoucle = $size - ($useAlphaMin + $useAlphaMaj + $useNum + $useSymbols);

    // complete le password
    for($i = 0; $i < $limitBoucle; $i++) {
        $values = array_values($sequences);
        $randomSequence = $values[rand(0, count($values) - 1)];
        $password .= takeRandom($randomSequence);
    }
        $password = str_shuffle($password);

    return $password;
}


// -- render

// on génère les options du select pour la taille du mot de passe
$sizeSelectorOptions = generateSelectOptions($size);

// on voit si on doit pré-cocher les cases à cocher ou pas
$useAlphaMinChecked = $useAlphaMin == 1 ? "checked" : "";
$useAlphaMajChecked = $useAlphaMaj == 1 ? "checked" : "";
$useNumChecked = $useNum == 1 ? "checked" : "";
$useSymbolsChecked = $useSymbols == 1 ? "checked" : "pas-checked";
// on génère notre page
$page = <<< HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Générateur de mot de passe</title>
  </head>
  <body>

    <div class="container">
        <h1>Générateur de mot de passe</h1>
            <style>
            h1 {
            margin-top: 30px;
            }
</style>
        <div class="row pt-4">
            <div class="col-md-12">
                <div class="alert alert-dark" role="alert">
                  <div class="h3 mb-0 pb-0">{$generated}</div>
                </div>
            </div>
        </div>

        <div class="row pt-4">
            <div class="col-md-6">
               <h4>Paramètres</h4>

                <form method="POST" action="/">
                    <div class="form-check pb-2">
                        <label for="size" class="form-label">Taille</label>
                        <select class="form-select" aria-label="Default select example" name="size">
                            {$sizeSelectorOptions}
                        </select>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="1" id="use-alpha-min" name="use-alpha-min" {$useAlphaMinChecked}>
                      <label class="form-check-label" for="use-alpha-min">
                        Utiliser des lettres minuscules (a-z).
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="1" id="use-alpha-maj" name="use-alpha-maj" {$useAlphaMajChecked}>
                      <label class="form-check-label" for="use-alpha-maj">
                        Utiliser des lettres majuscules (A-Z).
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="1" id="use-num" name="use-num" {$useNumChecked}>
                      <label class="form-check-label" for="use-num">
                        Utiliser des chiffres (0-9).
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="1" id="use-symbols" name="use-symbols" {$useSymbolsChecked}>
                      <label class="form-check-label" for="use-symbols">
                        Utiliser des symboles (!@#$%^&*?()-_).
                      </label>
                    </div>

                    <div>
                        <button type="submit">Générer mon mot de passe !</button>
                        <style>
                        button {
                        background-color: red;
                        color: white;
                        border-radius: 10px;
                        width: 150px;
                        height: 90px;
                        margin-top: 30px;
                        margin-left: 100px;
                        }
</style>
                    </div>
                </form>
            </div>

        </div>


    </div>
  </body>
</html>
HTML;

echo $page;