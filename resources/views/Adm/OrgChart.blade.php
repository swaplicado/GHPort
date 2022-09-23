@extends('layouts.principal')

@section('headStyles')
    <style>    
        .field_0 {
            /* font-family: Impact; */
            text-transform: uppercase;
            fill: #ffffff;
            /* font-size: 15px !important; */
        }
    </style>    
@endsection

@section('headJs')
    <script src="{{ asset('js/orgchart.js') }}"></script>
@endsection

@section('content')
    <div id="tree"></div>
@endsection

@section('scripts')
    <script>
        function GlobalData(){
            this.lAreas = <?php echo json_encode($lAreas); ?>;
        }
        var oServerData = new GlobalData();
    </script>
    <script>
        OrgChart.templates.ana.field_0 = '<text data-width="230" data-text-overflow="multiline" style="font-size: 14px;" fill="#ffffff" x="125" y="95" text-anchor="middle" class="field_0">{val}</text>';
        var chart = new OrgChart(document.getElementById("tree"), {
            // mouseScrool: OrgChart.action.none,
            toolbar: {
                zoom: true
            },
            mouseScrool: OrgChart.action.scroll,
            enableDragDrop: false,
            editForm: {
                generateElementsFromFields: false,
                buttons: {
                    edit: null,
                    share: null,
                    pdf: null,
                    remove: null
                },
                elements: [
                        { type: 'textbox', label: 'Nombre', binding: 'name' },
                        { type: 'textbox', label: 'Titulo', binding: 'title' }
                    ]
            },
            nodeBinding: {
                field_0: "name",
                field_1: "title",
                img_0: "img"
            },
        });

        chart.load(oServerData.lAreas);
    </script>
@endsection