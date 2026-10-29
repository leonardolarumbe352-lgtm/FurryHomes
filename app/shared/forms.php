<?php

function field(string $label,string $name,string $type='text',$value='',bool $mandatory=true): void {
    echo '<label>'.e($label).'<input name="'.e($name).'" type="'.e($type).'" value="'.e($value).'" '.($mandatory?'required':'').' '.($type==='number'?'min="0" step="0.01"':'').($type==='file'?' accept="image/jpeg,image/png,image/webp"':'').'></label>';
}
function area(string $label,string $name,$value=''): void {
    echo '<label>'.e($label).'<textarea name="'.e($name).'" required rows="3">'.e($value).'</textarea></label>';
}
function selectField(string $label,string $name,array $options,$selected=''): void {
    echo '<label>'.e($label).'<select name="'.e($name).'" required><option value="">Choose…</option>';
    foreach($options as $k=>$v)echo '<option value="'.e($k).'" '.((string)$k===(string)$selected?'selected':'').'>'.e($v).'</option>';
    echo '</select></label>';
}
function options(array $values): array {
    return array_combine($values,$values);
}
function formStart(string $action): void {
    echo '<form method="post" enctype="multipart/form-data">'.csrf().'<input type="hidden" name="action" value="'.e($action).'">';
}
function button(string $label='Save changes'): void {
    echo '<button type="submit">'.e($label).'</button></form>';
}
function badge(string $s): string {
    return '<span class="badge '.strtolower(str_replace(' ','-',$s)).'">'.e($s).'</span>';
}
