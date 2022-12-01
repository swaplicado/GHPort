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
<script src="https://d3js.org/d3.v7.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-org-chart@2"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>
@endsection

@section('content')
<div class="card shadow mb-4" id="orgchart">
        @include('Adm.modal_OrgChart')
        <div class="card-header">
            <h3>
                <b>ORGANIGRAMA AETH</b>
                <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:organigrama" target="_blank">
                    <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
                </a>
            </h3>
        </div>
        <div class="card-body">
            <div class="chart-container" style="height: 1200px; background-color: #f6f6f6">
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function GlobalData(){
            this.lAreas = <?php echo json_encode($lAreas); ?>;
        }
        var oServerData = new GlobalData();
    </script>
    <script src="{{asset('myApp/Adm/vue_OrgChart.js')}}"></script>
    <script>
        function showModal(id, name, area, jobs, img){
            app.showModal(id, name, area, jobs, img);
        }
    </script>
    <script>
      var chart;
      var dataFlattened = oServerData.lAreas;
        chart = new d3.OrgChart()
          .container('.chart-container')
          .data(dataFlattened)
          .nodeWidth((d) => 250)
          .initialZoom(0.7)
          .nodeHeight((d) => 200)
          .childrenMargin((d) => 40)
          .compactMarginBetween((d) => 15)
          .compactMarginPair((d) => 80)
          .nodeContent(function (d, i, arr, state) {
            return `
            <div style="padding-top:30px;background-color:none;margin-left:1px;height:${
              d.height
            }px;border-radius:2px;overflow:visible" onclick="showModal('${d.data.id}', '${d.data.name}', '${d.data.positionName}', '${d.data.jobs}', '${d.data.imageUrl}')">
              <div style="height:${
                d.height - 32
              }px;padding-top:0px;background-color:white;border:1px solid lightgray;">

                <img src=" ${
                  d.data.imageUrl
                }" style="margin-top:-30px;margin-left:${d.width / 2 - 30}px;border-radius:100px;width:60px;height:60px;" />
               
               <div style="margin-top:-30px;background-color:#3AB6E3;height:10px;width:${
                 d.width - 2
               }px;border-radius:1px"></div>

               <div style="padding:20px; padding-top:35px;text-align:center">
                   <div style="color:#111672;font-size:16px;font-weight:bold"> ${
                     d.data.name
                   } </div>
                   <div style="color:#404040;font-size:16px;margin-top:4px"> ${
                     d.data.positionName
                   } </div>
               </div> 
               <div style="display:flex;justify-content:space-between;padding-left:15px;padding-right:15px;">
                 <div><b>Directos:</b> ${d.data._directSubordinates}</div>  
                 <div><b>Todos:</b> ${d.data._totalSubordinates}</div>    
                 <div><b>Puestos:</b> ${d.data.jobs}</div>    
               </div>
              </div>     
      </div>
  `;
          })
          .render();

          chart.expandAll();
    </script>
@endsection