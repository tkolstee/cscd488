<html><head><title>Config Test</title></head><body>
  <?php $db = Db::getInstance(); ?>
  install_date: <?php print($db->get_config('install_date')) ?>
</body></html>

