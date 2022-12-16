class SReDrawTables {
    reDrawVacationsTable(id_table, data){
        let oDateUtils = new SDateUtils();
        let dataVac = [];
        let footer = [];
        for(let vac of data.oUser.vacation){
            dataVac.push(
                [
                    oDateUtils.formatDate(vac.date_start) + ' a ' + oDateUtils.formatDate(vac.date_end),
                    vac.id_anniversary,
                    vac.vacation_days,
                    vac.num_vac_taken,
                    vac.expired,
                    vac.request,
                    vac.remaining
                ]
            );
        }
        footer =
            [
                '',
                'Total',
                data.oUser.tot_vacation_days,
                data.oUser.tot_vacation_taken,
                data.oUser.tot_vacation_expired,
                data.oUser.tot_vacation_request,
                data.oUser.tot_vacation_remaining
            ];

        table[id_table].clear().draw();
        document.getElementById(id_table).deleteTFoot();
        table[id_table].rows.add(dataVac).draw();
        let ofoot = document.getElementById(id_table).createTFoot();
        var row = ofoot.insertRow(0);
        var count = 0;
        for(var fo of footer){
            let cell = row.insertCell(count);
            if(fo == 'Total'){
                cell.classList.add('myTdHead');
            }
            cell.innerHTML = fo;
            count++;
        }
    }
}