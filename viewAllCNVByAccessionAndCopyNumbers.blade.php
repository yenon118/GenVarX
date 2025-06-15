@php
    include resource_path() . '/views/system/config.blade.php';

    $organism = $info['organism'];
    $accession = $info['accession'];
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

    @php
        if (
            isset($cnv_result_arr) &&
            is_array($cnv_result_arr) &&
            !empty($cnv_result_arr) &&
            !is_null($cnv_result_arr)
        ) {
            echo '<h3><b>CNV regions and CNs of accession ' . $accession . ':</b></h3>';

            echo "<div style=\"width:auto; height:auto; border:3px solid #000; max-height:1000px; overflow:scroll;\">";
            echo "<table style=\"text-align:center; width:100%;\">";
            // Table header
            echo '<tr>';
            foreach ($cnv_result_arr[0] as $key => $value) {
                echo "<th style=\"border:1px solid black; text-align:center; min-width:80px;\">" . $key . '</th>';
            }
            echo '</tr>';
            // Table row
            for ($i = 0; $i < count($cnv_result_arr); $i++) {
                // Table row
                echo "<tr bgcolor=\"" . ($i % 2 ? '#FFFFFF' : '#DDFDD') . "\">";
                foreach ($cnv_result_arr[$i] as $key => $value) {
                    echo "<td style=\"border:1px solid black; min-width:80px;\">" . $value . '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        } else {
            echo '<p>No CNV data found!!!</p>';
        }
    @endphp
</body>
