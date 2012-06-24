<?php foreach($images as $key => $image): ?>

<div style="width: <?php echo ((int)$image['w'] + 20) ?>px;" class="page-element" id="page-<?php echo $key ?>">
    <div>
        <img style="width: <?php echo $image['w'] ?>; display:block;" class="page-image lazy" src="<?php echo $image['thumb_src'] ?>" data-original="<?php echo $image['src'] ?>"/>
        <img style="width: <?php echo $image['w'] ?>; height: <?php echo $image['h'] ?>; display: none; border:0px solid blue;" class="page-image" src="<?php echo $image['thumb_src'] ?>" />
    </div>
</div>

<?php endforeach; ?>
