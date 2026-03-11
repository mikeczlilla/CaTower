<?php
session_start();


$kartya_adatok = [
    "Pixel-Kiscica" => 1, "Kód-Kölyök" => 1, "Zajos Nyávogó" => 2, "Árnyék-Egérfogó" => 2,
    "Szoftver-Sziámi" => 2, "Bináris Bolyhos" => 3, "Adat-Alvó" => 3, "Neon-Mancs" => 3,
    "Kiber-Kandúr" => 4, "Lézer-Szem" => 4, "Tűzfal-Tigris" => 4, "Plazma-Purrogó" => 5,
    "Wifi-Vadász" => 5, "Glitch-Gereblye" => 5, "Szonár-Szőrmók" => 6, "Hologram-Hektór" => 6,
    "Kvantum-Karmoló" => 6, "Bionikus-Brit" => 7, "Szatellit-Suttyó" => 7, "Virtuális-Vadorzó" => 7,
    "Titán-Tányérnyaló" => 8, "Szerver-Szörny" => 8, "Neon-Nagyúr" => 8, "Kripto-Király" => 9,
    "Mega-Mancs" => 9, "Áramkör-Alfa" => 9, "CaTower-Bajnok" => 10, "Kibernetikus-Káosz" => 10,
    "Frissített-Foltos" => 2, "Turbo-Turbékoló" => 4, "LED-Leopárd" => 6, "Adat-Dömötör" => 1,
    "Szignál-Szafari" => 3, "Bites-Behemót" => 8, "Kódolt-Kutyaűző" => 5, "Hálózati-Házimacska" => 2,
    "Lézer-Lujza" => 7, "Fantom-Folt" => 9, "Alap-Algoritmus" => 1, "Sztenderd-Szőr" => 1
];


if (!isset($_SESSION['pakli']) || isset($_GET['uj_jatek'])) {
    if(isset($_GET['uj_jatek'])) { session_unset();}

    $_SESSION['ertekek'] = $kartya_adatok;

    $pakli = array_keys($kartya_adatok);
    shuffle($pakli); 

    $_SESSION['pakli'] = $pakli;

    $_SESSION['torony'] = array_splice($_SESSION['pakli'], 0, 5); 
    $_SESSION['felfedett_torony'] = array_fill(0, 5, false);

    $_SESSION['kez'] = array_splice($_SESSION['pakli'], 0, 5); 

    $_SESSION['uzenet'] = "Rendszer online. Sok sikert, Kapitány!";
}


if (isset($_GET['tamadas']) && isset($_GET['sajat_nev'])) {

    $sajat_nev = $_GET['sajat_nev'];
    $t_idx = (int)$_GET['torony_index'];

    $k_idx = array_search($sajat_nev, $_SESSION['kez']);

    if ($k_idx !== false) {

        $torony_nev = $_SESSION['torony'][$t_idx];
        $sajat_te = $_SESSION['ertekek'][$sajat_nev];
        $torony_te = $_SESSION['ertekek'][$torony_nev];

        if ($sajat_te > $torony_te) {
            $_SESSION['torony'][$t_idx] = $sajat_nev;
            $_SESSION['felfedett_torony'][$t_idx] = true;
            unset($_SESSION['kez'][$k_idx]);
            $_SESSION['uzenet'] = "SIKER! $sajat_nev ($sajat_te) legyőzte a bástyát.";
        } else {
            unset($_SESSION['kez'][$k_idx]); 
            $_SESSION['uzenet'] = "VESZTESÉG! $sajat_nev ($sajat_te) elbukott a védelem ($torony_te) ellen.";
        }

        
        if (count($_SESSION['pakli']) > 0) {
            $_SESSION['kez'][] = array_shift($_SESSION['pakli']);
        }
        $_SESSION['kez'] = array_values($_SESSION['kez']);
    }
}


if (isset($_GET['passz'])) {
    if (count($_SESSION['pakli']) > 0) {
        $_SESSION['kez'][] = array_shift($_SESSION['pakli']);
    }
    $_SESSION['uzenet'] = "Passzoltál és húztál egy lapot.";
}


if (!in_array(false, $_SESSION['felfedett_torony'])) {
    $_SESSION['uzenet'] = "GRATULÁLOK! Elfoglaltad az összes tornyot, nyertél!";
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>CaTower Duel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="kartya.css">
</head>
<body class="asztal">

<h1>CaTower Duel</h1>
<p class="info"><?php echo $_SESSION['uzenet']; ?></p>

<!-- TORNYOK -->
<div class="torony-resz">
    <h3>BÁSTYÁK</h3>
    <?php foreach ($_SESSION['torony'] as $i => $nev): ?>
        <div class="kartya-blokk">
            <?php $img = $_SESSION['felfedett_torony'][$i] ? $nev : "borito"; ?>
            <div class="kartya" style="background-image: url('kepek/<?php echo $img; ?>.png');"></div>
            <br><span><?php echo $i+1; ?>. bástya</span>
        </div>
    <?php endforeach; ?>
</div>

<hr>


<div class="kez-resz">
    <h3>A TE KEZED (Pakli: <?php echo count($_SESSION['pakli']); ?>)</h3>
    <?php foreach ($_SESSION['kez'] as $nev): ?>
        <form method="GET" class="kartya-blokk">
            <div class="kartya" style="background-image: url('kepek/<?php echo $nev; ?>.png');"></div>
            <br>
            <select name="torony_index">
                <option value="0">1. bástya</option>
                <option value="1">2. bástya</option>
                <option value="2">3. bástya</option>
                <option value="3">4. bástya</option>
                <option value="4">5. bástya</option>
            </select>
            <br>
            <input type="hidden" name="sajat_nev" value="<?php echo $nev; ?>">
            <button type="submit" name="tamadas" value="1" style="margin-top:5px;">TÁMADÁS</button>
        </form>
    <?php endforeach; ?>
</div>


<form method="GET">
    <button type="submit" name="passz" value="1">PASSZ / LAP HÚZÁS</button>
</form>

<br><br>
<a href="?uj_jatek=1" style="color: #075a8a;">[ ÚJ JÁTÉK INDÍTÁSA ]</a>
<br>
<br>
<a class="vissza" href="kartyaweb.html"> Vissza a főoldalra</a>
</body>
</html>