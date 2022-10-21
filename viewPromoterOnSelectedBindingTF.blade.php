@php
include resource_path() . '/views/system/config.blade.php';

$organism = $info['organism'];
$motif = $info['Motif'];
$gene = $info['Gene'];
$chromosome = $info['Chromosome'];
$motif_start = $info['Motif_Start'];
$motif_end = $info['Motif_End'];
$gene_binding_sequence = $info['Gene_Binding_Sequence'];
$binding_tf_result_arr = $info['binding_tf_result_arr'];
$genotype_count_result_arr = $info['genotype_count_result_arr'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <link rel="shortcut icon" href="{{ asset('css/images/Header/kbcommons_icon.ico') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>

<body>

@php

echo "<div id=\"" . $gene . "_b\" style='width:auto; height:auto; overflow:visible; max-height:1000px;'><p><b>Selected TF: </b>" . $motif . "</p></div>";


// Binding TF table
echo "<div style='width:auto; height:auto; overflow:scroll; max-height:1000px;'>";
echo "<table style='text-align:center; border:3px solid #000;'>";

// Table header
echo "<tr>";
foreach ($binding_tf_result_arr[0] as $key => $value) {
    echo "<th style=\"border:1px solid black; min-width:80px;\">" . $key . "</th>";
}
echo "</tr>";

// Table body
for ($j = 0; $j < count($binding_tf_result_arr); $j++) {
    $tr_bgcolor = ($j % 2 ? "#FFFFFF" : "#DDFFDD");

    echo "<tr bgcolor=\"" . $tr_bgcolor . "\">";
    foreach ($binding_tf_result_arr[$j] as $key => $value) {
        echo "<td style=\"border:1px solid black; min-width:80px;\">" . $value . "</td>";
    }
    echo "</tr>";
}

echo "</table>";
echo "</div>";

echo "<br />";
echo "<br />";


// Sequence logo
if ($organism == "Osativa") {
    echo "<img src=\"" . asset('system/home/GenVarX/assets/mViz_Rice_Japonica_motif_weblogos') . "/" . $motif . ".png" . "\">";
} elseif($organism == "Athaliana") {
    echo "<img src=\"" . asset('system/home/GenVarX/assets/mViz_Arabidopsis_motif_weblogos') . "/" . $motif . ".png" . "\">";
} elseif($organism == "Zmays") {
    echo "<img src=\"" . asset('system/home/GenVarX/assets/mViz_Maize_motif_weblogos') . "/" . $motif . ".png" . "\">";
}


// Break-down table
$gene_binding_nucleotide_array = str_split($gene_binding_sequence);

echo "<div style='width:auto; height:auto; overflow:scroll; max-height:1000px;'>";
echo "<table style='text-align:center; border:3px solid #000;'>";

// Table header
echo "<tr>";
foreach ($gene_binding_nucleotide_array as $key => $value) {
    echo "<th style=\"border:1px solid black; min-width:80px; height:18.5px;\">" . $key . "</th>";
}
echo "</tr>";
echo "<tr>";
for ($i = 0; $i < count($gene_binding_nucleotide_array); $i++) {
    echo "<th style=\"border:1px solid black; min-width:80px; height:18.5px;\">" . intval(intval($motif_start)+$i) . "</th>";
}
echo "</tr>";
echo "<tr>";
foreach ($gene_binding_nucleotide_array as $key => $value) {
    echo "<td style=\"border:1px solid black; min-width:80px; height:18.5px;\">" . $value . "</td>";
}
echo "</tr>";

if (!empty($genotype_count_result_arr) && (count($genotype_count_result_arr) >0)) {
    echo "<tr>";
    for ($i = 0; $i < count($gene_binding_nucleotide_array); $i++) {
        $current_position = intval(intval($motif_start)+$i);

        echo "<td style=\"border:1px solid black; min-width:80px; height:18.5px;\">";

        $table_flag = False;

        for ($j = 0; $j < count($genotype_count_result_arr); $j++) {
            if (intval($genotype_count_result_arr[$j]->Position) == $current_position && $table_flag == False) {
                echo "<table>";
                echo "<tr>";
                echo "<th style=\"border:1px solid black; min-width:20px; height:18.5px;\">Genotype</th>";
                echo "<th style=\"border:1px solid black; min-width:20px; height:18.5px;\">Category</th>";
                echo "<th style=\"border:1px solid black; min-width:20px; height:18.5px;\">Count</th>";
                echo "</tr>";
                echo "<tr>";
                echo "<td style=\"border:1px solid black; min-width:80px; height:18.5px;\">" . $genotype_count_result_arr[$j]->Genotype . "</td>";
                echo "<td style=\"border:1px solid black; min-width:80px; height:18.5px;\">" . $genotype_count_result_arr[$j]->Category . "</td>";
                echo "<td style=\"border:1px solid black; min-width:80px; height:18.5px;\"><a target=\"_blank\" href=\"" . route('system.tools.GenVarX.viewVariantAndPhenotype', ['organism'=>$organism, 'Chromosome'=>$genotype_count_result_arr[$j]->Chromosome, 'Position'=>$genotype_count_result_arr[$j]->Position, 'Genotype'=>$genotype_count_result_arr[$j]->Genotype]) . "\">" . $genotype_count_result_arr[$j]->Count . "</a></td>";
                echo "</tr>";
                $table_flag = True;
            }
            elseif (intval($genotype_count_result_arr[$j]->Position) == $current_position && $table_flag == True) {
                echo "<tr>";
                echo "<td style=\"border:1px solid black; min-width:80px; height:18.5px;\">" . $genotype_count_result_arr[$j]->Genotype . "</td>";
                echo "<td style=\"border:1px solid black; min-width:80px; height:18.5px;\">" . $genotype_count_result_arr[$j]->Category . "</td>";
                echo "<td style=\"border:1px solid black; min-width:80px; height:18.5px;\"><a target=\"_blank\" href=\"" . route('system.tools.GenVarX.viewVariantAndPhenotype', ['organism'=>$organism, 'Chromosome'=>$genotype_count_result_arr[$j]->Chromosome, 'Position'=>$genotype_count_result_arr[$j]->Position, 'Genotype'=>$genotype_count_result_arr[$j]->Genotype]) . "\">" . $genotype_count_result_arr[$j]->Count . "</a></td>";
                echo "</tr>";
            }
        }

        if($table_flag == True) {
            echo "</table>";
            $table_flag = False;
        }

        echo "</td>";

    }
    echo "</tr>";
}

echo "</table>";
echo "</div>";

echo "<br />";
echo "<br />";
@endphp

</body>

<script type="text/javascript">
</script>
