<p>
    Доброго дня, {{$maintainer["name"]}}!
</p>
<p>
    Нагадування про необхідність оновлення наборів даних: <br>
    (через простроченість термінів оновлення)
</p>

@foreach($maintainer["datasets"] as $dataset)
<p>{{$loop->index + 1}}. {{$dataset["title"]}}
    (прострочено з: {{$dataset["next_update_at"]}},
    кількість днів: {{-1 * $dataset["days_to_update"]}},
    посилання: <a>{{$datasourceUrl.$dataset['id']}}</a>)</p>
@endforeach


