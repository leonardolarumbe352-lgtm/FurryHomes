<?php $report=analytics_report();?>
<div class="filters">
<a class="button" href="?page=analytics&export=csv">Download CSV</a>
<button type="button" onclick="window.print()">Print / Save PDF</button>
<span class="muted">In the print dialog, select “Save as PDF”.</span>
</div>
<section class="panel">
<h2>Intake & adoption report</h2>
<p class="muted">All recorded intakes and finalized adoptions, grouped by calendar month. Generated <?=date('d M Y, H:i')?>.</p>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Month</th>
<th>Intakes</th>
<th>Adoptions</th>
<th>Adoptions / intakes</th>
</tr>
</thead>
<tbody><?php foreach($report as $r):?>
<tr>
<td><?=e($r['month'])?>
</td>
<td><?=$r['intakes']?>
</td>
<td><?=$r['adoptions']?>
</td>
<td><?=$r['intakes']?round(100*$r['adoptions']/$r['intakes']).'%':'—'?>
</td>
</tr><?php endforeach;?>
</tbody>
</table>
</div>
<p class="muted">Monthly adoption-to-intake ratio compares activity; adoptions may include pets from earlier months.</p>
</section>
<section class="panel">
<h2>Recent activity</h2><?php foreach(analytics_activity() as $a):?>
<p><?=e($a['action'])?>
<small><?=e($a['name'])?> · <?=e($a['created_at'])?>
</small>
</p><?php endforeach;?>
</section>
