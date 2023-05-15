Звіт про розсилку (нагадування):

Успішно:
@foreach($result["success"] as $user)

{{$loop->index + 1}}. {{$user["name"]}} ({{$user["email"]}})
    @foreach($user["datasets"] as $dataset)
    - {{$dataset["title"]}}
    @endforeach
@endforeach

Невдало:
@foreach($result["error"] as $user)

{{$loop->index + 1}}. {{$user["name"]}} ({{$user["email"]}})
    @foreach($user["datasets"] as $dataset)
    - {{$dataset["title"]}}
    @endforeach
@endforeach

