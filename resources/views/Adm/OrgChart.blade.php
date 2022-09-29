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
        OrgChart.templates.ana.size = [250, 150];
        
        // OrgChart.templates.ana.field_0 = '<text data-width="230" data-text-overflow="multiline" style="font-size: 14px;" fill="#ffffff" x="125" y="95" text-anchor="middle" class="field_0">{val}</text><rect x="110" y="10" height="50" width="130" fill="#ffffff" stroke-width="1" stroke="#aeaeae" rx="7" ry="7"></rect>';
        OrgChart.templates.ana.field_0 = '<text data-width="230" data-text-overflow="multiline" style="font-size: 14px;" fill="#ffffff" x="125" y="105" text-anchor="middle" class="field_0">{val}</text>';
        OrgChart.templates.ana.field_1 = '<text data-width="150" data-text-overflow="multiline" style="font-size: 14px;" fill="#ffffff" x="170" y="30" text-anchor="middle" class="">{val}</text>';
        OrgChart.templates.ana.field_2 = '<text data-width="50" data-text-overflow="multiline" style="font-size: 14px;" fill="#ffffff" x="170" y="80" text-anchor="middle" class="">{val}</text>';
        
        OrgChart.templates.yellow = Object.assign({}, OrgChart.templates.ana);
        OrgChart.templates.yellow.field_1 = '<text data-width="150" data-text-overflow="multiline" style="font-size: 14px;" fill="#ffffff" x="170" y="30" text-anchor="middle" class="">{val}</text>';
        OrgChart.templates.yellow.field_0 = '<rect fill="none" stroke="red" stroke-width="5" x="0" y="0" rx="10" ry="10" width="250" height="150"></rect>';
        OrgChart.templates.yellow.field_2 = '<text data-width="50" data-text-overflow="multiline" style="font-size: 14px;" fill="#ffffff" x="170" y="80" text-anchor="middle" class="">{val}</text>';
        
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
                field_2: "jobs",
                img_0: "img",
            },

            tags: {
                yellow: {
                    template: "yellow"
                }
            }
        });

        chart.load(oServerData.lAreas);
    </script>
@endsection