<?php
$neighborhoodsCount = $entry->neighborhoods()->count();
?>

<span>
    {{ $neighborhoodsCount }} {{ $neighborhoodsCount > 1 ? 'bairros' : 'bairro' }}

    @include('vendor.backpack.crud.buttons.editNeighborhoods')
</span>