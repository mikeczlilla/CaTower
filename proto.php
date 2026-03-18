<?php
session_start();

// 1. Inicializálás (Új játék vagy első indítás)
if (!isset($_SESSION['pakli']) || isset($_GET['uj_jatek'])) {
    $ertekek = range(1, 10); 
    $_SESSION['pakli'] = array_merge($ertekek, $ertekek, $ertekek, $ertekek);
    shuffle($_SESSION['pakli']);

    $_SESSION['torony'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['felfedett_torony'] = array_fill(0, 5, false); // Kezdetben minden lefedve
    $_SESSION['jatekos_kez'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['ai_kez'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['uzenet'] = "Vezesd győzelemre a sereged! A 10-es a legerősebb.";
}

// --- JÁTÉKMENET ---
if (isset($_GET['akcio'])) {
    $kor_vege = false;

    // Játékos támadása
    if ($_GET['akcio'] == 'tamadas' && isset($_GET['kez_idx'])) {
        $k_idx = $_GET['kez_idx'];
        $t_idx = $_GET['torony_idx'];
        $sajat_lap = $_SESSION['jatekos_kez'][$k_idx];
        $torony_lap = $_SESSION['torony'][$t_idx];

        if ($sajat_lap > $torony_lap) {
            $_SESSION['torony'][$t_idx] = $sajat_lap;
            $_SESSION['felfedett_torony'][$t_idx] = true;
            unset($_SESSION['jatekos_kez'][$k_idx]);
            $_SESSION['uzenet'] = "Sikeres ostrom! Legyőzted a(z) " . ($t_idx+1) . ". bástyát.";
        } else {
            if(count($_SESSION['pakli']) > 0) $_SESSION['jatekos_kez'][] = array_shift($_SESSION['pakli']);
            $_SESSION['uzenet'] = "A támadás elbukott! Büntetésből húztál egyet.";
        }
        $kor_vege = true;
    } 
    // Játékos passzol
    elseif ($_GET['akcio'] == 'passz') {
        if(count($_SESSION['pakli']) > 0) $_SESSION['jatekos_kez'][] = array_shift($_SESSION['pakli']);
        $_SESSION['uzenet'] = "Visszavonultál és új lapot húztál.";
        $kor_vege = true;
    }

    // AI KÖRE (Csak ha a játékos lépett)
    if ($kor_vege) {
        $_SESSION['jatekos_kez'] = array_values($_SESSION['jatekos_kez']);
        sort($_SESSION['ai_kez']); // Az AI okosan a legkisebb alkalmas lapját használja
        
        $ai_lepett = false;
        foreach ($_SESSION['ai_kez'] as $a_idx => $ai_lap) {
            foreach ($_SESSION['torony'] as $t_idx => $torony_lap) {
                if ($ai_lap > $torony_lap) {
                    $_SESSION['torony'][$t_idx] = $ai_lap;
                    $_SESSION['felfedett_torony'][$t_idx] = true;
                    unset($_SESSION['ai_kez'][$a_idx]);
                    $_SESSION['ai_kez'] = array_values($_SESSION['ai_kez']);
                    $_SESSION['uzenet'] .= "<br>Az ellenfél elfoglalta a(z) " . ($t_idx+1) . ". bástyát!";
                    $ai_lepett = true;
                    break 2;
                }
            }
        }
        if (!$ai_lepett && count($_SESSION['pakli']) > 0) {
            $_SESSION['ai_kez'][] = array_shift($_SESSION['pakli']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Torony Ostrom - Kártyajáték</title>
    <style>
        body { background: #1a1a1a; color: white; font-family: 'Segoe UI', sans-serif; text-align: center; }
        .palyatabla { background: #234d20; border: 10px solid #3d2a1a; border-radius: 20px; display: inline-block; padding: 20px; margin-top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.8); }
        
        /* KÁRTYA MEGJELENÍTÉSE */
        .kartya { 
            display: inline-block; width: 100px; height: 150px; 
            margin: 5px; border-radius: 10px; border: 2px solid #000;
            background-size: cover; background-position: center;
            box-shadow: 3px 3px 8px rgba(0,0,0,0.5);
        }
        .ai-kartya { width: 60px; height: 90px; } /* Kisebb lapok az AI-nak */
        
        .info-panel { margin: 15px; padding: 15px; background: rgba(0,0,0,0.7); color: #00ff00; border-radius: 10px; font-weight: bold; border: 1px solid #444; }
        .passz-btn { background: #b32d00; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .passz-btn:hover { background: #ff4000; }
        select, button[type="submit"] { margin-top: 5px; padding: 3px; cursor: pointer; }
    </style>
</head>
<body>

<div class="palyatabla">
    <h1>TORONY OSTROM</h1>
    <div class="info-panel"><?php echo $_SESSION['uzenet']; ?></div>

    <!-- ELLENFÉL KEZE (Mindig lefedve) -->
    <div style="margin-bottom: 20px;">
        <small>Ellenfél lapjai:</small><br>
        <?php foreach ($_SESSION['ai_kez'] as $lap): ?>
            <div class="kartya ai-kartya" style="background-image: url('kepek/hatlap.png');"></div>
        <?php endforeach; ?>
    </div>

    <!-- A TORONY (5 lap a közepén) -->
    <div style="padding: 20px; border-top: 3px double rgba(255,255,255,0.2); border-bottom: 3px double rgba(255,255,255,0.2); background: rgba(0,0,0,0.1);">
        <?php foreach ($_SESSION['torony'] as $i => $lap): ?>
            <?php 
                // Ha a lap fel van fedve, mutassa a számát, különben a hátlapot
                $kep = $_SESSION['felfedett_torony'][$i] ? "kepek/$lap.png" : "kepek/hatlap.png"; 
            ?>
            <div class="kartya" style="background-image: url('<?php echo $kep; ?>');"></div>
        <?php endforeach; ?>
    </div>

    <!-- JÁTÉKOS KEZE ÉS AKCIÓK -->
    <h3>A te sereged:</h3>
    <form method="GET">
        <?php foreach ($_SESSION['jatekos_kez'] as $i => $lap): ?>
            <div style="display:inline-block; margin: 5px; vertical-align: top;">
                <div class="kartya" style="background-image: url('kepek/<?php echo $lap; ?>.png');"></div><br>
                
                <select name="torony_idx">
                    <?php for($j=0; $j<5; $j++): ?>
                        <option value="<?php echo $j; ?>"><?php echo $j+1; ?>. bástya</option>
                    <?php endfor; ?>
                </select><br>
                
                <button type="submit" name="akcio" value="tamadas">TÁMADÁS</button>
                <input type="hidden" name="kez_idx" value="<?php echo $i; ?>">
            </div>
        <?php endforeach; ?>
        
        <br><br>
        <button type="submit" name="akcio" value="passz" class="passz-btn">PASSZ / HÚZÁS</button>
    </form>

    <p style="margin-top: 30px;">
        <a href="?uj_jatek=1" style="color: #aaa; text-decoration: none; font-size: 13px;">[ Új játék indítása ]</a>
    </p>
    <small style="color: #666;">Pakliban maradt: <?php echo count($_SESSION['pakli']); ?> lap</small>
</div>

</body>
</html>