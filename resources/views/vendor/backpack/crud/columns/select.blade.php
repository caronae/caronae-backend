{{-- single relationships (1-1, 1-n) --}}
<td>
	<?php
		if ($entry->{$column['entity']}) {
	    	echo $entry->{$column['entity']}->{$column['attribute']};
	    }
	?>
</td>