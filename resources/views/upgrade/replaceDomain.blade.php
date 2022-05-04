@extends('phobrv::adminlte3.layout')

@section('header')
<h1>Upgrade Data</h1>
@endsection

@section('content')
<div class="card">
	<div class="card-body">
		<form class="form-horizontal" action="{{route('upgrade.run')}}" method="post">
			@csrf
			<div class="form-group">
				<label for="inputEmail3" class="col-sm-3 control-label">Accept</label>
				<div class="col-sm-1">
					<input type="checkbox" name="choose[]"  value="replace_domain">
				</div>
				<div class="col-sm-4">
					<input type="text" name="domain_old" value="" class="form-control" placeholder="Domain old">
				</div>
				<div class="col-sm-4">
					<input type="text" name="domain_new" value=""  class="form-control" placeholder="Domain new">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<button class="btn btn-primary">Run</button>
				</div>
			</div>
		</form>
	</div>
</div>

@endsection

@section('styles')

@endsection

@section('scripts')

@endsection