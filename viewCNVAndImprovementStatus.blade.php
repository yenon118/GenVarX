@php
    include resource_path() . '/views/system/config.blade.php';

    $organism = $info['organism'];
    $chromosome = $info['chromosome'];
    $position_start = $info['position_start'];
    $position_end = $info['position_end'];
    $data_option = $info['cnv_data_option'];

    if ($organism == 'Osativa') {
        $div_text = 'subpopulation';
    } elseif ($organism == 'Athaliana') {
        $div_text = 'admixture group';
    } elseif ($organism == 'Zmays') {
        $div_text = 'improvement status';
    } else {
        $div_text = 'improvement status';
    }

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.plot.ly/plotly-3.0.0.min.js" charset="utf-8"></script>
</head>

<body>

    <h3>Figure:</h3>
    <div>
        <div id="cn_and_improvement_status_summary_plot_div">Loading CN and {{ $div_text }} summary plot...</div>
        <div id="cn_and_improvement_status_summary_table_div">Loading CN and {{ $div_text }} summary table...</div>
        <div>
            <hr>
            <h3>Full Table:</h3>
            <div id="cn_and_improvement_status_table_div">Loading CN and {{ $div_text }} table...</div>

</body>


<script src="{{ asset('system/home/GenVarX/js/viewCNVAndImprovementStatus.js') }}" type="text/javascript"></script>

<script type="text/javascript">
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
    var data_option = <?php if (isset($data_option)) {
        echo json_encode($data_option, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var div_text = <?php if (isset($div_text)) {
        echo json_encode($div_text, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;

    if (organism && chromosome && position_start && position_end && data_option) {
        $.ajax({
            url: 'queryCNVAndImprovementStatus/' + organism,
            type: 'GET',
            contentType: 'application/json',
            data: {
                Organism: organism,
                Chromosome: chromosome,
                Start: position_start,
                End: position_end,
                Data_Option: data_option
            },
            success: function(response) {
                res = JSON.parse(response);

                if (res) {
                    document.getElementById("cn_and_improvement_status_summary_plot_div").style.minHeight =
                        "800px";

                    // Render data
                    document.getElementById('cn_and_improvement_status_table_div').innerText = "";
                    document.getElementById('cn_and_improvement_status_table_div').innerHTML = "";
                    document.getElementById('cn_and_improvement_status_table_div').appendChild(
                        constructInfoTable(JSON.parse(JSON.stringify(res)))
                    );
                    document.getElementById('cn_and_improvement_status_table_div').style.maxHeight =
                        '1000px';
                    document.getElementById('cn_and_improvement_status_table_div').style.display =
                        'inline-block';
                    document.getElementById('cn_and_improvement_status_table_div').style.overflow =
                    'scroll';


                    // Summarize data
                    var phenotype = "";
                    if (organism == "Osativa") {
                        phenotype = "Subpopulation";
                    } else if (organism == "Athaliana") {
                        phenotype = "Admixture_Group";
                    } else if (organism == "Zmays") {
                        phenotype = "Improvement_Status";
                    } else {
                        phenotype = "Improvement_Status";
                    }
                    var result_dict = summarizeQueriedData(
                        JSON.parse(JSON.stringify(res)),
                        phenotype,
                        'CN'
                    );

                    var result_arr = result_dict['Data'];
                    var summary_array = result_dict['Summary'];

                    for (let i = 0; i < summary_array.length; i++) {
                        if (summary_array[i].hasOwnProperty('Number_of_Accession_with_Phenotype')) {
                            delete summary_array[i]['Number_of_Accession_with_Phenotype']
                        }
                        if (summary_array[i].hasOwnProperty('Number_of_Accession_without_Phenotype')) {
                            delete summary_array[i]['Number_of_Accession_without_Phenotype']
                        }
                    }

                    // Make plot
                    var cnAndImprovementStatusData = collectDataForFigure(result_arr, phenotype, 'CN');
                    plotFigure(cnAndImprovementStatusData, 'CN', phenotype + '_Summary',
                        'cn_and_improvement_status_summary_plot_div');

                    // Render data
                    document.getElementById('cn_and_improvement_status_summary_table_div').innerText = "";
                    document.getElementById('cn_and_improvement_status_summary_table_div').innerHTML = "";
                    document.getElementById('cn_and_improvement_status_summary_table_div').appendChild(
                        constructInfoTable(summary_array)
                    );
                    document.getElementById('cn_and_improvement_status_summary_table_div').style.overflow =
                        'scroll';
                }
            },
            error: function(xhr, status, error) {
                console.log('Error with code ' + xhr.status + ': ' + xhr.statusText);
                document.getElementById('cn_and_improvement_status_summary_plot_div').innerText = "";
                document.getElementById('cn_and_improvement_status_summary_plot_div').innerHTML = "";
                var p_tag = document.createElement('p');
                p_tag.innerHTML = "CN and " + div_text +
                    " distribution summary figure is not available due to lack of data!!!";
                document.getElementById('cn_and_improvement_status_summary_plot_div').appendChild(p_tag);
                document.getElementById('cn_and_improvement_status_summary_table_div').innerText = "";
                document.getElementById('cn_and_improvement_status_summary_table_div').innerHTML = "";
                var p_tag = document.createElement('p');
                p_tag.innerHTML = "CN and " + div_text +
                    " distribution summary figure is not available due to lack of data!!!";
                document.getElementById('cn_and_improvement_status_summary_table_div').appendChild(p_tag);
                document.getElementById('cn_and_improvement_status_table_div').innerText = "";
                document.getElementById('cn_and_improvement_status_table_div').innerHTML = "";
                var p_tag = document.createElement('p');
                p_tag.innerHTML = "CN and " + div_text +
                    " distribution table is not available due to lack of data!!!";
                document.getElementById('cn_and_improvement_status_table_div').appendChild(p_tag);
            }
        });
    } else {
        document.getElementById('cn_and_improvement_status_summary_plot_div').innerText = "";
        document.getElementById('cn_and_improvement_status_summary_plot_div').innerHTML = "";
        var p_tag = document.createElement('p');
        p_tag.innerHTML = "CN and " + div_text + " distribution summary figure is not available due to lack of data!!!";
        document.getElementById('cn_and_improvement_status_summary_plot_div').appendChild(p_tag);
        document.getElementById('cn_and_improvement_status_summary_table_div').innerText = "";
        document.getElementById('cn_and_improvement_status_summary_table_div').innerHTML = "";
        var p_tag = document.createElement('p');
        p_tag.innerHTML = "CN and " + div_text + " distribution summary figure is not available due to lack of data!!!";
        document.getElementById('cn_and_improvement_status_summary_table_div').appendChild(p_tag);
        document.getElementById('cn_and_improvement_status_table_div').innerText = "";
        document.getElementById('cn_and_improvement_status_table_div').innerHTML = "";
        var p_tag = document.createElement('p');
        p_tag.innerHTML = "CN and " + div_text + " distribution table is not available due to lack of data!!!";
        document.getElementById('cn_and_improvement_status_table_div').appendChild(p_tag);
    }
</script>
