<!doctype html>
<html class="no-js">
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" >
      <meta name="apple-mobile-web-app-capable" content="yes" >
      <meta name="mobile-web-app-capable" content="yes"  >
      <link rel="apple-touch-icon" href="/apple-touch-icon.png">
      <?php $framework->show_dependencies(); ?>
      <?php $framework->show_share_meta(); ?>
    </head>
    <body>
        <?php $framework->show_header(); ?>
        <?php $framework->show_tagging_tools(); ?>
        <iframe id="game" frameborder="0" src="http://sail.spaceshipsin.space" scrolling="no" allowtransparency="true" style="position: absolute; width: 100%; height: 100%;"></iframe>
        <script>
          var header;
          var game;
          window.addEventListener("load",function(){
            game = document.getElementById('game')
            header = document.getElementById('header');
            window.addEventListener("resize",setSize);
            setSize();
          });
          function setSize(){
            game.style.height = (window.innerHeight-header.clientHeight)+"px";
          }
          var fW = new Framework('<?php echo $framework->exportToJS($force_name); ?>', null);
        </script>
    </body>
</html>