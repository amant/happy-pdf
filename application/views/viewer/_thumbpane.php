<div id="thumb-elements" class="" style="height:624px;">
  
<?php foreach($images as $key => $image): ?>  
  <div class="thumb-element">
      <div id="thumb-<?php echo $key ?>">
          <img style="width: <?php echo $image['w'] ?>;" class="" src="<?php echo $image['src'] ?>" />
      </div>
      <span class="page-number"><?php echo ($key + 1) ?></span>
  </div>  
<?php endforeach; ?>

</div>