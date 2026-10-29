<?php

function query(string $sql, array $params=[]): PDOStatement {
    global $db;
    $s=$db->prepare($sql);
    $s->execute($params);
    return $s;
}
function rows(string $sql,array $params=[]): array {
    return query($sql,$params)->fetchAll(PDO::FETCH_ASSOC);
}
function transaction(callable $work): void {
    global $db;
    $db->beginTransaction();
    try{
        $work();
        $db->commit();
    }
    catch(Throwable $e){
        $db->rollBack();
        throw $e;
    }
}
