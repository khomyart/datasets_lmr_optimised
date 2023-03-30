<p>
    Доброго дня, {{$maintainer["name"]}}!
</p>
<p>
    Нагадування про необхідність оновлення наборів даних: <br>
    (через простроченість термінів оновлення)
</p>

<table style="border: 1px solid black; border-collapse: collapse;">
    <tr>
        <td width="50" style="padding: 3px; text-align: center; border-bottom: 1px solid black;">
            <b>Номер</b>
        </td>
        <td width="300" style="padding: 3px; text-align: center; border-bottom: 1px solid black;">
            <b>Назва</b>
        </td>
        <td width="130" style="padding: 3px; text-align: center; border-bottom: 1px solid black;">
            <b>Оновити до</b>
        </td>
        <td width="150" style="padding: 3px; text-align: center; border-bottom: 1px solid black;">
            <b>Днів протерміновано</b>
        </td>
    </tr>
@foreach($maintainer["datasets"] as $dataset)
    <tr>
        <td style="padding: 3px; text-align: center; border-bottom: 1px solid black;">
            {{$loop->index + 1}}
        </td>
        <td style="padding: 3px; border-bottom: 1px solid black;">
        <a href="{{'https://data.lutskrada.gov.ua/dataset/'.$dataset['id']}}">{{$dataset["title"]}}</a>
        </td>
        <td style="padding: 3px; text-align: center; border-bottom: 1px solid black;">
            {{$dataset["next_update_at"]}}
        </td>
        <td style="padding: 3px; text-align: center; border-bottom: 1px solid black;">
            {{-1 * $dataset["days_to_update"]}}
        </td>
    </tr>
@endforeach
</table>

