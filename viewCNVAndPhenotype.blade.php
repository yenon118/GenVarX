@php
    include resource_path() . '/views/system/config.blade.php';

    $organism = $info['organism'];
    $chromosome = $info['chromosome'];
    $position_start = $info['position_start'];
    $position_end = $info['position_end'];
    $cnv_data_option = $info['cnv_data_option'];
    $phenotype_selection_arr = $info['phenotype_selection_arr'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    </link>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"
        integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>
    <script>
        $(function() {
            $("#accordion").accordion({
                active: false,
                collapsible: true
            });
        });
    </script>
</head>

<body>

    <div id="accordion">
        <h3>Region</h3>
        <div>
            <label for="chromosome_1">Chromosome:</label>
            <input type="text" id="chromosome_1" name="chromosome_1" size="30" value="{{ $chromosome }}"
                style="margin-right:50px;">

            <label for="position_start_1">Start:</label>
            <input type="text" id="position_start_1" name="position_start_1" size="30"
                value="{{ $position_start }}" style="margin-right:50px;">

            <label for="position_end_1">End:</label>
            <input type="text" id="position_end_1" name="position_end_1" size="30" value="{{ $position_end }}"
                style="margin-right:50px;">

            <label for="cnv_data_option_1"><b>Data Option:</b></label>
            <select name="cnv_data_option_1" id="cnv_data_option_1" onchange="updateCNInAccordion()">
                <option value="Individual_Hits" {{ $cnv_data_option == 'Individual_Hits' ? 'selected' : '' }}>
                    Individual Hits</option>
                <option value="Consensus_Regions" {{ $cnv_data_option == 'Consensus_Regions' ? 'selected' : '' }}>
                    Consensus Regions</option>
            </select>
        </div>
        <h3>CN</h3>
        <div id=div_cn_in_accordion>
        </div>
        @php
            if (
                isset($phenotype_selection_arr) &&
                is_array($phenotype_selection_arr) &&
                !empty($phenotype_selection_arr)
            ) {
                echo '<h3>Phenotype</h3>';
                echo '<div>';
                for ($i = 0; $i < count($phenotype_selection_arr); $i++) {
                    echo "<input type=\"checkbox\" id=\"" .
                        $phenotype_selection_arr[$i]->ID .
                        "\" name=\"" .
                        $phenotype_selection_arr[$i]->ID .
                        "\" value=\"" .
                        $phenotype_selection_arr[$i]->Phenotype .
                        "\"><label for=\"" .
                        $phenotype_selection_arr[$i]->ID .
                        "\" style=\"margin-right:10px;\">" .
                        $phenotype_selection_arr[$i]->Phenotype .
                        '</label>';
                }
                echo '</div>';
            }
        @endphp
    </div>

    <br />
    <br />

    <div style='margin-top:10px;' align='center'>
        <button onclick="uncheck_all_cn()" style="margin-right:20px; background-color:#FFFFFF;">Uncheck All CNs</button>
        <button onclick="check_all_cn()" style="margin-right:20px; background-color:#FFFFFF;">Check All CNs</button>
        @php
            if (
                isset($phenotype_selection_arr) &&
                is_array($phenotype_selection_arr) &&
                !empty($phenotype_selection_arr)
            ) {
                echo "<button onclick=\"uncheck_all_phenotypes('" .
                    $organism .
                    "')\" style=\"margin-right:20px; background-color:#FFFFFF;\">Uncheck All Phenotypes</button>";
                echo "<button onclick=\"check_all_phenotypes('" .
                    $organism .
                    "')\" style=\"margin-right:20px; background-color:#FFFFFF;\">Check All Phenotypes</button>";
                echo "<button onclick=\"queryPhenotypeDescription('" .
                    $organism .
                    "')\" style=\"margin-right:20px; background-color:#FFFFFF;\">Download Phenotype Description</button>";
            }
        @endphp
        <button onclick="queryCNVAndPhenotype('{{ $organism }}')"
            style="margin-right:20px; background-color:#99DDFF;">View Data</button>
        <button onclick="downloadCNVAndPhenotype('{{ $organism }}')"
            style="margin-right:20px; background-color:#FFFFFF;">Download Data</button>
    </div>
    <br /><br />

    <div id="CNV_and_Phenotye_detail_table" style='width:auto; height:auto; overflow:scroll; max-height:1000px;'></div>

</body>

<script src="{{ asset('system/home/GenVarX/js/viewCNVAndPhenotype.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    updateCNInAccordion();
</script>
