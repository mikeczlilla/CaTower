<?php
session_start();

$kartya_adatok = [
    "Pixel-Kiscica" => 1,
    "Kód-Kölyök" => 1,
    "Zajos Nyávogó" => 2,
    "Árnyék-Egérfogó" => 2,
    "Szoftver-Sziámi" => 2,
    "Bináris Bolyhos" => 3,
    "Adat-Alvó" => 3,
    "Neon-Mancs" => 3,
    "Kiber-Kandúr" => 4,
    "Lézer-Szem" => 4,
    "Tűzfal-Tigris" => 4,
    "Plazma-Purrogó" => 5,
    "Wifi-Vadász" => 5,
    "Glitch-Gereblye" => 5,
    "Szonár-Szőrmók" => 6,
    "Hologram-Hektór" => 6,
    "Kvantum-Karmoló" => 6,
    "Bionikus-Brit" => 7,
    "Szatellit-Suttyó" => 7,
    "Virtuális-Vadorzó" => 7,
    "Titán-Tányérnyaló" => 8,
    "Szerver-Szörny" => 8,
    "Neon-Nagyúr" => 8,
    "Kripto-Király" => 9,
    "Mega-Mancs" => 9,
    "Áramkör-Alfa" => 9,
    "CaTower-Bajnok" => 10,
    "Kibernetikus-Káosz" => 10,
    "Frissített-Foltos" => 2,
    "Turbo-Turbékoló" => 4,
    "LED-Leopárd" => 6,
    "Adat-Dömötör" => 1,
    "Szignál-Szafari" => 3,
    "Bites-Behemót" => 8,
    "Kódolt-Kutyaűző" => 5,
    "Hálózati-Házimacska" => 2,
    "Lézer-Lujza" => 7,
    "Fantom-Folt" => 9,
    "Alap-Algoritmus" => 1,
    "Sztenderd-Szőr" => 1
];

if (!isset($_SESSION['pakli_jatekos']) || isset($_GET['uj_jatek'])) {
    session_unset();
    $_SESSION['ertekek'] = $kartya_adatok;
    $alap = array_keys($kartya_adatok);
    $p1 = $p2 = $alap;
    shuffle($p1);
    shuffle($p2);

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
    $_SESSION['uzenet'] = "A csata elkezdődött!";
}


if ($_SESSION['kor'] == 'jatekos' && !$_SESSION['jatek_vege']) {
    if (isset($_GET['tamadas'])) {
        $sajat_nev = $_GET['sajat_nev'];
        $t_idx = (int)$_GET['torony_index'];
        $k_idx = array_search($sajat_nev, $_SESSION['kez']);

        if ($k_idx !== false && !$_SESSION['felfedett_ellenseg'][$t_idx]) {
            if ($_SESSION['ertekek'][$sajat_nev] >= $_SESSION['ertekek'][$_SESSION['ellenseg_torony'][$t_idx]]) {
                $_SESSION['ellenseg_torony'][$t_idx] = $sajat_nev;
                $_SESSION['felfedett_ellenseg'][$t_idx] = true;
                $_SESSION['uzenet'] = "Sikeres foglalás!";
            } else {
                $_SESSION['uzenet'] = "A támadás elbukott.";
            }
            unset($_SESSION['kez'][$k_idx]);
            if (count($_SESSION['pakli_jatekos']) > 0) $_SESSION['kez'][] = array_shift($_SESSION['pakli_jatekos']);
            else $_SESSION['jatek_vege'] = true;

            $_SESSION['kez'] = array_values($_SESSION['kez']);
            $_SESSION['kor'] = 'gep_gondolkodik';
        }
    } elseif (isset($_GET['passz'])) {
        if (count($_SESSION['pakli_jatekos']) > 0) $_SESSION['kez'][] = array_shift($_SESSION['pakli_jatekos']);
        else $_SESSION['jatek_vege'] = true;
        $_SESSION['uzenet'] = "Passzoltál.";
        $_SESSION['kor'] = 'gep_gondolkodik';
    }
}


if (isset($_GET['gep_lep']) && $_SESSION['kor'] == 'gep_gondolkodik') {
    $gep_k_idx = array_rand($_SESSION['gep_keze']);
    $t_idx = rand(0, 4);

    if ($_SESSION['ertekek'][$_SESSION['gep_keze'][$gep_k_idx]] >= $_SESSION['ertekek'][$_SESSION['sajat_torony'][$t_idx]]) {
        $_SESSION['sajat_torony'][$t_idx] = $_SESSION['gep_keze'][$gep_k_idx];
        $_SESSION['felfedett_sajat'][$t_idx] = false;
        $_SESSION['uzenet'] = "Az ellenfél áttörte a védelmed!";
    } else {
        $_SESSION['uzenet'] = "Az ellenfél támadása kudarcba fulladt.";
    }

    unset($_SESSION['gep_keze'][$gep_k_idx]);
    if (count($_SESSION['pakli_gep']) > 0) $_SESSION['gep_keze'][] = array_shift($_SESSION['pakli_gep']);
    else $_SESSION['jatek_vege'] = true;

    $_SESSION['gep_keze'] = array_values($_SESSION['gep_keze']);
    $_SESSION['kor'] = 'jatekos';
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit;
}


if (!in_array(false, $_SESSION['felfedett_ellenseg'])) {
    $_SESSION['jatek_vege'] = true;
    $_SESSION['uzenet'] = "NYERTÉL!";
}
if (!in_array(true, $_SESSION['felfedett_sajat'])) {
    $_SESSION['jatek_vege'] = true;
    $_SESSION['uzenet'] = "VESZTETTÉL!";
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <title>CaTower Duel</title>
    <link rel="stylesheet" href="kartya.css">
    <style>
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }

        .disabled-ui {
            pointer-events: none;
            opacity: 0.6;
            filter: grayscale(100%);
        }
    </style>
</head>

<body class="asztal">

    <?php if ($_SESSION['jatek_vege']): ?>
        <div class="overlay">
            <div>
                <h1 style="font-size: 80px;"><?php echo $_SESSION['uzenet']; ?></h1>
                <a href="?uj_jatek=1" style="padding: 20px 40px; background: #075a8a; color: white; text-decoration: none; border-radius: 10px; font-size: 24px;">ÚJ JÁTÉK</a>
            </div>
        </div>
    <?php endif; ?>

    <h1>CaTower Duel</h1>
    <p class="info"><?php echo $_SESSION['uzenet']; ?></p>

    <div class="<?php echo ($_SESSION['kor'] == 'gep_gondolkodik') ? 'disabled-ui' : ''; ?>">
        <div class="torony-resz">
            <h3>ELLENSÉG BÁSTYÁI (Pakli: <?php echo count($_SESSION['pakli_gep']); ?>)</h3>
            <?php foreach ($_SESSION['ellenseg_torony'] as $i => $nev): ?>
                <div class="kartya-blokk">
                    <div class="kartya" style="background-image: url('kepek/<?php echo $_SESSION['felfedett_ellenseg'][$i] ? $nev : 'borito'; ?>.png'); border: 2px solid red;"></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="torony-resz">
            <h3>SAJÁT BÁSTYÁID</h3>
            <?php foreach ($_SESSION['sajat_torony'] as $i => $nev): ?>
                <div class="kartya-blokk">
                    <div class="kartya" style="background-image: url('kepek/<?php echo $_SESSION['felfedett_sajat'][$i] ? $nev : 'borito'; ?>.png'); border: 2px solid green;"></div>
                    <br><span>(<?php echo $_SESSION['felfedett_sajat'][$i] ? $_SESSION['ertekek'][$nev] : '???'; ?>)</span>
                </div>
            <?php endforeach; ?>
        </div>

        <hr>

        <div class="kez-resz">
            <h3>A TE KEZED (Pakli: <?php echo count($_SESSION['pakli_jatekos']); ?>)</h3>
            <?php if ($_SESSION['kor'] == 'jatekos'): ?>
                <?php foreach ($_SESSION['kez'] as $nev): ?>
                    <form method="GET" class="kartya-blokk">
                        <div class="kartya" style="background-image: url('kepek/<?php echo $nev; ?>.png');"></div>
                        <br>
                        <select name="torony_index">
                            <?php foreach ($_SESSION['felfedett_ellenseg'] as $idx => $f): if (!$f) echo "<option value='$idx'>" . ($idx + 1) . ". ellenséges</option>";
                            endforeach; ?>
                        </select>
                        <input type="hidden" name="sajat_nev" value="<?php echo $nev; ?>">
                        <button type="submit" name="tamadas" value="1">TÁMADÁS</button>
                    </form>
                <?php endforeach; ?>
                <br>
                <form method="GET"><button type="submit" name="passz" value="1">PASSZ / HÚZÁS</button></form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($_SESSION['kor'] == 'gep_gondolkodik' && !$_SESSION['jatek_vege']): ?>
        <div style="text-align: center; margin-top: 20px;">
            <h2 style="color: #e74c3c;">AZ ELLENFÉL ELEMEZ... <span id="countdown">3</span> MP</h2>
        </div>
        <script>
            let timeleft = 3;
            let timer = setInterval(function() {
                timeleft--;
                document.getElementById("countdown").textContent = timeleft;
                if (timeleft <= 0) {
                    clearInterval(timer);
                    window.location.href = "?gep_lep=1";
                }
            }, 1000);
        </script>
    <?php endif; ?>
    <br><a href="?uj_jatek=1" style="color: #075a8a;">[ ÚJ JÁTÉK ]</a>

</body>

</html>