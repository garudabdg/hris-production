<?php
use Illuminate\Support\Facades\DB;
$groups = DB::table('permission_groups')->get();
foreach ($groups as $group) {
    echo $group->id . " - " . $group->name . "\n";
}
