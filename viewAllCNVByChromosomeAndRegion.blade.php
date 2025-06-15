@php
    include resource_path() . '/views/system/config.blade.php';

    $organism = $info['organism'];
    $cnv_data_option = $info['cnv_data_option'];
    $cnv_accession_count_result_arr = $info['cnv_accession_count_result_arr'];
    $cnv_result_arr = $info['cnv_result_arr'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body>
    <!-- Back button -->
    <a href="{{ route('system.tools.GenVarX', ['organism' => $organism]) }}"><button> &lt; Back </button></a>

    <br />
    <br />

    <h3><b>Queried CNV region:</b></h3>

    @php
        if (
            isset($cnv_accession_count_result_arr) &&
            is_array($cnv_accession_count_result_arr) &&
            !empty($cnv_accession_count_result_arr) &&
            !is_null($cnv_accession_count_result_arr)
        ) {
            echo "<div style=\"width:auto; height:auto; border:3px solid #000; max-height:1000px; overflow:scroll;\">";
            echo "<table style=\"text-align:center; width:100%;\">";
            // Table header
            echo '<tr>';
            foreach ($cnv_accession_count_result_arr[0] as $key => $value) {
                echo "<th style=\"border:1px solid black; text-align:center; min-width:80px;\">" . $key . '</th>';
            }
            echo '</tr>';
            // Table row
            for ($i = 0; $i < count($cnv_accession_count_result_arr); $i++) {
                // Table row
                echo "<tr bgcolor=\"" . ($i % 2 ? '#FFFFFF' : '#DDFDD') . "\">";
                foreach ($cnv_accession_count_result_arr[$i] as $key => $value) {
                    echo "<td style=\"border:1px solid black; min-width:80px;\">" . $value . '</td>';
                }
                echo '<td>';
                echo "<a href=\"" .
                    route('system.tools.GenVarX.viewCNVAndImprovementStatus', [
                        'organism' => $organism,
                        'Chromosome' => $cnv_accession_count_result_arr[$i]->Chromosome,
                        'Position_Start' => $cnv_accession_count_result_arr[$i]->Start,
                        'Position_End' => $cnv_accession_count_result_arr[$i]->End,
                        'CNV_Data_Option' => $cnv_data_option,
                    ]) .
                    "\" target=\"_blank\">";
                echo '<button>View Details</button>';
                echo '</a>';
                echo '</td>';
                echo '<td>';
                echo "<a href=\"" .
                    route('system.tools.GenVarX.viewCNVAndPhenotype', [
                        'organism' => $organism,
                        'Chromosome' => $cnv_accession_count_result_arr[$i]->Chromosome,
                        'Position_Start' => $cnv_accession_count_result_arr[$i]->Start,
                        'Position_End' => $cnv_accession_count_result_arr[$i]->End,
                        'CNV_Data_Option' => $cnv_data_option,
                    ]) .
                    "\" target=\"_blank\">";
                echo '<button>Connect Phenotypes</button>';
                echo '</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        } else {
            echo '<p>No CNV accession count data found!!!</p>';
        }
    @endphp

    <br />
    <br />

    <h3><b>Accessions and CNs within the queried CNV region:</b></h3>

    @php
        if (
            isset($cnv_result_arr) &&
            is_array($cnv_result_arr) &&
            !empty($cnv_result_arr) &&
            !is_null($cnv_result_arr)
        ) {
            for ($i = 0; $i < count($cnv_result_arr); $i++) {
                echo "<div style=\"width:auto; height:auto; border:3px solid #000; max-height:1000px; overflow:scroll;\">";
                echo "<table style=\"text-align:center; width:100%;\">";
                // Table header
                echo '<tr>';
                foreach ($cnv_result_arr[$i][0] as $key => $value) {
                    echo "<th style=\"border:1px solid black; text-align:center; min-width:80px;\">" . $key . '</th>';
                }
                echo '</tr>';
                // Table row
                for ($j = 0; $j < count($cnv_result_arr[$i]); $j++) {
                    // Table row
                    echo "<tr bgcolor=\"" . ($j % 2 ? '#FFFFFF' : '#DDFDD') . "\">";
                    foreach ($cnv_result_arr[$i][$j] as $key => $value) {
                        echo "<td style=\"border:1px solid black; min-width:80px;\">" . $value . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
                echo '<br /><br />';
            }
        } else {
            echo '<p>No CNV data found!!!</p>';
        }
    @endphp

</body>


<script type="text/javascript"></script>
