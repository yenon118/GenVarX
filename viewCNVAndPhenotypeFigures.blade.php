@php
    include resource_path() . '/views/system/config.blade.php';

    $organism = $info['organism'];
    $chromosome = $info['chromosome'];
    $position_start = $info['position_start'];
    $position_end = $info['position_end'];
    $width = $info['width'];
    $strand = $info['strand'];
    $cnv_data_option = $info['cnv_data_option'];
    $cn_array = $info['cn_array'];
    $phenotype = $info['phenotype'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.plot.ly/plotly-3.0.0.min.js" charset="utf-8"></script>
</head>

<body>

    <h3>Queried CNV and Phenotype:</h3>
    <div style='width:auto; height:auto; overflow:visible; max-height:1000px;'>
        <table style='text-align:center; border:3px solid #000;'>
            <tr>
                <th style="border:1px solid black; min-width:80px;">Chromsome</th>
                <th style="border:1px solid black; min-width:80px;">Position Start</th>
                <th style="border:1px solid black; min-width:80px;">Position End</th>
                <th style="border:1px solid black; min-width:80px;">Width</th>
                <th style="border:1px solid black; min-width:80px;">Strand</th>
                <th style="border:1px solid black; min-width:80px;">Data Option</th>
                <th style="border:1px solid black; min-width:80px;">CN</th>
                <th style="border:1px solid black; min-width:80px;">Phenotype</th>
            </tr>
            <tr bgcolor="#DDFFDD">
                <td style="border:1px solid black; min-width:80px;">{{ $chromosome }}</td>
                <td style="border:1px solid black; min-width:80px;">{{ $position_start }}</td>
                <td style="border:1px solid black; min-width:80px;">{{ $position_end }}</td>
                <td style="border:1px solid black; min-width:80px;">{{ $width }}</td>
                <td style="border:1px solid black; min-width:80px;">{{ $strand }}</td>
                <td style="border:1px solid black; min-width:80px;">{{ $cnv_data_option }}</td>
                <td style="border:1px solid black; min-width:80px;">{{ implode(',', $cn_array) }}</td>
                <td style="border:1px solid black; min-width:80px;">{{ $phenotype }}</td>
            </tr>
        </table>
    </div>
    <br /><br />

    <h3>Figures:</h3>
    <div id="cn_section_div">
        <div id="cn_figure_div">Loading CN plot...</div>
        <div id="cn_summary_table_div">Loading CN summary table...</div>
    </div>
    <hr />
    <div id="improvement_status_summary_figure_div"></div>
    <!-- <div id="status_figure_div">Loading status plot...</div> -->

</body>

<script src="{{ asset('system/home/GenVarX/js/viewCNVAndPhenotypeFigures.js') }}" type="text/javascript"></script>

<script type="text/javascript" language="javascript">
    var organism = <?php if (isset($organism)) {
        echo json_encode($organism, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var chromosome = <?php if (isset($chromosome)) {
        echo json_encode($chromosome, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var position_start = <?php if (isset($position_start)) {
        echo json_encode($position_start, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var position_end = <?php if (isset($position_end)) {
        echo json_encode($position_end, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var cnv_data_option = <?php if (isset($cnv_data_option)) {
        echo json_encode($cnv_data_option, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var phenotype = <?php if (isset($phenotype)) {
        echo json_encode($phenotype, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var cn_array = <?php if (isset($cn_array) && is_array($cn_array) && !empty($cn_array)) {
        echo json_encode($cn_array, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;

    if (organism == "Osativa") {
        document.getElementById('improvement_status_summary_figure_div').innerHTML =
            "Loading subpopulation summary plot...";
        summaryPhenotype = "Subpopulation";
    } else if (organism == "Athaliana") {
        document.getElementById('improvement_status_summary_figure_div').innerHTML =
            "Loading admixture group summary plot...";
        summaryPhenotype = "Admixture_Group";
    } else if (organism == "Zmays") {
        document.getElementById('improvement_status_summary_figure_div').innerHTML =
            "Loading improvement status summary plot...";
        summaryPhenotype = "Improvement_Status";
    }

    if (organism && chromosome && position_start && position_end && cnv_data_option && phenotype && cn_array.length >
        0) {
        $.ajax({
            url: 'queryCNVAndPhenotypeFigures/' + organism,
            type: 'GET',
            contentType: 'application/json',
            data: {
                Organism: organism,
                Chromosome: chromosome,
                Start: position_start,
                End: position_end,
                Data_Option: cnv_data_option,
                CN: cn_array,
                Phenotype: phenotype
            },
            success: function(response) {
                res = JSON.parse(response);

                if (res && phenotype) {

                    document.getElementById("cn_figure_div").style.minHeight = "800px";
                    document.getElementById("improvement_status_summary_figure_div").style.minHeight =
                        "800px";
                    // document.getElementById("status_figure_div").style.minHeight = "800px";

                    // Summarize data
                    var result_dict = summarizeQueriedData(
                        JSON.parse(JSON.stringify(res)),
                        phenotype,
                        'CN'
                    );

                    var result_arr = result_dict['Data'];
                    var summary_array = result_dict['Summary'];

                    var cnData = collectDataForFigure(result_arr, phenotype, 'CN');
                    var cnAndImprovementStatusData = collectDataForFigure(result_arr, summaryPhenotype,
                        'CN');
                    // var statusData = collectDataForFigure(result_arr, phenotype, 'Status');

                    plotFigure(cnData, 'CN', 'CN', 'cn_figure_div');
                    plotFigure(cnAndImprovementStatusData, 'CN', summaryPhenotype + '_Summary',
                        'improvement_status_summary_figure_div');
                    // plotFigure(statusData, 'Status', 'Status', 'status_figure_div');

                    // Render summarized data
                    document.getElementById('cn_summary_table_div').innerText = "";
                    document.getElementById('cn_summary_table_div').innerHTML = "";
                    document.getElementById('cn_summary_table_div').appendChild(
                        constructInfoTable(summary_array)
                    );
                    document.getElementById('cn_summary_table_div').style.overflow = 'scroll';
                }
            },
            error: function(xhr, status, error) {
                console.log('Error with code ' + xhr.status + ': ' + xhr.statusText);
                document.getElementById('cn_figure_div').innerText = "";
                document.getElementById('cn_summary_table_div').innerHTML = "";
                document.getElementById('improvement_status_summary_figure_div').innerHTML = "";
                // document.getElementById('status_figure_div').innerHTML="";
                var p_tag = document.createElement('p');
                p_tag.innerHTML = "CN distribution figure is not available due to lack of data!!!";
                document.getElementById('cn_figure_div').appendChild(p_tag);
                var p_tag = document.createElement('p');
                p_tag.innerHTML = "CN summary table is not available due to lack of data!!!";
                document.getElementById('cn_summary_table_div').appendChild(p_tag);
                var p_tag = document.createElement('p');
                p_tag.innerHTML = summaryPhenotype +
                    " summary figure is not available due to lack of data!!!";
                document.getElementById('improvement_status_summary_figure_div').appendChild(p_tag);
                // var p_tag = document.createElement('p');
                // p_tag.innerHTML = "Status distribution figure is not available due to lack of data!!!";
                // document.getElementById('status_figure_div').appendChild(p_tag);
            }
        });
    } else {
        document.getElementById('cn_figure_div').innerText = "";
        document.getElementById('cn_summary_table_div').innerHTML = "";
        document.getElementById('improvement_status_summary_figure_div').innerHTML = "";
        // document.getElementById('status_figure_div').innerHTML="";
        var p_tag = document.createElement('p');
        p_tag.innerHTML = "CN distribution figure is not available due to lack of data!!!";
        document.getElementById('cn_figure_div').appendChild(p_tag);
        var p_tag = document.createElement('p');
        p_tag.innerHTML = "CN summary table is not available due to lack of data!!!";
        document.getElementById('cn_summary_table_div').appendChild(p_tag);
        var p_tag = document.createElement('p');
        p_tag.innerHTML = summaryPhenotype + " summary figure is not available due to lack of data!!!";
        document.getElementById('improvement_status_summary_figure_div').appendChild(p_tag);
        // var p_tag = document.createElement('p');
        // p_tag.innerHTML = "Status distribution figure is not available due to lack of data!!!";
        // document.getElementById('status_figure_div').appendChild(p_tag);
    }
</script>
