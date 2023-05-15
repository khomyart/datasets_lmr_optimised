Доброго дня, {{$maintainer["name"]}}!

Нагадування про необхідність оновлення наборів даних:
(через простроченість термінів оновлення)

@foreach($maintainer["datasets"] as $dataset)
{{$loop->index + 1}}. {{$dataset["title"]}}
    - прострочено з: {{$dataset["next_update_at"]}};
    - кількість днів: {{-1 * $dataset["days_to_update"]}};
    - посилання: {{$datasourceUrl.$dataset['id']}};
@endforeach


