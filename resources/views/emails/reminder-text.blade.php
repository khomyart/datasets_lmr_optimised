Доброго дня, {{$maintainer["name"]}}!

Нагадування про необхідність оновлення наборів даних:
(через наближення кінцевого терміну оновлення)

@foreach($maintainer["datasets"] as $dataset)
{{$loop->index + 1}}. {{$dataset["title"]}}
    - кінцева дата оновлення: {{$dataset["next_update_at"]}};
    - днів залишилось: {{$dataset["days_to_update"]}};
    - посилання: {{$datasourceUrl.$dataset['id']}};
@endforeach

