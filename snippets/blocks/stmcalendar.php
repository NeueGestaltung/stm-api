<?php
/** @var \Kirby\Cms\Block $block */
?>
<div class="stm-calendar">
  <?php if ($block->title()->isNotEmpty()): ?>
    <h2><?= html($block->title()) ?></h2>
  <?php endif ?>
</div>
