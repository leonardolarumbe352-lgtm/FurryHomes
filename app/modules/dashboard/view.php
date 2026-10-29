<?php $counts=[['Pets in our care',dashboard_care_count()->fetchColumn(),'Every little life matters'],['Happy adoptions',dashboard_adoption_count()->fetchColumn(),'New beginnings, together'],['Applications',dashboard_application_count()->fetchColumn(),'The next step toward home'],['In foster care',dashboard_foster_count()->fetchColumn(),'Loved while they wait']];?>
<div class="stats"><?php foreach($counts as [$label,$count,$hint]):?>
<article>
<span><?=e($label)?>
</span>
<strong><?=e($count)?>
</strong>
<small><?=e($hint)?>
</small>
</article><?php endforeach;?>
</div>
<section class="welcome">
<div>
<span class="eyebrow">A HOME CHANGES EVERYTHING</span>
<h2>Small moments.<br>Lifelong companions.</h2>
<p>Help a rescue pet take their next step toward a loving home.</p>
<a class="button" href="?page=pets">Meet the pets →</a>
</div>
<div class="pet-art" aria-hidden="true">🐕 🐈</div>
</section>
<section class="panel">
<h2>Your updates <span class="live">● Live</span>
</h2>
<div id="notifications"><?php $notes=dashboard_notifications();if(!$notes)echo '<p class="muted">You’re all caught up. Application updates will appear here.</p>';foreach($notes as $n)echo '<p class="notification">'.e($n['message']).'<small>'.e($n['created_at']).'</small>
</p>';?>
</div>
</section>
