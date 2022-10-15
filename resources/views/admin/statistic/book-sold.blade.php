<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Thống kê hàng đã bán</title>
	@include('layout.link')
</head>
<body>
	@include('admin.menu_admin')
	
	<div class="container mt-5">
		<h1 class="title-admin">Thống kê hàng đã bán</h1>
	</div>

	<div class="container-fluid mt-5">
		<form action="{{ route('book-sold') }}" method="GET" class="row mt-3">
			<div class="col-3">
				<input type="text" name="name" placeholder="Tên sản phẩm" class="form-control w-100">
			</div>
			<div class="col-2">
				<input type="date" name="from_date" class="form-control w-100">
			</div>
			<div class="col-2">
				<input type="date" name="to_date" class="form-control w-100">
			</div>
			<div class="col-2">
				<button class="btn btn-success w-75">Tìm kiếm</button>
			</div>
		</form>
		<table class="table table-bordered table-striped mt-3 mb-5">
			<tr class="text-center">
				<th>STT</th>
				<th>Mã sản phẩm</th>
				<th>Tên sản phẩm</th>
				<th>Số lượng đã bán</th>
				<th>Chiết khấu</th>
				<th>Tiền hàng đã bán</th>
				<th>Tiền hàng</th>
			</tr>
			<tr>
				<td colspan="3" class="text-center">
					Tổng {{ $books->count() }} sản phẩm
				</td>
				<td class="text-center">{{ number_format($books->sum('quantity_sold')) }}</td>
				<td class="text-center">{{ number_format($books->sum('total_money_sold')) }}</td>
				<td class="text-center">{{ $books->sum('discount') ? '-'.number_format($books->sum('discount')) : 0 }}</td>
				<td class="text-center">{{ $books->sum('total_money_sold') - $books->sum('discount') }}</td>
			</tr>
			<?php $stt = 1; ?>
			@foreach ($books as $book)
			<tr>
				<td class="text-center">{{ $stt++ }}</td>
				<td>{{ $book->code }}</td>
				<td>{{ $book->name }}</td>
				<td class="text-center">{{ number_format($book->quantity_sold) }}</td>
				<td class="text-center">{{ number_format($book->total_money_sold) }}</td>
				<td class="text-center">{{ $book->discount ? '-'.number_format($book->discount) : 0 }}</td>
				<td class="text-center">{{ number_format($book->total_money_sold - $book->discount) }}</td>
			</tr>
			@endforeach
		</table>
		<div class="text-center">
			{{ $books->appends([
				'name' => $request->name,
				'from_date' => $request->from_date,
				'to_date' => $request->to_date,
			])->links()}}
		</div>
	</div>

	@include('layout.script')
</body>
</html>