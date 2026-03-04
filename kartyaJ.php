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
    "Lézer-Lujza" => 7, "Fantom-Folt" => 9, "Alap-Algoritmus-1" => 1, "Alap-Algoritmus-2" => 1,
    "Sztenderd-Szőr-1" => 1, "Sztenderd-Szőr-2" => 1
];


if (!isset($_SESSION['pakli']) || isset($_GET['uj_jatek'])) {
    if(isset($_GET['uj_jatek'])) { session_unset(); session_start(); }
    
    $_SESSION['ertekek'] = $kartya_adatok;
    $pakli = array_keys($kartya_adatok);
    shuffle($pakli);
    $_SESSION['pakli'] = $pakli;

    $_SESSION['torony'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['felfedett_torony'] = array_fill(0, 5, false);
    $_SESSION['kez'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['uzenet'] = "A CaTower Duel System online. A támadó lapod megmarad a kezedben!";
}


// Támadás logika
if (isset($_GET['tamadas']) && isset($_GET['kez_index'])) {
    $k_idx = $_GET['kez_index'];
    $t_idx = $_GET['torony_index'];

    $sajat_nev = $_SESSION['kez'][$k_idx];
    $torony_nev = $_SESSION['torony'][$t_idx];

    $sajat_te = $_SESSION['ertekek'][$sajat_nev];
    $torony_te = $_SESSION['ertekek'][$torony_nev];

    if ($sajat_te > $torony_te) {
       
        $_SESSION['torony'][$t_idx] = $sajat_nev;
        $_SESSION['felfedett_torony'][$t_idx] = true;
        $_SESSION['uzenet'] = "SIKER! $sajat_nev ($sajat_te) elfoglalta a bástyát.";
    } else {
        
        if (count($_SESSION['pakli']) > 0) {
            $_SESSION['kez'][] = array_shift($_SESSION['pakli']);
        }
        $_SESSION['uzenet'] = "HIBA! A védelem ($torony_te) túl erős volt. Erősítés érkezett a kezedbe.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaTower</title>
    <link rel="stylesheet" href="kartya.css">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaTower</title>
    <link rel="stylesheet" href="kartya.css">
</head>



<body>

    <div class="asztal">
        <h1>Torony Ostrom</h1>
        <p class="info"><?php echo $_SESSION['uzenet']; ?></p>

        <h3>Torony alapja (Célpontok)</h3>
        <?php foreach ($_SESSION['torony'] as $i => $lap): ?>
            <?php 
               
                $kep = $_SESSION['felfedett_torony'][$i] ? "kepek/$lap.png" : "kepek/borito.png"; 
            ?>
            <div class="kartya" style="background-image: url('<?php echo $kep; ?>');">
              
            </div>
        <?php endforeach; ?>

        <hr>

        <h3>A te kezed (Válassz egy lapot a támadáshoz)</h3>
        <form method="GET">
            <?php foreach ($_SESSION['kez'] as $i => $lap): ?>
                <div style="display:inline-block; text-align: center;">
                    <!-- A saját lapod képe -->
                    <div class="kartya" style="background-image: url('kepek/<?php echo $lap; ?>.png');"></div><br>
                    
                    <select name="torony_index">
                        <option value="0">1. Torony</option>
                        <option value="1">2. Torony</option>
                        <option value="2">3. Torony</option>
                        <option value="3">4. Torony</option>
                        <option value="4">5. Torony</option>
                    </select><br>
                    <button type="submit" name="tamadas" value="1">Támadás</button>
                    <input type="hidden" name="kez_index" value="<?php echo $i; ?>">
                </div>
            <?php endforeach; ?>
        </form>

        <br><br>
        <a href="?uj_jatek=1" style="color: white;">Új játék indítása</a>
        <?php if (isset($_GET['uj_jatek'])) {
            session_destroy();
            header("Location: kartyaJ.php");
        } ?>
    </div>

</body>

</html>
