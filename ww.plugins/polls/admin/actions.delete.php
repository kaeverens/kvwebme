<?php
dbQuery("delete from poll where id=$id");
dbQuery("delete from poll_answer where poll_id=$id");
dbQuery("delete from poll_vote where poll_id=$id");
echo '<em>Poll deleted</em>';
Core_cacheClear('polls');
