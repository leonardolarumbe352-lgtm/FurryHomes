<?php

function csrf(): string {
    return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';
}
