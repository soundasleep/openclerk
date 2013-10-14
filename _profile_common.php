<?php

// get all pages
$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? AND is_removed=0 ORDER BY page_order ASC, id ASC");
$q->execute(array(user_id()));
$pages = $q->fetchAll();

// how many securities do we have?
$q = db()->prepare("SELECT COUNT(*) AS c FROM securities WHERE user_id=? AND is_recent=1");
$q->execute(array(user_id()));
$count = $q->fetch();
$securities_count = $count['c'];
