<?php if ($file = $attrs->id()->toFile()): ?>
<?php
$linkText = $attrs->linkText() != '' ? $attrs->linkText() : $file->name();
?>
<a href="<?= $file->url(); ?>" target="<?= $attrs->target() ?>"><?= $linkText ?></a>
<?php endif ?>



