<!doctype html>
<html class="no-js">
    <head>
      <title>Admin - Manage languages</title>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="apple-touch-icon" href="/apple-touch-icon.png">
      <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
      <script>window.jQuery || document.write("<script src='/common/js/jquery-3.2.1.min.js'><\/script>")</script>
      <style>
      body {
        margin: 20px;
        padding: 0;
      }

      h1{
        font-size: 30px;
        text-transform: uppercase;
        margin: 0;
        padding: 0;
      }

      h2 {
        font-size: 20px;
        text-transform: uppercase;
        margin: 0 0 20px 0;
        padding: 0;
      }

      ul{
        list-style-type: none;
        margin: 0 0 30px 0;
        padding: 0;
      }

   
      </style>
    </head>
    <body>
      <h1>Très Très Court</h1>
      <h2>Admin - Gestion des langues</h2>
      <div class='projets'>
       
        <?php
        $result = file_get_contents("http://".$_SERVER["SERVER_NAME"]."/includes/admin/languages.json");
        $obj = json_decode($result, true);
        
        foreach($obj as $key => $item)
        {
        ?>
          <ul>
          <li data-name='<?php echo $key; ?>'><strong><?php echo $key; ?></strong></li>
          <li>
            <div class='box'>FR <input data-name='fr' type='checkbox' <?php if($item["fr"] === true) echo "checked='checked'"; ?> />
            EN <input data-name='en' type='checkbox' <?php if($item["en"] === true) echo "checked='checked'"; ?> />
            DE <input data-name='de' type='checkbox' <?php if($item["de"] === true) echo "checked='checked'"; ?> />
            </div>
          </li>
          </ul>
        <?php
        }
        ?>
      </div>
      <button id='submit'>Enregistrer</button>
      <script type="text/javascript">
      $(document).ready(function(){
        
        var button = $("#submit");
        button.on("click", function(e){

            var output = {};

            $("ul li:first-child").each(function(i, li){
              
                var $li = $(li);

                output[$li.data('name')] = {};

                $li.siblings("li").find("input").each(function(i, input){

                    var $input = $(input);
                    output[$(li).data('name')][$input.data('name')] = $input[0].checked;
                });
            });

            output = JSON.stringify(output);

            $.ajax({
              url: "http://"+document.location.host+"/onf_admin_save",
              method: "POST",
              data: {json: output},
              success: function(data) {
                  if(data === '1') document.location.reload();
              }
            })
        });
      });
      </script>
    </body>
</html>