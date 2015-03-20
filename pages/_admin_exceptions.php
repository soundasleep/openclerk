<?php
require_admin();
?>

<table class="standard">
<thead>
  <tr>
    <th>ID</th>
    <th class="date">Date</th>
    <th>Type</th>
    <th>Message</th>
    <th>Source</th>
  </tr>
</thead>
<tbody>
<?php
  $q = db()->prepare("SELECT * FROM uncaught_exceptions
    ORDER BY id DESC LIMIT " . ((int) $limit));
  $q->execute();
  while ($e = $q->fetch()) {
    $path = str_replace("\\", "/", $e['filename']); ?>
  <tr>
    <td><?php echo number_format($e['id']); ?></td>
    <td class="date"><?php echo recent_format_html($e['created_at']); ?></td>
    <td><?php echo htmlspecialchars($e['class_name']); ?></td>
    <td><?php echo htmlspecialchars($e['message']); ?></td>
    <td><?php echo htmlspecialchars(substr($path, strrpos($path, '/') + 1) . ":" . $e['line_number']); ?></td>
  </tr>
  <?php }
?>
</tbody>
</table>
