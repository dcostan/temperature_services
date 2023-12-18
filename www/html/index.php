<html>
 <head>

  <title>PAGINA LOGIN</title>

  <?php

   if(isset($_POST["name"])){
       $postedUser = $_POST['name'];
       $postedPwd = $_POST['password'];
   }else{
       $postedUser = "";
       $postedPwd = "";
   }

   $user = "dcostan";
   $pwd = "sec";

   $isLogged = False;

   if($user == $postedUser && $pwd == $postedPwd){
      $isLogged = True;
      // Save cookie and redirect to clear POST
      setcookie ("access", md5($pwd), time()+3600);
      header("Location: /");
   }
   else if($_COOKIE["access"] == md5($pwd))
      $isLogged = True;
   else if($postedUser != "" && $postedPwd != "")
      echo "<script type='text/javascript'>alert('Errore di accesso')</script>";

   if($isLogged){
      // Temperatura
      $tempFile = fopen("/var/www/temperature.txt", "r");
      $temperature = (float)fgets($tempFile);
      fclose($tempFile);
      // Umidità
      $humFile = fopen("/var/www/humidity.txt", "r");
      $humidity = (float)fgets($humFile);
      fclose($humFile);
      // Termostato
      $termFile = fopen("/var/www/data.json", "r");
      $json = json_decode(fgets($termFile), true);
      fclose($termFile);
      // Modatilata notte
      $nightmode = $json['night'];
      // Aggiornamento delle variabili
      if(isset($_POST["min"]) && isset($_POST["max"])){
         if(is_numeric($_POST['min']) && is_numeric($_POST['max'])){
            $json['min'] = doubleval($_POST['min']);
            $json['max'] = doubleval($_POST['max']);
            $termFile = fopen("/var/www/data.json", "w");
            fwrite($termFile, json_encode($json));
            fclose($termFile);
            header("Location: /");
         }else{
            echo "<script type='text/javascript'>";
            echo "  alert('Sono stati inseriti valori non numerici');";
            echo "  window.location = '/';";
            echo "</script>";
         }
      }else if(isset($_POST["enabler"])){
         if($_POST["enabler"] == "0")
            $json['active'] = 0;
         else
            $json['active'] = 1;
         $termFile = fopen("/var/www/data.json", "w");
         fwrite($termFile, json_encode($json));
         fclose($termFile);
         header("Location: /");
      }
   }

   if($nightmode)
     $color = "1a1aff";
   else
     $color = "f34e54";

  ?>

  <style class="cp-pen-styles">
      body {
        font-family: Helvetica;
        min-width: 850px;
      }

      form {
        margin: 0px;
      }

      .donation-meter {
        position: relative;
        left: 50%;
        margin-left: -50px;
        margin-top: 40;
        width: 100px;
      }
      .donation-meter .glass {
        background: #e5e5e5;
        border-radius: 100px 100px 0 0;
        display: block;
        height: 300px;
        margin: 0 35px 10px;
        padding: 5px;
        position: relative;
        width: 20px;
      }
      .donation-meter .amount {
        background: #<?php echo $color; ?>;
        border-radius: 100px;
        display: block;
        width: 20px;
        position: absolute;
        bottom: 5px;
      }
      .donation-meter strong {
        display: block;
        text-align: center;
      }
      .donation-meter .goal {
        font-size: 30px;
      }
      .donation-meter .total {
        font-size: 16px;
        position: absolute;
        right: 35px;
      }
      .bulb {
        background: #e5e5e5;
        border-radius: 100px;
        display: block;
        height: 50px;
        margin: 0 35px 10px;
        padding: 5px;
        position: relative;
        top: -20px;
        right: 15px;
        width: 50px;
      }
      .bulb .red-circle {
        background: #<?php echo $color; ?>;
        border-radius: 100px;
        display: block;
        height: 50px;
        width: 50px;
      }
      .bulb .filler {
        background: #<?php echo $color; ?>;
        border-radius: 100px 100px 0 0;
        display: block;
        height: 30px;
        width: 20px;
        position: relative;
        top: -65px;
        right: -15px;
        z-index: 30;
      }
      .shadow {
        position: relative;
        left: 50%;
        margin-left: -40%;
        -webkit-box-shadow: 3px 3px 5px 6px #ccc;  /* Safari 3-4, iOS 4.0.2 - 4.2, Android 2.3+ */
        -moz-box-shadow:    3px 3px 5px 6px #ccc;  /* Firefox 3.5 - 3.6 */
        box-shadow:         3px 3px 5px 6px #ccc;  /* Opera 10.5, IE 9, Firefox 4+, Chrome 6+, iOS 5 */
        background: #ccc;
        overflow: auto;
      }
      #progressbar {
        display: block;
        background-color: #e6e6e6;
        border-radius: 13px; /* (height of inner div) / 2 + padding */
        padding: 3px;
        width: 600px;
        margin-left: auto;
        margin-right: auto;
      }
      #progressbar > div {
        background-color: #00dbff;
        width: 0%;
        height: 20px;
        border-radius: 10px;
      }
  </style>

  <script src="jquery.min.js"></script>

 </head>

 <body style="font-family: sans-serif">

  <?php if($isLogged) { ?>

    <table border="0" style="height: 100%; width: 100%; min-width: 840px;">
      <tr>
        <td colspan="3">
          <div style="float:left; display:block; width:33%; height:100%; position:relative; min-height:540px;">

            <div class="shadow" style="margin-top: 50%; width: 60%; height: 210px; min-width: 240px;">
              <br><font size="5px"><center><b>GESTIONE AUTOMATICA</b></center></font>
              <form method="post">
                <p style="padding-left: 15px; margin-bottom: 0px;">
                  Soglia diurna: <input type="text" style="width: 45px; height: 25px; text-align: center;" value="<?php echo $json['max'] ?>" name="max">&nbsp;°C<br>
                  Soglia notturna: <input type="text" style="width: 45px; height: 25px; text-align: center;" value="<?php echo $json['min'] ?>" name="min">&nbsp;°C<br><br>
                  <input type="submit" value="Salva">
                </p>
              </form>
            </div>

          </div>

          <div style="float:left; display:block; width:33%; height:540px; min-width: 270px;">
            <center><h1>TEMPERATURA ATTUALE</h1></center>
            <div class="donation-meter">
              <strong class="goal">50&deg;C</strong>
              <span class="glass">
                <strong class="total" style="bottom: <?php echo $temperature*2; ?>%"><?php echo $temperature; ?>&deg;C</strong>
                <span class="amount" style="height: <?php echo $temperature*2; ?>%"></span>
              </span>
              <div class="bulb">
                <span class="red-circle"></span>
                <span class="filler">
                  <span></span>
                </span>
              </div>
            </div>
          </div>


         <div style="float:left; display:block; width:33%; height:100%; position:relative; min-height:540px;">

            <div style="position: absolute; top: 5px; right: 5px;"><form action="logout.php"><input type="submit" value="Esci"></form></div>

            <div class="shadow" style="margin-top: 60%; width: 65%; height: 100px; min-width: 240px;">
              <div style="padding: 2px; text-align: center; margin: 10px; position: relative; top: 5%;">
                <form method="post">
                  <b>RISCALDAMENTO<br> <font color='<?php echo $json['active'] ? "green'>ATTIVATO" : "red'>DISATTIVATO" ?></font></b><br>
                  <input type="hidden" name="enabler" value="<?php echo $json['active'] ? 0 : 1 ?>">
                  <input type="submit" style="height: 30px;" value="<?php echo $json['active'] ? 'Spegni' : 'Attiva' ?>">
                </form>
              </div>
            </div>

            <div id="fireDiv" style="position: relative; margin-top: 40%; margin-left: 35%; width: 80px; visibility: hidden;"><img src="fire.svg" alt="Il riscaldamento è acceso"></div>

          </div>

        </td>
      </tr>

<!-- Parte inferiore -->

      <tr height="50px">
        <td width="10%"><form action="settings.php"><input type="submit" value="Impostazioni"></form></td>
        <td width="80%">
          <div id="progressbar">
            <div style="width : <?php echo $humidity; ?>%; text-align: center; color: white;">Umidità:&nbsp;<?php echo $humidity; ?>%</div>
          </div>
        </td>
        <td width="10%"><input type="button" style="float: right;" value="Ricarica" onClick="window.location.reload()"></td>
      </tr>
    </table>

    <script type="text/javascript">
      $.get('fire.php', function(data) {
        if(Number(data))
           document.getElementById("fireDiv").style.visibility = "visible";
        else
           document.getElementById("fireDiv").style.visibility = "hidden";
      });

      window.setInterval(function(){
        $.get('fire.php', function(data) {
           if(Number(data))
              document.getElementById("fireDiv").style.visibility = "visible";
           else
              document.getElementById("fireDiv").style.visibility = "hidden";
        });
      }, 1000);
    </script>

  <?php } else { ?>

    <div align="center">

     <form method="post">

      <div style="margin-top:200px;margin-bottom:10px;">
       <span style="width:500px;color:blue;font-size:30px;font-weight:bold;border-bottom:1px solid blue;">Accounting System</span>
      </div>
      <div style="margin-bottom:5px;">

       <span style="width:100px;">Nome</span>
       <input style="width:150px;" type="text" name="name" id="name" required>

      </div>
      <div>
       <span style="width:100px;">Password&nbsp;</span>
       <input style="width:150px;" type="password" name="password" id="password" value="" required>
      </div>

      <input type="submit" value="Login">
     </form>
    </div>

  <?php } ?>

 </body>
</html>
