<?php

function pets_search($filter, $search) {
    return rows('SELECT * FROM pets WHERE (name LIKE ? OR breed LIKE ?)'.($filter?' AND status=?':'').' ORDER BY id DESC',$filter?['%'.$search.'%','%'.$search.'%',$filter]:['%'.$search.'%','%'.$search.'%']);
}
function pets_find(int $id) {
    return query('SELECT * FROM pets WHERE id=?',[$id]);
}
