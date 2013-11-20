<?php

// get all pages
$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? AND is_removed=0 ORDER BY page_order ASC, id ASC");
$q->execute(array(user_id()));
$pages = $q->fetchAll();

// how many securities do we have?
// we have a new job 'securities_count' to update the count of securities for each user, otherwise we'd have to recalculate this on every profile page load
$securities_count = $user['securities_count'];
