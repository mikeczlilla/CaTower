<?php
session_start(); 

// Játék indítása
if (!isset($_SESSION['pakli'])) {
    $ertekek = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; 
    $_SESSION['pakli'] = array_merge($ertekek, $ertekek, $ertekek, $ertekek); // 4 szín
    shuffle($_SESSION['pakli']);

    // Torony alapja (5 lap lefordítva)
    $_SESSION['torony'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['felfedett_torony'] = [false, false, false, false, false]; // Még nincs felfedve semmi

    // Játékos keze (5 lap)
    $_SESSION['kez'] = array_splice($_SESSION['pakli'], 0, 5);
    $_SESSION['uzenet'] = "A játék elkezdődött! Támadd meg a Tornyot!";
}

// 2. Támadás logika
if (isset($_GET['tamadas'])) {
    $kez_index = $_GET['kez_index'];
    $torony_index = $_GET['torony_index'];

    $sajat_lap = $_SESSION['kez'][$kez_index];
    $torony_lap = $_SESSION['torony'][$torony_index];

    if ($sajat_lap > $torony_lap) {
        // Siker: Torony lapja kuka, saját lapunk megy a helyére felfordítva
        $_SESSION['torony'][$torony_index] = $sajat_lap;
        $_SESSION['felfedett_torony'][$torony_index] = true;
        unset($_SESSION['kez'][$kez_index]);
        $_SESSION['kez'] = array_values($_SESSION['kez']); // Indexek újrarendezése
        $_SESSION['uzenet'] = "Sikeres ostrom! Legyőzted a $torony_lap értéket.";
    } else {
        // Kudarc: Büntetésből húzás
        if (count($_SESSION['pakli']) > 0) {
            $_SESSION['kez'][] = array_shift($_SESSION['pakli']);
        }
        $_SESSION['uzenet'] = "Bukta! A Torony lapja ($torony_lap) erősebb volt. Húztál egyet!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <style>
        .asztal { background: #38b6ff; padding: 20px; color: white; text-align: center; }
        .kartya { 
            display: inline-block; width: 80px; height: 120px; 
            border: 2px solid white; border-radius: 8px; margin: 10px;
            vertical-align: top; cursor: pointer; background: white; color: black;
        }
        .leforditott { background: #333; color: white; }
        .info { font-weight: bold; margin: 20px; color: yellow; }
    </style>
</head>
<body>

<div class="asztal">
    <h1>Torony Ostrom</h1>
    <p class="info"><?php echo $_SESSION['uzenet']; ?></p>

    <h3>Torony alapja (Célpontok)</h3>
    <?php foreach ($_SESSION['torony'] as $i => $lap): ?>
        <div class="kartya <?php echo $_SESSION['felfedett_torony'][$i] ? '' : 'leforditott'; ?>">
            <?php echo $_SESSION['felfedett_torony'][$i] ? "Érték: $lap" : "???"; ?>
            <!-- Itt cserélheted le képre: <img src="hatlap.png"> -->
        </div>
    <?php endforeach; ?>

    <hr>

    <h3>A te kezed (Válassz egy lapot a támadáshoz)</h3>
    <form method="GET">
        <?php foreach ($_SESSION['kez'] as $i => $lap): ?>
            <div style="display:inline-block">
                <div class="kartya">Érték: <?php echo $lap; ?></div><br>
                <select name="torony_index">
                    <option value="0">1. Torony</option>
                    <option value="1">2. Torony</option>
                    <option value="2">3. Torony</option>
                    <option value="3">4. Torony</option>
                    <option value="4">5. Torony</option>
                </select>
                <button type="submit" name="tamadas" value="1">Támadás</button>
                <input type="hidden" name="kez_index" value="<?php echo $i; ?>">
            </div>
        <?php endforeach; ?>
    </form>
    
    <br><br>
    <a href="?uj_jatek=1" style="color: white;">Új játék indítása</a>
    <?php if(isset($_GET['uj_jatek'])) { session_destroy(); header("Location: kartyaJ.php"); } ?>
</div>

</body>
</html>