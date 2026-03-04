<?php
session_start();

// 1. Inicializálás 1-10 közötti értékekkel
if (!isset($_SESSION['pakli']) || isset($_GET['uj_jatek'])) {
    $ertekek = range(1, 10); 
    // Egy pakli 4 színből áll, 1-10-ig
    $_SESSION['pakli'] = array_merge($ertekek, $ertekek, $ertekek, $ertekek);
    shuffle($_SESSION['pakli']);

    $_SESSION['torony'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['felfedett_torony'] = array_fill(0, 5, false);
    $_SESSION['jatekos_kez'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['ai_kez'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['uzenet'] = "A játék elindult! A 10-es a legerősebb lap.";
}

// --- JÁTÉKOS KÖRE ---
if (isset($_GET['akcio'])) {
    $kor_vege = false;

    if ($_GET['akcio'] == 'tamadas' && isset($_GET['kez_idx'])) {
        $k_idx = $_GET['kez_idx'];
        $t_idx = $_GET['torony_idx'];
        
        $sajat_lap = $_SESSION['jatekos_kez'][$k_idx];
        $torony_lap = $_SESSION['torony'][$t_idx];

        if ($sajat_lap > $torony_lap) {
            $_SESSION['torony'][$t_idx] = $sajat_lap;
            $_SESSION['felfedett_torony'][$t_idx] = true;
            unset($_SESSION['jatekos_kez'][$k_idx]);
            $_SESSION['uzenet'] = "Sikeresen legyőzted a $torony_lap-ast egy $sajat_lap-assal!";
        } else {
            $_SESSION['jatekos_kez'][] = array_shift($_SESSION['pakli']);
            $_SESSION['uzenet'] = "A támadás elbukott ($sajat_lap vs $torony_lap). Büntetésből húztál egyet!";
        }
        $kor_vege = true;
    } elseif ($_GET['akcio'] == 'passz') {
        $_SESSION['jatekos_kez'][] = array_shift($_SESSION['pakli']);
        $_SESSION['uzenet'] = "Passzoltál, húztál egy lapot a pakliból.";
        $kor_vege = true;
    }

    if ($kor_vege) {
        $_SESSION['jatekos_kez'] = array_values($_SESSION['jatekos_kez']);

        // --- AI KÖRE ---
        sort($_SESSION['ai_kez']); // Az AI a legkisebb lapjaival próbálkozik először
        $ai_lepett = false;

        foreach ($_SESSION['ai_kez'] as $a_idx => $ai_lap) {
            foreach ($_SESSION['torony'] as $t_idx => $torony_lap) {
                if ($ai_lap > $torony_lap) {
                    $_SESSION['torony'][$t_idx] = $ai_lap;
                    $_SESSION['felfedett_torony'][$t_idx] = true;
                    unset($_SESSION['ai_kez'][$a_idx]);
                    $_SESSION['ai_kez'] = array_values($_SESSION['ai_kez']);
                    $_SESSION['uzenet'] .= "<br>Az AI megtámadta a " . ($t_idx+1) . ". oszlopot egy $ai_lap-assal!";
                    $ai_lepett = true;
                    break 2;
                }
            }
        }

        if (!$ai_lepett) {
            $_SESSION['ai_kez'][] = array_shift($_SESSION['pakli']);
            $_SESSION['uzenet'] .= "<br>Az AI nem tudott támadni, ezért húzott egyet.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Torony Ostrom 1-10</title>
    <style>
        body { background: #1a1a1a; color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; }
        .palyatabla { background: #234d20; border: 10px solid #5d3a1a; border-radius: 20px; display: inline-block; padding: 20px; margin-top: 20px; box-shadow: 0 0 50px rgba(0,0,0,0.5); }
        .kartya { 
            display: inline-block; width: 70px; height: 100px; border-radius: 10px; 
            margin: 5px; line-height: 100px; font-size: 24px; font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.3); border: 2px solid #fff;
        }
        .torony { background: #444; color: #aaa; }
        .felfedett { background: #fff; color: #d4af37; border-color: #d4af37; text-shadow: 1px 1px 2px black; }
        .jatekos-lap { background: #fff; color: #222; }
        .ai-lap { background: #700; border-color: #a00; color: #700; }
        .info-panel { margin: 20px; padding: 15px; background: rgba(0,0,0,0.7); border-radius: 10px; min-height: 50px; color: #00ff00; }
        button { cursor: pointer; padding: 5px 10px; background: #d4af37; border: none; border-radius: 3px; font-weight: bold; }
        .passz-btn { padding: 15px 30px; font-size: 18px; background: #cc3300; color: white; margin-top: 20px; }
    </style>
</head>
<body>

<div class="palyatabla">
    <h1>TORONY OSTROM</h1>
    
    <div class="info-panel"><?php echo $_SESSION['uzenet']; ?></div>

    <h3>Ellenfél keze (<?php echo count($_SESSION['ai_kez']); ?> lap)</h3>
    <?php foreach ($_SESSION['ai_kez'] as $lap): ?>
        <div class="kartya ai-lap">X</div>
    <?php endforeach; ?>

    <div style="margin: 40px 0;">
        <h3>A Torony Alapja</h3>
        <?php foreach ($_SESSION['torony'] as $i => $lap): ?>
            <div class="kartya torony <?php echo $_SESSION['felfedett_torony'][$i] ? 'felfedett' : ''; ?>">
                <?php echo $_SESSION['felfedett_torony'][$i] ? $lap : "??"; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <h3>A te kezed</h3>
    <form method="GET">
        <?php foreach ($_SESSION['jatekos_kez'] as $i => $lap): ?>
            <div style="display:inline-block; margin: 0 10px;">
                <div class="kartya jatekos-lap"><?php echo $lap; ?></div><br>
                <select name="torony_idx">
                    <?php for($j=0; $j<5; $j++): ?>
                        <option value="<?php echo $j; ?>"><?php echo $j+1; ?>. torony</option>
                    <?php endfor; ?>
                </select><br>
                <button type="submit" name="akcio" value="tamadas" style="margin-top:5px;">TÁMADÁS</button>
                <input type="hidden" name="kez_idx" value="<?php echo $i; ?>">
            </div>
        <?php endforeach; ?>
        <br>
        <button type="submit" name="akcio" value="passz" class="passz-btn">PASSZ / HÚZÁS</button>
    </form>

    <p><a href="?uj_jatek=1" style="color: #888; text-decoration: none;">[ Új játék kezdése ]</a></p>
</div>

</body>
</html>