@php

include resource_path() . '/views/system/config.blade.php';
$organism = $info['organism'];
$upstream_length = $info['upstream_length'];
$binding_tf_arr = $info['binding_tf_arr'];
$result_arr = $info['result_arr'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <link rel="shortcut icon" href="{{ asset('css/images/Header/kbcommons_icon.ico') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>

<body>
    <!-- Back button -->
    <a href="{{ route('system.tools.GenVarX', ['organism'=>$organism]) }}"><button> &lt; Back </button></a>


    <br />
    <br />

    @php
    $result_arr_keys = array_keys($result_arr);
    if (isset($result_arr) && !empty($result_arr)){
        for ($i = 0; $i < count($result_arr_keys); $i++) {
            $motif_result_arr = $result_arr[$result_arr_keys[$i]];

            echo "<p><b>Queried Binding TF: </b>" . $binding_tf_arr[$i] . "</p>";
            if (!empty($motif_result_arr) && (count($motif_result_arr) > 0)){
                echo "<div style='width:auto; height:auto; overflow:scroll; max-height:1000px;'>";
                echo "<table style='text-align:center; border:3px solid #000;'>";
                
                // Table header
                echo "<tr>";
                foreach ($motif_result_arr[0] as $key => $value) {
                    echo "<th style=\"border:1px solid black; min-width:80px;\">" . $key . "</th>";
                }
                echo "</tr>";

                // Table body
                for ($j = 0; $j < count($motif_result_arr); $j++) {
                    $tr_bgcolor = ($j % 2 ? "#FFFFFF" : "#DDFFDD");

                    echo "<tr bgcolor=\"" . $tr_bgcolor . "\">";
                    foreach ($motif_result_arr[$j] as $key => $value) {
                        if ($key == "Binding_TF") {
                            echo "<td style=\"border:1px solid black; min-width:80px;\"><a target=\"_blank\" href=\"" . route('system.tools.GenVarX.viewPromoterOnSelectedBindingTF', ['organism'=>$organism, 'Motif'=>$value, 'Gene'=>$motif_result_arr[$j]->Gene, 'Chromosome'=>$motif_result_arr[$j]->Binding_Chromosome, 'Motif_Start'=>$motif_result_arr[$j]->Binding_Start, 'Motif_End'=>$motif_result_arr[$j]->Binding_End, 'Gene_Binding_Sequence'=>$motif_result_arr[$j]->Gene_Binding_Sequence, 'Upstream_Length'=>$upstream_length]) . "\">" . $value . "</a></td>";
                        } else {
                            echo "<td style=\"border:1px solid black; min-width:80px;\">" . $value . "</td>";
                        }
                    }
                    echo "</tr>";
                }

                echo "</table>";
                echo "</div>";

                echo "<br />";
                echo "<br />";
            } else {
                // Display no motif message if none is found
                echo "<div style='width:auto; height:auto; overflow:visible; max-height:1000px;'>";
                echo "No binding TF found in our database!!!";
                echo "</div>";

                echo "<br />";
                echo "<br />";
            }

        }
    }
    @endphp

</body>

<script type="text/javascript">
</script>
