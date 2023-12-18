<html>
    <head>
        <title>Impostazioni</title>
        <?php
            $pwd = "sec";

            if($_COOKIE["access"] == md5($pwd)){
                $isLogged = True;
                $termFile = fopen("/var/www/data.json", "r");
                $json = json_decode(fgets($termFile), true);
                fclose($termFile);
                if(isset($_POST["night"])){
                    if($_POST["night"] == "0")
                        $json['night'] = 0;
                    else
                        $json['night'] = 1;
                    $termFile = fopen("/var/www/data.json", "w");
                    fwrite($termFile, json_encode($json));
                    fclose($termFile);
                    header("Location: /");
                }
            }
            else
                header("Location: /");
        ?>

        <link rel="stylesheet" type="text/css" href="style.css">

    </head>
    <body style="font-family: sans-serif">
        <?php if($isLogged) { ?>

            <div id="content">
                <div id="title">
                    Impostazioni
                </div>
                <form method="post" action="settings.php">
                    <div class="setting">
                        Abilita la modalità notturna
                        <label class="switch switch_type1" role="switch">
                            <input type="hidden" value="0" name="night">
                            <input type="checkbox" class="switch__toggle" name="night"<?php if($json['night']) echo " checked"; ?>>
                            <span class="switch__label"></span>
                        </label>
                    </div>
                    <br><br>
                    <input type="submit" id="save" value="Salva">
                    <input type="button" id="cancel" value="Annulla" onClick="location.href='/'">
                </form>
            </div>
        <?php } ?>

    </body>
</html>
