🏠 <b>Nouvelle maison {{ $article->data["surfaceArea"] }}m² / {{ number_format($article->data["price"], 2, ",", " ")
    }}€</b>
<i>{{ $article->data["postalCode"] }}, {{ $article->data["city"] }} </i>

@if (!empty($article->data["roomsQuantity"]))
{{ $article->data["roomsQuantity"] }} pièces
@endif
@if (!empty($article->data["bedroomsQuantity"]))
{{ $article->data["bedroomsQuantity"] }} chambres
@endif
@if (!empty($article->data["floorQuantity"]))
{{ $article->data["floorQuantity"]-1 }} étages
@endif


<a href="{{ $article->link }}">👉 Voir le bien</a>