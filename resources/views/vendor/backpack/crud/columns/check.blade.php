{{-- checkbox with loose false/null/0 checking --}}
<?php
	$icon = "fa-check-square-o";
	if (strip_tags($entry->{$column['name']}) == false)
	{
		$icon = "fa-square-o";
	}
?>

<td>
	<i class="fa {{ $icon }}"></i>
</td>