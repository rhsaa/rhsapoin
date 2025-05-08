<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Tambah Pelanggaran</title>
</head>

<body>
  <h1>Tambah Pelanggaran</h1>
  <br><br>

  <a href="{{ route('pelanggaran.index') }}">Kembali</a><br><br>

  @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <form action="{{ route('pelanggaran.store') }}" method="POST">

    @csrf
    <label>Jenis Pelanggaran</label><br>
    <textarea name="jenis" id="jenis" cols="50" rows="7" value="{{ old('jenis') }}"></textarea>
    <br>

    <br>
    <label>Konsekuensi</label><br>
    <textarea name="konsekuensi" id="konsekuensi" cols="50" rows="7" value="{{ old('konsekuensi') }}"></textarea>
    <br>

    <br>
    <label>poin</label><br>
    <input type="text" id="poin" name="poin" value="{{ old('poin') }}"><br>
    <br><br>

    <input type="submit" value="Submit">
  </form>
  
</body>
</html>