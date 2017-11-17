<?php $framework->update_geoloc(); ?>
<!doctype html>
<html class="no-js">
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" >
      <meta name="apple-mobile-web-app-capable" content="yes" >
      <meta name="mobile-web-app-capable" content="yes"  >
      <link rel="apple-touch-icon" href="/apple-touch-icon.png">
      <style>
        #game {
          position: absolute; width: 100%; height: 100%; left: 0; right: 0;
        }
      </style>
      <?php $framework->show_dependencies(); ?>
      <?php $framework->show_share_meta(); ?>
    </head>
    <body>
        <?php $framework->show_header(); ?>
        <?php $framework->show_tagging_tools(); ?>
        <iframe id="game" frameborder="0" src="https://papersail.lab.arte.tv/<?php $lang = $framework->get("geoloc")->language; echo ($lang?$lang:"en") ?>/" scrolling="no" allowtransparency="true"></iframe>
        <script>
          var header;
          var game;
          var playing = false;
          window.addEventListener("load",function(){
            game = document.getElementById('game')
            game.contentWindow.postMessage("host", "*");
            header = document.getElementById('header');
            if (!header){
              header = document.getElementById('arte-header');
            }
            window.addEventListener("resize",setSize);
            setSize();
          });
          function setSize(){
            if (playing) {
              game.style.top = "0";
              game.style.bottom = "0";
              game.style.height = "100%";
              game.style["z-index"] = 2000;
            } else {
              game.style.height = (window.innerHeight-header.clientHeight)+"px";
              game.style.top = header.clientHeight+"px";
            }
          }

          var fW = new Framework('<?php echo $framework->exportToJS($force_name); ?>', null);
          window.addEventListener("message",function(event) {
              var dat = event.data;
              if (dat.hasOwnProperty("event")){
                switch (dat.event) {
                  case "gamestart":
                    playing = true;
                    setSize();
                    break;
                  default:
                    
                }
              }
          })
        </script>
    </body>
</html>