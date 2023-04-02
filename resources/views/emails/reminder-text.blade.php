<p>
    Доброго дня, {{$maintainer["name"]}}!
</p>
<p>
    Нагадування про необхідність оновлення наборів даних: <br>
    (через наближення кінцевого терміну оновлення)
</p>

@foreach($maintainer["datasets"] as $dataset)
<p>{{$loop->index + 1}}. {{$dataset["title"]}}
    (кінцева дата оновлення: {{$dataset["next_update_at"]}},
    днів залишилось: {{$dataset["days_to_update"]}},
    посилання: <a>{{$datasourceUrl.$dataset['id']}}</a>)</p>
@endforeach

