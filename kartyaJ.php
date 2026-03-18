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

if (!isset($_SESSION['pakli_jatekos']) || isset($_GET['uj_jatek'])) {
    session_unset();
    $_SESSION['ertekek'] = $kartya_adatok;
    $alap = array_keys($kartya_adatok);
    $p1 = $alap; $p2 = $alap;
    shuffle($p1); shuffle($p2);

    $_SESSION['pakli_jatekos'] = $p1;
    $_SESSION['pakli_gep'] = $p2;
    $_SESSION['sajat_torony'] = array_splice($_SESSION['pakli_jatekos'], 0, 5);
    $_SESSION['kez'] = array_splice($_SESSION['pakli_jatekos'], 0, 5);
    $_SESSION['ellenseg_torony'] = array_splice($_SESSION['pakli_gep'], 0, 5);
    $_SESSION['gep_keze'] = array_splice($_SESSION['pakli_gep'], 0, 5);
    
    $_SESSION['felfedett_ellenseg'] = array_fill(0, 5, false);
    $_SESSION['felfedett_sajat'] = array_fill(0, 5, true);
    $_SESSION['kor'] = 'jatekos';
    $_SESSION['jatek_vege'] = false;
    $_SESSION['uzenet'] = "Mindkét fél a teljes 40 lapos készlettel indul!";
}


if (isset($_GET['tamadas']) && $_SESSION['kor'] == 'jatekos' && !$_SESSION['jatek_vege']) {
    $sajat_nev = $_GET['sajat_nev'];
    $t_idx = (int)$_GET['torony_index'];
    $k_idx = array_search($sajat_nev, $_SESSION['kez']);


    if ($k_idx !== false && $_SESSION['felfedett_ellenseg'][$t_idx] === false) {
        $cel_nev = $_SESSION['ellenseg_torony'][$t_idx];
        if ($_SESSION['ertekek'][$sajat_nev] >= $_SESSION['ertekek'][$cel_nev]) {
            $_SESSION['ellenseg_torony'][$t_idx] = $sajat_nev;
            $_SESSION['felfedett_ellenseg'][$t_idx] = true;
            $_SESSION['uzenet'] = "SIKER! Elfoglaltad a bástyát.";
        } else {
            $_SESSION['uzenet'] = "VESZTESÉG! Nem volt elég erőd.";
        }
        unset($_SESSION['kez'][$k_idx]);
        if (count($_SESSION['pakli_jatekos']) > 0) {
            $_SESSION['kez'][] = array_shift($_SESSION['pakli_jatekos']);
        } else {
            $_SESSION['jatek_vege'] = true;
            $_SESSION['uzenet'] = "Elfogyott a paklid! VESZTETTÉL!";
        }
        $_SESSION['kez'] = array_values($_SESSION['kez']);
        if(!$_SESSION['jatek_vege']) $_SESSION['kor'] = 'gep';
    }
}


if (isset($_GET['passz']) && $_SESSION['kor'] == 'jatekos' && !$_SESSION['jatek_vege']) {
    if (count($_SESSION['pakli_jatekos']) > 0) {
        $_SESSION['kez'][] = array_shift($_SESSION['pakli_jatekos']);
        $_SESSION['uzenet'] = "Passzoltál és húztál.";
        $_SESSION['kor'] = 'gep';
    } else {
        $_SESSION['jatek_vege'] = true;
        $_SESSION['uzenet'] = "Elfogyott a paklid! VESZTETTÉL!";
    }
}


if ($_SESSION['kor'] == 'gep' && !$_SESSION['jatek_vege']) {
    $gep_k_idx = array_rand($_SESSION['gep_keze']);
    $gep_kartya = $_SESSION['gep_keze'][$gep_k_idx];
    
   
    $t_idx = rand(0, 4);
    
    if ($_SESSION['ertekek'][$gep_kartya] >= $_SESSION['ertekek'][$_SESSION['sajat_torony'][$t_idx]]) {
        $_SESSION['sajat_torony'][$t_idx] = $gep_kartya;
        $_SESSION['felfedett_sajat'][$t_idx] = false; 
        $_SESSION['uzenet'] .= " <br>Az ellenfél elfoglalta a bástyádat!";
    }
    unset($_SESSION['gep_keze'][$gep_k_idx]);
    if (count($_SESSION['pakli_gep']) > 0) {
        $_SESSION['gep_keze'][] = array_shift($_SESSION['pakli_gep']);
    } else {
        $_SESSION['jatek_vege'] = true;
        $_SESSION['uzenet'] = "Az ellenfél paklija kifogyott! NYERTÉL!";
    }
    $_SESSION['gep_keze'] = array_values($_SESSION['gep_keze']);
    if(!$_SESSION['jatek_vege']) $_SESSION['kor'] = 'jatekos';
}


if (!in_array(false, $_SESSION['felfedett_ellenseg'])) {
    $_SESSION['jatek_vege'] = true;
    $_SESSION['uzenet'] = "Gratulálok! Elfoglaltad az összes tornyot, NYERTÉL!";
}
if (!in_array(true, $_SESSION['felfedett_sajat'])) {
    $_SESSION['jatek_vege'] = true;
    $_SESSION['uzenet'] = "Az ellenfél elfoglalta az összes tornyodat, VESZTETTÉL!";
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>CaTower Duel</title>
    <link rel="stylesheet" href="kartya.css">
</head>
<body class="asztal">

    <h1>CaTower Duel</h1>
    <p class="info"><?php echo $_SESSION['uzenet']; ?></p>

    <div class="torony-resz">
        <h3>ELLENSÉG BÁSTYÁI (Pakli: <?php echo count($_SESSION['pakli_gep']); ?>)</h3>
        <?php foreach ($_SESSION['ellenseg_torony'] as $i => $nev): ?>
            <div class="kartya-blokk">
                <?php $img = $_SESSION['felfedett_ellenseg'][$i] ? $nev : "borito"; ?>
                <div class="kartya" style="background-image: url('kepek/<?php echo $img; ?>.png'); border: 2px solid #ff4d4d;"></div>
                <br><span><?php echo $i + 1; ?>. bástya</span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="torony-resz">
        <h3>SAJÁT BÁSTYÁID </h3>
        <?php foreach ($_SESSION['sajat_torony'] as $i => $nev): ?>
            <div class="kartya-blokk">
                <?php $img = $_SESSION['felfedett_sajat'][$i] ? $nev : "borito"; ?>
                <div class="kartya" style="background-image: url('kepek/<?php echo $img; ?>.png'); border: 2px solid #2ecc71;"></div>
                <br><span><?php echo $i + 1; ?>. bástya <?php if($_SESSION['felfedett_sajat'][$i]) echo "(". $_SESSION['ertekek'][$nev] .")"; ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <hr>

    <?php if (!$_SESSION['jatek_vege']): ?>
    <div class="kez-resz">
        <h3>A TE KEZED (Pakli: <?php echo count($_SESSION['pakli_jatekos']); ?>)</h3>
        <?php if ($_SESSION['kor'] == 'jatekos'): ?>
            <?php foreach ($_SESSION['kez'] as $nev): ?>
                <form method="GET" class="kartya-blokk">
                    <div class="kartya" style="background-image: url('kepek/<?php echo $nev; ?>.png');"></div>
                    <br>
                    <select name="torony_index">
                        <?php foreach ($_SESSION['felfedett_ellenseg'] as $idx => $felfedve): ?>
                            <?php if (!$felfedve): ?>
                                <option value="<?php echo $idx; ?>"><?php echo $idx + 1; ?>. bástya</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="sajat_nev" value="<?php echo $nev; ?>">
                    <button type="submit" name="tamadas" value="1" style="margin-top:5px;">TÁMADÁS</button>
                </form>
            <?php endforeach; ?>
            <br>
            <form method="GET"><button type="submit" name="passz" value="1">PASSZ / HÚZÁS</button></form>
        <?php else: ?>
            <p><strong>Az ellenfél lép...</strong></p>
            <meta http-equiv="refresh" content="2">
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <br><a href="?uj_jatek=1" style="color: #075a8a;">[ ÚJ JÁTÉK ]</a>

</body>
</html>
