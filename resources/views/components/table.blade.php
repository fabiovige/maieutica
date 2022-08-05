<div class="card">
    <div class="card-header">
        {{ $title }}
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
