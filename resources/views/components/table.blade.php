<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0 text-dark">{{ $title }}</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover dataTable">
                <thead>
                <tr>
                    <th>#</th>
                    @foreach($columns as $key => $column)
                        <th>{{ $column }}</th>
                    @endforeach
                    <th style="width: 30px"></th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
