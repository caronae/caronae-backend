{{-- regular object attribute --}}
@php
    if( !empty($entry->{$column['name']}) ) {

        // if attribute casting is used, convert to object
        if (is_array($entry->{$column['name']})) {
            $video = (object)$entry->{$column['name']};
        } elseif (is_string($entry->{$column['name']})) {
            $video = json_decode($entry->{$column['name']});
        } else {
            $video = $entry->{$column['name']};
        }
        $bgColor = $video->provider == 'vimeo' ? '#00ADEF' : '#DA2724';
    }
@endphp
<td>
    @if( isset($video) )
    <a target="_blank" href="{{$video->url}}" title="{{$video->title}}" style="background: {{$bgColor}}; color: #fff; display: inline-block; width: 30px; height: 25px; text-align: center; border-top-left-radius: 3px; border-bottom-left-radius: 3px; transform: translateY(-1px);">
        <i class="fa fa-{{$video->provider}}" style="transform: translateY(2px);"></i>
    </a><img src="{{$video->image}}" alt="{{$video->title}}" style="height: 25px; border-top-right-radius: 3px; border-bottom-right-radius: 3px;" />
    @else
    -
    @endif
</td>
