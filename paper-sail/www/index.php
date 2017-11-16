<!doctype html>
<html class="no-js">
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="apple-touch-icon" href="/apple-touch-icon.png">
      <?php $framework->show_dependencies(); ?>
      <?php $framework->show_share_meta(); ?>
    </head>
    <body>
        <?php $framework->show_header(); ?>
        <?php $framework->show_tagging_tools(); ?>
        <iframe frameborder="0" src="http://sail.spaceshipsin.space" scrolling="no" allowtransparency="true" style="position: absolute; left: 0; right: 0; top: 0; bottom: 0; width: 100%; height: 100%;"></iframe>
        <script>   
          var fW = new Framework('<?php echo $framework->exportToJS($force_name); ?>', null);
        </script>
    </body>
</html>