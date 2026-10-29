<?php

function analytics_report() {
    return rows("SELECT month,SUM(intakes) intakes,SUM(adoptions) adoptions FROM (SELECT substr(intake_date,1,7) month,COUNT(*) intakes,0 adoptions FROM pets GROUP BY month UNION ALL SELECT substr(signed_date,1,7) month,0 intakes,COUNT(*) adoptions FROM contracts GROUP BY month) GROUP BY month ORDER BY month DESC");
}
function analytics_activity() {
    return rows('SELECT a.*,u.name FROM audit a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 30');
}
