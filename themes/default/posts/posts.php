<h4><?php print $count->fetchColumn(); ?> Posts found in the database</h4>

<?php while($row = $result->fetch(PDO::FETCH_ASSOC)) { ?>

<h2><?php print $row['title']; ?></h2>
<small>Posted by <?php print $row['author']; ?></small>
<p><?php print $row['text']; ?></p>

<?php } ?>
