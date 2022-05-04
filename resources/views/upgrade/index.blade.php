@extends('phobrv::adminlte3.layout')

@section('header')
<h1>Upgrade Data</h1>
@endsection

@section('content')
<div class="card">
	<div class="card-body">
		<form class="form-horizontal" action="{{route('upgrade.run')}}" method="post">
			@csrf
			<div class="row">
				<div class="col-sm-4">
					<label>Important</label>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">User</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="user">
						</div>
					</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Post Group</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="post_group">
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<label>Longer</label>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Post</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="post">
						</div>
					</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Replace post content</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="replace_post_content">
						</div>
					</div>

				</div>
				<div class="col-sm-4">
					<label>No Important</label>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Menu</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="menu">
						</div>
					</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Udate box sidebar Menu</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="update_menu_box_sidebar">
						</div>
					</div>

					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Question</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="question">
						</div>
					</div>

					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Config web</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="config_web">
						</div>
					</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">DrugStore</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="drugstore">
						</div>
					</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Author</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="author">
						</div>
					</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Video</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="video">
						</div>
					</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">Album</label>
						<div class="col-sm-8">
							<input type="checkbox" name="choose[]" value="album">
						</div>
					</div>
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