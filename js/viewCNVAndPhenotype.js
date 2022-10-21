function updateCNInAccordion() {
    var cn_data_option = document.getElementById("cnv_data_option_1").value;
    var div_cn_in_accordion = document.getElementById("div_cn_in_accordion");
    div_cn_in_accordion.innerHTML = '';
    if (cn_data_option == "Consensus_Regions") {
        for (let i = 0; i < 9; i++) {
            var input_tag = document.createElement('input');
            input_tag.type = 'checkbox';
            input_tag.id = 'CN' + i;
            input_tag.name = 'CN' + i;
            input_tag.value = 'CN' + i;
            var label_tag = document.createElement('label');
            label_tag.htmlFor = 'CN' + i;
            label_tag.setAttribute("style", "margin-right:10px;");
            label_tag.innerHTML = 'CN' + i;
            div_cn_in_accordion.appendChild(input_tag);
            div_cn_in_accordion.appendChild(label_tag);
        }
    } else if (cn_data_option == "Individual_Hits") {
        for (let i = 0; i < 9; i++) {
            if (i != 2) {
                var input_tag = document.createElement('input');
                input_tag.type = 'checkbox';
                input_tag.id = 'CN' + i;
                input_tag.name = 'CN' + i;
                input_tag.value = 'CN' + i;
                var label_tag = document.createElement('label');
                label_tag.htmlFor = 'CN' + i;
                label_tag.setAttribute("style", "margin-right:10px;");
                label_tag.innerHTML = 'CN' + i;
                div_cn_in_accordion.appendChild(input_tag);
                div_cn_in_accordion.appendChild(label_tag);
            }
        }
    }
}


function convertJsonToCsv(jsonObject) {
    let csvString = '';
    let th_keys = Object.keys(jsonObject[0]);
    for (let i = 0; i < th_keys.length; i++) {
        th_keys[i] = "\"" + th_keys[i] + "\"";
    }
    csvString += th_keys.join(',') + '\n';
    for (let i = 0; i < jsonObject.length; i++) {
        let tr_keys = Object.keys(jsonObject[i]);
        for (let j = 0; j < tr_keys.length; j++) {
            csvString += ((jsonObject[i][tr_keys[j]] === null) || (jsonObject[i][tr_keys[j]] === undefined)) ? '\"\"' : "\"" + jsonObject[i][tr_keys[j]] + "\"";
            if (j < (tr_keys.length-1)) {
                csvString += ',';
            }
        }
        csvString += '\n';
    }
    return csvString;
}


function createAndDownloadCsvFile(csvString, filename) {
    let dataStr = "data:text/csv;charset=utf-8," + encodeURI(csvString);
    let downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", filename + ".csv");
    document.body.appendChild(downloadAnchorNode); // required for firefox
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
}


function uncheck_all_cn() {
    let ids = document.querySelectorAll('input[id^=CN]');

    for (let i = 0; i < ids.length; i++) {
        if (ids[i].checked) {
            ids[i].checked = false;
        }
    }
}


function check_all_cn() {
    let ids = document.querySelectorAll('input[id^=CN]');

    for (let i = 0; i < ids.length; i++) {
        if (!ids[i].checked) {
            ids[i].checked = true;
        }
    }
}


function uncheck_all_phenotypes(organism) {
    let ids = document.querySelectorAll('input[id^='+organism+'_phenotype_]');

    for (let i = 0; i < ids.length; i++) {
        if (ids[i].checked) {
            ids[i].checked = false;
        }
    }
}


function check_all_phenotypes(organism) {
    let ids = document.querySelectorAll('input[id^='+organism+'_phenotype_]');

    for (let i = 0; i < ids.length; i++) {
        if (!ids[i].checked) {
            ids[i].checked = true;
        }
    }
}


function uncheck_all(organism) {
    let ids = document.querySelectorAll('input[id^=CN],input[id^='+organism+'_phenotype_]');

    for (let i = 0; i < ids.length; i++) {
        if (ids[i].checked) {
            ids[i].checked = false;
        }
    }
}


function check_all(organism) {
    let ids = document.querySelectorAll('input[id^=CN],input[id^='+organism+'_phenotype_]');

    for (let i = 0; i < ids.length; i++) {
        if (!ids[i].checked) {
            ids[i].checked = true;
        }
    }
}


function constructInfoTable(organism, res, chromosome, position_start, position_end, cnv_data_option, cn_array) {

    // Create table
    let detail_table = document.createElement("table");
    detail_table.setAttribute("style", "width:100%; text-align:center; border:3px solid #000;");
    let detail_header_tr = document.createElement("tr");

    let header_array = Object.keys(res[0]);
    for (let i = 0; i < header_array.length; i++) {

        if (header_array[i] == "Chromosome" || header_array[i] == "Start" || header_array[i] == "End" || header_array[i] == "Width" || header_array[i] == "Strand" || header_array[i] == "Accession" || header_array[i] == "CN" || header_array[i] == "Status" || header_array[i] == "Improvement_Status" || header_array[i] == "Admixture_Group" || header_array[i] == "Subpopulation") {
            var detail_th = document.createElement("th");
            detail_th.setAttribute("style", "border:1px solid black; min-width:80px; height:18.5px;");
            detail_th.innerHTML = header_array[i];
            detail_header_tr.appendChild(detail_th);
        } else {
            // Create clickable links in the header
            var detail_th = document.createElement("th");
            detail_th.setAttribute("style", "border:1px solid black; min-width:80px; height:18.5px;");
            var detail_a = document.createElement('a');
            detail_a.target = "_blank";
            detail_a.href = "../viewCNVAndPhenotypeFigures/"+organism+"?chromosome_1=" + chromosome + "&position_start_1=" + position_start + "&position_end_1=" + position_end + "&width_1=" + res[0]['Width'] + "&strand_1=" + res[0]['Strand'] + "&cnv_data_option_1=" + cnv_data_option + "&phenotype_1=" + header_array[i] +"&cn_1=" + cn_array.join("%0D%0A");;
            detail_a.innerHTML = header_array[i];
            detail_th.appendChild(detail_a);
            detail_header_tr.appendChild(detail_th);
        }

    }

    detail_table.appendChild(detail_header_tr);

    for (let i = 0; i < res.length; i++) {
        var detail_tr = document.createElement("tr");
        detail_tr.style.backgroundColor = ((i%2) ? "#FFFFFF" : "#DDFFDD");
        for (let j = 0; j < header_array.length; j++) {
            var detail_td = document.createElement("td");
            detail_td.setAttribute("style", "border:1px solid black; min-width:80px; height:18.5px;");
            detail_td.innerHTML = res[i][header_array[j]];
            detail_tr.appendChild(detail_td);
        }
        detail_table.appendChild(detail_tr);
    }

    return detail_table;
}


function queryPhenotypeDescription(organism) {
    $.ajax({
        url: 'queryPhenotypeDescription/'+organism,
        type: 'GET',
        contentType: 'application/json',
        data: {
            Organism: organism
        },
        success: function (response) {
            res = JSON.parse(response);

            if (res.length > 0) {
                let csvString = convertJsonToCsv(res);
                createAndDownloadCsvFile(csvString, String(organism) + "_phenotype_description");
            }

        },
        error: function (xhr, status, error) {
            console.log('Error with code ' + xhr.status + ': ' + xhr.statusText);
        }
    });
}


function qeuryCNVAndPhenotype(organism) {

    // Clear data appended to the div tags, if there is any
    if (document.getElementById('CNV_and_Phenotye_detail_table').innerHTML) {
        document.getElementById('CNV_and_Phenotye_detail_table').innerHTML = null;
    }

    let chromosome_1 = document.getElementById('chromosome_1').value;
    let position_start_1 = document.getElementById('position_start_1').value;
    let position_end_1 = document.getElementById('position_end_1').value;
    let cnv_data_option_1 = document.getElementById("cnv_data_option_1").value;

    let cn_ids = document.querySelectorAll('input[id^=CN]');
    let cn_array = [];

    let phenotype_ids = document.querySelectorAll('input[id^='+organism+'_phenotype_]');
    let phenotype_array = [];

    for (let i = 0; i < cn_ids.length; i++) {
        if (cn_ids[i].checked) {
            cn_array.push(cn_ids[i].value);
        }
    }

    for (let i = 0; i < phenotype_ids.length; i++) {
        if (phenotype_ids[i].checked) {
            phenotype_array.push(phenotype_ids[i].value);
        }
    }


    if (chromosome_1 && position_start_1 && position_end_1 && cn_array.length > 0) {
        $.ajax({
            url: 'qeuryCNVAndPhenotype/'+organism,
            type: 'GET',
            contentType: 'application/json',
            data: {
                Organism: organism,
                Chromosome: chromosome_1,
                Start: position_start_1,
                End: position_end_1,
                Data_Option: cnv_data_option_1,
                CN: cn_array,
                Phenotype: phenotype_array
            },
            success: function (response) {
                res = JSON.parse(response);

                if (res.length > 0) {
                    document.getElementById('CNV_and_Phenotye_detail_table').appendChild(
                        constructInfoTable(organism, res, chromosome_1, position_start_1, position_end_1, cnv_data_option_1, cn_array)
                    );
                    document.getElementById('CNV_and_Phenotye_detail_table').style.overflow = 'scroll';
                } else {
                    let error_message = document.createElement("p");
                    error_message.innerHTML = "Please select CN to view data!!!";
                    document.getElementById('CNV_and_Phenotye_detail_table').appendChild(error_message);
                    document.getElementById('CNV_and_Phenotye_detail_table').style.overflow = 'visible';
                }
                
            },
            error: function (xhr, status, error) {
                console.log('Error with code ' + xhr.status + ': ' + xhr.statusText);
            }
        });
    } else {
        let error_message = document.createElement("p");
        error_message.innerHTML = "Please select CN to view data!!!";
        document.getElementById('CNV_and_Phenotye_detail_table').appendChild(error_message);
        document.getElementById('CNV_and_Phenotye_detail_table').style.overflow = 'visible';
    }

}


function downloadCNVAndPhenotype(organism) {

    let chromosome_1 = document.getElementById('chromosome_1').value;
    let position_start_1 = document.getElementById('position_start_1').value;
    let position_end_1 = document.getElementById('position_end_1').value;
    let cnv_data_option_1 = document.getElementById("cnv_data_option_1").value;

    let cn_ids = document.querySelectorAll('input[id^=CN]');
    let cn_array = [];

    let phenotype_ids = document.querySelectorAll('input[id^='+organism+'_phenotype_]');
    let phenotype_array = [];

    for (let i = 0; i < cn_ids.length; i++) {
        if (cn_ids[i].checked) {
            cn_array.push(cn_ids[i].value);
        }
    }

    for (let i = 0; i < phenotype_ids.length; i++) {
        if (phenotype_ids[i].checked) {
            phenotype_array.push(phenotype_ids[i].value);
        }
    }

    if (chromosome_1 && position_start_1 && position_end_1 && cn_array.length > 0) {
        $.ajax({
            url: 'qeuryCNVAndPhenotype/'+organism,
            type: 'GET',
            contentType: 'application/json',
            data: {
                Organism: organism,
                Chromosome: chromosome_1,
                Start: position_start_1,
                End: position_end_1,
                Data_Option: cnv_data_option_1,
                CN: cn_array,
                Phenotype: phenotype_array
            },
            success: function (response) {
                res = JSON.parse(response);

                if (res.length > 0) {
                    let csvString = convertJsonToCsv(res);
                    createAndDownloadCsvFile(csvString, String(organism) + "_" + String(chromosome_1) + "_" + String(position_start_1) + "_" + String(position_end_1) + "_data");
                    
                } else {
                    alert("Please select CN to download data!!!");
                }
                
            },
            error: function (xhr, status, error) {
                console.log('Error with code ' + xhr.status + ': ' + xhr.statusText);
            }
        });
    } else {
        alert("Please select CN to download data!!!");
    }

}