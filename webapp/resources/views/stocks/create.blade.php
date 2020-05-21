@extends('layouts.app')

@section('content')
    <div class="container px-5">
        {!! Form::open(['action' => 'StocksController@store', 'method' => 'POST']) !!}
            <div class="d-flex justify-content-between">
                <h1 class="page-header">Add Stock</h1> 
                <button type="submit" class="btn btn-success">
                    Save
                </button>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-6">
                        {{Form::label('ticker-name', 'Ticker')}}
                        {{Form::text('ticker-name', '', ['id' => 'symbol-search',
                                                    'class' => 'form-control', 
                                                    'placeholder' => 'Company, name or symbol'])}}
                        
                    </div>
                    <div class="col-6">
                        {{Form::label('symbol', 'Symbol')}}
                        {{Form::text('symbol', '', ['id' => 'symbol',
                                                    'class' => 'form-control', 
                                                    'placeholder' => 'Symbol',
                                                    'readonly' => 'true'])}}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button id="add-topic" type="button" class="btn btn-primary">
                    Add Topic
                </button>
            </div>
            <div class="form-group">
                {{Form::label('topics', 'Twitter-Topics')}}
                <table name="topics" class="table">
                    <tbody>
                    </tbody>
                </table>
            </div>
        {!! Form::close() !!}
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        var counter = 0;
        $(document).ready(function(){
            $("#add-topic").click(function(){
                var topicId = "topic".concat(counter);
                var markup = "<tr id=" + topicId  + "> \
                                <td><input type='text' name='topics[]' class='form-control' placeholder='Topic'/> \
                                </td><td class='text-right'> \
                                <button type='button' class='btn btn-danger' onclick='deleteTopic(" + topicId +");'> \
                                    Delete \
                                </button></td> \
                              </tr>";
                $("table tbody").append(markup);
                counter++;
            });
        });

        function deleteTopic(topicId) {
                $(topicId).remove();
        } 
    </script>

    <script type="text/javascript">
        var symbols = <?php echo json_encode($symbols['data']); ?>;
        var searchList = [];
        for (var i = 0; i < symbols.length; i++) { 
            searchList.push({'value': symbols[i]['symbol'],
                             'label': symbols[i]['name'].concat(' (', symbols[i]['symbol'],')')});
        }

        $(document).ready(function(){
            $('#symbol-search').autocomplete({
                minLength: 2,
                source: searchList,
                select: function( event, ui ) {
                    $('#symbol-search').val( ui.item.label );
                    $('#symbol').val( ui.item.value );
                    return false;
                }
            });
        });
    </script>
@endsection